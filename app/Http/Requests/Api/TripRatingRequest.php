<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class TripRatingRequest extends FormRequest
{
	use ApiResponse;
	public function rules(): array
	{
		return [
			'booking_id' => 'required|exists:bookings,id',
			'rating' => 'required|integer|between:1,5',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
