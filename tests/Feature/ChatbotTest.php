<?php

use App\Services\ChatbotService;

test('chatbot endpoint accepts valid message', function () {
    $response = $this->postJson('/api/chatbot/chat', [
        'message' => 'مرحباً، أريد حجز فندق',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'message',
                'api_calls',
                'api_results',
                'suggested_actions',
            ],
            'meta',
        ]);
});


test('chatbot endpoint requires message field', function () {
    $response = $this->postJson('/api/chatbot/chat', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('chatbot endpoint validates message max length', function () {
    $longMessage = str_repeat('أ', 1001);

    $response = $this->postJson('/api/chatbot/chat', [
        'message' => $longMessage,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('chatbot accepts conversation history', function () {
    $response = $this->postJson('/api/chatbot/chat', [
        'message' => 'وماذا عن السعر؟',
        'conversation_history' => [
            [
                'role' => 'user',
                'content' => 'أريد فندق في القاهرة',
            ],
            [
                'role' => 'assistant',
                'content' => 'يوجد لدينا عدة فنادق في القاهرة',
            ],
        ],
    ]);

    $response->assertSuccessful();
});

test('chatbot capabilities endpoint returns available info', function () {
    $response = $this->get('/api/chatbot/capabilities');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'name',
                'version',
                'description',
                'capabilities',
                'available_apis' => [
                    'hotels',
                    'rooms',
                    'trips',
                    'data',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Happiness Trips Chatbot',
            ],
        ]);
});

test('chatbot service can process message', function () {
    $service = app(ChatbotService::class);

    $result = $service->processMessage('مرحباً');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('success')
        ->and($result)->toHaveKey('message');
});

test('chatbot service handles conversation history', function () {
    $service = app(ChatbotService::class);

    $history = [
        [
            'role' => 'user',
            'content' => 'مرحباً',
        ],
    ];

    $result = $service->processMessage('كيف حالك؟', $history);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue();
});
