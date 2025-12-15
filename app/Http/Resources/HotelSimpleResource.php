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
		return [
			'id' => $this->id,
			'city' => $this->city->name,
			'name' => $this->name,
			'type' =>  $this->hotelTypes->pluck('name')->toArray(),
			'rating' => (int) $this->rating,
			'main_image' => FileService::get($this->files->first()->path),
			'cheapest_room' => $this->getCheapestRoomForToday($currency),
		];
	}
}
