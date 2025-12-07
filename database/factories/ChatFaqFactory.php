<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChatFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFaqFactory extends Factory
{
    protected $model = ChatFaq::class;

    public function definition(): array
    {
        return [
            'question' => fake()->sentence() . '?',
            'answer' => fake()->paragraph(),
            'tags' => fake()->randomElements(['booking', 'trips', 'hotels', 'payments', 'account'], 2),
            'usage_count' => fake()->numberBetween(0, 100),
        ];
    }
}

