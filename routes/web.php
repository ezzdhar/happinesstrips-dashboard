<?php

use App\Http\Controllers\LanguageController;
use App\Livewire\Dashboard\Amenity\AmenityData;
use App\Livewire\Dashboard\BookingHotel\BookingHotelData;
use App\Livewire\Dashboard\BookingHotel\CreateBookingHotel;
use App\Livewire\Dashboard\BookingHotel\Custom\BookingCustomHotelData;
use App\Livewire\Dashboard\BookingHotel\ShowBookingHotel;
use App\Livewire\Dashboard\BookingHotel\UpdateBookingHotel;
use App\Livewire\Dashboard\BookingTrip\BookingTripData;
use App\Livewire\Dashboard\BookingTrip\CreateBookingTrip;
use App\Livewire\Dashboard\BookingTrip\ShowBookingTrip;
use App\Livewire\Dashboard\BookingTrip\UpdateBookingTrip;
use App\Livewire\Dashboard\City\CityData;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\Employee\EmployeeData;
use App\Livewire\Dashboard\Hotel\CreateHotel;
use App\Livewire\Dashboard\Hotel\HotelData;
use App\Livewire\Dashboard\Hotel\UpdateHotel;
use App\Livewire\Dashboard\HotelType\HotelTypeData;
use App\Livewire\Dashboard\MainCategory\MainCategoryData;
use App\Livewire\Dashboard\Profile\Profile;
use App\Livewire\Dashboard\Role\RoleData;
use App\Livewire\Dashboard\Room\CreateRoom;
use App\Livewire\Dashboard\Room\RoomData;
use App\Livewire\Dashboard\Room\UpdateRoom;
use App\Livewire\Dashboard\SubCategory\SubCategoryData;
use App\Livewire\Dashboard\Trip\CreateTrip;
use App\Livewire\Dashboard\Trip\TripData;
use App\Livewire\Dashboard\Trip\UpdateTrip;
use App\Livewire\Dashboard\User\UserData;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Notifications\UserNotification;
use App\Services\NotificationFirebaseService;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
	// Get a user with FCM token for testing
	$user = User::find(18);

	if (!$user) {
		return response()->json([
			'error' => 'No user found with FCM token. Please register a user with FCM token first.',
		], 404);
	}

	$title = [
		'en' => 'Trip Booking Status Updated',
		'ar' => 'تم تحديث حالة حجز الرحلة',
	];
	$body = [
		'en' => 'Your booking has been updated successfully',
		'ar' => 'تم تحديث حالة حجزك بنجاح',
	];
	$data = ['id' => '123', 'type' => 'booking_trip'];

	try {
		$user->notify(new UserNotification($title, $body, $data, $user->language ?? 'en'));

		return response()->json([
			'success' => true,
			'message' => 'Notification sent successfully',
			'user_id' => $user->id,
			'user_email' => $user->email,
			'fcm_token' => substr($user->fcm_token, 0, 20) . '...',
		]);
	} catch (\Exception $e) {
		return response()->json([
			'error' => $e->getMessage(),
			'user_id' => $user->id,
		], 500);
	}
});


