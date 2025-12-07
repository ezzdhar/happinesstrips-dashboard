<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ChatMessage;
use App\Services\PrismChatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ChatMessage $userMessage,
        public array $conversationHistory = [],
        public array $context = []
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(PrismChatService $chatService): void
    {
        try {
            // Build messages array from conversation history
            $messages = $this->buildMessages();

            // Call AI service
            $result = $chatService->chat($messages, $this->context);

            if ($result['success']) {
                // Create assistant response message
                $assistantMessage = ChatMessage::create([
                    'user_id' => $this->userMessage->user_id,
                    'session_id' => $this->userMessage->session_id,
                    'role' => 'assistant',
                    'content' => $result['data'],
                    'status' => 'sent',
                    'meta' => [
                        'model' => config('services.chatbot.model'),
                        'provider' => config('services.chatbot.provider'),
                        'processed_at' => now()->toIso8601String(),
                    ],
                ]);

                // Mark user message as sent
                $this->userMessage->markAsSent();

                Log::channel('chatbot')->info('Chat message processed successfully', [
                    'user_message_id' => $this->userMessage->id,
                    'assistant_message_id' => $assistantMessage->id,
                ]);
            } else {
                // Mark as failed with error details
                $this->userMessage->markAsFailed([
                    'error_code' => $result['error_code'],
                    'error_message' => $result['error_message'],
                ]);

                Log::channel('chatbot')->error('Chat message processing failed', [
                    'user_message_id' => $this->userMessage->id,
                    'error' => $result['error_message'],
                ]);

                // Throw exception to trigger retry
                throw new \Exception($result['error_message']);
            }
        } catch (\Exception $e) {
            Log::channel('chatbot')->error('Job execution error', [
                'user_message_id' => $this->userMessage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Build messages array for AI service.
     */
    private function buildMessages(): array
    {
        $messages = [];

        // Add conversation history
        foreach ($this->conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $this->userMessage->content,
        ];

        return $messages;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->userMessage->markAsFailed([
            'error_code' => 'JOB_FAILED',
            'error_message' => $exception->getMessage(),
            'failed_at' => now()->toIso8601String(),
        ]);

        Log::channel('chatbot')->error('Job failed permanently', [
            'user_message_id' => $this->userMessage->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

