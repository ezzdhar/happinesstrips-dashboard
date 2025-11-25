<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Room;

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

			if ((int)$this->input('adults_count') > $room->adults_count) {
				$validator->errors()->add('adults_count', __('lang.adults_count_exceeds_room_capacity', ['capacity' => $room->adults_count]));
			}

			if ((int)$this->input('children_count', 0) > $room->children_count) {
				$validator->errors()->add('children_count', __('lang.children_count_exceeds_room_capacity', ['capacity' => $room->children_count]));
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
