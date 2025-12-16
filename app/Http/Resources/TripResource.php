<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		$currency = $request->attributes->get('currency', 'egp');
		return [
			'id' => $this->id,
			'main_category' => $this->mainCategory->name,
			'sub_category' => $this->subCategory->name,
			'is_featured' => $this->is_featured ? true : false,
			'price' => (float) $this->price[$currency],
			'price_before_discount' => (float) $this->price_before_discount[$currency],
			'discount_percentage' =>(float) $this->discount_percentage,
			'currency' => $currency,
			'city' => $this->city->name,
			'duration_from' => $this->duration_from->format('Y-m-d'),
			'duration_to' => $this->duration_to->format('Y-m-d'),
			'name' => $this->name,
			'nights_count' => (int)$this->nights_count,
			'type' => $this->type->value,
			'rating' => (int)$this->rating,
			'first_child_price_percentage' => (int)$this->first_child_price_percentage,
			'second_child_price_percentage' => (int)$this->second_child_price_percentage,
			'third_child_price_percentage' => (int)$this->third_child_price_percentage,
			'additional_child_price_percentage' => (int)$this->additional_child_price_percentage,
			'free_child_age' => (int)$this->free_child_age,
			'adult_age' => (int)$this->adult_age,
			'main_image' => FileService::get($this->files->first()->path),
			'image' => $this->files->map(function ($image) {
				return FileService::get($image->path);
			}),
			'program' => $this->program,
			'notes' => $this->notes,
		];
	}
}
