<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingHotelResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'room_name' => $this->bookingHotel->room->name,
			'user' => [
				'name' => $this->user->name,
				'full_phone' => $this->user->full_phone
			],
			'check_in' => $this->check_in,
			'check_out' => $this->check_out,
			'nights' => $this->nights,
			'adults_count' => $this->adults_count,
			'children_count' => $this->children_count,
			'notes' => $this->notes,
			'type' => $this->type,
			'booking_number' => $this->booking_number,
			'total_price' => $this->total_price,
			'currency' => $this->currency,
			'city' => $this->bookingHotel->hotel->city->name,
			'status' => [
				'title' => $this->status->title(),
				'value' => $this->status->value
			],
			'hotel_information' => [
				'hotel_name' => $this->bookingHotel->hotel->name,
				'room_name' => $this->bookingHotel->room->name,
				'room_capacity' => [
					'adults' => $this->bookingHotel->room->adults_count,
					'children' => $this->bookingHotel->room->children_count,
				],
				'room_includes' => $this->bookingHotel->room_includes
			],

			'main_image' => FileService::get($this->bookingHotel->hotel->files->first()->path),
		];
	}
}
