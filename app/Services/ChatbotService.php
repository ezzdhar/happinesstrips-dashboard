<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon; // إضافة Carbon للتواريخ
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
			if (empty($chat_session)) {
				$chat_session = 'session-' . time() . '-' . Str::random(8);
			}

			$historyText = $this->formatHistoryForPrompt($conversationHistory);
			$staticDataContext = $this->getStaticDataContext();

			$systemPrompt = view('prompts.chatbot-system-v2')->render() . "\n\n" . $staticDataContext;

			// إضافة التاريخ الحالي للبرومبت ليتمكن الـ AI من حساب "غداً" بدقة
			$today = Carbon::now()->format('Y-m-d');
			$enhancedPrompt = $historyText . "\nتاريخ اليوم: $today\nالمستخدم: " . $userMessage;

			// Pass 1: Planning
			$response = Prism::text()->using(Provider::Gemini, 'gemini-2.0-flash')->withSystemPrompt($systemPrompt)->withPrompt($enhancedPrompt)
				->withMaxTokens(1000)->usingTemperature(0.5)->asText();

			$aiResponse = $response->text;
			$structuredResponse = $this->parseStructuredResponse($aiResponse);

			$data = null;
			$dataType = null;
			$finalMessage = $structuredResponse['response_message'] ?? $aiResponse;
			$apiSuccess = true;

			if (!empty($structuredResponse['api_calls'])) {
				// تنفيذ الـ APIs
				$executionResult = $this->executeApiCalls($structuredResponse['api_calls']);

				$apiResults = $executionResult['results'];
				$apiSuccess = $executionResult['success'];

				$extracted = $this->extractDataFromApiResults($apiResults);
				$data = $extracted['data'];
				$dataType = $extracted['data_type'];

				// Recovery Mode (إذا فشلت العملية أو عادت البيانات فارغة رغم النجاح)
				if (!$apiSuccess || empty($data)) {
					$errorContext = json_encode($apiResults, JSON_UNESCAPED_UNICODE);

					// نطلب من الـ AI تحليل سبب الفشل (مثل نقص التواريخ أو عدم توفر غرف)
					$recoveryResponse = Prism::text()
						->using(Provider::Gemini, 'gemini-2.0-flash')
						->withSystemPrompt("أنت مساعد ذكي. فشل البحث أو لم يتم العثور على بيانات. حلل رد الـ API واشرح السبب للمستخدم.\nتلميح: إذا كان الخطأ يتعلق بـ start_date أو params، اطلب من المستخدم تحديدها.\nسياق النتائج: $errorContext")
						->withPrompt("سؤال المستخدم: $userMessage\nالرد السابق: $finalMessage\n\nصغ رداً جديداً يوضح المشكلة ويقترح الحل:")
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

			$this->storeConversation($chat_session, $userMessage, $result, $structuredResponse);

			return $result;

		} catch (Exception $e) {
			Log::error('Chatbot error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return $this->getErrorResponse($chat_session);
		}
	}

	protected function executeApiCalls(array $apiCalls): array
	{
		$results = [];
		$collectedData = [];
		$allSuccess = true;
		$baseUrl = rtrim(config('app.url'), '/');

		foreach ($apiCalls as $index => $call) {
			try {
				$endpoint = $call['endpoint'] ?? '';
				$params = $call['params'] ?? [];

				// حل الباراميترز (Chaining + Dynamic Dates)
				$params = $this->resolveApiParameters($params, $collectedData);

				if ($this->hasMissingDependencies($params)) {
					$results[$index] = ['success' => false, 'error' => 'Missing dependency from previous call'];
					$allSuccess = false;
					break;
				}

				$response = Http::timeout(8)->get($baseUrl . $endpoint, $params);

				if ($response->successful()) {
					$responseData = $response->json();
					$results[$index] = [
						'success' => true,
						'endpoint' => $endpoint,
						'data' => $responseData,
					];

					if (isset($responseData['data'])) {
						$firstItem = is_array($responseData['data']) && !empty($responseData['data'])
							? (array_key_exists(0, $responseData['data']) ? $responseData['data'][0] : $responseData['data'])
							: $responseData['data'];

						$collectedData = array_merge($collectedData, is_array($firstItem) ? $firstItem : []);
					}
				} else {
					// هنا نسجل جسم الخطأ كاملاً ليقرأه الـ AI في الـ Recovery Mode
					$results[$index] = [
						'success' => false,
						'endpoint' => $endpoint,
						'status' => $response->status(),
						'error' => $response->json() ?? $response->body() // محاولة قراءة JSON Error message
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
	 * دالة ذكية لحل الباراميترز والتواريخ الافتراضية
	 */
	protected function resolveApiParameters(array $params, array $collectedData): array
	{
		foreach ($params as $key => $value) {
			if (!is_string($value)) continue;

			// 1. استبدال التواريخ الافتراضية
			if ($value === 'TOMORROW_DATE') {
				$params[$key] = Carbon::tomorrow()->format('Y-m-d');
				continue;
			}
			if ($value === 'AFTER_TOMORROW_DATE') {
				$params[$key] = Carbon::tomorrow()->addDay()->format('Y-m-d');
				continue;
			}

			// 2. استبدال الـ Placeholders (Chaining)
			if (str_contains($value, '_FROM_') || str_contains($value, 'HOTEL_ID') || str_contains($value, 'TRIP_ID')) {
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

	// ... (باقي الدوال كما هي دون تغيير) ...

	protected function hasMissingDependencies(array $params): bool
	{
		foreach ($params as $value) {
			if (is_string($value) && (str_contains($value, '_FROM_API'))) {
				return true;
			}
		}
		return false;
	}

	protected function extractDataFromApiResults(array $apiResults): array
	{
		$reversedResults = array_reverse($apiResults);
		foreach ($reversedResults as $result) {
			if (!$result['success'] || empty($result['data']['data'])) continue;

			$endpoint = $result['endpoint'];
			$data = $result['data']['data'];

			if (str_contains($endpoint, '/hotels/rooms')) return ['data' => $data, 'data_type' => 'rooms'];
			if (str_contains($endpoint, '/hotels')) return ['data' => $data, 'data_type' => 'hotels'];
			if (str_contains($endpoint, '/trips')) return ['data' => $data, 'data_type' => 'trips'];
			if (str_contains($endpoint, '/cities')) return ['data' => $data, 'data_type' => 'cities'];
			if (str_contains($endpoint, 'calculate')) return ['data' => $result['data'], 'data_type' => 'price_calculation'];
		}
		return ['data' => null, 'data_type' => null];
	}

	protected function getStaticDataContext(): string
	{
		return Cache::remember('chatbot_static_context_v3', 3600, function () {
			$baseUrl = rtrim(config('app.url'), '/');
			$context = "\n\n## 📊 البيانات المتاحة (استخدم هذه الـ IDs):\n\n";
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/cities', 'المدن المتاحة');
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/categories', 'فئات الرحلات');
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/sub-categories', 'الفئات الفرعية');
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/hotel-types', 'أنواع الفنادق');
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/hotels', 'الفنادق');
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/trips', 'الرحلات');
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

	public function submitFeedback($session, $helpful, $feedback) {
		return ChatbotConversation::where('chat_session', $session)->latest()->first()?->update([
			'was_helpful' => $helpful, 'feedback' => $feedback
		]);
	}

	public function getConversationHistory(string $chat_session) {
		return ChatbotConversation::where('chat_session', $chat_session)->latest()->limit(20)->get();
	}
}