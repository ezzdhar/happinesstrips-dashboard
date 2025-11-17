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

        // إنشاء فترات سعرية واقعية
        $pricePeriods = [];
        $currentDate = now();

        // موسم منخفض
        $pricePeriods[] = [
            'start_date' => $currentDate->copy()->format('Y-m-d'),
            'end_date' => $currentDate->copy()->addMonths(3)->format('Y-m-d'),
            'adult_price_egp' => $fakerEn->numberBetween(800, 1500),
            'adult_price_usd' => $fakerEn->numberBetween(26, 48),
        ];

        // موسم متوسط
        $pricePeriods[] = [
            'start_date' => $currentDate->copy()->addMonths(3)->addDay()->format('Y-m-d'),
            'end_date' => $currentDate->copy()->addMonths(6)->format('Y-m-d'),
            'adult_price_egp' => $fakerEn->numberBetween(1200, 2000),
            'adult_price_usd' => $fakerEn->numberBetween(39, 65),
        ];

        // موسم مرتفع
        $pricePeriods[] = [
            'start_date' => $currentDate->copy()->addMonths(6)->addDay()->format('Y-m-d'),
            'end_date' => $currentDate->copy()->addMonths(9)->format('Y-m-d'),
            'adult_price_egp' => $fakerEn->numberBetween(1800, 3500),
            'adult_price_usd' => $fakerEn->numberBetween(58, 113),
        ];

        // موسم عادي
        $pricePeriods[] = [
            'start_date' => $currentDate->copy()->addMonths(9)->addDay()->format('Y-m-d'),
            'end_date' => $currentDate->copy()->addYear()->format('Y-m-d'),
            'adult_price_egp' => $fakerEn->numberBetween(1000, 1800),
            'adult_price_usd' => $fakerEn->numberBetween(32, 58),
        ];

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
