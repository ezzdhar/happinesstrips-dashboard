<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currency = $request->attributes->get('currency', 'egp');

	    return [
            'id' => $this->id,
            'type' => $this->type->value,
            'main_category' => $this->mainCategory->name,
            'sub_category' => $this->subCategory->name,
		    'city' => $this->city->name,
		    'city_id' => $this->city_id,
            'name' => $this->name,
            'duration_from' => $this->duration_from,
            'duration_to' => $this->duration_to,
            'rating' => (int) $this->rating,
            'main_image' => FileService::get($this->files->first()->path),
            'currency' => $currency,
		    'is_featured' => $this->is_featured ? true : false,
	        'price' => (float) $this->price[$currency],
	        'price_before_discount' => (float) $this->price_before_discount[$currency],
	        'discount_percentage' =>(float) $this->discount_percentage,
        ];
    }
}
