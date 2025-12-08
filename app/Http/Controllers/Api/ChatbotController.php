<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotMessageRequest;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService) {}

    /**
     * Process chatbot message and return AI-generated response
     */
    public function chat(ChatbotMessageRequest $request): JsonResponse
    {
        $message = $request->input('message');
        $conversationHistory = $request->input('conversation_history', []);

        // Get chat_session from request (renamed from session_id to avoid WAF blocking)
        $sessionId = $request->input('chat_session');
		if ($sessionId){
			$conversationHistory = $this->chatbotService->getConversationHistory($sessionId);
		}

        // Process the message
        $result = $this->chatbotService->processMessage($message, $conversationHistory, $sessionId);

        // Return simplified response with data field
        return response()->json([
            'success' => $result['success'],
            'chat_session' => $result['session_id'],
            'message' => $result['message'],
            'data' => $result['data'] ?? null,
            'data_type' => $result['data_type'] ?? null,
            'suggestions' => $result['suggestions'] ?? [],
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Submit feedback for a conversation
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_session' => ['required', 'string'],
            'conversation_id' => ['required', 'integer'],
            'was_helpful' => ['required', 'boolean'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $success = $this->chatbotService->submitFeedback(
            $validated['chat_session'],
            $validated['conversation_id'],
            $validated['was_helpful'],
            $validated['feedback'] ?? null
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'شكراً على ملاحظاتك!' : 'فشل في حفظ الملاحظات',
        ]);
    }

    /**
     * Get conversation history for a session
     */
    public function history(Request $request): JsonResponse
    {
        $sessionId = $request->input('chat_session');

        if (! $sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Chat session is required',
            ], 400);
        }

        $history = $this->chatbotService->getConversationHistory($sessionId);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get chatbot capabilities and available APIs
     */
    public function capabilities(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'name' => 'Happiness Trips Chatbot',
                'version' => '1.0',
                'description' => 'مساعد ذكي لحجز الفنادق والرحلات السياحية',
                'capabilities' => [
                    'البحث عن الفنادق والغرف',
                    'البحث عن الرحلات السياحية',
                    'حساب أسعار الحجز',
                    'عرض تفاصيل الفنادق والرحلات',
                    'الإجابة على الأسئلة العامة',
                ],
                'available_apis' => [
                    'hotels' => [
                        'GET /api/v1/hotels',
                        'GET /api/v1/hotels/details/{hotel_id}',
                        'GET /api/v1/hotels/cheapest-room/{hotel_id}',
                    ],
                    'rooms' => [
                        'GET /api/v1/hotels/rooms',
                        'GET /api/v1/hotels/rooms/{room_id}',
                        'GET /api/v1/hotels/rooms/calculate/booking-room/price/{room_id}',
                    ],
                    'trips' => [
                        'GET /api/v1/trips',
                        'GET /api/v1/trips/{trip_id}',
                        'GET /api/v1/trips/calculate/booking-trip/price/{trip_id}',
                    ],
                    'data' => [
                        'GET /api/v1/hotel-types',
                        'GET /api/v1/cities',
                        'GET /api/v1/categories',
                        'GET /api/v1/sub-categories',
                    ],
                ],
            ],
        ]);
    }
}