Route::middleware(['web-language'])->group(function () {
	Route::get('web-language/{lang}', LanguageController::class)->name('web-language');
	Route::get('/', function () {
		return to_route('login');
	})->name('home');

	// authentication routes
	Route::middleware(['auth', 'verified'])->group(function () {
		// Session Keep Alive
		Route::get('/keep-alive', fn() => response()->json(['success' => true, 'time' => now()->toIso8601String()]))->name('keep-alive');

		Route::get('profile', Profile::class)->name('profile'); // profile
		Route::get('dashboard', Dashboard::class)->name('dashboard'); // dashboard
		Route::get('users', UserData::class)->name('users')->middleware('permission:show_user'); // users
		Route::get('employees', EmployeeData::class)->name('employees')->middleware('permission:show_employee'); // employees
		Route::get('roles', RoleData::class)->name('roles')->middleware('permission:show_role'); // roles
		Route::get('main-categories', MainCategoryData::class)->name('main-categories')->middleware('permission:show_main_category'); // main categories
		Route::get('sub-categories', SubCategoryData::class)->name('sub-categories')->middleware('permission:show_sub_category'); // sub categories
		Route::get('cities', CityData::class)->name('cities')->middleware('permission:show_city'); // cities
		Route::get('amenities', AmenityData::class)->name('amenities')->middleware('permission:show_amenity'); // amenities
		Route::get('hotel-types', HotelTypeData::class)->name('hotel-types')->middleware('permission:show_hotel'); // hotel types
		Route::prefix('hotels')->middleware('permission:show_hotel')->group(function () {
			Route::get('/', HotelData::class)->name('hotels');
			Route::get('/create-hotel', CreateHotel::class)->name('hotels.create')->middleware('permission:create_hotel');
			Route::get('/edit/{hotel}', UpdateHotel::class)->name('hotels.edit')->middleware('permission:update_hotel');
			//		    Route::get('/show/{hotel}', HotelData::class)->name('hotels.show');
		});

		// rooms
		Route::prefix('rooms')->middleware('permission:show_room')->group(function () {
			Route::get('/', RoomData::class)->name('rooms');
			Route::get('/create-room', CreateRoom::class)->name('rooms.create')->middleware('permission:create_room');
			Route::get('/clone/{room}', CreateRoom::class)->name('rooms.clone')->middleware('permission:create_room');
			Route::get('/edit/{room}', UpdateRoom::class)->name('rooms.edit')->middleware('permission:update_room');
		});

		// Trips
		Route::prefix('trips')->middleware('permission:show_trip')->group(function () {
			Route::get('/', TripData::class)->name('trips');
			Route::get('/create-trip', CreateTrip::class)->name('trips.create')->middleware('permission:create_trip');
			Route::get('/edit/{trip}', UpdateTrip::class)->name('trips.edit')->middleware('permission:update_trip');
		});

		// Hotel Bookings
		Route::prefix('bookings/hotels')->middleware('permission:show_booking_hotel')->group(function () {
			Route::get('/', BookingHotelData::class)->name('bookings.hotels');
			Route::get('/custom', BookingCustomHotelData::class)->name('bookings.hotels.custom');
			Route::get('/create', CreateBookingHotel::class)->name('bookings.hotels.create')->middleware('permission:create_booking_hotel');
			Route::get('/edit/{booking}', UpdateBookingHotel::class)->name('bookings.hotels.edit')->middleware('permission:update_booking_hotel');
			Route::get('/show/{booking}', ShowBookingHotel::class)->name('bookings.hotels.show');
			Route::get('/print/{booking}', function (Booking $booking) {
				$booking->load(['user', 'bookingHotel.hotel', 'bookingHotel.room', 'travelers']);
				return view('livewire.dashboard.booking-hotel.print-booking-hotel', compact('booking'));
			})->name('bookings.hotels.print');
		});

		// Trip Bookings
		Route::prefix('bookings/trips')->middleware('permission:show_booking_trip')->group(function () {
			Route::get('/', BookingTripData::class)->name('bookings.trips');
			Route::get('/create', CreateBookingTrip::class)->name('bookings.trips.create')->middleware('permission:create_booking_trip');
			Route::get('/edit/{booking}', UpdateBookingTrip::class)->name('bookings.trips.edit')->middleware('permission:update_booking_trip');
			Route::get('/show/{booking}', ShowBookingTrip::class)->name('bookings.trips.show');
			Route::get('/print/{booking}', function (Booking $booking) {
				$booking->load(['user', 'trip', 'travelers']);

				return view('livewire.dashboard.booking-trip.print-booking-trip', compact('booking'));
			})->name('bookings.trips.print');
		});
	});

	// guest routes
	require __DIR__ . '/auth.php';
});
