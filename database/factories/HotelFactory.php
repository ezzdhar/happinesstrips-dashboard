<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Permission;

class HotelFactory extends Factory
{
	protected $model = Hotel::class;

	public function definition(): array
	{
		$user = User::create([
			'name' => fake()->name(),
			'email' => fake()->unique()->safeEmail(),
			'password' => 12345678,
			'image' => FileService::fakeImage(name: 'users', folder: 'users'),
			'email_verified_at' => now(),
		])->assignRole('hotel')->givePermissionTo(Permission::role('hotel')->pluck('name')->toArray());

		return [
			'user_id' => $user->id,
			'city_id' => City::inRandomOrder()->first()->id,
			'email' => fake()->unique()->safeEmail(),
			'name' => [
				'ar' => 'فندق ' . fake('ar_SA')->company(),
				'en' => fake('en_US')->company() . ' Hotel',
			],
			'latitude' => fake()->latitude(24, 31),
			'longitude' => fake()->longitude(34, 36),
			'address' => [
				'ar' => fake('ar_SA')->address(),
				'en' => fake('en_US')->address(),
			],
			'description' => [
				'ar' => fake('ar_SA')->paragraphs(3, true),
				'en' => fake('en_US')->paragraphs(3, true),
			],
			'rating' => fake()->numberBetween(1, 5),
			'facilities' => [
				'ar' => fake('ar_SA')->paragraphs(2, true),
				'en' => fake('en_US')->paragraphs(2, true),
			],
			'phone_key' => '+20',
			'phone' => fake('ar_EG')->phoneNumber(),
			'include_services' =>[
				'ar' => fake('ar_SA')->sentence(),
				'en' => fake('en_US')->sentence(),
			],
			'status' => fake()->randomElement([Status::Active, Status::Inactive]),
		];
	}

	public function active(): static
	{
		return $this->state(fn(array $attributes) => [
			'status' => Status::Active,
		]);
	}

	public function inactive(): static
	{
		return $this->state(fn(array $attributes) => [
			'status' => Status::Inactive,
		]);
	}

	public function fiveStars(): static
	{
		return $this->state(fn(array $attributes) => [
			'rating' => 5,
		]);
	}
}

