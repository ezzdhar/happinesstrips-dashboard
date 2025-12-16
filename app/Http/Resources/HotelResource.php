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
	    $is_featured = $request->attributes->get('is_featured', false);
        return [
            'id' => $this->id,
            'city' => $this->city->name,
            'name' => $this->name,
	        'type' =>  $this->hotelTypes->pluck('name')->toArray(),
            'rating' => (int) $this->rating,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'description' => $this->description,
            'address' => $this->address,
            'facilities' => $this->facilities,
            'first_child_price_percentage' => (int) $this->first_child_price_percentage,
            'second_child_price_percentage' => (int) $this->second_child_price_percentage,
            'third_child_price_percentage' => (int) $this->third_child_price_percentage,
            'additional_child_price_percentage' => (int) $this->additional_child_price_percentage,
            'free_child_age' => (int) $this->free_child_age,
            'adult_age' => (int) $this->adult_age,
            'main_image' => FileService::get($this->files->first()->path),
	        'hotel_types' => HotelTypeResource::collection($this->hotelTypes),
            'image' => $this->files->map(function ($image) {
                return FileService::get($image->path);
            }),
            'cheapest_room' => $this->getCheapestRoomForToday($currency,$is_featured),
        ];
    }
}
