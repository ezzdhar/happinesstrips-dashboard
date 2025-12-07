<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\User;
use App\Services\PrismChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can send a message to chatbot synchronously', function () {
    // Mock the PrismChatService
    $mockService = mock(PrismChatService::class);
    $mockService->shouldReceive('chat')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => 'Hello! How can I help you today?',
            'error_code' => null,
            'error_message' => null,
        ]);

    $this->app->instance(PrismChatService::class, $mockService);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send?sync=true', [
            'message' => 'Hello, I need help with booking',
            'session_id' => 'test-session-123',
            'language' => 'en',
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Message sent successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'user_message_id',
                'assistant_message_id',
                'response',
            ],
        ]);

    expect(ChatMessage::count())->toBe(2);
    expect(ChatMessage::where('role', 'user')->count())->toBe(1);
    expect(ChatMessage::where('role', 'assistant')->count())->toBe(1);
});

test('user can send a message to chatbot asynchronously', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send', [
            'message' => 'What hotels are available?',
            'session_id' => 'test-session-456',
            'language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'status' => 201,
            'message' => 'Message queued for processing',
        ])
        ->assertJsonStructure([
            'data' => [
                'user_message_id',
                'session_id',
                'status',
            ],
        ]);

    expect(ChatMessage::where('status', 'pending')->count())->toBe(1);
});

test('message validation fails with invalid data', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send', [
            'message' => '',
            'session_id' => '',
        ]);

    $response->assertUnprocessable();
});

test('message validation fails when message is too long', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send', [
            'message' => str_repeat('a', 4001),
            'session_id' => 'test-session',
        ]);

    $response->assertUnprocessable();
});

test('user can send message with context', function () {
    $mockService = mock(PrismChatService::class);
    $mockService->shouldReceive('chat')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => 'Your booking details show...',
            'error_code' => null,
            'error_message' => null,
        ]);

    $this->app->instance(PrismChatService::class, $mockService);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send?sync=true', [
            'message' => 'Tell me about my booking',
            'session_id' => 'test-session-789',
            'context' => [
                'booking_id' => 123,
            ],
            'language' => 'en',
        ]);

    $response->assertSuccessful();
});

test('sync message returns error when service fails', function () {
    $mockService = mock(PrismChatService::class);
    $mockService->shouldReceive('chat')
        ->once()
        ->andReturn([
            'success' => false,
            'data' => null,
            'error_code' => 'SERVICE_ERROR',
            'error_message' => 'AI service is unavailable',
        ]);

    $this->app->instance(PrismChatService::class, $mockService);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/chat/send?sync=true', [
            'message' => 'Hello',
            'session_id' => 'test-session-error',
        ]);

    $response->assertStatus(502)
        ->assertJson([
            'success' => false,
        ]);

    expect(ChatMessage::where('status', 'failed')->count())->toBe(1);
});

test('unauthenticated user cannot send messages', function () {
    $response = $this->postJson('/api/chat/send', [
        'message' => 'Hello',
        'session_id' => 'test-session',
    ]);

    $response->assertUnauthorized();
});

test('rate limiting is applied to chat send endpoint', function () {
    // Send 61 requests (exceeds 60 per minute limit)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/chat/send', [
                'message' => "Message {$i}",
                'session_id' => 'test-session-rate-limit',
            ]);

        if ($i < 60) {
            $response->assertStatus(201);
        } else {
            $response->assertStatus(429); // Too Many Requests
        }
    }
});

