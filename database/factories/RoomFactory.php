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
		$fakerAr = fake('ar_SA');
		$fakerEn = fake('en_US');

		// إنشاء أسعار الأسبوع (لكل يوم)
		$days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
		$weeklyPrices = [];

		foreach ($days as $day) {
			$weeklyPrices[] = [
				'day_of_week' => $day,
				'price_egp'   => $fakerEn->numberBetween(500, 5000),
				'price_usd'   => $fakerEn->numberBetween(20, 200),
			];
		}

		return [
			'hotel_id'       => Hotel::inRandomOrder()->value('id'),
			'name'           => [
				'ar' => 'غرفة ' . $fakerAr->randomElement(['مفردة', 'مزدوجة', 'ثلاثية', 'عائلية', 'جناح']),
				'en' => $fakerEn->randomElement(['Single', 'Double', 'Triple', 'Family', 'Suite']) . ' Room',
			],
			'adults_count'   => $fakerEn->numberBetween(1, 4),
			'children_count' => $fakerEn->numberBetween(0, 2),
			'weekly_prices'  => $weeklyPrices,
			'includes'       => [
				'ar' => $fakerAr->paragraphs(2, true),
				'en' => $fakerEn->paragraphs(2, true),
			],
			'status'         => $fakerEn->randomElement([Status::Active, Status::Inactive]),
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
