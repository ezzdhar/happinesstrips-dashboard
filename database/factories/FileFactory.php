<?php

namespace Database\Factories;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'path' => FileService::fakeImage(name: 'name' ,folder: 'files'),
            'type' => fake()->randomElement(['image', 'document', 'video']),
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'path' => FileService::fakeImage(name: 'name' ,folder: 'files'),
        ]);
    }
}

