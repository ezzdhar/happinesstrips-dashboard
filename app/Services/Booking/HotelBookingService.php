<?php

namespace App\Services\Booking;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class HotelBookingService
{
	/**
	 * @throws Exception
	 */
	public function createBooking(array $data): Booking
	{
		// 1. جلب الغرفة
		$room = Room::find($data['room_id']);

		if (!$room) {
			throw new Exception(__('lang.room_not_found'));
		}

		// 2. حساب السعر (نفس اللوجيك المستخدم لديك)
		$pricingResult = $room->calculateBookingPrice(
			checkIn: $data['check_in'],
			checkOut: $data['check_out'],
			adultsCount: $data['adults_count'],
			childrenAges: $data['children_ages'] ?? [],
			currency: $data['currency']
		);

		// التحقق من نجاح الحساب
		if (!$pricingResult['success']) {
			throw new Exception($pricingResult['error']);
		}

		// 3. بدء الترانزكشن وإنشاء البيانات
		try {
			return DB::transaction(function () use ($data, $pricingResult, $room) {
				// Create Booking
				$checkIn = Carbon::parse($data['check_in']);
				$checkOut = Carbon::parse($data['check_out']);
				$nights_count = $checkIn->diffInDays($checkOut);
				$booking = Booking::create([
					'user_id' => $data['user_id'],
					'check_in' => $data['check_in'],
					'check_out' => $data['check_out'],
					'adults_count' => $data['adults_count'],
					'children_count' => count($data['children_ages'] ?? []),
					'nights_count' => $pricingResult['nights_count'],
					'price' => $pricingResult['total_price'],
					'total_price' => $pricingResult['total_price'],
					'currency' => strtoupper($data['currency']),
					'notes' => $data['notes'] ?? null,
					'status' => $data['status'] ?? Status::Pending,
				]);

				// Create Hotel Booking Details
				BookingHotel::create([
					'booking_id' => $booking->id,
					'hotel_id' => $room->hotel_id, // نأخذه من الغرفة لضمان الدقة
					'room_id' => $room->id,
					'room_includes' => $room->includes,
					'adults_price' => $pricingResult['adults_total'],
					'children_price' => $pricingResult['children_total'],
					'children_breakdown' => $pricingResult['children_breakdown'],
					'pricing_details' => $pricingResult,
				]);

				// Create Travelers
				foreach ($data['travelers'] as $travelerData) {
					BookingTraveler::create([
						'booking_id' => $booking->id,
						'full_name' => $travelerData['full_name'],
						'phone_key' => $travelerData['phone_key'] ?? '+20',
						'phone' => $travelerData['phone'],
						'nationality' => $travelerData['nationality'],
						'age' => $travelerData['age'],
						'id_type' => $travelerData['id_type'],
						'id_number' => $travelerData['id_number'],
						'type' => $travelerData['type'],
					]);
				}

				return $booking;
			});
		} catch (Exception $e) {
			// نقوم بتسجيل الخطأ هنا، ونعيد رميه ليمسك به الكنترولر أو اللايف واير
			Log::error('Booking creation error: ' . $e->getMessage());
			throw new Exception(__('lang.error_occurred'));
		}
	}

