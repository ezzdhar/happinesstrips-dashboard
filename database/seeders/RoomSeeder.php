<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\File;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();
        $amenities = Amenity::all();

        if ($hotels->isEmpty()) {
            return;
        }

        foreach ($hotels as $hotel) {
            // Create 3-5 rooms per hotel with real data
            $roomsData = $this->getRoomsForHotel($hotel);

            foreach ($roomsData as $roomData) {
                $room = Room::create($roomData);

                // Attach random amenities
                if ($amenities->isNotEmpty()) {
                    $amenityIds = $amenities->random(rand(3, 8))->pluck('id');
                    $room->amenities()->attach($amenityIds);
                }

                // Create images
                File::factory()->count(rand(3, 6))->image()->create([
                    'fileable_id' => $room->id,
                    'fileable_type' => Room::class,
                ]);
            }
        }
    }

    protected function getRoomsForHotel(Hotel $hotel): array
    {
        $today = Carbon::today();

        return [
            // غرفة مفردة
            [
                'hotel_id' => $hotel->id,
                'name' => [
                    'ar' => 'غرفة مفردة قياسية',
                    'en' => 'Standard Single Room',
                ],
                'adults_count' => 1,
                'children_count' => 0,
                'status' => Status::Active,
                'includes' => [
                    'ar' => '<ul><li>سرير مفرد مريح</li><li>واي فاي مجاني</li><li>تلفزيون بشاشة مسطحة</li><li>حمام خاص</li><li>إفطار مجاني</li></ul>',
                    'en' => '<ul><li>Comfortable single bed</li><li>Free WiFi</li><li>Flat-screen TV</li><li>Private bathroom</li><li>Free breakfast</li></ul>',
                ],
                'price_periods' => [
                    // موسم منخفض
                    [
                        'start_date' => $today->copy()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(3)->format('Y-m-d'),
                        'adult_price_egp' => 800,
                        'adult_price_usd' => 26,
                    ],
                    // موسم متوسط
                    [
                        'start_date' => $today->copy()->addMonths(3)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(6)->format('Y-m-d'),
                        'adult_price_egp' => 1000,
                        'adult_price_usd' => 32,
                    ],
                    // موسم مرتفع
                    [
                        'start_date' => $today->copy()->addMonths(6)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(9)->format('Y-m-d'),
                        'adult_price_egp' => 1200,
                        'adult_price_usd' => 39,
                    ],
                    // موسم عادي
                    [
                        'start_date' => $today->copy()->addMonths(9)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addYear()->format('Y-m-d'),
                        'adult_price_egp' => 900,
                        'adult_price_usd' => 29,
                    ],
                ],
            ],

            // غرفة مزدوجة
            [
                'hotel_id' => $hotel->id,
                'name' => [
                    'ar' => 'غرفة مزدوجة ديلوكس',
                    'en' => 'Deluxe Double Room',
                ],
                'adults_count' => 2,
                'children_count' => 1,
                'status' => Status::Active,
                'includes' => [
                    'ar' => '<ul><li>سرير مزدوج كبير</li><li>واي فاي مجاني</li><li>تلفزيون بشاشة مسطحة</li><li>حمام خاص فاخر</li><li>ميني بار</li><li>إفطار مجاني</li><li>بلكونة</li></ul>',
                    'en' => '<ul><li>King-size bed</li><li>Free WiFi</li><li>Flat-screen TV</li><li>Luxury private bathroom</li><li>Mini bar</li><li>Free breakfast</li><li>Balcony</li></ul>',
                ],
                'price_periods' => [
                    [
                        'start_date' => $today->copy()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(3)->format('Y-m-d'),
                        'adult_price_egp' => 1500,
                        'adult_price_usd' => 48,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(3)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(6)->format('Y-m-d'),
                        'adult_price_egp' => 1800,
                        'adult_price_usd' => 58,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(6)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(9)->format('Y-m-d'),
                        'adult_price_egp' => 2200,
                        'adult_price_usd' => 71,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(9)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addYear()->format('Y-m-d'),
                        'adult_price_egp' => 1700,
                        'adult_price_usd' => 55,
                    ],
                ],
            ],

            // غرفة عائلية
            [
                'hotel_id' => $hotel->id,
                'name' => [
                    'ar' => 'غرفة عائلية كبيرة',
                    'en' => 'Family Suite',
                ],
                'adults_count' => 4,
                'children_count' => 2,
                'status' => Status::Active,
                'includes' => [
                    'ar' => '<ul><li>سريرين مزدوجين</li><li>غرفة معيشة منفصلة</li><li>واي فاي مجاني</li><li>تلفزيونين</li><li>حمامين</li><li>ميني بار</li><li>إفطار مجاني للعائلة</li><li>بلكونة كبيرة</li></ul>',
                    'en' => '<ul><li>Two double beds</li><li>Separate living room</li><li>Free WiFi</li><li>Two TVs</li><li>Two bathrooms</li><li>Mini bar</li><li>Free family breakfast</li><li>Large balcony</li></ul>',
                ],
                'price_periods' => [
                    [
                        'start_date' => $today->copy()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(3)->format('Y-m-d'),
                        'adult_price_egp' => 2500,
                        'adult_price_usd' => 81,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(3)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(6)->format('Y-m-d'),
                        'adult_price_egp' => 3000,
                        'adult_price_usd' => 97,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(6)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(9)->format('Y-m-d'),
                        'adult_price_egp' => 3500,
                        'adult_price_usd' => 113,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(9)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addYear()->format('Y-m-d'),
                        'adult_price_egp' => 2800,
                        'adult_price_usd' => 90,
                    ],
                ],
            ],

            // جناح ملكي
            [
                'hotel_id' => $hotel->id,
                'name' => [
                    'ar' => 'جناح ملكي فاخر',
                    'en' => 'Royal Suite',
                ],
                'adults_count' => 2,
                'children_count' => 1,
                'status' => Status::Active,
                'includes' => [
                    'ar' => '<ul><li>سرير ملكي فاخر</li><li>غرفة معيشة كبيرة</li><li>جاكوزي</li><li>واي فاي مجاني</li><li>تلفزيون ذكي 65 بوصة</li><li>حمام فاخر</li><li>ميني بار مجاني</li><li>خدمة الغرف 24 ساعة</li><li>إفطار فاخر مجاني</li><li>بلكونة بإطلالة رائعة</li></ul>',
                    'en' => '<ul><li>Luxury king bed</li><li>Large living room</li><li>Jacuzzi</li><li>Free WiFi</li><li>65" Smart TV</li><li>Luxury bathroom</li><li>Free mini bar</li><li>24-hour room service</li><li>Free luxury breakfast</li><li>Balcony with stunning view</li></ul>',
                ],
                'price_periods' => [
                    [
                        'start_date' => $today->copy()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(3)->format('Y-m-d'),
                        'adult_price_egp' => 4000,
                        'adult_price_usd' => 129,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(3)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(6)->format('Y-m-d'),
                        'adult_price_egp' => 5000,
                        'adult_price_usd' => 161,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(6)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addMonths(9)->format('Y-m-d'),
                        'adult_price_egp' => 6000,
                        'adult_price_usd' => 194,
                    ],
                    [
                        'start_date' => $today->copy()->addMonths(9)->addDay()->format('Y-m-d'),
                        'end_date' => $today->copy()->addYear()->format('Y-m-d'),
                        'adult_price_egp' => 4500,
                        'adult_price_usd' => 145,
                    ],
                ],
            ],
        ];
    }
}
