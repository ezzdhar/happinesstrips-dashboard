<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'notification_id' => 'required|exists:notifications,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
