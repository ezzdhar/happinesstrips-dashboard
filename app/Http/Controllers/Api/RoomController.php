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


	public function roomDetails(GetRoomDetailsRequest $request,Room $room)
	{
		return $this->responseOk(message: __('lang.room_details'), data: new RoomResource($room));
	}
}
