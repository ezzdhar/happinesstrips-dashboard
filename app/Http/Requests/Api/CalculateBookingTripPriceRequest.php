<?php

namespace App\Http\Requests\Api;

use App\Enums\TripType;
use App\Models\Trip;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class CalculateBookingTripPriceRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'check_in' => 'nullable|date',
			'check_out' => 'nullable|date|after:check_in',
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'nullable|min:0|max:18',
		];
	}

	//check_in and check_out are required if trip type is fixed
	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			if ($validator->errors()->count() > 0) {
				return;
			}
			$trip = $this->route('trip');
			if (!$trip) {
				$validator->errors()->add('check_in', __('lang.trip_required'));
				return;
			}
			if ($trip && $trip->type->value === TripType::Flexible) {
				if (empty($this->check_in)) {
					$validator->errors()->add('check_in', __('lang.check_in_required_for_trip'));
				}
				if (empty($this->check_out)) {
					$validator->errors()->add('check_out', __('lang.check_out_required_trip'));
				}
				// Validate check_in is on or after trip's check_in date
				if (!empty($this->check_in) && Carbon::parse($this->check_in)->lt(Carbon::parse($trip->duration_from))) {
					$validator->errors()->add('check_in', __('lang.check_in_must_be_on_or_after_trip_check_in'));
				}
			}
		});
	}

	public function authorize(): bool
	{
		return true;
	}
}
