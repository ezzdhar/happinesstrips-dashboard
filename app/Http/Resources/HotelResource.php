<?php

namespace App\Http\Resources;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Hotel */
class HotelResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'email' => $this->email,
			'name' => $this->name,
			'status' => $this->status,
			'rating' => $this->rating,
			'phone_key' => $this->phone_key,
			'phone' => $this->phone,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'description' => $this->description,
			'address' => $this->address,
			'facilities' => $this->facilities,
			'first_child_price_percentage' => $this->first_child_price_percentage,
			'second_child_price_percentage' => $this->second_child_price_percentage,
			'third_child_price_percentage' => $this->third_child_price_percentage,
			'additional_child_price_percentage' => $this->additional_child_price_percentage,
			'free_child_age' => $this->free_child_age,
			'adult_age' => $this->adult_age,


			'booking_hotels_count' => $this->booking_hotels_count,
			'files_count' => $this->files_count,
			'hotel_types_count' => $this->hotel_types_count,
			'rooms_count' => $this->rooms_count,
			'trips_count' => $this->trips_count,
		];
	}
}
