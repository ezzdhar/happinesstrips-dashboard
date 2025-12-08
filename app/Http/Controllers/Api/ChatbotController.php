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

        // Always generate session_id server-side to avoid firewall blocking
        $sessionId = null;

        // Process the message
        $result = $this->chatbotService->processMessage($message, $conversationHistory, $sessionId);

        // Return response
        return response()->json([
            'success' => $result['success'],
            'data' => [
                'session_id' => $result['session_id'],
                'message' => $result['message'],
                'api_calls' => $result['api_calls'] ?? [],
                'api_results' => $result['api_results'] ?? [],
                'suggested_actions' => $result['suggested_actions'] ?? [],
                'intent' => $result['intent'] ?? null,
                'needs_user_input' => $result['needs_user_input'] ?? false,
            ],
            'meta' => [
                'usage' => $result['usage'] ?? null,
            ],
        ], $result['success'] ? 200 : 500);
    }

    /**
     * Submit feedback for a conversation
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string'],
            'conversation_id' => ['required', 'integer'],
            'was_helpful' => ['required', 'boolean'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $success = $this->chatbotService->submitFeedback(
            $validated['session_id'],
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
        $sessionId = $request->input('session_id');

        if (! $sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID is required',
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
                        'GET /api/hotels',
                        'GET /api/hotels/details/{hotel_id}',
                        'GET /api/hotels/cheapest-room/{hotel_id}',
                    ],
                    'rooms' => [
                        'GET /api/hotels/rooms',
                        'GET /api/hotels/rooms/{room_id}',
                        'GET /api/hotels/rooms/calculate/booking-room/price/{room_id}',
                    ],
                    'trips' => [
                        'GET /api/trips',
                        'GET /api/trips/{trip_id}',
                        'GET /api/trips/calculate/booking-trip/price/{trip_id}',
                    ],
                    'data' => [
                        'GET /api/hotel-types',
                        'GET /api/cities',
                        'GET /api/categories',
                        'GET /api/sub-categories',
                    ],
                ],
            ],
        ]);
    }
}
