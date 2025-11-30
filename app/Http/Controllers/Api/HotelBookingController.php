<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\HotelRoomBookingRequest;
use App\Http\Requests\Api\HotelRoomCustomBookingRequest;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Booking\HotelBookingService;

class HotelBookingController extends Controller
{

	use ApiResponse;


	public function hotelRoomBooking(HotelRoomBookingRequest $request, HotelBookingService $bookingService)
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

	public function hotelCustomBooking(HotelRoomCustomBookingRequest $request, HotelBookingService $bookingService)
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
