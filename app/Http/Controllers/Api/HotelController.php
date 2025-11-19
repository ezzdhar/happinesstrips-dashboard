<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\HotelCollection;
use App\Models\Hotel;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
	use ApiResponse;

	public function hotels(Request $request)
	{
		$hotels = Hotel::status(Status::Active)
			->when($request->has('name'), function ($q) use ($request) {
				$q->where('name', 'like', '%' . $request->get('search') . '%');
			})
			->paginate($request->get('per_page', 15));
		return $this->responseOk(message: __('lang.hotel'), data: HotelRes::collection($hotels), paginate: true);
	}
}
