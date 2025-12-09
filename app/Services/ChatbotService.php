<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class ChatbotService
{
	/**
	 * المعالجة الرئيسية للرسالة (Agent Loop Logic)
	 */
	public function processMessage(string $userMessage, array $conversationHistory, ?string $chat_session = null): array
	{
		try {
			// 1. إدارة الجلسة
			if (empty($chat_session)) {
				$chat_session = 'session-' . time() . '-' . Str::random(8);
			}

			// 2. تجهيز السياق
			$historyText = $this->formatHistoryForPrompt($conversationHistory);
			$staticDataContext = $this->getStaticDataContext();

			// 3. بناء البرومبت الأولي
			$systemPrompt = view('prompts.chatbot-system-v3')->render() . "\n\n" . $staticDataContext;
			$enhancedPrompt = $historyText . "\nالمستخدم: " . $userMessage;

			// 4. استدعاء الذكاء الاصطناعي (Pass 1 - Planning)
			$response = Prism::text()
				->using(Provider::Gemini, 'gemini-2.0-flash')
				->withSystemPrompt($systemPrompt)
				->withPrompt($enhancedPrompt)
				->withMaxTokens(1000)
				->usingTemperature(0.5) // حرارة منخفضة لضمان الدقة في JSON
				->asText();

			$aiResponse = $response->text;
			$structuredResponse = $this->parseStructuredResponse($aiResponse);

			// 5. تنفيذ الـ APIs (Agent Execution)
			$data = null;
			$dataType = null;
			$finalMessage = $structuredResponse['response_message'] ?? $aiResponse;
			$apiSuccess = true;

			if (!empty($structuredResponse['api_calls'])) {
				// تنفيذ سلسلة الاستدعاءات
				$executionResult = $this->executeApiCalls($structuredResponse['api_calls']);

				$apiResults = $executionResult['results'];
				$apiSuccess = $executionResult['success'];

				// استخراج البيانات للفرونت إند
				$extracted = $this->extractDataFromApiResults($apiResults);
				$data = $extracted['data'];
				$dataType = $extracted['data_type'];

				// 6. التحقق من النتائج (Agent Observation) - سيناريو الخطأ
				// إذا فشلت العمليات أو عادت ببيانات فارغة، نطلب من الـ AI صياغة رد جديد بناءً على الخطأ
				if (!$apiSuccess || empty($data)) {
					$errorContext = json_encode($apiResults, JSON_UNESCAPED_UNICODE);

					// استدعاء ثاني للذكاء الاصطناعي لشرح المشكلة (Recovery Mode)
					$recoveryResponse = Prism::text()
						->using(Provider::Gemini, 'gemini-2.0-flash')
						->withSystemPrompt("أنت مساعد ذكي. حاولت تنفيذ طلب المستخدم لكن حدث خطأ أو لم توجد بيانات. اشرح المشكلة للمستخدم بلطف واقترح بدائل.\nسياق الخطأ من الـ API: $errorContext")
						->withPrompt("المستخدم سأل: $userMessage\nالرد السابق المقترح: $finalMessage\n\nقم بصياغة رد نهائي يوضح المشكلة:")
						->asText();

					$finalMessage = $recoveryResponse->text;
				}
			}

			$result = [
				'success' => true,
				'chat_session' => $chat_session,
				'message' => $finalMessage,
				'data' => $data,
				'data_type' => $dataType,
				'suggestions' => $structuredResponse['suggested_actions'] ?? [],
			];

			// 7. تخزين المحادثة
			$this->storeConversation($chat_session, $userMessage, $result, $structuredResponse);

			return $result;

		} catch (Exception $e) {
			Log::error('Chatbot error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return $this->getErrorResponse($chat_session);
		}
	}

	/**
	 * تنفيذ استدعاءات API مع دعم الـ Chaining
	 */
	protected function executeApiCalls(array $apiCalls): array
	{
		$results = [];
		$collectedData = []; // لتخزين نتائج الطلبات السابقة
		$allSuccess = true;
		$baseUrl = rtrim(config('app.url'), '/');

		foreach ($apiCalls as $index => $call) {
			try {
				$endpoint = $call['endpoint'] ?? '';
				$params = $call['params'] ?? [];

				// 1. حل الباراميترز (Chaining Logic)
				// نستبدل PLACEHOLDERS بالقيم الحقيقية من الطلبات السابقة
				$params = $this->resolveApiParameters($params, $collectedData);

				// التحقق من وجود ID مطلوب ولكنه مفقود (بسبب فشل طلب سابق)
				if ($this->hasMissingDependencies($params)) {
					$results[$index] = ['success' => false, 'error' => 'Missing dependency from previous call'];
					$allSuccess = false;
					break; // توقف السلسلة
				}

				// 2. تنفيذ الطلب
				$response = Http::timeout(8)->get($baseUrl . $endpoint, $params);

				if ($response->successful()) {
					$responseData = $response->json();
					$results[$index] = [
						'success' => true,
						'endpoint' => $endpoint,
						'data' => $responseData,
					];

					// 3. تجميع البيانات للاستخدام في الخطوة التالية
					// نخزن أول عنصر في الـ data أو الـ data نفسها
					if (isset($responseData['data'])) {
						// إذا كانت قائمة، نأخذ العنصر الأول لاستخراج الـ IDs (تخمين ذكي)
						$firstItem = is_array($responseData['data']) && !empty($responseData['data'])
							? (array_key_exists(0, $responseData['data']) ? $responseData['data'][0] : $responseData['data'])
							: $responseData['data'];

						$collectedData = array_merge($collectedData, is_array($firstItem) ? $firstItem : []);
					}
				} else {
					$results[$index] = [
						'success' => false,
						'endpoint' => $endpoint,
						'status' => $response->status(),
						'error' => $response->body()
					];
					$allSuccess = false;
				}
			} catch (Exception $e) {
				$results[$index] = ['success' => false, 'error' => $e->getMessage()];
				$allSuccess = false;
			}
		}

		return ['results' => $results, 'success' => $allSuccess];
	}

	/**
	 * حل الباراميترز واستبدال الـ Placeholders
	 */
	protected function resolveApiParameters(array $params, array $collectedData): array
	{
		foreach ($params as $key => $value) {
			if (!is_string($value)) continue;

			// البحث عن نمط PLACEHOLDER مثل HOTEL_ID_FROM_FIRST_API
			if (str_contains($value, '_FROM_') || str_contains($value, 'HOTEL_ID') || str_contains($value, 'TRIP_ID')) {
				// محاولة ذكية لإيجاد القيمة في البيانات المجمعة
				if ($key === 'hotel_id' && isset($collectedData['id'])) {
					$params[$key] = $collectedData['id'];
				} elseif ($key === 'city_id' && isset($collectedData['city_id'])) {
					$params[$key] = $collectedData['city_id'];
				} elseif (isset($collectedData[$key])) {
					$params[$key] = $collectedData[$key];
				}
			}
		}
		return $params;
	}

	/**
	 * التحقق مما إذا كان هناك باراميتر معتمد مفقود
	 */
	protected function hasMissingDependencies(array $params): bool
	{
		foreach ($params as $value) {
			if (is_string($value) && (str_contains($value, '_FROM_API'))) {
				return true; // ما زال الـ Placeholder موجوداً ولم يتم استبداله
			}
		}
		return false;
	}

	/**
	 * استخراج البيانات للفرونت إند
	 */
	protected function extractDataFromApiResults(array $apiResults): array
	{
		// نبحث عن آخر نتيجة ناجحة تحتوي على بيانات ذات معنى
		// نبدأ من الأخير للأول لأن النتيجة النهائية عادة تكون في آخر API call
		$reversedResults = array_reverse($apiResults);

		foreach ($reversedResults as $result) {
			if (!$result['success'] || empty($result['data']['data'])) continue;

			$endpoint = $result['endpoint'];
			$data = $result['data']['data'];

			if (str_contains($endpoint, '/hotels/rooms')) return ['data' => $data, 'data_type' => 'rooms'];
			if (str_contains($endpoint, '/hotels')) return ['data' => $data, 'data_type' => 'hotels'];
			if (str_contains($endpoint, '/trips')) return ['data' => $data, 'data_type' => 'trips'];
			if (str_contains($endpoint, '/cities')) return ['data' => $data, 'data_type' => 'cities'];
			if (str_contains($endpoint, 'calculate')) return ['data' => $result['data'], 'data_type' => 'price_calculation']; // للأسعار الهيكل مختلف قليلاً
		}

		return ['data' => null, 'data_type' => null];
	}

	/**
	 * جلب البيانات الثابتة (Cached) لتضمينها في البرومبت
	 */
	protected function getStaticDataContext(): string
	{
		return Cache::remember('chatbot_static_context_v3', 3600, function () {
			$baseUrl = rtrim(config('app.url'), '/');
			$context = "\n\n## 📊 البيانات المتاحة (استخدم هذه الـ IDs):\n\n";

			// جلب المدن
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/cities', 'المدن المتاحة');
			// جلب الفئات
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/categories', 'فئات الرحلات');
			// جلب الفئات الفرعية
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/sub-categories', 'الفئات الفرعية');
			// جلب أنواع الفنادق
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/hotel-types', 'أنواع الفنادق');

			return $context;
		});
	}

	private function fetchAndFormatList(string $url, string $title): string
	{
		try {
			$response = Http::timeout(3)->get($url, ['per_page' => 100]);
			if ($response->successful()) {
				$items = $response->json('data', []);
				$text = "### {$title}:\n";
				foreach ($items as $item) {
					$text .= "- {$item['name']}: ID = {$item['id']}\n";
				}
				return $text . "\n";
			}
		} catch (Exception $e) {
			Log::warning("Failed to fetch {$title}");
		}
		return "";
	}

	// --- Helper Methods ---

	protected function formatHistoryForPrompt(array $history): string
	{
		if (empty($history)) return '';
		$text = "\n\n## سياق المحادثة الحالية:\n";
		foreach ($history as $msg) {
			$text .= "User: {$msg['user_message']}\nBot: " . Str::limit($msg['bot_response'], 100) . "\n";
		}
		return $text;
	}

	public function getConversationHistoryForContext(string $chat_session): array
	{
		return ChatbotConversation::where('chat_session', $chat_session)
			->latest()
			->take(3)
			->get(['user_message', 'bot_response'])
			->reverse()
			->toArray();
	}

	protected function parseStructuredResponse(string $response): array
	{
		if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
			return json_decode($matches[1], true) ?? [];
		}
		$decoded = json_decode($response, true);
		return is_array($decoded) ? $decoded : ['response_message' => $response, 'api_calls' => []];
	}

	protected function storeConversation($session, $msg, $result, $structured)
	{
		ChatbotConversation::create([
			'chat_session' => $session,
			'user_message' => $msg,
			'bot_response' => $result['message'],
			'api_calls' => $structured['api_calls'] ?? null,
			'intent' => $structured['intent'] ?? 'unknown',
		]);
	}

	protected function getErrorResponse($session): array
	{
		return [
			'success' => false,
			'chat_session' => $session,
			'message' => 'عذراً، حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
			'data' => null
		];
	}

	// Public API methods required by Controller
	public function submitFeedback($session, $helpful, $feedback) {
		return ChatbotConversation::where('chat_session', $session)->latest()->first()?->update([
			'was_helpful' => $helpful, 'feedback' => $feedback
		]);
	}

	public function getConversationHistory(string $chat_session) {
		return ChatbotConversation::where('chat_session', $chat_session)->latest()->limit(20)->get();
	}
}