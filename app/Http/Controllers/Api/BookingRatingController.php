<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TripRatingRequest;
use App\Models\Booking;
use App\Models\BookingRating;
use App\Traits\ApiResponse;

class BookingRatingController extends Controller
{
	use ApiResponse;

	public function __invoke(TripRatingRequest $request)
	{
		$booking = Booking::with(['trip', 'bookingHotel.hotel'])->findOrFail($request->booking_id);

		// تخزين/تحديث تقييم اليوزر على الحجز
		BookingRating::updateOrCreate([
				'booking_id' => $booking->id,
				'user_id' => auth()->id(),
			],
			[
				'rating' => $request->rating,
			]
		);

		// لو النوع رحلة
		if ($booking->type === 'trip' && $booking->trip) {
			$trip = $booking->trip;

			$avgRating = BookingRating::whereHas('booking', function ($q) use ($trip) {
				$q->where('trip_id', $trip->id);
			})->avg('rating');

			$trip->update([
				'rating' => $avgRating ? round($avgRating, 1) : 0,
			]);

		} else {
			// النوع فندق
			$hotel = optional($booking->bookingHotel)->hotel;

			if ($hotel) {
				$avgRating = BookingRating::whereHas('booking.bookingHotel', function ($q) use ($hotel) {
					$q->where('hotel_id', $hotel->id);
				})->avg('rating');

				$hotel->update([
					'rating' => $avgRating ? round($avgRating, 1) : 0,
				]);
			}
		}

		return $this->responseOk(message: __('lang.rating_updated'));
	}

}
