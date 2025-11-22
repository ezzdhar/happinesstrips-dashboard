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
			'main_category' => $this->mainCategory->name,
			'sub_category' => $this->subCategory->name,
			'city' => $this->city->name,
			'name' => $this->name,
			'rating' => (int)$this->rating,
			'main_image' => FileService::get($this->files->first()->path),
			'price' => $this->price[$currency] . __('lang.' . strtolower($currency)),
			'is_featured' => $this->is_featured,
		];
	}
}
