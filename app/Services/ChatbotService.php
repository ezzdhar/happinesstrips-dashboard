<?php

namespace App\Services;

use App\Models\ChatbotConversation;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class ChatbotService
{
    /**
     * Process user message and generate response using Gemini AI
     */
    public function processMessage(string $userMessage, ?array $conversationHistory = null, ?string $sessionId = null): array
    {
        try {
            // Generate session ID ONLY if not provided (preserve existing session)
            if (empty($sessionId)) {
                $sessionId = 'session-'.time().'-'.Str::random(8);
            }

            // Get conversation history from database for this session
            $sessionHistory = $this->getSessionHistory($sessionId);

            // Get learning context from previous conversations
            $learningContext = $this->getLearningContext($userMessage);

            // Build enhanced prompt with learning and session history
            $enhancedPrompt = $this->buildEnhancedPrompt($userMessage, $learningContext, $sessionHistory);

            // Get AI response
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-2.0-flash')
                ->withSystemPrompt(view('prompts.chatbot-system-v2'))
                ->withPrompt($enhancedPrompt)
                ->withMaxTokens(2000)
                ->usingTemperature(0.7)
                ->asText();

            // Parse AI response
            $aiResponse = $response->text;

            // Try to extract structured response
            $structuredResponse = $this->parseStructuredResponse($aiResponse);

            // Execute API calls if suggested and extract data
            $data = null;
            $dataType = null;
            
            if (! empty($structuredResponse['api_calls'])) {
                $apiResults = $this->executeApiCalls($structuredResponse['api_calls']);

                // Extract data from API results for frontend
                $extractedData = $this->extractDataFromApiResults($apiResults, $structuredResponse['intent'] ?? 'general_inquiry');
                $data = $extractedData['data'];
                $dataType = $extractedData['data_type'];

                // Enhance response message with API results
                $structuredResponse['response_message'] = $this->enhanceResponseWithResults(
                    $structuredResponse['response_message'] ?? $aiResponse,
                    $apiResults,
                    $structuredResponse['intent'] ?? 'general_inquiry'
                );
            }

            $result = [
                'success' => true,
                'session_id' => $sessionId,
                'message' => $structuredResponse['response_message'] ?? $aiResponse,
                'data' => $data,
                'data_type' => $dataType,
                'suggestions' => $structuredResponse['suggested_actions'] ?? [],
            ];

            // Store conversation for learning (with all metadata for internal use)
            $this->storeConversation($sessionId, $userMessage, $result, $structuredResponse);

            return $result;

        } catch (Exception $e) {
            Log::error('Chatbot error: '.$e->getMessage(), [
                'message' => $userMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'session_id' => $sessionId ?? 'session-'.time().'-'.Str::random(8),
                'message' => 'عذراً، حدث خطأ في معالجة رسالتك. يرجى المحاولة مرة أخرى.',
                'data' => null,
                'data_type' => null,
                'suggestions' => ['حاول مرة أخرى', 'اتصل بخدمة العملاء'],
            ];
        }
    }

    /**
     * Get conversation history from current session
     */
    protected function getSessionHistory(string $sessionId): string
    {
        try {
            // Get recent conversations from this session
            $conversations = ChatbotConversation::query()
                ->where('session_id', $sessionId)
                ->latest()
                ->limit(5) // Last 5 messages
                ->get(['user_message', 'bot_response'])
                ->reverse(); // Oldest first

            if ($conversations->isEmpty()) {
                return '';
            }

            $history = "\n\n## سياق المحادثة الحالية:\n";
            foreach ($conversations as $conv) {
                $history .= "المستخدم: {$conv->user_message}\n";
                $history .= "البوت: ".Str::limit($conv->bot_response, 100)."\n\n";
            }

            return $history;
        } catch (Exception $e) {
            Log::warning('Failed to get session history: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Get learning context from previous similar conversations
     */
    protected function getLearningContext(string $userMessage): string
    {
        try {
            // Get similar successful conversations (prioritize those with positive feedback)
            $similarConversations = ChatbotConversation::query()
                ->where(function ($query) use ($userMessage) {
                    $keywords = explode(' ', Str::limit($userMessage, 50, ''));
                    foreach ($keywords as $keyword) {
                        if (strlen($keyword) > 3) {
                            $query->orWhere('user_message', 'LIKE', "%{$keyword}%");
                        }
                    }
                })
                ->where(function ($query) {
                    // Prioritize conversations with positive feedback
                    $query->where('was_helpful', true)
                        ->orWhereNull('was_helpful'); // Include unrated but recent conversations
                })
                ->latest()
                ->limit(5)
                ->get(['user_message', 'bot_response', 'intent', 'was_helpful']);

            if ($similarConversations->isEmpty()) {
                return '';
            }

            $context = "\n\n## أمثلة من محادثات سابقة ناجحة:\n";
            foreach ($similarConversations as $conv) {
                $helpful = $conv->was_helpful ? '✅' : '';
                $context .= "- {$helpful} المستخدم: {$conv->user_message}\n";
                $context .= "  الرد: ".Str::limit($conv->bot_response, 150)."\n";
            }

            return $context;
        } catch (Exception $e) {
            Log::warning('Failed to get learning context: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Build enhanced prompt with learning context and session history
     */
    protected function buildEnhancedPrompt(string $userMessage, string $learningContext, string $sessionHistory): string
    {
        $prompt = $userMessage;

        // Add session history first (most important context)
        if ($sessionHistory) {
            $prompt .= $sessionHistory;
        }

        // Add learning context from similar conversations
        if ($learningContext) {
            $prompt .= $learningContext;
        }

        return $prompt;
    }

    /**
     * Store conversation for future learning
     */
    protected function storeConversation(string $sessionId, string $userMessage, array $result, array $structuredResponse = []): void
    {
        try {
            ChatbotConversation::create([
                'session_id' => $sessionId,
                'user_message' => $userMessage,
                'bot_response' => $result['message'],
                'api_calls' => $structuredResponse['api_calls'] ?? null,
                'api_results' => $result['data'] ?? null,
                'suggested_actions' => $result['suggestions'] ?? null,
                'intent' => $structuredResponse['intent'] ?? 'general_inquiry',
                'was_helpful' => null, // Will be updated via feedback
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to store conversation: '.$e->getMessage());
        }
    }

    /**
     * Parse structured response from AI
     */
    protected function parseStructuredResponse(string $response): array
    {
        // Try to find JSON in the response
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $jsonString = $matches[1];
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try direct JSON parsing
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // Fallback: return raw response
        return [
            'response_message' => $response,
            'api_calls' => [],
            'suggested_actions' => [],
            'intent' => 'general_inquiry',
        ];
    }

    /**
     * Execute API calls suggested by AI
     */
    protected function executeApiCalls(array $apiCalls): array
    {
        $results = [];

        foreach ($apiCalls as $index => $call) {
            try {
                $endpoint = $call['endpoint'] ?? '';
                $method = strtoupper($call['method'] ?? 'GET');
                $params = $call['params'] ?? [];

                // Only allow GET requests
                if ($method !== 'GET') {
                    $results[$index] = [
                        'success' => false,
                        'endpoint' => $endpoint,
                        'error' => 'Only GET requests are allowed',
                        'message' => 'هذه العملية تحتاج تسجيل دخول',
                    ];

                    continue;
                }

                // Build full URL
                $baseUrl = rtrim(config('app.url'), '/');
                $fullUrl = $baseUrl.$endpoint;

                // Add query parameters
                if (! empty($params)) {
                    $fullUrl .= '?'.http_build_query($params);
                }

                // Execute API call
                $response = Http::timeout(10)->get($fullUrl);

                if ($response->successful()) {
                    $results[$index] = [
                        'success' => true,
                        'endpoint' => $endpoint,
                        'data' => $response->json(),
                        'status' => $response->status(),
                    ];
                } else {
                    $results[$index] = [
                        'success' => false,
                        'endpoint' => $endpoint,
                        'error' => $response->body(),
                        'status' => $response->status(),
                    ];
                }

            } catch (Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'endpoint' => $call['endpoint'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Extract structured data from API results for frontend
     */
    protected function extractDataFromApiResults(array $apiResults, string $intent): array
    {
        $data = null;
        $dataType = null;

        foreach ($apiResults as $result) {
            if (! $result['success'] || ! isset($result['data']['data'])) {
                continue;
            }

            $responseData = $result['data']['data'];
            $endpoint = $result['endpoint'] ?? '';

            // Determine data type based on endpoint
            if (str_contains($endpoint, '/cities')) {
                $dataType = 'cities';
                $data = $this->formatSimpleList($responseData);
            } elseif (str_contains($endpoint, '/hotel-types')) {
                $dataType = 'hotel_types';
                $data = $this->formatSimpleList($responseData);
            } elseif (str_contains($endpoint, '/categories')) {
                $dataType = 'categories';
                $data = $this->formatSimpleList($responseData);
            } elseif (str_contains($endpoint, '/sub-categories')) {
                $dataType = 'sub_categories';
                $data = $this->formatSimpleList($responseData);
            } elseif (str_contains($endpoint, '/hotels/rooms') && ! str_contains($endpoint, '/calculate')) {
                $dataType = 'rooms';
                $data = $this->formatRoomsList($responseData);
            } elseif (str_contains($endpoint, '/hotels') && ! str_contains($endpoint, '/rooms')) {
                $dataType = 'hotels';
                $data = $this->formatHotelsList($responseData);
            } elseif (str_contains($endpoint, '/trips')) {
                $dataType = 'trips';
                $data = $this->formatTripsList($responseData);
            }

            // Only return first successful data extraction
            if ($data !== null) {
                break;
            }
        }

        return [
            'data' => $data,
            'data_type' => $dataType,
        ];
    }

    /**
     * Format simple list (cities, categories, etc.)
     */
    protected function formatSimpleList(array $items): array
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? $item['title'] ?? 'غير محدد',
            ];
        }

        return $formatted;
    }

    /**
     * Format hotels list
     */
    protected function formatHotelsList(array $items): array
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? 'غير محدد',
                'price' => $item['price'] ?? $item['min_price'] ?? null,
                'rating' => $item['rating'] ?? null,
                'city' => $item['city']['name'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Format rooms list
     */
    protected function formatRoomsList(array $items): array
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? 'غير محدد',
                'price' => $item['price'] ?? null,
                'capacity' => $item['capacity'] ?? null,
                'hotel' => $item['hotel']['name'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Format trips list
     */
    protected function formatTripsList(array $items): array
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $formatted[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? $item['title'] ?? 'غير محدد',
                'price' => $item['price'] ?? null,
                'category' => $item['category']['name'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Enhance response message with API results
     */
    protected function enhanceResponseWithResults(string $baseMessage, array $apiResults, string $intent): string
    {
        $enhanced = $baseMessage."\n\n";

        foreach ($apiResults as $result) {
            if (! $result['success']) {
                continue;
            }

            $data = $result['data'] ?? [];

            // Format based on intent
            if (str_contains($intent, 'city') || str_contains($intent, 'data_request')) {
                $enhanced .= $this->formatDataList($data);
            } elseif (str_contains($intent, 'hotel')) {
                $enhanced .= $this->formatHotelList($data);
            } elseif (str_contains($intent, 'trip')) {
                $enhanced .= $this->formatTripList($data);
            } elseif (str_contains($intent, 'price')) {
                $enhanced .= $this->formatPriceInfo($data);
            }
        }

        return trim($enhanced);
    }

    /**
     * Format data list (cities, categories, etc.)
     */
    protected function formatDataList(array $data): string
    {
        if (! isset($data['data']) || empty($data['data'])) {
            return '';
        }

        $formatted = "📋 القائمة المتاحة:\n\n";
        foreach ($data['data'] as $index => $item) {
            $id = $item['id'] ?? '?';
            $name = $item['name'] ?? $item['title'] ?? 'غير محدد';
            $formatted .= ($index + 1).". {$name} (ID: {$id})\n";
        }

        return $formatted."\n";
    }

    /**
     * Format hotel list
     */
    protected function formatHotelList(array $data): string
    {
        if (! isset($data['data']) || empty($data['data'])) {
            return '❌ لم أجد فنادق تطابق بحثك.';
        }

        $formatted = "🏨 الفنادق المتاحة:\n\n";
        $count = 0;
        foreach ($data['data'] as $hotel) {
            if ($count >= 5) {
                break;
            } // Show max 5
            $name = $hotel['name'] ?? 'غير محدد';
            $price = $hotel['price'] ?? $hotel['min_price'] ?? 'غير متوفر';
            $rating = $hotel['rating'] ?? 'N/A';

            $formatted .= "📍 {$name}\n";
            $formatted .= "   💰 السعر: {$price} جنيه\n";
            $formatted .= "   ⭐ التقييم: {$rating}\n";
            $formatted .= "   🆔 ID: {$hotel['id']}\n\n";
            $count++;
        }

        if (count($data['data']) > 5) {
            $formatted .= "... وهناك ".(count($data['data']) - 5)." فنادق أخرى\n";
        }

        return $formatted;
    }

    /**
     * Format trip list
     */
    protected function formatTripList(array $data): string
    {
        if (! isset($data['data']) || empty($data['data'])) {
            return '❌ لم أجد رحلات تطابق بحثك.';
        }

        $formatted = "🎒 الرحلات المتاحة:\n\n";
        $count = 0;
        foreach ($data['data'] as $trip) {
            if ($count >= 5) {
                break;
            }
            $name = $trip['name'] ?? $trip['title'] ?? 'غير محدد';
            $price = $trip['price'] ?? 'غير متوفر';

            $formatted .= "🗺️ {$name}\n";
            $formatted .= "   💰 السعر: {$price} جنيه\n";
            $formatted .= "   🆔 ID: {$trip['id']}\n\n";
            $count++;
        }

        if (count($data['data']) > 5) {
            $formatted .= "... وهناك ".(count($data['data']) - 5)." رحلات أخرى\n";
        }

        return $formatted;
    }

    /**
     * Format price information
     */
    protected function formatPriceInfo(array $data): string
    {
        if (! isset($data['data'])) {
            return '';
        }

        $priceData = $data['data'];
        $formatted = "💵 تفاصيل السعر:\n\n";

        if (isset($priceData['total_price'])) {
            $formatted .= "✅ السعر الإجمالي: {$priceData['total_price']} جنيه\n";
        }
        if (isset($priceData['price_per_night'])) {
            $formatted .= "🌙 السعر لليلة: {$priceData['price_per_night']} جنيه\n";
        }
        if (isset($priceData['nights'])) {
            $formatted .= "📅 عدد الليالي: {$priceData['nights']}\n";
        }

        return $formatted;
    }

    /**
     * Submit feedback for a conversation
     */
    public function submitFeedback(string $sessionId, int $conversationId, bool $wasHelpful, ?string $feedback = null): bool
    {
        try {
            $conversation = ChatbotConversation::query()
                ->where('id', $conversationId)
                ->where('session_id', $sessionId)
                ->first();

            if (! $conversation) {
                return false;
            }

            $conversation->update([
                'was_helpful' => $wasHelpful,
                'feedback' => $feedback,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Failed to submit feedback: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get conversation history by session
     */
    public function getConversationHistory(string $sessionId, int $limit = 10): array
    {
        try {
            return ChatbotConversation::query()
                ->where('session_id', $sessionId)
                ->latest()
                ->limit($limit)
                ->get(['user_message', 'bot_response', 'intent', 'created_at'])
                ->map(function ($conv) {
                    return [
                        'role' => 'user',
                        'content' => $conv->user_message,
                        'timestamp' => $conv->created_at,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Failed to get conversation history: '.$e->getMessage());

            return [];
        }
    }
}

