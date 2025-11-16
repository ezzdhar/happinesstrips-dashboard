<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Requests\Api\SendCodeRequest;
use App\Http\Requests\Api\VerifyCodeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\SendCodeNotification;
use App\Services\FileService;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::updateOrCreate(['email' => $request->email, 'email_verified_at' => null],
            [
                'name' => $request->name,
                'password' => $request->password,
                'email' => $request->email,
                'image' => FileService::fakeImage(),
	            'device_token' => $request->device_token
            ]
        );
	    $user->assignRole('user');
        $user->notify(new SendCodeNotification($user, randomOtpCode()));
        return $this->responseCreated(__('lang.registered_successfully_and_code_sent'));
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $data = [
            'token' => $user->createToken(Str::random(50))->plainTextToken,
            'user' => new UserResource($user),
        ];

        return $this->responseCreated(__('lang.login_successfully'), $data);
    }

    public function sendCode(SendCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->notify(new SendCodeNotification($user, randomOtpCode()));

        return $this->responseCreated(__('lang.resend_successfully'));
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->update(['verification_code' => null, 'email_verified_at' => now()]);
        $data = [
            'token' => $user->createToken(Str::random(50))->plainTextToken,
            'user' => new UserResource($user),
        ];

        return $this->responseCreated(__('lang.verified_successfully'), $data);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => $request->password]);
        $data = [
            'token' => $user->createToken('api')->plainTextToken,
            'user' => new UserResource($user),
        ];

        return $this->responseCreated(__('lang.reset_password_successfully'), $data);
    }
}
