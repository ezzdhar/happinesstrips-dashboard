<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoriteRequest extends FormRequest
{
	use ApiResponse;

	public function rules(): array
	{
		return [
			'model' => 'required|in:hotel,trip',
			'id' => 'required',
		];
	}

	public function authorize(): bool
	{
		return true;
	}
}
