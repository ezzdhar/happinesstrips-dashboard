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
            'is_featured' => $this->is_featured ? true : false,
            'price' => $this->price,
            'price_before_discount' => (float) $this->price_before_discount,
            'discount_percentage' => (float) $this->discount_percentage,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            // 'includes' => $this->includes,
            'currency' => $currency,
            'amenities' => AmenityResource::collection($this->amenities),
            'includes' => AmenityResource::collection($this->amenities),
            'adult_age' => (int) $this->adult_age,
            'children_policies' => $this->childrenPolicies->map(function ($policy) {
                return [
                    'child_number' => (int) $policy->child_number,
                    'from_age' => (int) $policy->from_age,
                    'to_age' => (int) $policy->to_age,
                    'price_percentage' => (float) $policy->price_percentage,
                ];
            })->values()->toArray(),
            'main_image' => FileService::get($this->files->first()->path),
            'image' => $this->files->map(function ($image) {
                return FileService::get($image->path);
            }),
            'price_periods' => $this->price_periods,
        ];
    }
}
