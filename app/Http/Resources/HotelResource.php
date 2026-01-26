<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Hotel */
class HotelResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$currency = $request->attributes->get('currency', 'egp');
		$is_featured = $request->get('is_featured', false);
		return [
			'id' => $this->id,
			'city' => $this->city->name,
			'name' => $this->name,
			'type' => $this->hotelTypes->pluck('name')->toArray(),
			'rating' => (int)$this->rating,
			'latitude' => (float)$this->latitude,
			'longitude' => (float)$this->longitude,
			'description' => $this->description,
			'address' => $this->address,
			'facilities' => $this->facilities,
			'main_image' => FileService::get($this->files->first()->path),
			'hotel_types' => HotelTypeResource::collection($this->hotelTypes),
			'image' => $this->files->map(function ($image) {
				return FileService::get($image->path);
			}),
			'is_featured' => $is_featured,
			'cheapest_room' => $this->getCheapestRoomForToday($currency, $is_featured),
		];
	}
}
