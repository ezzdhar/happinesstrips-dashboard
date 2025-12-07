<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class PrismChatService
{
    private string $provider;
    private string $model;
    private int $maxRetries = 3;
    private int $retryDelay = 1000; // milliseconds

    public function __construct()
    {
        $this->provider = config('services.chatbot.provider', 'openai');
        $this->model = config('services.chatbot.model', 'gpt-4o-mini');
    }

    /**
     * Send a message to the AI and get a response.
     *
     * @param array $messages Array of messages with 'role' and 'content'
     * @param array $context Additional context data
     * @return array Returns ['success' => bool, 'data' => string|null, 'error_code' => string|null, 'error_message' => string|null]
     */
    public function chat(array $messages, array $context = []): array
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $prompt = $this->buildPrompt($messages, $context);

                $response = Prism::text()
                    ->using($this->getProvider(), $this->model)
                    ->withPrompt($prompt)
                    ->withMaxTokens(config('services.chatbot.max_tokens', 1000))
                    ->asText();

                Log::channel('chatbot')->info('Chat response received', [
                    'attempt' => $attempt + 1,
                    'provider' => $this->provider,
                    'model' => $this->model,
                ]);

                return [
                    'success' => true,
                    'data' => $response,
                    'error_code' => null,
                    'error_message' => null,
                ];
            } catch (Exception $e) {
                $attempt++;

                Log::channel('chatbot')->error('Chat request failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                if ($attempt >= $this->maxRetries) {
                    return [
                        'success' => false,
                        'data' => null,
                        'error_code' => 'CHAT_SERVICE_ERROR',
                        'error_message' => 'Failed to get response from AI service: ' . $e->getMessage(),
                    ];
                }

                // Exponential backoff
                usleep($this->retryDelay * pow(2, $attempt - 1) * 1000);
            }
        }

        return [
            'success' => false,
            'data' => null,
            'error_code' => 'MAX_RETRIES_EXCEEDED',
            'error_message' => 'Maximum retry attempts exceeded',
        ];
    }

    /**
     * Build the prompt from messages and context.
     */
    private function buildPrompt(array $messages, array $context): string
    {
        $systemContext = $this->buildSystemContext($context);

        $prompt = $systemContext . "\n\n";

        foreach ($messages as $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';

            if ($role === 'system') {
                $prompt .= "System: {$content}\n";
            } elseif ($role === 'assistant') {
                $prompt .= "Assistant: {$content}\n";
            } else {
                $prompt .= "User: {$content}\n";
            }
        }

        $prompt .= "Assistant: ";

        return $prompt;
    }

    /**
     * Build system context from provided context data.
     */
    private function buildSystemContext(array $context): string
    {
        $language = $context['language'] ?? 'en';

        $systemPrompt = $language === 'ar'
            ? "أنت مساعد ذكي لتطبيق Happiness Trips للسياحة والسفر. أجب على الأسئلة بطريقة مهذبة ومفيدة باللغة العربية."
            : "You are a helpful AI assistant for Happiness Trips, a travel and tourism booking application. Answer questions politely and helpfully in English.";

        $contextInfo = [];

        if (isset($context['booking_id'])) {
            $contextInfo[] = "Current booking ID: {$context['booking_id']}";
        }

        if (isset($context['trip_id'])) {
            $contextInfo[] = "Current trip ID: {$context['trip_id']}";
        }

        if (isset($context['hotel_id'])) {
            $contextInfo[] = "Current hotel ID: {$context['hotel_id']}";
        }

        if (isset($context['user_name'])) {
            $contextInfo[] = "User name: {$context['user_name']}";
        }

        if (! empty($contextInfo)) {
            $systemPrompt .= "\n\nContext:\n" . implode("\n", $contextInfo);
        }

        return "System: {$systemPrompt}";
    }

    /**
     * Get the Prism provider enum.
     */
    private function getProvider(): Provider
    {
        return match ($this->provider) {
            'anthropic' => Provider::Anthropic,
            'gemini' => Provider::Gemini,
            'mistral' => Provider::Mistral,
            'groq' => Provider::Groq,
            'ollama' => Provider::Ollama,
            'xai' => Provider::XAI,
            'deepseek' => Provider::DeepSeek,
            'openrouter' => Provider::OpenRouter,
            default => Provider::OpenAI,
        };
    }

    /**
     * Set custom provider.
     */
    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Set custom model.
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set max retries.
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = $retries;
        return $this;
    }
}

