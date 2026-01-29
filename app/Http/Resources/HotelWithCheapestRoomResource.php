<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelWithCheapestRoomResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'city' => $this->city->name,
			'name' => $this->name,
			'type' => $this->hotelTypes->pluck('name')->map(fn($name) => (string) $name)->values()->toArray(),
			'hotel_types' => HotelTypeResource::collection($this->hotelTypes),
			'rating' => (int) $this->rating,
			'latitude' => (float) $this->latitude,
			'longitude' => (float) $this->longitude,
			'description' => $this->description,
			'address' => $this->address,
			'facilities' => $this->facilities,
			'main_image' => FileService::get($this->files->first()->path),
			'image' => $this->files->map(function ($image) {
				return FileService::get($image->path);
			}),
		];
	}
}
