<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingSimpleHotelResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'name'=> $this->bookingHotel->room->name,
			'booking_number' => $this->booking_number,
			'total_price' => $this->total_price,
			'currency' => $this->currency,
			'city' => $this->bookingHotel->hotel->city->name,
			'status' => [
				'title' => $this->status->title(),
				'value' => $this->status->value
			],
			'main_image' => FileService::get($this->bookingHotel->hotel->files->first()->path),
		];
	}
}
