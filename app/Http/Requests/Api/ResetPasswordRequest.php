<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email:dns', 'exists:users,email'],
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
