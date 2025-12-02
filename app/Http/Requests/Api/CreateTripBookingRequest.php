<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class CreateTripBookingRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'trip_id' => 'required|exists:trips,id',
			'check_in' => 'required|date',
			'check_out' => 'required|date|after:check_in',
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'required|integer|min:0|max:18',
			'currency' => 'required|in:egp,usd',
			'notes' => 'nullable|string',
			'travelers' => 'required|array|min:1',
			'travelers.*.full_name' => 'required|string',
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
