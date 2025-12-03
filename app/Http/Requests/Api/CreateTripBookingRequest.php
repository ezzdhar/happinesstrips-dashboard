<?php

namespace App\Http\Requests\Api;

use App\Enums\TripType;
use App\Models\Trip;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class CreateTripBookingRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'trip_id' => 'required|exists:trips,id',
			'check_in' => 'nullable|date',
			'check_out' => 'nullable|date|after:check_in',
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'nullable|min:0|max:18',
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

	//check_in and check_out are required if trip type is fixed
	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$trip = Trip::find($this->trip_id);
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
