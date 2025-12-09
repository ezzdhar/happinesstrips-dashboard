<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotFeedbackRequest;
use App\Http\Requests\ChatbotMessageRequest;
use App\Services\ChatbotService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
	use ApiResponse;

	public function __construct(protected ChatbotService $chatbotService)
	{
	}

	/**
	 * معالجة رسالة الشات بوت
	 */
	public function chat(ChatbotMessageRequest $request): JsonResponse
	{
		$message = $request->input('message');

		// استعادة الجلسة أو إنشاء واحدة جديدة
		$chat_session = $request->input('chat_session');

		// جلب تاريخ المحادثة إذا وجد
		$conversationHistory = [];
		if ($chat_session) {
			$conversationHistory = $this->chatbotService->getConversationHistoryForContext($chat_session);
		}

		// معالجة الرسالة
		$result = $this->chatbotService->processMessage($message, $conversationHistory, $chat_session);

		// إرجاع الرد بصيغة JSON نظيفة للموبايل
		return response()->json([
			'success' => $result['success'],
			'chat_session' => $result['chat_session'],
			'message' => $result['message'], // رسالة نصية قصيرة من البوت
			'data' => $result['data'] ?? null, // البيانات الخام لعرضها كـ Cards في التطبيق
			'data_type' => $result['data_type'] ?? null, // نوع البيانات (hotels, trips, etc)
			'suggestions' => $result['suggestions'] ?? [], // اقتراحات أزرار
		], $result['success'] ? 200 : 500);
	}

	/**
	 * إرسال التقييم
	 */
	public function feedback(ChatbotFeedbackRequest $request): JsonResponse
	{
		$success = $this->chatbotService->submitFeedback(
			$request->chat_session,
			$request->was_helpful,
			$request->feedback ?? null
		);

		return response()->json([
			'success' => $success,
			'message' => $success ? 'شكراً على ملاحظاتك!' : 'فشل في حفظ الملاحظات',
		]);
	}

	/**
	 * جلب سجل المحادثة
	 */
	public function history(Request $request): JsonResponse
	{
		$chat_session = $request->input('chat_session');

		if (!$chat_session) {
			return response()->json(['success' => false, 'message' => 'Chat session is required'], 400);
		}

		$history = $this->chatbotService->getConversationHistory($chat_session);

		return $this->responseCreated(message: __('lang.successfully'), data: $history);
	}

	/**
	 * القدرات المتاحة (للتوثيق أو للفرونت إند)
	 */
	public function capabilities(): JsonResponse
	{
		return response()->json([
			'success' => true,
			'data' => [
				'name' => 'Happiness Trips Assistant',
				'version' => '2.0',
				'features' => ['Hotel Search', 'Trip Search', 'Price Calculation', 'Smart Context'],
			]
		]);
	}
}