<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ChatSendRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:4000',
            'session_id' => 'required|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'context' => 'nullable|array',
            'context.booking_id' => 'nullable|integer|exists:bookings,id',
            'context.trip_id' => 'nullable|integer|exists:trips,id',
            'context.hotel_id' => 'nullable|integer|exists:hotels,id',
            'language' => 'nullable|string|in:ar,en',
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
            'message.required' => 'Message is required',
            'message.max' => 'Message cannot exceed 4000 characters',
            'session_id.required' => 'Session ID is required',
            'user_id.exists' => 'Invalid user ID',
            'context.booking_id.exists' => 'Invalid booking ID',
            'context.trip_id.exists' => 'Invalid trip ID',
            'context.hotel_id.exists' => 'Invalid hotel ID',
            'language.in' => 'Language must be either ar or en',
        ];
    }
}

