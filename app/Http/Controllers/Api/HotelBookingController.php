<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateRoomBookingRequest;
use App\Http\Requests\Api\CreateRoomCustomBookingRequest;
use App\Http\Resources\BookingHotelResource;
use App\Http\Resources\BookingSimpleHotelResource;
use App\Http\Resources\HotelSimpleResource;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Services\Booking\HotelBookingService;

class HotelBookingController extends Controller
{
	use ApiResponse;

	public function myBooking(Request $request)
	{
		$bookings = Booking::where('user_id', auth()->id())->where('type', 'hotel')->with(['bookingHotel'])
			->when($request->status, fn(Builder $query, $status) => $query->status($status))
			->when($request->is_special, fn(Builder $query, $is_special) => $query->where('is_special', 1))
			->when($request->city_id, fn(Builder $query, $city) => $query->whereHas('bookingHotel.hotel', fn(Builder $q) => $q->where('city_id', $city)))
			->when($request->booking_number, fn(Builder $query, $booking_number) => $query->where('booking_number', $booking_number))
			->latest()
			->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.my_booking'), data: BookingSimpleHotelResource::collection($bookings), paginate: true);
	}

	public function bookingDetails(Booking $booking)
	{
		if ($booking->user_id != auth()->id()) {
			return $this->responseError(message: __('lang.unauthorized'));
		}
		$booking->load(['bookingHotel.hotel', 'bookingHotel.room', 'user', 'travelers']);
		return $this->responseOk(message: __('lang.booking_details'), data: new BookingHotelResource($booking));
	}
	//cancelBooking
	public function cancelBooking(Booking $booking)
	{
		if ($booking->user_id != auth()->id()) {
			return $this->responseError(message: __('lang.unauthorized'));
		}
		$booking->update(['status' => Status::UnderCancellation]);
		return $this->responseOk(message: __('lang.booking_under_cancellation'));
	}

	public function createBooking(CreateRoomBookingRequest $request, HotelBookingService $bookingService)
	{
		try {
			$data = $request->validated();
			$data['currency'] = $request->attributes->get('currency', 'egp');
			$data['user_id'] = auth()->id();
			$bookingService->createBooking($data);
			return $this->responseCreated(message: __('lang.created_successfully', ['attribute' => __('lang.booking')]));
		} catch (\Exception $e) {
			return $this->responseError(message: $e->getMessage());
		}
	}

	public function createCustomBooking(CreateRoomCustomBookingRequest $request, HotelBookingService $bookingService)
	{
		try {
			$data = $request->validated();
			$data['currency'] = $request->attributes->get('currency', 'egp');
			$data['user_id'] = auth()->id();
			$bookingService->createCustomBooking($data);
			return $this->responseCreated(message: __('lang.created_successfully', ['attribute' => __('lang.booking')]));
		} catch (\Exception $e) {
			return $this->responseError(message: $e->getMessage());
		}
	}
}
