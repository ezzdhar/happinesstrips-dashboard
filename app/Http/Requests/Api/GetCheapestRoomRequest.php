<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class GetCheapestRoomRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'start_date' => 'required|date|after_or_equal:today',
			'end_date' => 'required|date|after:start_date',
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'integer|min:0|max:18',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}

