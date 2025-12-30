<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'email' => $this->email,
			'name' => $this->name,
			'phone_key' => $this->phone_key,
			'phone' => $this->phone,
			'points' => $this->points,
			'image' => FileService::get($this->image),
			'fcm_token' =>  $this->fcm_token,
		];
	}
}
