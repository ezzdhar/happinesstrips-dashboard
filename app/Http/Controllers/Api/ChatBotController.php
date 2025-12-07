<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChatFeedbackRequest;
use App\Http\Requests\Api\ChatSendRequest;
use App\Jobs\ProcessChatMessage;
use App\Models\ChatFaq;
use App\Models\ChatFeedback;
use App\Models\ChatMessage;
use App\Services\PrismChatService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatBotController extends Controller
{
    use ApiResponse;

    public function __construct(private PrismChatService $chatService)
    {
    }

    /**
     * Send a message to the chatbot.
     */
    public function send(ChatSendRequest $request): JsonResponse
    {
        try {
            $userId = $request->user_id ?? auth()->id();
            $sync = $request->boolean('sync', true);

            // Create user message
            $userMessage = ChatMessage::create([
                'user_id' => $userId,
                'session_id' => $request->session_id,
                'role' => 'user',
                'content' => $request->message,
                'status' => 'pending',
                'meta' => [
                    'context' => $request->context ?? [],
                    'language' => $request->language ?? 'en',
                ],
            ]);

            // Get conversation history (last 10 messages)
            $history = ChatMessage::forSession($request->session_id)
                ->where('id', '<', $userMessage->id)
                ->where('status', 'sent')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->reverse()
                ->map(fn ($msg) => [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ])
                ->toArray();

            $context = array_merge(
                $request->context ?? [],
                ['language' => $request->language ?? 'en']
            );

            // Add user name to context if authenticated
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $context['user_name'] = $user->name;
                }
            }

            if ($sync) {
                // Process synchronously
                $messages = array_merge($history, [
                    ['role' => 'user', 'content' => $request->message],
                ]);

                $result = $this->chatService->chat($messages, $context);

                if ($result['success']) {
                    $assistantMessage = ChatMessage::create([
                        'user_id' => $userId,
                        'session_id' => $request->session_id,
                        'role' => 'assistant',
                        'content' => $result['data'],
                        'status' => 'sent',
                        'meta' => [
                            'model' => config('services.chatbot.model'),
                            'provider' => config('services.chatbot.provider'),
                        ],
                    ]);

                    $userMessage->markAsSent();

                    return $this->responseOk(
                        message: 'Message sent successfully',
                        data: [
                            'user_message_id' => $userMessage->id,
                            'assistant_message_id' => $assistantMessage->id,
                            'response' => $result['data'],
                        ]
                    );
                } else {
                    $userMessage->markAsFailed([
                        'error_code' => $result['error_code'],
                        'error_message' => $result['error_message'],
                    ]);

                    return $this->responseError(
                        message: 'Failed to process message',
                        data: [
                            'error_code' => $result['error_code'],
                            'error_message' => $result['error_message'],
                        ],
                        status: 502
                    );
                }
            } else {
                // Process asynchronously via queue
                ProcessChatMessage::dispatch($userMessage, $history, $context);

                return $this->responseCreated(
                    message: 'Message queued for processing',
                    data: [
                        'user_message_id' => $userMessage->id,
                        'session_id' => $request->session_id,
                        'status' => 'pending',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::channel('chatbot')->error('Chat send error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->responseError(
                message: 'An error occurred while processing your message',
                status: 500
            );
        }
    }

    /**
     * Get chat history for a user/session.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'session_id' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ChatMessage::query()
            ->with(['user:id,name,image'])
            ->where('status', 'sent')
            ->orderByDesc('created_at');

        if ($request->user_id) {
            $query->forUser($request->user_id);
        }

        if ($request->session_id) {
            $query->forSession($request->session_id);
        }

        $perPage = $request->per_page ?? 20;
        $messages = $query->paginate($perPage);

        return $this->responseOk(
            data: $messages->items(),
            paginate: true
        );
    }

    /**
     * Submit feedback for a chat message.
     */
    public function feedback(ChatFeedbackRequest $request): JsonResponse
    {
        try {
            $feedback = ChatFeedback::create([
                'chat_message_id' => $request->chat_message_id,
                'user_id' => auth()->id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            Log::channel('chatbot')->info('Feedback received', [
                'feedback_id' => $feedback->id,
                'rating' => $request->rating,
            ]);

            return $this->responseCreated(
                message: 'Feedback submitted successfully',
                data: $feedback
            );
        } catch (\Exception $e) {
            Log::channel('chatbot')->error('Feedback submission error', [
                'error' => $e->getMessage(),
            ]);

            return $this->responseError(
                message: 'Failed to submit feedback',
                status: 500
            );
        }
    }

    /**
     * Get frequently asked questions.
     */
    public function faqs(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ChatFaq::query();

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->tags) {
            $query->withTags($request->tags);
        }

        $perPage = $request->per_page ?? 20;
        $faqs = $query->popular()->paginate($perPage);

        return $this->responseOk(
            data: $faqs->items(),
            paginate: true
        );
    }

    /**
     * Get a specific FAQ and increment usage.
     */
    public function getFaq(ChatFaq $faq): JsonResponse
    {
        $faq->incrementUsage();

        return $this->responseOk(data: $faq);
    }
}

