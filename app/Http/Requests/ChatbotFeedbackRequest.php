<?php

namespace App\Http\Requests;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ChatbotFeedbackRequest extends FormRequest
{
	use ApiResponse;
	public function rules(): array
	{
		return [
			'chat_session' => ['required', 'string'],
			'was_helpful' => ['required', 'boolean'],
			'feedback' => ['nullable', 'string', 'max:500'],
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
