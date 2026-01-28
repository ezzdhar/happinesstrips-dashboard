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
            'title' => is_array($this->data['title'])
                ? ($this->data['title'][app()->getLocale()] ?? $this->data['title']['en'] ?? '')
                : ($this->data['title'] ?? ''),
            'body' => is_array($this->data['body'])
                ? ($this->data['body'][app()->getLocale()] ?? $this->data['body']['en'] ?? '')
                : ($this->data['body'] ?? ''),
            'data' => $this->data['data'] ?? null,
            'is_read' => $this->read_at ? true : false,
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
