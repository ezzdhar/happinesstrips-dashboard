<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'api_calls' => 'array',
            'api_results' => 'array',
            'suggested_actions' => 'array',
            'was_helpful' => 'boolean',
        ];
    }
}
