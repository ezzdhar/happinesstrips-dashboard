<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'role' => fake()->randomElement(['user', 'assistant', 'system']),
            'content' => fake()->paragraph(),
            'meta' => null,
            'status' => 'sent',
            'external_id' => null,
        ];
    }

    /**
     * Indicate that the message is from a user.
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }

    /**
     * Indicate that the message is from an assistant.
     */
    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
        ]);
    }

    /**
     * Indicate that the message is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the message has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'meta' => [
                'error' => [
                    'code' => 'TEST_ERROR',
                    'message' => 'Test error message',
                ],
            ],
        ]);
    }
}

