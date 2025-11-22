<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->data['title'][app()->getLocale()],
	        'body' => $this->data['body'][app()->getLocale()],
	        'data' => $this->data['data'],
            'is_read' => $this->read_at ? true : false,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
