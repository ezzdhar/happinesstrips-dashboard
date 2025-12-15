<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class RoomSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currency = $request->attributes->get('currency', 'egp');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_featured' => $this->is_featured ? true : false,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'price' => (float) $this->calculateBookingPrice(
                checkIn: Carbon::parse($request->start_date),
                checkOut: Carbon::parse($request->end_date),
                adultsCount: $request->adults_count,
                childrenAges: $request->childrenAges ?? [],
                currency: $currency
            )['total_price'],
            'currency' => $currency,
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'main_image' => FileService::get($this->files->first()->path),
        ];
    }
}
