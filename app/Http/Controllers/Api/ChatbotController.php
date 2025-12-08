<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatbotMessageRequest;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;

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

        // Process the message
        $result = $this->chatbotService->processMessage($message, $conversationHistory);

        // Return response
        return response()->json([
            'success' => $result['success'],
            'data' => [
                'message' => $result['message'],
                'api_calls' => $result['api_calls'] ?? [],
                'api_results' => $result['api_results'] ?? [],
                'suggested_actions' => $result['suggested_actions'] ?? [],
            ],
            'meta' => [
                'usage' => $result['usage'] ?? null,
            ],
        ], $result['success'] ? 200 : 500);
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
