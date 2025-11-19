<?php

use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// guest routes
Route::controller(GuestController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/social-login', 'socialLogin');
    Route::post('/send/code', 'sendCode');
    Route::post('/verify-code', 'verifyCode');
    Route::post('/reset/password', 'resetPassword');
});

// hotels routes
Route::prefix('hotels')->controller(HotelController::class)->group(function () {
	Route::get('/', 'hotels');

});

Route::middleware('auth:sanctum')->group(function () {
    // notifications
    Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/read', 'read');
        Route::post('/read-all', 'readAll');
        Route::post('/delete', 'delete');
        Route::get('/unread/count', 'unreadNotificationCount');
        Route::post('/disable', 'disable');
    });

    // profile
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/update', 'update');
        Route::post('/change/password', 'changePassword');
        Route::post('/logout', 'logout');
        Route::post('/delete', 'deleteAccount');
    });
});
