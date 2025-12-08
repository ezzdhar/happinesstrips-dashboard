<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'chat_session',
        'user_message',
        'bot_response',
        'api_calls',
        'api_results',
        'suggested_actions',
        'intent',
        'was_helpful',
        'feedback',
    ];

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
