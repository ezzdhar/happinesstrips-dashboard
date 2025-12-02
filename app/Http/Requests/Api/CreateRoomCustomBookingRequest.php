<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoomCustomBookingRequest extends FormRequest
{
	use ApiResponse;
	public function rules(): array
	{
		return [
			'hotel_id' => 'required|exists:hotels,id',
			'check_in' => 'required|date|after:today',
			'check_out' => 'required|date|after:check_in',
			'adults_count' => 'required|integer|min:1',
			'children_count' => 'required|integer|min:0',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'nullable|min:0|max:18',
			'notes' => 'nullable|string',
			'travelers' => 'required|array|min:1',
			'travelers.*.full_name' => 'required|string',
			'travelers.*.phone_key' => 'required|string',
			'travelers.*.phone' => 'required|string',
			'travelers.*.nationality' => 'required|string',
			'travelers.*.age' => 'required|integer|min:1',
			'travelers.*.id_type' => 'required|in:passport,national_id',
			'travelers.*.id_number' => 'required|string',
			'travelers.*.type' => 'required|in:adult,child',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
