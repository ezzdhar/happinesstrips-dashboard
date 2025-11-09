<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingTraveler>
 */
class BookingTravelerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'full_name' => fake()->name(),
            'phone_key' => '+20',
            'phone' => fake()->numerify('##########'),
            'nationality' => fake()->country(),
            'age' => fake()->numberBetween(18, 70),
            'id_type' => fake()->randomElement(['passport', 'national_id']),
            'id_number' => fake()->numerify('##########'),
            'type' => 'adult',
        ];
    }

    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'age' => fake()->numberBetween(1, 17),
            'type' => 'child',
        ]);
    }
}
