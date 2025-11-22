<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\City;
use App\Models\File;
use App\Models\Hotel;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Trip;
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
				    'ar' => 'رحلة القاهرة والأقصر',
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
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'عرض شرم الشيخ الشاطئي',
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
				    'ar' => 'اليوم الأول: الوصول والاستقرار. اليوم الثاني: رحلة غوص. اليوم الثالث: يوم حر والعودة.',
				    'en' => 'Day 1: Arrival. Day 2: Diving trip. Day 3: Free day and departure.',
			    ],
			    'is_featured' => true,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة العمرة',
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
				    'ar' => 'يشمل الطيران والإقامة وزيارات دينية.',
				    'en' => 'Includes flights, accommodation, and religious visits.',
			    ],
			    'program' => [
				    'ar' => 'برنامج متكامل لأداء العمرة وزيارة المعالم.',
				    'en' => 'Complete Umrah program with visits.',
			    ],
			    'is_featured' => false,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة أسوان والنوبة',
				    'en' => 'Aswan & Nubia Trip - 4 Days',
			    ],
			    'price' => [
				    'egp' => 11000,
				    'usd' => 370,
			    ],
			    'duration_from' => now()->addDays(12),
			    'duration_to' => now()->addDays(16),
			    'people_count' => 3,
			    'notes' => [
				    'ar' => 'تشمل الإقامة ورحلة فلوكة وزيارة معبد فيلة.',
				    'en' => 'Includes accommodation, felucca ride, and Philae Temple visit.',
			    ],
			    'program' => [
				    'ar' => 'زيارة السد العالي – النوبة – فلوكة – فيلة.',
				    'en' => 'High Dam – Nubia – Felucca – Philae.',
			    ],
			    'is_featured' => false,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة الإسكندرية - يومين',
				    'en' => 'Alexandria Trip - 2 Days',
			    ],
			    'price' => [
				    'egp' => 3000,
				    'usd' => 110,
			    ],
			    'duration_from' => now()->addDays(5),
			    'duration_to' => now()->addDays(7),
			    'people_count' => 2,
			    'notes' => [
				    'ar' => 'تشمل زيارة مكتبة الإسكندرية وكورنيش البحر.',
				    'en' => 'Includes Library of Alexandria and Corniche.',
			    ],
			    'program' => [
				    'ar' => 'زيارة القلعة – مكتبة الإسكندرية – جولة بحر.',
				    'en' => 'Citadel – Library – Sea tour.',
			    ],
			    'is_featured' => false,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة دهب',
				    'en' => 'Dahab Trip - 4 Days',
			    ],
			    'price' => [
				    'egp' => 9000,
				    'usd' => 300,
			    ],
			    'duration_from' => now()->addDays(18),
			    'duration_to' => now()->addDays(22),
			    'people_count' => 2,
			    'notes' => [
				    'ar' => 'تشمل البلو هول ورحلة جبال.',
				    'en' => 'Includes Blue Hole and mountains tour.',
			    ],
			    'program' => [
				    'ar' => 'بلو هول – جولة بدوية – جبال.',
				    'en' => 'Blue Hole – Bedouin tour – Mountains.',
			    ],
			    'is_featured' => true,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة سيوة',
				    'en' => 'Siwa Trip - 5 Days',
			    ],
			    'price' => [
				    'egp' => 15000,
				    'usd' => 500,
			    ],
			    'duration_from' => now()->addDays(30),
			    'duration_to' => now()->addDays(35),
			    'people_count' => 4,
			    'notes' => [
				    'ar' => 'تشمل زيارة جبل الدكرور والبحيرات المالحة.',
				    'en' => 'Includes Dakrour Mountain and salt lakes.',
			    ],
			    'program' => [
				    'ar' => 'الواحة – جبل الدكرور – البحيرات – معبد آمون.',
				    'en' => 'Oasis – Dakrour – Lakes – Amun Temple.',
			    ],
			    'is_featured' => true,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة تركيا',
				    'en' => 'Turkey Trip - 6 Days',
			    ],
			    'price' => [
				    'egp' => 45000,
				    'usd' => 1500,
			    ],
			    'duration_from' => now()->addMonth()->addDays(10),
			    'duration_to' => now()->addMonth()->addDays(16),
			    'people_count' => 2,
			    'notes' => [
				    'ar' => 'تشمل الطيران والإقامة وجولات سياحية.',
				    'en' => 'Includes flights, hotel stays and tours.',
			    ],
			    'program' => [
				    'ar' => 'إسطنبول – بورصة – جولات تاريخية.',
				    'en' => 'Istanbul – Bursa – Tours.',
			    ],
			    'is_featured' => false,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة المغرب',
				    'en' => 'Morocco Trip - 7 Days',
			    ],
			    'price' => [
				    'egp' => 50000,
				    'usd' => 1650,
			    ],
			    'duration_from' => now()->addMonth()->addDays(20),
			    'duration_to' => now()->addMonth()->addDays(27),
			    'people_count' => 2,
			    'notes' => [
				    'ar' => 'تشمل مراكش والدار البيضاء.',
				    'en' => 'Includes Marrakech & Casablanca.',
			    ],
			    'program' => [
				    'ar' => 'مراكش – الدار البيضاء – الرباط.',
				    'en' => 'Marrakech – Casablanca – Rabat.',
			    ],
			    'is_featured' => false,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
		    [
			    'name' => [
				    'ar' => 'رحلة الساحل الشمالي',
				    'en' => 'North Coast Trip - 3 Days',
			    ],
			    'price' => [
				    'egp' => 7000,
				    'usd' => 230,
			    ],
			    'duration_from' => now()->addDays(7),
			    'duration_to' => now()->addDays(10),
			    'people_count' => 3,
			    'notes' => [
				    'ar' => 'تشمل إقامة فاخرة وجولة بحر.',
				    'en' => 'Includes luxury stay and sea tour.',
			    ],
			    'program' => [
				    'ar' => 'شاطئ – جولة بحر – وقت حر.',
				    'en' => 'Beach – Sea Tour – Free Time.',
			    ],
			    'is_featured' => true,
			    'status' => Status::Active,
			    'city_id' => City::inRandomOrder()->first()->id,
		    ],
	    ];


	    foreach ($trips as $tripData) {
            $mainCategory = $mainCategories->random();
            $subCategory = SubCategory::where('main_category_id', $mainCategory->id)->inRandomOrder()->first();

            if (! $subCategory) {
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
	            'city_id' => $tripData['city_id']
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
