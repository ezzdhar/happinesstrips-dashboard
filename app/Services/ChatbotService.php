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
	 * المعالجة الرئيسية للرسالة
	 */
	public function processMessage(string $userMessage, array $conversationHistory, ?string $chat_session = null): array
	{
		try {
			// 1. إدارة الجلسة
			if (empty($chat_session)) {
				$chat_session = 'session-' . time() . '-' . Str::random(8);
			}

			// 2. تجهيز السياق (الذاكرة + البيانات الثابتة)
			$historyText = $this->formatHistoryForPrompt($conversationHistory);
			$learningContext = $this->getLearningContext($userMessage);
			$staticDataContext = $this->getStaticDataContext(); // الآن تستخدم الكاش

			// 3. بناء البرومبت
			$enhancedPrompt = $this->buildEnhancedPrompt($userMessage, $learningContext, $historyText);

			// 4. استدعاء الذكاء الاصطناعي
			$response = Prism::text()
				->using(Provider::Gemini, 'gemini-2.0-flash')
				->withSystemPrompt(view('prompts.chatbot-system-v2')->render() . "\n\n" . $staticDataContext)
				->withPrompt($enhancedPrompt)
				->withMaxTokens(1000) // تقليل التوكنز لأننا لا نحتاج نصوص طويلة
				->usingTemperature(0.6)
				->asText();

			$aiResponse = $response->text;
			$structuredResponse = $this->parseStructuredResponse($aiResponse);

			// 5. تنفيذ الـ APIs
			$data = null;
			$dataType = null;

			if (!empty($structuredResponse['api_calls'])) {
				// تنفيذ الاستدعاءات
				$apiResults = $this->executeApiCalls($structuredResponse['api_calls']);

				// استخراج البيانات النظيفة للفرونت إند
				$extracted = $this->extractDataFromApiResults($apiResults);
				$data = $extracted['data'];
				$dataType = $extracted['data_type'];

				// ملاحظة: لم نعد نعدل رسالة البوت نصياً، سنترك التطبيق يعرض البيانات
			}

			$result = [
				'success' => true,
				'chat_session' => $chat_session,
				'message' => $structuredResponse['response_message'] ?? $aiResponse,
				'data' => $data,
				'data_type' => $dataType,
				'suggestions' => $structuredResponse['suggested_actions'] ?? [],
			];

			// 6. تخزين المحادثة
			$this->storeConversation($chat_session, $userMessage, $result, $structuredResponse);

			return $result;

		} catch (Exception $e) {
			Log::error('Chatbot error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return $this->getErrorResponse($chat_session);
		}
	}

	/**
	 * جلب البيانات الثابتة (Cached)
	 * تحسين: تم إزالة جلب الفنادق بالكامل لتوفير التوكنز
	 */
	protected function getStaticDataContext(): string
	{
		return Cache::remember('chatbot_static_context_v1', 3600, function () {
			$baseUrl = rtrim(config('app.url'), '/');
			$context = "\n\n## 📊 البيانات المتاحة (IDs للبحث):\n\n";

			// جلب المدن
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/cities', 'المدن المتاحة');

			// جلب أنواع الفنادق
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/hotel-types', 'أنواع الفنادق');

			// جلب الفئات
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/categories', 'فئات الرحلات');

			// جلب الفئات الفرعية
			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/sub-categories', 'الفئات الفرعية');

			$context .= "\n⚠️ **ملاحظة:** لا توجد قائمة فنادق هنا. إذا بحث المستخدم عن فندق بالاسم، استخدم API البحث `/api/v1/hotels?name=...` أولاً للحصول على الـ ID.\n";

			return $context;
		});
	}

	/**
	 * دالة مساعدة لجلب القوائم وتنسيقها للكاش
	 */
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
			Log::warning("Failed to fetch {$title}: " . $e->getMessage());
		}
		return "";
	}

	/**
	 * تنفيذ استدعاءات API
	 */
	protected function executeApiCalls(array $apiCalls): array
	{
		$results = [];
		$collectedData = [];

		foreach ($apiCalls as $index => $call) {
			try {
				$endpoint = $call['endpoint'] ?? '';
				$params = $call['params'] ?? [];

				// حل الباراميترز (مثل استبدال اسم مدينة بـ ID)
				$params = $this->resolveApiParameters($params, $collectedData);

				$baseUrl = rtrim(config('app.url'), '/');
				$response = Http::timeout(8)->get($baseUrl . $endpoint, $params);

				if ($response->successful()) {
					$responseData = $response->json();
					$results[$index] = [
						'success' => true,
						'endpoint' => $endpoint,
						'data' => $responseData,
					];

					// تجميع البيانات لاستخدامها في الاستدعاءات التالية (Chaining)
					if (isset($responseData['data']) && is_array($responseData['data'])) {
						$collectedData = array_merge($collectedData, $responseData['data']);
					}
				} else {
					$results[$index] = ['success' => false, 'error' => 'API Error: ' . $response->status()];
				}
			} catch (Exception $e) {
				$results[$index] = ['success' => false, 'error' => $e->getMessage()];
			}
		}
		return $results;
	}

	/**
	 * استخراج البيانات للفرونت إند (بدون تنسيق نصي)
	 */
	protected function extractDataFromApiResults(array $apiResults): array
	{
		foreach ($apiResults as $result) {
			if (!$result['success'] || empty($result['data']['data'])) continue;

			$endpoint = $result['endpoint'];
			$data = $result['data']['data']; // البيانات الخام

			// تحديد النوع ليتمكن الفرونت إند من اختيار شكل الكارد المناسب
			if (str_contains($endpoint, '/hotels/rooms')) return ['data' => $data, 'data_type' => 'rooms'];
			if (str_contains($endpoint, '/hotels')) return ['data' => $data, 'data_type' => 'hotels'];
			if (str_contains($endpoint, '/trips')) return ['data' => $data, 'data_type' => 'trips'];
			if (str_contains($endpoint, '/cities')) return ['data' => $data, 'data_type' => 'cities'];
		}

		return ['data' => null, 'data_type' => null];
	}

	/**
	 * تحويل تاريخ المحادثة لنص للبرومبت
	 */
	public function formatHistoryForPrompt(array $history): string
	{
		if (empty($history)) return '';

		$text = "\n\n## سياق المحادثة الحالية (للتذكر):\n";
		foreach ($history as $msg) {
			// نأخذ آخر 3 رسائل فقط لتوفير التوكنز
			$text .= "User: {$msg['user_message']}\nBot: " . Str::limit($msg['bot_response'], 100) . "\n";
		}
		return $text;
	}

	/**
	 * جلب سجل المحادثة كـ Array
	 */
	public function getConversationHistoryForContext(string $chat_session): array
	{
		return ChatbotConversation::where('chat_session', $chat_session)
			->latest()
			->take(3) // آخر 3 رسائل فقط
			->get(['user_message', 'bot_response'])
			->reverse()
			->toArray();
	}

	// --- Helper Methods (بقيت كما هي مع تحسينات طفيفة) ---

	protected function resolveApiParameters(array $params, array $collectedData): array
	{
		// تحويل الـ placeholders إلى القيم الفعلية من نتائج API السابقة
		foreach ($params as $key => $value) {
			if (!is_string($value)) {
				continue;
			}

			// البحث عن placeholders مثل HOTEL_ID_FROM_FIRST_API
			if (str_contains(strtoupper($value), '_FROM_FIRST_API') ||
			    str_contains(strtoupper($value), '_FROM_PREVIOUS_API') ||
			    str_contains(strtoupper($value), 'HOTEL_ID') && !is_numeric($value)) {

				// محاولة استخراج ID من البيانات المجمعة
				if (!empty($collectedData)) {
					// إذا كانت البيانات المجمعة array of items
					if (isset($collectedData[0]) && is_array($collectedData[0])) {
						// أخذ أول عنصر (غالباً النتيجة الأكثر صلة)
						if (isset($collectedData[0]['id'])) {
							$params[$key] = (string) $collectedData[0]['id'];
						}
					}
					// إذا كان العنصر الأول مباشرة
					elseif (isset($collectedData['id'])) {
						$params[$key] = (string) $collectedData['id'];
					}
				}
			}
		}

		return $params;
	}

	protected function getLearningContext(string $userMessage): string
	{
		// جلب أمثلة من المحادثات الناجحة المشابهة
		try {
			$similarConversations = ChatbotConversation::where('was_helpful', true)
				->where('user_message', 'LIKE', '%' . substr($userMessage, 0, 20) . '%')
				->latest()
				->limit(2)
				->get(['user_message', 'bot_response', 'api_calls']);

			if ($similarConversations->isEmpty()) {
				return '';
			}

			$context = "\n\n## أمثلة من محادثات سابقة ناجحة:\n";
			foreach ($similarConversations as $conv) {
				$context .= "User: {$conv->user_message}\n";
				$context .= "Bot Response: " . Str::limit($conv->bot_response, 80) . "\n";
				if ($conv->api_calls) {
					$context .= "API Calls Used: " . json_encode($conv->api_calls) . "\n";
				}
				$context .= "---\n";
			}

			return $context;
		} catch (Exception $e) {
			Log::warning('Failed to get learning context: ' . $e->getMessage());
			return '';
		}
	}

	protected function parseStructuredResponse(string $response): array
	{
		// محاولة استخراج JSON نظيف
		if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
			return json_decode($matches[1], true) ?? [];
		}
		$decoded = json_decode($response, true);
		return is_array($decoded) ? $decoded : ['response_message' => $response, 'api_calls' => []];
	}

	protected function buildEnhancedPrompt(string $msg, string $learning, string $history): string
	{
		return $history . $learning . "\nالمستخدم: " . $msg;
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
			'message' => 'عذراً، واجهت مشكلة تقنية بسيطة. هل يمكنك إعادة المحاولة؟',
			'data' => null
		];
	}

	// دوال الـ Public API للـ Controller
	public function getConversationHistory(string $chat_session) {
		return ChatbotConversation::where('chat_session', $chat_session)->latest()->limit(20)->get();
	}

	public function submitFeedback($session, $helpful, $feedback) {
		return ChatbotConversation::where('chat_session', $session)->latest()->first()?->update([
			'was_helpful' => $helpful, 'feedback' => $feedback
		]);
	}
}