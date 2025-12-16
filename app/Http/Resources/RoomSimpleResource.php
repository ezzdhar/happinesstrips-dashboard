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
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'currency' => $currency,
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'main_image' => FileService::get($this->files->first()->path),
	        'is_featured' => $this->is_featured ? true : false,
	        'price' => $this->price,
	        'price_before_discount' => (float) $this->price_before_discount,
	        'discount_percentage' =>(float) $this->discount_percentage,
        ];
    }
}
