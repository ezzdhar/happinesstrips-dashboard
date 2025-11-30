<?php

use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\HotelBookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TripController;
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
	Route::get('/details/{hotel}', 'hotelDetails');

	// rooms routes
	Route::prefix('rooms')->controller(RoomController::class)->group(function () {
		Route::get('/', 'rooms');
		Route::get('/{room}', 'roomDetails');
		Route::get('/calculate/booking-room/price/{room}', 'calculateBookingRoomPrice');
	});

	//bookings
	Route::prefix('booking')->middleware('auth:sanctum')->controller(HotelBookingController::class)->group(function () {
		Route::post('/', 'myBooking');
		Route::post('/create', 'createBooking');
		Route::post('/create/custom', 'createCustomBooking');
	});
});


// trips routes
Route::prefix('trips')->controller(TripController::class)->group(function () {
	Route::get('/', 'trips');
	Route::get('/{trip}', 'tripDetails');
});


// data routes
Route::controller(DataController::class)->group(function () {
	Route::get('/hotel-types', 'hotelTypes');
	Route::get('/cities', 'cities');
	Route::get('/categories', 'categories');
	Route::get('/sub-categories', 'subCategories');
});

Route::middleware('auth:sanctum')->group(function () {

	//Favorites
	Route::prefix('favorites')->controller(FavoriteController::class)->group(function () {
		Route::get('/', 'favorites');
		Route::post('/toggle', 'toggleFavorite');
	});

	// notifications
	Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
		Route::get('/', 'index');
		Route::post('/read', 'read');
		Route::post('/read-all', 'readAll');
		Route::post('/delete', 'delete');
		Route::get('/unread/count', 'unreadNotificationCount');
	});

	// profile
	Route::prefix('profile')->controller(ProfileController::class)->group(function () {
		Route::get('/', 'index');
		Route::post('/update', 'update');
		Route::post('/update/password', 'changePassword');
		Route::post('/update/language', 'changeLanguage');
		Route::post('/logout', 'logout');
		Route::post('/delete-account', 'deleteAccount');
	});
});
