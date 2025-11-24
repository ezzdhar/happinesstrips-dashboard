<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ToggleFavoriteRequest;
use App\Http\Resources\HotelSimpleResource;
use App\Http\Resources\TripSimpleResource;
use App\Models\Favorite;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
	use ApiResponse;

	public function favorites(Request $request)
	{
		$model = $request->model == 'hotel' ? 'App\Models\Hotel' : 'App\Models\Trip';
		$favorites = Favorite::where('user_id', auth()->id())->where('favoritable_type', $model)->pluck('favoritable_id');
		$data = $request->model == 'hotel' ? $model::whereIn('id', $favorites)->get() : collect();
		$data = $request->model == 'hotel' ? HotelSimpleResource::collection($data) : TripSimpleResource::collection($data);
		return $this->responseOk(message: __('lang.favorites'), data: $data);
	}

	public function toggleFavorite(ToggleFavoriteRequest $request)
	{
		$model = $request->model == 'hotel' ? 'App\Models\Hotel' : 'App\Models\Trip';
		$favoritable = $model::findOrFail($request->id);
		$favorite = Favorite::where('user_id', auth()->id())->where('favoritable_type', $model)->where('favoritable_id', $favoritable->id)->first();

		if ($favorite) {
			$favorite->delete();
			return $this->responseOk(message: __('lang.removed_from_favorites'));
		} else {
			Favorite::create([
				'user_id' => auth()->id(),
				'favoritable_type' => $model,
				'favoritable_id' => $favoritable->id,
			]);
			return $this->responseOk(message: __('lang.added_to_favorites'));
		}
	}
}
