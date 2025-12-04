<?php

namespace App\Http\Controllers\Api;

use App\Enums\TripType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TripRatingRequest;
use App\Http\Requests\Api\CreateTripBookingRequest;
use App\Http\Resources\BookingSimpleTripResource;
use App\Http\Resources\BookingTripResource;
use App\Models\Booking;
use App\Models\BookingRating;
use App\Models\Trip;
use App\Services\Booking\TripBookingService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TripBookingController extends Controller
{
	use ApiResponse;

	public function myBooking(Request $request)
	{
		$bookings = Booking::where('user_id', auth()->id())->where('type', 'trip')->with(['bookingTrip'])
			->when($request->status, fn(Builder $query, $status) => $query->status($status))
			->when($request->main_category_id, fn(Builder $query, $main_category) => $query->where('main_category_id', $main_category))
			->when($request->sub_category_id, fn(Builder $query, $sub_category) => $query->where('sub_category_id', $sub_category))
			->when($request->city_id, fn(Builder $query, $city) => $query->whereHas('trip', fn(Builder $q) => $q->where('city_id', $city)))
			->when($request->main_category_id, fn(Builder $query, $main_category) => $query->whereHas('trip', fn(Builder $q) => $q->where('main_category_id', $main_category)))
			->when($request->sub_category_id, fn(Builder $query, $sub_category) => $query->whereHas('trip', fn(Builder $q) => $q->where('sub_category_id', $sub_category)))
			->when($request->booking_number, fn(Builder $query, $booking_number) => $query->where('booking_number', $booking_number))
			->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.my_booking'), data: BookingSimpleTripResource::collection($bookings), paginate: true);
	}


	public function bookingDetails(Booking $booking)
	{
		if ($booking->user_id != auth()->id()) {
			return $this->responseError(message: __('lang.unauthorized'));
		}
		$booking->load(['bookingTrip', 'trip', 'user', 'travelers']);
		return $this->responseOk(message: __('lang.booking_details'), data: new BookingTripResource($booking));
	}


	public function createBooking(CreateTripBookingRequest $request, TripBookingService $tripBookingService)
	{
		$trip = Trip::find($request->trip_id);
		try {
			$data = $request->validated();
			$data['currency'] = $request->attributes->get('currency', 'egp');
			$data['user_id'] = auth()->id();
			if ($trip->type->value === TripType::Fixed) {
				$data['check_in'] = $trip->duration_from;
				$data['check_out'] = $trip->duration_to;
			}
			$tripBookingService->createBooking($data);
			return $this->responseCreated(message: __('lang.created_successfully', ['attribute' => __('lang.booking')]));
		} catch (\Exception $e) {
			return $this->responseError(message: $e->getMessage());
		}
	}

	public function rating(TripRatingRequest $request)
	{
		$booking = Booking::with('trip')->findOrFail($request->booking_id);
		$trip = $booking->trip;

		BookingRating::updateOrCreate(['booking_id' => $booking->id, 'user_id' => auth()->id()],
			[
				'rating' => $request->rating,
			]
		);

		$avgRating = BookingRating::whereHas('booking', function ($q) use ($trip) {
			$q->where('trip_id', $trip->id);
		})->avg('rating');

		$trip->update(['rating' => round($avgRating, 1)]);
		return $this->responseOk(message: __('lang.rating_updated'));
	}
}
