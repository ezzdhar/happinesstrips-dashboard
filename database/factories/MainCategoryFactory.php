<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\Factory;

class MainCategoryFactory extends Factory
{
    protected $model = MainCategory::class;

    public function definition(): array
    {
        return [
            'name' => [
                'ar' => fake('ar_SA')->words(3, true),
                'en' => fake('en_US')->words(3, true),
            ],
            'image' => FileService::fakeImage(name: 'image', folder: 'main_categories'),
            'status' => fake()->randomElement([Status::Active, Status::Inactive]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Inactive,
        ]);
    }
}
