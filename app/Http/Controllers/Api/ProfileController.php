<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\FileService;
use App\Services\PhoneService;
use App\Traits\ApiResponse;

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
            'phone' => [
                'key' => $request->phone_key,
                'number' => PhoneService::formatNumber($request->phone),
            ],
            'image' => $request->image ? FileService::save($request->image) : auth()->user()->image,
        ]);

        return $this->responseOk(message: __('lang.success'));

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

        return $this->responseOk(message: __('lang.success'));
    }
}
