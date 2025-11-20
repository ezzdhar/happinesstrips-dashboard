<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'image' =>FileService::get($this->image),
		];
	}
}
