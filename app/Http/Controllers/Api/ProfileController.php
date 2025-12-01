<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\FileService;
use App\Services\PhoneService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
	use ApiResponse;

	public function index()
	{
		return $this->responseOk(data: new UserResource(auth()->user()));
	}

	public function update(ProfileUpdateRequest $request)
	{
		auth()->user()->update([
			'name' => $request->name,
			'phone_key' => $request->phone_key,
			'phone' => PhoneService::formatNumber($request->phone),
			'image' => $request->image ? FileService::save($request->image) : auth()->user()->image,
		]);
		return $this->responseOk(message: __('lang.updated_successfully', ['attribute' => __('lang.profile')]),data: UserResource::make(auth()->user()));

	}

	public function changePassword(ChangePasswordRequest $request)
	{
		auth()->user()->update(['password' => $request->new_password]);

		return $this->responseOk(message: __('lang.reset_password_successfully'));
	}

	public function logout()
	{
		auth()->user()->tokens()->delete();
		return $this->responseOk(message: __('lang.logout_successfully'));
	}

	public function deleteAccount()
	{
		auth()->user()->delete();
		return $this->responseOk(message: __('lang.deleted_successfully', ['attribute' => __('lang.profile')]));
	}

	public function changeLanguage(Request $request)
	{
		auth()->user()->update(['language' => $request->language]);
		return $this->responseOk(message: __('lang.updated_successfully', ['attribute' => __('lang.language')]));
	}
}
