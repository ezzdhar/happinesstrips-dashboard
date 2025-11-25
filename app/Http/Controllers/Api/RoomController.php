<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRoomDetailsRequest;
use App\Http\Requests\Api\GetRoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Resources\RoomSimpleResource;
use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RoomController extends Controller
{
	use ApiResponse;

	public function rooms(GetRoomRequest $request)
	{
		$rooms = Room::query()
			->filter($request->name)
			->hotelId($request->hotel_id)
			->where('adults_count', (int)$request->adults_count)
			->when($request->children_count, fn($q) => $q->where('children_count', $request->children_count))
			->isAvailableRangeCovered()
			->filterByCalculatedPrice()
			->with(['amenities'])
			->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.rooms'), data: RoomSimpleResource::collection($rooms));
	}


	public function roomDetails(GetRoomDetailsRequest $request, Room $room)
	{
		return $this->responseOk(message: __('lang.room_details'), data: new RoomResource($room));
	}

	public function calculateBookingRoomPrice(GetRoomDetailsRequest $request, Room $room)
	{
		$calculate_booking_price = $room->calculateBookingPrice(
			checkIn: Carbon::parse($request->start_date),
			checkOut: Carbon::parse($request->end_date),
			adultsCount: $request->adults_count,
			childrenAges: $request->childrenAges ?? [],
			currency: $request->attributes->get('currency', 'egp')
		);
		if (!$calculate_booking_price['success']) {
			return $this->responseError(message: $calculate_booking_price['error']);
		}
		return $this->responseOk(message: __('lang.calculate_booking_room_price'), data: $calculate_booking_price);
	}

}
