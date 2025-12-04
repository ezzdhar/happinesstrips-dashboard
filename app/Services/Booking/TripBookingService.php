<?php

namespace App\Services\Booking;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\BookingTrip;
use App\Models\Room;
use App\Models\Trip;
use App\Services\TripPricingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TripBookingService
{
	/**
	 * @throws Exception
	 */
	public function createBooking(array $data): Booking
	{
		$trip = Trip::find($data['trip_id']);

		$pricingResult = TripPricingService::calculateTripPriceWithAges(
			trip: $trip,
			checkIn: $data['check_in'],
			checkOut: $data['check_out'],
			adultsCount: (int)$data['adults_count'],
			childrenAges: $data['children_ages'],
			currency: $data['currency']
		);

		// التحقق من نجاح الحساب
//		if (!$pricingResult['success']) {
//			throw new Exception($pricingResult['error']);
//		}

		// 3. بدء الترانزكشن وإنشاء البيانات
		try {
			return DB::transaction(function () use ($data, $pricingResult, $trip) {
				// Create Booking
				$booking = Booking::create([
					'user_id' => $data['user_id'],
					'check_in' => $data['check_in'],
					'check_out' => $data['check_out'],
					'adults_count' => $data['adults_count'],
					'children_count' => $data['children_count'] ?? count($data['children_ages'] ?? []),
					'nights_count' => $pricingResult['nights_count'],
					'price' => $pricingResult['sub_total'],
					'total_price' => $pricingResult['total_price'],
					'currency' => strtoupper($data['currency']),
					'notes' => $data['notes'] ?? null,
					'trip_id' => $trip->id,
					'status' => $data['status'] ?? Status::Pending,
					'type' => 'trip',
				]);

				// Create trip booking with pricing details
				BookingTrip::create([
					'booking_id' => $booking->id,
					'trip_id' => $trip->id,
					'adults_price' => $pricingResult['adults_price'] ?? 0,
					'children_price' => $pricingResult['children_breakdown']['total_children_price'] ?? 0,
					'children_breakdown' => $pricingResult['children_breakdown'] ?? null,
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


}