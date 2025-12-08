<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;

class ChatbotService
{
    /**
     * Process user message and generate response using Gemini AI
     */
    public function processMessage(string $userMessage, ?array $conversationHistory = null): array
    {
        try {
            // Build conversation messages
            $messages = $this->buildConversationMessages($userMessage, $conversationHistory);

            // Get AI response
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-1.5-flash')
                ->withSystemPrompt(view('prompts.chatbot-system'))
                ->withPrompt($userMessage)
                ->withMaxTokens(2000)
                ->usingTemperature(0.7)
                ->asText();

            // Parse AI response
            $aiResponse = $response->text;

            // Try to extract structured response
            $structuredResponse = $this->parseStructuredResponse($aiResponse);

            // Execute API calls if suggested
            $apiResults = [];
            if (! empty($structuredResponse['api_calls'])) {
                $apiResults = $this->executeApiCalls($structuredResponse['api_calls']);
            }

            return [
                'success' => true,
                'message' => $structuredResponse['response_message'] ?? $aiResponse,
                'api_calls' => $structuredResponse['api_calls'] ?? [],
                'api_results' => $apiResults,
                'suggested_actions' => $structuredResponse['suggested_actions'] ?? [],
                'raw_response' => $aiResponse,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Chatbot error: '.$e->getMessage(), [
                'message' => $userMessage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'عذراً، حدث خطأ في معالجة رسالتك. يرجى المحاولة مرة أخرى.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build conversation messages array
     */
    protected function buildConversationMessages(string $currentMessage, ?array $history = null): array
    {
        $messages = [];

        if ($history && is_array($history)) {
            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['role'] ?? 'user',
                    'content' => $msg['content'] ?? '',
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
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

                // Build full URL
                $baseUrl = config('app.url');
                $fullUrl = $baseUrl.$endpoint;

                // Execute API call
                $response = null;

                if ($method === 'GET') {
                    $response = Http::get($fullUrl, $params);
                } elseif ($method === 'POST') {
                    $response = Http::post($fullUrl, $params);
                }

                if ($response && $response->successful()) {
                    $results[$index] = [
                        'success' => true,
                        'endpoint' => $endpoint,
                        'data' => $response->json(),
                    ];
                } else {
                    $results[$index] = [
                        'success' => false,
                        'endpoint' => $endpoint,
                        'error' => $response ? $response->body() : 'Unknown error',
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
     * Format API results into readable text
     */
    public function formatApiResults(array $apiResults): string
    {
        $formatted = '';

        foreach ($apiResults as $result) {
            if ($result['success'] && isset($result['data'])) {
                $data = $result['data'];

                // Format based on data structure
                if (isset($data['data']) && is_array($data['data'])) {
                    $formatted .= $this->formatDataArray($data['data']);
                } else {
                    $formatted .= json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return $formatted;
    }

    /**
     * Format data array into readable text
     */
    protected function formatDataArray(array $data): string
    {
        $formatted = '';

        foreach ($data as $item) {
            if (is_array($item)) {
                // Format hotel/trip/room data
                if (isset($item['name'])) {
                    $formatted .= '📍 '.$item['name']."\n";
                }
                if (isset($item['price'])) {
                    $formatted .= '💰 السعر: '.$item['price']." جنيه\n";
                }
                if (isset($item['rating'])) {
                    $formatted .= '⭐ التقييم: '.$item['rating']."\n";
                }
                $formatted .= "\n";
            }
        }

        return $formatted;
    }
}
