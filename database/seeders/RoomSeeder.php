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
			],
			// غرفة ثلاثية
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة ثلاثية مريحة',
					'en' => 'Comfort Triple Room',
				],
				'adults_count' => 3,
				'children_count' => 1,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>ثلاثة أسرّة مريحة</li><li>واي فاي مجاني</li><li>تلفزيون</li><li>حمام خاص</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>Three comfortable beds</li><li>Free WiFi</li><li>TV</li><li>Private bathroom</li><li>Free breakfast</li></ul>',
				],
			],
			// غرفة تنفيذية
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة تنفيذية',
					'en' => 'Executive Room',
				],
				'adults_count' => 2,
				'children_count' => 1,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سرير كبير</li><li>مكتب عمل</li><li>واي فاي سريع</li><li>ميني بار</li><li>خدمة غرف</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>Large bed</li><li>Work desk</li><li>High-speed WiFi</li><li>Mini bar</li><li>Room service</li><li>Free breakfast</li></ul>',
				],
			],

			// جناح جونيور
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'جناح جونيور',
					'en' => 'Junior Suite',
				],
				'adults_count' => 2,
				'children_count' => 1,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سرير كينج</li><li>منطقة جلوس</li><li>واي فاي مجاني</li><li>ميني بار</li><li>حمام فاخر</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>King bed</li><li>Sitting area</li><li>Free WiFi</li><li>Mini bar</li><li>Luxury bathroom</li><li>Free breakfast</li></ul>',
				],
			],
			// غرفة اقتصادية
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة اقتصادية',
					'en' => 'Economy Room',
				],
				'adults_count' => 1,
				'children_count' => 0,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سرير فردي</li><li>واي فاي</li><li>حمام خاص</li></ul>',
					'en' => '<ul><li>Single bed</li><li>WiFi</li><li>Private bathroom</li></ul>',
				],
			],
			// فيلا خاصة
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'فيلا خاصة مع مسبح',
					'en' => 'Private Pool Villa',
				],
				'adults_count' => 4,
				'children_count' => 3,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>مسبح خاص</li><li>غرفتين نوم</li><li>غرفة معيشة</li><li>مطبخ صغير</li><li>واي فاي</li><li>إفطار مجاني</li><li>حديقة خاصة</li></ul>',
					'en' => '<ul><li>Private pool</li><li>Two bedrooms</li><li>Living room</li><li>Kitchenette</li><li>WiFi</li><li>Free breakfast</li><li>Private garden</li></ul>',
				],
			],
			// جناح بانورامي
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'جناح بانورامي بإطلالة',
					'en' => 'Panoramic View Suite',
				],
				'adults_count' => 2,
				'children_count' => 1,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سرير كينج</li><li>إطلالة بانورامية</li><li>منطقة جلوس</li><li>واي فاي مجاني</li><li>ميني بار</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>King bed</li><li>Panoramic view</li><li>Sitting area</li><li>Free WiFi</li><li>Mini bar</li><li>Free breakfast</li></ul>',
				],
			],
			// غرفة ديلوكس مطلة على البحر
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة ديلوكس مطلة على البحر',
					'en' => 'Deluxe Sea View Room',
				],
				'adults_count' => 2,
				'children_count' => 1,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سرير مزدوج</li><li>إطلالة على البحر</li><li>واي فاي</li><li>بلكونة</li><li>تكييف</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>Double bed</li><li>Sea view</li><li>WiFi</li><li>Balcony</li><li>Air conditioning</li><li>Free breakfast</li></ul>',
				],
			],
			// غرفة توأم
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة توأم',
					'en' => 'Twin Room',
				],
				'adults_count' => 2,
				'children_count' => 0,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>سريران منفصلان</li><li>واي فاي</li><li>تلفزيون</li><li>حمام خاص</li></ul>',
					'en' => '<ul><li>Two single beds</li><li>WiFi</li><li>TV</li><li>Private bathroom</li></ul>',
				],
			],
			// بنتهاوس فاخر
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'بنتهاوس فاخر',
					'en' => 'Luxury Penthouse',
				],
				'adults_count' => 4,
				'children_count' => 2,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>إطلالة عالية</li><li>غرفتي نوم</li><li>مطبخ كامل</li><li>غرفة معيشة</li><li>ميني بار</li><li>إفطار مجاني</li></ul>',
					'en' => '<ul><li>High panoramic view</li><li>Two bedrooms</li><li>Full kitchen</li><li>Living room</li><li>Mini bar</li><li>Free breakfast</li></ul>',
				],
			],
			// غرفة استوديو
			[
				'hotel_id' => $hotel->id,
				'name' => [
					'ar' => 'غرفة استوديو',
					'en' => 'Studio Room',
				],
				'adults_count' => 2,
				'children_count' => 0,
				'status' => Status::Active,
				'includes' => [
					'ar' => '<ul><li>مساحة مفتوحة</li><li>سرير كبير</li><li>مطبخ صغير</li><li>واي فاي</li><li>حمام خاص</li></ul>',
					'en' => '<ul><li>Open space</li><li>Large bed</li><li>Kitchenette</li><li>WiFi</li><li>Private bathroom</li></ul>',
				],
			],
		];
	}
}
