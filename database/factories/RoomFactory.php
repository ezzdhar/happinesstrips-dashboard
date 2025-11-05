<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::inRandomOrder()->first()->id,
            'name' => [
                'ar' => 'غرفة ' . fake('ar_SA')->randomElement(['مفردة', 'مزدوجة', 'ثلاثية', 'عائلية', 'جناح']),
                'en' => fake('en_US')->randomElement(['Single', 'Double', 'Triple', 'Family', 'Suite']) . ' Room',
            ],
            'adults_count' => fake()->numberBetween(1, 4),
            'children_count' => fake()->numberBetween(0, 2),
            'price' => [
                'egp' => fake()->numberBetween(500, 5000),
                'usd' => fake()->numberBetween(20, 200),
            ],
            'includes' => [
                'ar' => fake('ar_SA')->paragraphs(2, true),
                'en' => fake('en_US')->paragraphs(2, true),
            ],
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

