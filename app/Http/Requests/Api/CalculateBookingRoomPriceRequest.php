<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CalculateBookingRoomPriceRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'adults_count' => 'required|integer|min:1',
			'children_ages' => 'nullable|array',
			'children_ages.*' => 'nullable|integer|min:1|max:12',
			'start_date' => 'required|date',
			'end_date' => 'required|date|after:start_date',
		];
	}

	public function withValidator(Validator $validator)
	{
		$validator->after(function ($validator) {
			if ($validator->errors()->count() > 0) {
				return;
			}
			$room = $this->route('room');
			if (!$room) {
				return;
			}

			$requestedAdults = (int)$this->input('adults_count');
			$childrenAges = $this->input('children_ages', []);
			$requestedChildren = is_array($childrenAges) ? count($childrenAges) : 0;
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
			$isCovered = $room->isDateRangeCovered($this->start_date, $this->end_date);
			if (!$isCovered) {
				$validator->errors()->add('start_date', __('lang.date_range_not_covered'));
			}
		});
	}


	public function authorize(): bool
	{
		return true;
	}
}
