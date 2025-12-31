<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email'],
            'password' => ['bail', 'required', 'min:8'],
	        'fcm_token' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! auth()->attempt(['email' => $this->email, 'password' => $this->password])) {
                    $validator->errors()->add('password', __('auth.failed'));
                }
                if (User::where('email', $this->email)->exists() && ! User::where('email', $this->email)->first()->hasVerifiedEmail()) {
                    $validator->errors()->add('email', __('lang.email_not_verified'));
                }
            },
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
