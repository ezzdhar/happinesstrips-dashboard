<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => User::factory(),
            'trip_id' => Trip::factory(),
            'check_in' => fake()->dateTimeBetween('now', '+1 month'),
            'check_out' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'nights_count' => fake()->numberBetween(3, 14),
            'adults_count' => fake()->numberBetween(1, 4),
            'children_count' => fake()->numberBetween(0, 2),
            'price' => fake()->numberBetween(5000, 20000),
            'total_price' => fake()->numberBetween(8000, 30000),
            'currency' => fake()->randomElement(['egp', 'usd']),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement([Status::Pending, Status::UnderPayment, Status::UnderCancellation, Status::Cancelled, Status::Completed]),
        ];
    }

    public function flexible(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in' => fake()->dateTimeBetween('now', '+1 month'),
            'check_out' => fake()->dateTimeBetween('+1 month', '+2 months'),
            'nights_count' => fake()->numberBetween(3, 14),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Pending,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Confirmed,
        ]);
    }
}
