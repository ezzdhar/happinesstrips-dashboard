<?php

namespace App\Http\Requests\Api;

use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateRoomBookingRequest extends FormRequest
{
	use ApiResponse;
	public function rules(): array
	{
		return [
			'room_id' => 'required|exists:rooms,id',
			'check_in' => 'required|date|after:today',
			'check_out' => 'required|date|after:check_in',
			'adults_count' => 'required|integer|min:1',
			'children_count' => 'nullable|integer|min:0',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'nullable|integer|min:0|max:18',
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
	public function withValidator(Validator $validator)
	{
		$validator->after(function ($validator) {
			if ($validator->errors()->count() > 0) {
				return;
			}
			$room = Room::findOrFail($this->room_id);
			if (!$room) {
				return;
			}

			$requestedAdults = (int)$this->adults_count;
			$requestedChildren = (int)$this->children_count;
			$totalRequested = $requestedAdults + $requestedChildren;
			$roomCapacity = $room->adults_count + $room->children_count;

			// التحقق من أن مجموع الأفراد <= مجموع سعة الغرفة
			// الأطفال الزائدين عن سعة الأطفال سيُحاسبون كبالغين
			if ($totalRequested > $roomCapacity) {
				$validator->errors()->add('adults_count', __('lang.total_guests_exceeds_room_capacity', [
					'capacity' => $roomCapacity,
					'requested' => $totalRequested
				]));
			}

			// تحقق من تغطية نطاق التواريخ
			$isCovered = $room->isDateRangeCovered($this->check_in, $this->check_out);
			if (!$isCovered) {
				$validator->errors()->add('start_date', __('lang.date_range_not_covered'));
			}
			$travelers_count = count($this->travelers ?? []);
			$expected_count = (int)$this->adults_count + (int)$this->children_count;
			if ($travelers_count !== $expected_count) {
				$validator->errors()->add('travelers', __('lang.travelers_count_mismatch', ['expected' => $expected_count, 'provided' => $travelers_count]));
			}
		});
	}

	public function authorize(): bool
	{
		return true;
	}
}
