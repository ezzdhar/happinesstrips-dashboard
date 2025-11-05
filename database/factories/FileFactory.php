<?php

namespace Database\Factories;

use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'path' => fake()->imageUrl(640, 480, 'hotel', true),
            'type' => fake()->randomElement(['image', 'document', 'video']),
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'path' => fake()->imageUrl(640, 480),
        ]);
    }
}

