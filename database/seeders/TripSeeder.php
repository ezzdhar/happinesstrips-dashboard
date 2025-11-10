<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Trip;
use App\Models\Hotel;
use App\Models\File;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $mainCategories = MainCategory::all();
        $hotels = Hotel::all();

        if ($mainCategories->isEmpty() || $hotels->isEmpty()) {
            $this->command->error('No main categories or hotels found. Please run their seeders first.');
            return;
        }

        $trips = [
            [
                'name' => [
                    'ar' => 'رحلة القاهرة والأقصر - 5 أيام',
                    'en' => 'Cairo and Luxor Trip - 5 Days',
                ],
                'price' => [
                    'egp' => 12000,
                    'usd' => 400,
                ],
                'duration_from' => now()->addDays(10),
                'duration_to' => now()->addDays(15),
                'people_count' => 4,
                'notes' => [
                    'ar' => 'الأسعار شاملة الإقامة والمواصلات. غير شاملة للوجبات والأنشطة الإضافية.',
                    'en' => 'Prices include accommodation and transportation. Meals and additional activities are not included.',
                ],
                'program' => [
                    'ar' => 'اليوم الأول: الوصول والتوجه للفندق. اليوم الثاني: زيارة الأهرامات والمتحف المصري. اليوم الثالث: السفر للأقصر. اليوم الرابع: زيارة معابد الكرنك والأقصر. اليوم الخامس: العودة للقاهرة.',
                    'en' => 'Day 1: Arrival and hotel check-in. Day 2: Visit Pyramids and Egyptian Museum. Day 3: Travel to Luxor. Day 4: Visit Karnak and Luxor Temples. Day 5: Return to Cairo.',
                ],
                'is_featured' => true,
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'عرض شرم الشيخ الشاطئي - 3 أيام',
                    'en' => 'Sharm El Sheikh Beach Package - 3 Days',
                ],
                'price' => [
                    'egp' => 8000,
                    'usd' => 260,
                ],
                'duration_from' => now()->addDays(20),
                'duration_to' => now()->addDays(23),
                'people_count' => 2,
                'notes' => [
                    'ar' => 'العرض يشمل الإقامة في منتجع 5 نجوم مع نظام all inclusive.',
                    'en' => 'Package includes stay at 5-star resort with all inclusive system.',
                ],
                'program' => [
                    'ar' => 'اليوم الأول: الوصول والاستقرار في المنتجع. اليوم الثاني: رحلة غوص أو سنوركلينج. اليوم الثالث: يوم حر والعودة.',
                    'en' => 'Day 1: Arrival and resort check-in. Day 2: Diving or snorkeling trip. Day 3: Free day and departure.',
                ],
                'is_featured' => true,
                'status' => Status::Active,
            ],
            [
                'name' => [
                    'ar' => 'رحلة العمرة - 7 أيام',
                    'en' => 'Umrah Trip - 7 Days',
                ],
                'price' => [
                    'egp' => 25000,
                    'usd' => 850,
                ],
                'duration_from' => now()->addMonth(),
                'duration_to' => now()->addMonth()->addDays(7),
                'people_count' => 1,
                'notes' => [
                    'ar' => 'يشمل تذكرة الطيران، الإقامة في فندق قريب من الحرم، المواصلات، وزيارة المعالم الدينية.',
                    'en' => 'Includes flight ticket, accommodation in hotel near Haram, transportation, and religious sites visits.',
                ],
                'program' => [
                    'ar' => 'برنامج شامل لأداء العمرة مع زيارة المعالم الإسلامية في مكة والمدينة المنورة.',
                    'en' => 'Comprehensive program for performing Umrah with visits to Islamic landmarks in Mecca and Medina.',
                ],
                'is_featured' => false,
                'status' => Status::Active,
            ],
        ];

        foreach ($trips as $tripData) {
            $mainCategory = $mainCategories->random();
            $subCategory = SubCategory::where('main_category_id', $mainCategory->id)->inRandomOrder()->first();

            if (!$subCategory) {
                $subCategory = SubCategory::factory()->create(['main_category_id' => $mainCategory->id]);
            }

            $trip = Trip::create([
                'main_category_id' => $mainCategory->id,
                'sub_category_id' => $subCategory->id,
                'name' => $tripData['name'],
                'price' => $tripData['price'],
                'duration_from' => $tripData['duration_from'],
                'duration_to' => $tripData['duration_to'],
                'people_count' => $tripData['people_count'],
                'notes' => $tripData['notes'],
                'program' => $tripData['program'],
                'is_featured' => $tripData['is_featured'],
                'status' => $tripData['status'],
            ]);

            // Attach random hotels to the trip
            $trip->hotels()->attach(
                $hotels->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Create 3-6 images for each trip
            File::factory()
                ->count(rand(3, 6))
                ->image()
                ->create([
                    'fileable_id' => $trip->id,
                    'fileable_type' => Trip::class,
                ]);
        }

        // Create additional random trips
//        Trip::factory()
//            ->count(20)
//            ->create()
//            ->each(function ($trip) use ($hotels) {
//                // Attach random hotels
//                $trip->hotels()->attach(
//                    $hotels->random(rand(1, 4))->pluck('id')->toArray()
//                );
//
//                // Create images
//                File::factory()
//                    ->count(rand(3, 8))
//                    ->image()
//                    ->create([
//                        'fileable_id' => $trip->id,
//                        'fileable_type' => Trip::class,
//                    ]);
//            });
    }
}

