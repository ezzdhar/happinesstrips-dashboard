<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelTypeResource;
use App\Models\City;
use App\Models\HotelType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DataController extends Controller
{
	use ApiResponse;

	public function hotelTypes(Request $request)
	{
		$hotelTypes = HotelType::get(['id', 'name']);
		return $this->responseOk(message: __('lang.hotel_types'), data: HotelTypeResource::collection($hotelTypes));
	}

	public function cities(Request $request)
	{
		$cities= City::get(['id', 'name','image']);
		return $this->responseOk(message: __('lang.hotel_types'), data: HotelTypeResource::collection($cities));
	}
}
