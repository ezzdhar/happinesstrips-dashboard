<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRoomRequest;
use App\Http\Resources\RoomResource;
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
			->where('adults_count', '>=', (int) $request->adults_count)
			->when($request->children_count, fn($q) => $q->where('children_count','>=', $request->children_count))
			->availableBetween($request->start_date, $request->end_date)
			->with(['amenities'])
			->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.rooms'), data: RoomResource::collection($rooms));
	}
}
