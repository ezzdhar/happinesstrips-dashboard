<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ToggleFavoriteRequest;
use App\Http\Resources\HotelSimpleResource;
use App\Http\Resources\TripSimpleResource;
use App\Models\Favorite;
use App\Models\Hotel;
use App\Models\Trip;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
	use ApiResponse;

	protected function resolveModel(string $type): string
	{
		return match ($type) {
			'hotel' => Hotel::class,
			'trip' => Trip::class,
			default => abort(422, 'Invalid model type'),
		};
	}

	protected function resolveResource(string $type): string
	{
		return match ($type) {
			'hotel' => HotelSimpleResource::class,
			'trip' => TripSimpleResource::class,
			default => abort(422, 'Invalid model type'),
		};
	}

	public function favorites(Request $request)
	{
		$userId = auth()->id();
		$type = $request->model; // مفروض متحقق منه في الـ Request
		$modelClass = $this->resolveModel($type);
		$resource = $this->resolveResource($type);

		$favoriteIds = Favorite::query()
			->where('user_id', $userId)
			->where('favoritable_type', $modelClass)
			->pluck('favoritable_id');

		$items = $modelClass::whereIn('id', $favoriteIds)->get();

		return $this->responseOk(
			message: __('lang.favorites'),
			data: $resource::collection($items)
		);
	}

	public function toggleFavorite(ToggleFavoriteRequest $request)
	{
		$userId = auth()->id();
		$type = $request->model;
		$modelClass = $this->resolveModel($type);

		$favoritable = $modelClass::findOrFail($request->id);

		$favorite = Favorite::firstOrNew([
			'user_id' => $userId,
			'favoritable_type' => $modelClass,
			'favoritable_id' => $favoritable->id,
		]);

		if ($favorite->exists) {
			$favorite->delete();

			return $this->responseOk(
				message: __('lang.removed_from_favorites')
			);
		}

		$favorite->save();

		return $this->responseOk(
			message: __('lang.added_to_favorites')
		);
	}
}
