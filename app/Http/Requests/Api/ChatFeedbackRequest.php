<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ChatFeedbackRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'chat_message_id' => 'required|integer|exists:chat_messages,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'chat_message_id.required' => 'Chat message ID is required',
            'chat_message_id.exists' => 'Invalid chat message ID',
            'rating.required' => 'Rating is required',
            'rating.between' => 'Rating must be between 1 and 5',
            'comment.max' => 'Comment cannot exceed 1000 characters',
        ];
    }
}

