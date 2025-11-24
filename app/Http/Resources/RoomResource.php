<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class RoomResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$currency = $request->attributes->get('currency', 'egp');
		return [
			'id' => $this->id,
			'name' => $this->name,
			'adults_count' => $this->adults_count,
			'children_count' => $this->children_count,
			'includes' => $this->includes,
			'price' => $this->calculateBookingPrice(
				checkIn: Carbon::parse($request->start_date),
				checkOut: Carbon::parse($request->end_date),
				adultsCount: $request->adults_count,
				childrenAges: $request->childrenAges ?? [],
				currency: $currency
			)['grand_total'] . __("lang.$currency"),
			'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
			'main_image' => FileService::get($this->files->first()->path),
			'image' => $this->files->map(function ($image) {
				return FileService::get($image->path);
			}),
		];
	}
}
