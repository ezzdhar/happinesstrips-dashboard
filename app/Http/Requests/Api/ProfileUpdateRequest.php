<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ApiResponse;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_key' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone->number'.auth()->id(),
            'image' => 'nullable|image|max:4048',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
