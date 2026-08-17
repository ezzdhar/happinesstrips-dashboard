<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use App\Models\Hotel;
use App\Models\Trip;
use App\Models\City;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
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

			// اكتشاف لغة المستخدم: إذا كتب بالعربية نستخدم العربية وإلا نستخدم الإنجليزية
			$lang = $this->detectLanguage($userMessage);
			$langInstruction = $lang === 'en' ? "Please reply in English.\n" : "الرد باللغة العربية فقط.\n";

			// إضافة التاريخ الحالي للبرومبت ليتمكن الـ AI من حساب "غداً" بدقة
			$today = Carbon::now()->format('Y-m-d');
			$enhancedPrompt = $langInstruction . $historyText . "\nتاريخ اليوم: $today\nالمستخدم: " . $userMessage;

			$prism_provider = config('prism.prism_provider');
			$prism_provider_model = config('prism.prism_provider_model');
			// Pass 1: Planning
			$response = Prism::text()
				->using($prism_provider, $prism_provider_model)
				->withSystemPrompt($systemPrompt)
				->withPrompt($enhancedPrompt)
				->withMaxTokens(1000)
				->usingTemperature(0.5)
				->asText();

			$aiResponse = $response->text;
			$structuredResponse = $this->parseStructuredResponse($aiResponse);

			$data = null;
			$dataType = null;
			$finalMessage = $structuredResponse['response_message'] ?? $aiResponse;
			$finalMessage = $structuredResponse['response_message'] ?? $aiResponse;
			$dbSuccess = true;

			if (!empty($structuredResponse['db_actions'])) {
				// تنفيذ العمليات على الداتابيز مباشرة
				$executionResult = $this->executeDbActions($structuredResponse['db_actions']);

				$dbResults = $executionResult['results'];
				$dbSuccess = $executionResult['success'];

				// استخراج البيانات لعرضها
				$data = $this->extractDataFromDbResults($dbResults);
				$dataType = $data['type'] ?? null;
				$data = $data['data'] ?? null;

				// Recovery Mode (إذا فشلت العملية أو عادت البيانات فارغة رغم النجاح)
				if (!$dbSuccess || empty($data)) {
					$errorContext = json_encode($dbResults, JSON_UNESCAPED_UNICODE);

					// نطلب من الـ AI تحليل سبب الفشل
					$recoveryResponse = Prism::text()
						->using(Provider::Groq, config('prism.prism_provider_model'))
						->withSystemPrompt("أنت مساعد ذكي. فشل البحث في قاعدة البيانات أو لم يتم العثور على نتائج. حلل السبب واشرح للمستخدم.\nتلميح: ربما التواريخ غير متاحة أو الفلاتر ضيقة جداً.\nسياق النتائج: $errorContext")
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

	protected function executeDbActions(array $actions): array
	{
		$results = [];
		$collectedData = [];
		$allSuccess = true;

		foreach ($actions as $index => $action) {
			try {
				$actionName = $action['action'] ?? '';
				$params = $action['params'] ?? [];

				// حل الباراميترز (Dynamic Dates & Chaining)
				$params = $this->resolveDbParameters($params, $collectedData);

				// تنفيذ الاستعلام
				$queryResult = $this->processDbQuery($actionName, $params);

				if ($queryResult['success']) {
					$results[$index] = [
						'success' => true,
						'action'  => $actionName,
						'data'    => $queryResult['data'],
					];

					// جمع البيانات للخطوات التالية (Chaining)
					if (!empty($queryResult['data'])) {
						$firstItem = is_array($queryResult['data']) && array_key_exists(0, $queryResult['data'])
							? $queryResult['data'][0]
							: $queryResult['data'];

						if (is_array($firstItem)) {
							$collectedData = array_merge($collectedData, $firstItem);
						} elseif (is_object($firstItem)) {
							$collectedData = array_merge($collectedData, $firstItem->toArray());
						}
					}
				} else {
					$results[$index] = [
						'success' => false,
						'action'  => $actionName,
						'error'   => $queryResult['error'] ?? 'Unknown DB Error'
					];
					$allSuccess = false;
				}
			} catch (Exception $e) {
				Log::error("DB Action Failed: " . $e->getMessage());
				$results[$index] = ['success' => false, 'error' => $e->getMessage()];
				$allSuccess = false;
			}
		}

		return ['results' => $results, 'success' => $allSuccess];
	}

	protected function processDbQuery(string $action, array $params): array
	{
		try {
			$data = [];

			switch ($action) {
				case 'search_hotels':
					$query = Hotel::query()->where('status', 'active');

					if (!empty($params['city_id'])) $query->where('city_id', $params['city_id']);
					if (!empty($params['name'])) $query->scopeFilter($params['name']);
					if (!empty($params['rating'])) $query->where('rating', $params['rating']);
					if (!empty($params['hotel_type_id'])) $query->scopeHotelTypeFilter($params['hotel_type_id']);

					$data = $query->with(['city', 'hotelTypes'])->limit(10)->get();
					break;

				case 'get_hotel_details':
					if (empty($params['id'])) throw new Exception("Hotel ID required");
					$data = Hotel::with(['city', 'hotelTypes', 'rooms' => function ($q) {
						$q->where('status', 'active');
					}, 'files'])->find($params['id']);
					break;

				case 'check_room_availability':
					if (empty($params['hotel_id'])) throw new Exception("Hotel ID required");
					$hotel = Hotel::find($params['hotel_id']);
					if (!$hotel) throw new Exception("Hotel not found");

					$startDate = $params['start_date'] ?? Carbon::tomorrow()->format('Y-m-d');
					$endDate = $params['end_date'] ?? Carbon::tomorrow()->addDay()->format('Y-m-d');
					$adults = $params['adults_count'] ?? 2;

					// استخدام دالة الفندق الذكية لجلب الغرف وارخص سعر
					// ملاحظة: نحتاج جلب كل الغرف المتاحة وليس فقط الأرخص للعرض
					// سنقوم بعمل فلتر يدوي للغرف هنا
					$rooms = $hotel->rooms()->where('status', 'active')
						->where('adults_count', '>=', $adults)
						->get();

					$availableRooms = [];
					foreach ($rooms as $room) {
						$calc = $room->calculateBookingPrice($startDate, $endDate, $adults, [], 'egp');
						if ($calc['success']) {
							$roomData = $room->toArray();
							$roomData['calculated_price'] = $calc;
							$availableRooms[] = $roomData;
						}
					}
					$data = $availableRooms;
					break;

				case 'search_trips':
					$query = Trip::query()->where('status', 'active');

					if (!empty($params['city_id'])) $query->where('city_id', $params['city_id']);
					if (!empty($params['main_category_id'])) $query->where('main_category_id', $params['main_category_id']);
					if (!empty($params['sub_category_id'])) $query->where('sub_category_id', $params['sub_category_id']);
					if (!empty($params['name'])) $query->scopeFilter($params['name']); // Assuming filter scope exists or standard where

					// ترتيب بالسعر اذا طلب
					if (!empty($params['sort_price'])) {
						// هذا يتطلب معالجة خاصة لأن السعر JSON، لكن للتبسيط:
						// يمكننا تجاهل الترتيب المعقد الآن أو جلبه كما هو
					}

					$data = $query->with(['city', 'mainCategory', 'subCategory'])->limit(10)->get();
					break;

				case 'get_trip_details':
					if (empty($params['id'])) throw new Exception("Trip ID required");
					$data = Trip::with(['city', 'mainCategory', 'subCategory', 'hotels', 'files'])->find($params['id']);
					break;

				case 'get_cities':
					$query = City::query();
					if (!empty($params['name'])) $query->scopeFilter($params['name']);
					$data = $query->limit(20)->get();
					break;

				default:
					return ['success' => false, 'error' => "Unknown action: $action"];
			}

			return ['success' => true, 'data' => $data];
		} catch (Exception $e) {
			return ['success' => false, 'error' => $e->getMessage()];
		}
	}

	protected function resolveDbParameters(array $params, array $collectedData): array
	{
		foreach ($params as $key => $value) {
			if (!is_string($value)) continue;

			// تواريخ
			if ($value === 'TOMORROW_DATE') {
				$params[$key] = Carbon::tomorrow()->format('Y-m-d');
				continue;
			}
			if ($value === 'AFTER_TOMORROW_DATE') {
				$params[$key] = Carbon::tomorrow()->addDay()->format('Y-m-d');
				continue;
			}

			// Chaining
			if (str_contains($value, 'HOTEL_ID') || str_contains($value, 'TRIP_ID') || str_contains($value, 'CITY_ID')) {
				// محاولة ذكية لإيجاد الـ ID من البيانات السابقة
				if ($key === 'id' || str_ends_with($key, '_id')) {
					if (isset($collectedData['id'])) {
						$params[$key] = $collectedData['id'];
					} elseif (isset($collectedData[$key])) {
						$params[$key] = $collectedData[$key];
					}
				}
			}
		}
		return $params;
	}

	protected function extractDataFromDbResults(array $results): array
	{
		// نأخذ آخر نتيجة ناجحة وفيها بيانات
		$reversed = array_reverse($results);
		foreach ($reversed as $res) {
			if ($res['success'] && !empty($res['data'])) {
				$action = $res['action'];
				$type = 'generic';

				if (str_contains($action, 'hotel')) $type = 'hotels';
				if (str_contains($action, 'trip')) $type = 'trips';
				if (str_contains($action, 'room')) $type = 'rooms';
				if (str_contains($action, 'cities')) $type = 'cities';

				return ['data' => $res['data'], 'type' => $type];
			}
		}
		return ['data' => null, 'type' => null];
	}


	protected function getStaticDataContext(): string
	{
		return Cache::remember('chatbot_static_context_v4_db', 3600, function () {
			$context = "\n\n## 📊 البيانات المتاحة (IDs):\n\n";
			$context .= $this->fetchFromDbAndFormat('City', 'المدن المتاحة');
			$context .= $this->fetchFromDbAndFormat('MainCategory', 'فئات الرحلات');
			$context .= $this->fetchFromDbAndFormat('SubCategory', 'الفئات الفرعية');
			$context .= $this->fetchFromDbAndFormat('HotelType', 'أنواع الفنادق');
			//			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/hotels', ' الفنادق');
			//			$context .= $this->fetchAndFormatList($baseUrl . '/api/v1/trips', 'الرحلات');
			return $context;
		});
	}



	private function fetchFromDbAndFormat(string $modelName, string $title): string
	{
		try {
			$modelClass = "App\\Models\\$modelName";
			$items = $modelClass::limit(100)->get();

			$text = "### {$title}:\n";
			foreach ($items as $item) {
				$name = is_array($item->name) ? ($item->name['ar'] ?? $item->name['en'] ?? '') : $item->name;
				$text .= "- {$name}: ID = {$item->id}\n";
			}
			return $text . "\n";
		} catch (Exception $e) {
			Log::warning("Failed to fetch {$title}: " . $e->getMessage());
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

	/**
	 * اكتشاف لغة النص البسيط: إذا وجدنا أحرف عربية نرجع 'ar'، وإلا 'en'
	 */
	protected function detectLanguage(string $text): string
	{
		if (preg_match('/\p{Arabic}/u', $text)) {
			return 'ar';
		}
		return 'en';
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
		return is_array($decoded) ? $decoded : ['response_message' => $response, 'db_actions' => []];
	}

	protected function storeConversation($session, $msg, $result, $structured)
	{
		ChatbotConversation::create([
			'chat_session' => $session,
			'user_message' => $msg,
			'bot_response' => $result['message'],
			'api_calls' => $structured['db_actions'] ?? null, // storing db_actions in api_calls column for now
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

	public function submitFeedback($session, $helpful, $feedback)
	{
		return ChatbotConversation::where('chat_session', $session)->latest()->first()?->update([
			'was_helpful' => $helpful,
			'feedback' => $feedback
		]);
	}

	public function getConversationHistory(string $chat_session)
	{
		return ChatbotConversation::where('chat_session', $chat_session)->latest()->limit(20)->get();
	}
}
