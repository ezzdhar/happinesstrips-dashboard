<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\HotelTypeResource;
use App\Http\Resources\MainCategoryResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\City;
use App\Models\HotelType;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
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
		$cities = City::get(['id', 'name', 'image']);
		return $this->responseOk(message: __('lang.cities'), data: CityResource::collection($cities));
	}

	public function categories(Request $request)
	{
		$categories = MainCategory::active()->get(['id', 'name', 'image']);
		return $this->responseOk(message: __('lang.categories'), data: MainCategoryResource::collection($categories));
	}

	public function subCategories(Request $request)
	{
		$subCategories = SubCategory::active()->when($request->main_category_id, fn(Builder $query) => $query->where('main_category_id', $request->main_category_id))
			->get(['id', 'name', 'image']);
		return $this->responseOk(message: __('lang.sub_categories'), data: SubCategoryResource::collection($subCategories));
	}

	//bookingStatus
	public function bookingStatus()
	{
		$data = [
			['value' => Status::Pending, 'name' => __('lang.pending')],
			['value' => Status::UnderPayment, 'name' => __('lang.under_payment')],
			['value' => Status::UnderCancellation, 'name' => __('lang.under_cancellation')],
			['value' => Status::Cancelled, 'name' => __('lang.cancelled')],
			['value' => Status::Completed, 'name' => __('lang.completed')],
		];
		return $this->responseOk(message: __('lang.booking_status'), data: $data);
	}
}
