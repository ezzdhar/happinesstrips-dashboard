<?php

use App\Http\Controllers\Api\BookingRatingController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\HotelBookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TripBookingController;
use App\Http\Controllers\Api\TripController;
use Illuminate\Support\Facades\Route;

// chatbot routes (public - no authentication)
Route::prefix('chatbot')->controller(ChatbotController::class)->group(function () {
	Route::post('/chat', 'chat');
	Route::get('/capabilities', 'capabilities');
	Route::post('/feedback', 'feedback');
	Route::get('/history', 'history');
});

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
Route::prefix('hotels')->group(function () {

	// hotels
	Route::controller(HotelController::class)->group(function () {
		Route::get('/', 'hotels');
		Route::get('/details/{hotel}', 'hotelDetails');
		Route::get('/cheapest-room/{hotel}', 'cheapestRoom');
	});

	// rooms routes
	Route::prefix('rooms')->controller(RoomController::class)->group(function () {
		Route::get('/', 'rooms');
		Route::get('/{room}', 'roomDetails');
		Route::get('/calculate/booking-room/price/{room}', 'calculateBookingRoomPrice');
	});

});

// trips routes
Route::prefix('trips')->controller(TripController::class)->group(function () {
	Route::get('/', 'trips');
	Route::get('/{trip}', 'tripDetails');
	Route::get('/calculate/booking-trip/price/{trip}', 'calculateBookingTripPrice');
});

// data routes
Route::controller(DataController::class)->group(function () {
	Route::get('/hotel-types', 'hotelTypes');
	Route::get('/cities', 'cities');
	Route::get('/categories', 'categories');
	Route::get('/sub-categories', 'subCategories');
	Route::get('/booking-status', 'bookingStatus');
});

Route::middleware('auth:sanctum')->group(function () {

	// Favorites
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
		Route::post('/send', 'send');
	});

	// bookings
	Route::prefix('booking')->group(function () {

		// bookings hotels
		Route::prefix('hotels')->controller(HotelBookingController::class)->group(function () {
			Route::get('/', 'myBooking');
			Route::get('/details/{booking}', 'bookingDetails');
			Route::post('/create', 'createBooking');
			Route::post('/create/custom', 'createCustomBooking');
		});

		// bookings trips
		Route::prefix('trips')->controller(TripBookingController::class)->group(function () {
			Route::get('/', 'myBooking');
			Route::get('/details/{booking}', 'bookingDetails');
			Route::post('/create', 'createBooking');
		});
		Route::post('/rating', BookingRatingController::class);
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