	public function updateBooking(Booking $booking, array $data): Booking
	{
		$room = Room::find($data['room_id']);
		if (!$room) {
			throw new Exception(__('lang.room_not_found'));
		}

		// 1. حساب السعر بناءً على المعطيات الجديدة (العدد والأعمار)
		$pricingResult = $room->calculateBookingPrice(
			checkIn: $data['check_in'],
			checkOut: $data['check_out'],
			adultsCount: $data['adults_count'], // العدد الجديد
			childrenAges: $data['children_ages'] ?? [], // الأعمار الجديدة
			currency: $data['currency']
		);

		if (!$pricingResult['success']) {
			throw new Exception($pricingResult['error']);
		}

		try {
			return DB::transaction(function () use ($booking, $data, $pricingResult, $room) {

				// 2. تحديث الحجز الرئيسي
				$booking->update([
					'user_id' => $data['user_id'],
					'check_in' => $data['check_in'],
					'check_out' => $data['check_out'],
					'adults_count' => $data['adults_count'],
					'children_count' => count($data['children_ages'] ?? []),
					'nights_count' => $pricingResult['nights_count'],
					'price' => $pricingResult['total_price'],
					'total_price' => $pricingResult['total_price'],
					'currency' => strtoupper($data['currency']),
					'notes' => $data['notes'] ?? null,
				]);

				// 3. تحديث تفاصيل الفندق
				$booking->bookingHotel()->updateOrCreate(
					['booking_id' => $booking->id],
					[
						'hotel_id' => $room->hotel_id,
						'room_id' => $room->id,
						'room_includes' => $room->includes,
						'adults_price' => $pricingResult['adults_total'],
						'children_price' => $pricingResult['children_total'],
						'children_breakdown' => $pricingResult['children_breakdown'],
						'pricing_details' => $pricingResult,
					]
				);

				// 4. تحديث المسافرين (الجزئية الأصعب)
				// نقوم بحذف القديم وإعادة الإنشاء أو التحديث الذكي
				// للتبسيط وضمان تطابق البيانات مع العدد الجديد، سنستخدم التحديث الذكي

				$currentIds = []; // لتتبع من سيبقى

				foreach ($data['travelers'] as $travelerData) {
					$tData = [
						'booking_id' => $booking->id,
						'full_name' => $travelerData['full_name'],
						'phone_key' => $travelerData['phone_key'] ?? '+20',
						'phone' => $travelerData['phone'],
						'nationality' => $travelerData['nationality'],
						'age' => $travelerData['age'],
						'id_type' => $travelerData['id_type'],
						'id_number' => $travelerData['id_number'],
						'type' => $travelerData['type'],
					];

					if (isset($travelerData['id']) && $travelerData['id']) {
						// تحديث مسافر موجود
						BookingTraveler::where('id', $travelerData['id'])->update($tData);
						$currentIds[] = $travelerData['id'];
					} else {
						// إنشاء مسافر جديد (تمت إضافته في التعديل)
						$newTraveler = BookingTraveler::create($tData);
						$currentIds[] = $newTraveler->id;
					}
				}

				// حذف المسافرين الذين تم تقليل عددهم
				BookingTraveler::where('booking_id', $booking->id)
					->whereNotIn('id', $currentIds)
					->delete();

				return $booking;
			});
		} catch (\Exception $e) {
			Log::error('Update Booking Error: ' . $e->getMessage());
			throw new Exception($e->getMessage());
		}
	}

	public function createCustomBooking(array $data): Booking
	{
		// 3. بدء الترانزكشن وإنشاء البيانات
		try {
			return DB::transaction(function () use ($data) {
				// Create Booking
				$checkIn = Carbon::parse($data['check_in']);
				$checkOut = Carbon::parse($data['check_out']);
				$nights_count = $checkIn->diffInDays($checkOut);
				$booking = Booking::create([
					'user_id' => $data['user_id'],
					'check_in' => $data['check_in'],
					'check_out' => $data['check_out'],
					'adults_count' => $data['adults_count'],
					'children_count' => $data['children_count'] ?? count($data['children_ages'] ?? []),
					'nights_count' => $nights_count,
					'price' => 0,
					'total_price' => 0,
					'currency' => strtoupper($data['currency']),
					'notes' => $data['notes'] ?? null,
					'status' => $data['status'] ?? Status::Pending,
					'is_special' => true
				]);

				// Create Hotel Booking Details
				BookingHotel::create([
					'booking_id' => $booking->id,
					'hotel_id' => $data['hotel_id'],
					'room_id' => null,
					'room_includes' => null,
					'adults_price' => 0,
					'children_price' => 0,
					'children_breakdown' => [],
					'pricing_details' => 0,
				]);

				// Create Travelers
				foreach ($data['travelers'] as $travelerData) {
					BookingTraveler::create([
						'booking_id' => $booking->id,
						'full_name' => $travelerData['full_name'],
						'phone_key' => $travelerData['phone_key'] ?? '+20',
						'phone' => $travelerData['phone'],
						'nationality' => $travelerData['nationality'],
						'age' => $travelerData['age'],
						'id_type' => $travelerData['id_type'],
						'id_number' => $travelerData['id_number'],
						'type' => $travelerData['type'],
					]);
				}

				return $booking;
			});
		} catch (Exception $e) {
			// نقوم بتسجيل الخطأ هنا، ونعيد رميه ليمسك به الكنترولر أو اللايف واير
			Log::error('Booking creation error: ' . $e->getMessage());
			throw new Exception(__('lang.error_occurred'));
		}
	}

}