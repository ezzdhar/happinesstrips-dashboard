<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelSimpleResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$currency = $request->attributes->get('currency', 'egp');
		$is_featured = $request->get('is_featured', false);
		return [
			'id' => $this->id,
			'city' => $this->city->name,
			'name' => $this->name,
			'type' =>  $this->hotelTypes->pluck('name')->toArray(),
			'rating' => (int) $this->rating,
			'main_image' => FileService::get($this->files->first()->path),
			'is_favorite' => auth('sanctum')->check() ? $this->favorites()->where('user_id', auth('sanctum')->id())->exists() : false,
			'cheapest_room' => $this->getCheapestRoomForToday($currency,$is_featured),
		];
	}
}
