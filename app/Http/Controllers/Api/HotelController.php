<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetCheapestRoomRequest;
use App\Http\Requests\Api\GetRoomRequest;
use App\Http\Resources\HotelResource;
use App\Http\Resources\HotelSimpleResource;
use App\Http\Resources\HotelWithCheapestRoomResource;
use App\Models\Hotel;
use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HotelController extends Controller
{
	use ApiResponse;

	public function hotels(Request $request)
	{
		$hotels = Hotel::status(Status::Active)
			->when($request->name, fn(Builder $query) => $query->filter($request->name))
			->when($request->city_id, fn(Builder $query) => $query->where('city_id', $request->city_id))
			->when($request->rating, fn(Builder $query) => $query->orderBy('rating', $request->rating))
			->when($request->hotel_type_id, fn(Builder $query) => $query->whereHas('hotelTypes', fn(Builder $q) => $q->where('hotel_type_id', $request->hotel_type_id)))
			->when($request->adults_count, function ($q) use ($request) {
				$q->whereHas('rooms', fn(Builder $q) => $q->where('adults_count', $request->adults_count)
					->when($request->children_count, function ($q) use ($request) {
						$q->where('children_count', $request->children_count);
					}));
			})->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.hotel'), data: HotelSimpleResource::collection($hotels), paginate: true);
	}


	public function hotelDetails(Hotel $hotel)
	{
		return $this->responseOk(message: __('lang.hotel_details'), data: new HotelResource($hotel));
	}

	public function cheapestRoom(GetCheapestRoomRequest $request, Hotel $hotel)
	{
		$currency = $request->attributes->get('currency', 'egp');

		// الحصول على أرخص غرفة بناءً على المعايير المحددة
		$cheapestRoomData = $hotel->getCheapestRoomForPeriod(
			$request->start_date,
			$request->end_date,
			$request->adults_count,
			$request->children_count ?? 0,
			$currency
		);

		if ($cheapestRoomData === null) {
			return $this->responseOk(message: __('lang.no_available_rooms'));

		}

		// إضافة بيانات الفندق للنتيجة
		$hotelResource = new HotelWithCheapestRoomResource($hotel);
		$result = array_merge($hotelResource->toArray($request), ['cheapest_room' => $cheapestRoomData]);
		return $this->responseOk(
			message: __('lang.hotel_details'),
			data: $result
		);
	}


}
