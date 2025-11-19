<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
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
			->when($request->children_count, function ($q) use ($request) {
				$q->whereHas('rooms', fn(Builder $q) => $q->where('children_count', $request->children_count));
			})->when($request->adults_count, function ($q) use ($request) {
				$q->whereHas('rooms', fn(Builder $q) => $q->where('adults_count', $request->adults_count));
			})->paginate($request->per_page ?? 15);
		return $this->responseOk(message: __('lang.hotel'), data: HotelResource::collection($hotels), paginate: true);
	}


	public function hotelDetails(Hotel $hotel)
	{
		return $this->responseOk(message: __('lang.hotel_details'), data: new HotelResource($hotel));
	}
}
