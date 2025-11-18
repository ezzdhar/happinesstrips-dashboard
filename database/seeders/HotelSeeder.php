<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\City;
use App\Models\File;
use App\Models\Hotel;
use App\Models\HotelType;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
	public function run(): void
	{
		$users = User::role('hotel')->get();
		$cities = City::get();

		$hotels = [
			[
				'user_id' => $users->random()->id,
				'city_id' => $cities->random()->id,
				'email' => fake()->unique()->safeEmail(),
				'name' => [
					'ar' => 'فندق الأهرامات الذهبية',
					'en' => 'Golden Pyramids Hotel',
				],
				'latitude' => 29.9773,
				'longitude' => 31.1325,
				'address' => [
					'ar' => 'القاهرة، مصر',
					'en' => 'Cairo, Egypt',
				],
				'description' => [
					'ar' => 'فندق فاخر بإطلالة رائعة على الأهرامات مع خدمات متميزة وغرف مجهزة بأحدث التقنيات.',
					'en' => 'Luxury hotel with stunning views of the pyramids, excellent services, and rooms equipped with the latest technology.',
				],
				'rating' => 5,
				'facilities' => [
					'ar' => 'مسبح، مطعم، واي فاي مجاني، مواقف سيارات، صالة ألعاب رياضية',
					'en' => 'Swimming pool, Restaurant, Free WiFi, Parking, Gym',
				],
				'phone_key' => '+20',
				'phone' => '0123456789',
				'status' => Status::Active,
				// Children Policy
				'free_child_age' => 4,
				'adult_age' => 12,
				'first_child_price_percentage' => 50,
				'second_child_price_percentage' => 30,
				'third_child_price_percentage' => 20,
				'additional_child_price_percentage' => 10,
			],
			[
				'user_id' => $users->random()->id,
				'city_id' => $cities->random()->id,
				'email' => fake()->unique()->safeEmail(),
				'name' => [
					'ar' => 'منتجع البحر الأحمر',
					'en' => 'Red Sea Resort',
				],
				'latitude' => 27.2579,
				'longitude' => 33.8116,
				'phone_key' => '+20',
				'phone' => '0123456788',
				'status' => Status::Active,
				'address' => [
					'ar' => 'شرم الشيخ، مصر',
					'en' => 'Sharm El Sheikh, Egypt',
				],
				'description' => [
					'ar' => 'منتجع شاطئي راقي مع شاطئ خاص وأنشطة مائية متنوعة وإطلالة ساحرة على البحر الأحمر.',
					'en' => 'Upscale beach resort with private beach, various water activities, and charming views of the Red Sea.',
				],
				'rating' => 5,
				'facilities' => [
					'ar' => 'شاطئ خاص، غوص، سنوركلينج، سبا، مطاعم متعددة',
					'en' => 'Private beach, Diving, Snorkeling, Spa, Multiple restaurants',
				],
				// Children Policy
				'free_child_age' => 3,
				'adult_age' => 12,
				'first_child_price_percentage' => 40,
				'second_child_price_percentage' => 25,
				'third_child_price_percentage' => 15,
				'additional_child_price_percentage' => 10,
			],
			[
				'user_id' => $users->random()->id,
				'city_id' => $cities->random()->id,
				'email' => fake()->unique()->safeEmail(),
				'name' => [
					'ar' => 'فندق الأقصر الملكي',
					'en' => 'Luxor Royal Hotel',
				],
				'latitude' => 25.6872,
				'longitude' => 32.6396,
				'phone_key' => '+20',
				'phone' => '0123456787',
				'status' => Status::Active,
				'address' => [
					'ar' => 'الأقصر، مصر',
					'en' => 'Luxor, Egypt',
				],
				'description' => [
					'ar' => 'فندق تاريخي يقع في قلب الأقصر بالقرب من المعابد الشهيرة مع خدمات راقية.',
					'en' => 'Historic hotel located in the heart of Luxor near famous temples with premium services.',
				],
				'rating' => 4,
				'facilities' => [
					'ar' => 'إطلالة على النيل، مطعم، بار، حمام سباحة على السطح',
					'en' => 'Nile view, Restaurant, Bar, Rooftop pool',
				],
				// Children Policy
				'free_child_age' => 5,
				'adult_age' => 14,
				'first_child_price_percentage' => 60,
				'second_child_price_percentage' => 40,
				'third_child_price_percentage' => 25,
				'additional_child_price_percentage' => 15,
			],
		];

		foreach ($hotels as $hotelData) {
			$hotel = Hotel::create($hotelData);

			// Create 3-5 images for each hotel
			File::factory()
				->count(rand(3, 5))
				->image()
				->create([
					'fileable_id' => $hotel->id,
					'fileable_type' => Hotel::class,
				]);
			//hotelTypes
			$hotelTypes = HotelType::inRandomOrder()->take(rand(1, 3))->pluck('id');
			$hotel->hotelTypes()->attach($hotelTypes);
		}

		// Create additional random hotels with files
		//		Hotel::factory()
		//			->count(15)
		//			->create()
		//			->each(function ($hotel) {
		//				File::factory()
		//					->count(rand(2, 6))
		//					->image()
		//					->create([
		//						'fileable_id' => $hotel->id,
		//						'fileable_type' => Hotel::class,
		//					]);
		//			});
	}
}
