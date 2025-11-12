<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Enums\TripType;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        $durationFrom = fake()->dateTimeBetween('now', '+1 month');
        $durationTo = fake()->dateTimeBetween($durationFrom, '+3 months');

        return [
            'main_category_id' => MainCategory::factory(),
            'sub_category_id' => SubCategory::factory(),
            'name' => [
                'ar' => 'عرض '.fake('ar_SA')->words(3, true),
                'en' => fake('en_US')->words(3, true).' Package',
            ],
            'price' => [
                'egp' => fake()->numberBetween(3000, 50000),
                'usd' => fake()->numberBetween(100, 2000),
            ],
            'duration_from' => $durationFrom,
            'duration_to' => $durationTo,
            'people_count' => fake()->numberBetween(1, 10),
            'type' => fake()->randomElement([TripType::Fixed, TripType::Flexible]),
            'notes' => [
                'ar' => fake('ar_SA')->paragraphs(2, true),
                'en' => fake('en_US')->paragraphs(2, true),
            ],
            'program' => [
                'ar' => fake('ar_SA')->paragraphs(5, true),
                'en' => fake('en_US')->paragraphs(5, true),
            ],
            'is_featured' => fake()->boolean(30),
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

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
