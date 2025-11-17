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

        // إنشاء فترات سعرية عشوائية
        $pricePeriods = [];
        $currentDate = now();

        // إنشاء 3-5 فترات سعرية
        $periodsCount = $fakerEn->numberBetween(3, 5);

        for ($i = 0; $i < $periodsCount; $i++) {
            $startDate = $currentDate->copy()->addDays($i * 30);
            $endDate = $startDate->copy()->addDays($fakerEn->numberBetween(20, 40));

            $pricePeriods[] = [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'adult_price_egp' => $fakerEn->numberBetween(500, 5000),
                'adult_price_usd' => $fakerEn->numberBetween(20, 200),
            ];

            $currentDate = $endDate->copy()->addDay();
        }

        return [
            'hotel_id' => Hotel::inRandomOrder()->value('id'),
            'name' => [
                'ar' => 'غرفة '.$fakerAr->randomElement(['مفردة', 'مزدوجة', 'ثلاثية', 'عائلية', 'جناح']),
                'en' => $fakerEn->randomElement(['Single', 'Double', 'Triple', 'Family', 'Suite']).' Room',
            ],
            'adults_count' => $fakerEn->numberBetween(1, 4),
            'children_count' => $fakerEn->numberBetween(0, 2),
            'price_periods' => $pricePeriods,
            'includes' => [
                'ar' => $fakerAr->paragraphs(2, true),
                'en' => $fakerEn->paragraphs(2, true),
            ],
            'status' => $fakerEn->randomElement([Status::Active, Status::Inactive]),
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
