<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class GetRoomDetailsRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'adults_count' => 'required|integer|min:1',
			'children_count' => 'nullable|integer|min:0',
			'childrenAges' => 'nullable|array',
			'childrenAges.*' => 'nullable|integer|min:1|max:12',
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
