<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint - no authentication required
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
            'conversation_history' => ['nullable', 'array'],
            'conversation_history.*.role' => ['required_with:conversation_history', 'string', 'in:user,assistant'],
            'conversation_history.*.content' => ['required_with:conversation_history', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'message.required' => 'الرسالة مطلوبة',
            'message.string' => 'الرسالة يجب أن تكون نص',
            'message.max' => 'الرسالة يجب ألا تتجاوز 1000 حرف',
            'conversation_history.array' => 'سجل المحادثة يجب أن يكون مصفوفة',
        ];
    }
}
