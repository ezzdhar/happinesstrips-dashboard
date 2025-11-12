<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubCategoryFactory extends Factory
{
    protected $model = SubCategory::class;

    public function definition(): array
    {
        return [
            'main_category_id' => MainCategory::factory(),
            'name' => [
                'ar' => fake('ar_SA')->words(2, true),
                'en' => fake('en_US')->words(2, true),
            ],
            'image' => FileService::fakeImage(name: 'image', folder: 'sub_categories'),
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
