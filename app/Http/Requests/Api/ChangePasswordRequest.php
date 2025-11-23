<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class ChangePasswordRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'old_password' => 'bail|required|string',
            'new_password' => 'bail|required|string|min:6',
            'password_confirmation' => 'bail|required|string|same:new_password',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! Hash::check($this->old_password, auth()->user()->password)) {
                    $validator->errors()->add('old_password', __('lang.old_password_not_correct'));
                }
            },
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
