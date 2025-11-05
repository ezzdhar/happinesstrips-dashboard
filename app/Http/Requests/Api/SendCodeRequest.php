<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class SendCodeRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email:dns', 'exists:users,email'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
