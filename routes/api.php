<?php

use App\Http\Controllers\Api\BookingRatingController;
use App\Http\Controllers\Api\ChatBotController;
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
use Illuminate\Support\Facades\Http;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Tool;

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
		Route::post('/send', 'send');
	});

	//bookings
	Route::prefix('booking')->group(function () {

		//bookings hotels
		Route::prefix('hotels')->controller(HotelBookingController::class)->group(function () {
			Route::get('/', 'myBooking');
			Route::get('/details/{booking}', 'bookingDetails');
			Route::post('/create', 'createBooking');
			Route::post('/create/custom', 'createCustomBooking');
		});

		//bookings trips
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

// Chatbot routes
Route::prefix('chat')->group(function () {
	// Public FAQ endpoints
	Route::controller(ChatBotController::class)->group(function () {
		Route::get('/faqs', 'faqs');
		Route::get('/faqs/{faq}', 'getFaq');
	});

	// Authenticated chatbot endpoints with rate limiting
	Route::middleware(['auth:sanctum', 'throttle:60,1'])->controller(ChatBotController::class)->group(function () {
		Route::post('/send', 'send');
		Route::get('/history', 'history');
		Route::post('/feedback', 'feedback');
	});
});



Route::get('/test-chat/{message}', function () {
	$message = request()->route('message');

	// إنشاء Tools من الـ API endpoints المتاحة
	$tools = [
		(new Tool())
			->as('get_all_hotels')
			->for('Get list of all available hotels with their details, amenities, and pricing')
			->withStringParameter('city_id', 'Filter hotels by city ID (optional)')
			->withStringParameter('hotel_type_id', 'Filter hotels by hotel type ID (optional)')
			->using(function (string $city_id = '', string $hotel_type_id = '') {
				$params = array_filter(['city_id' => $city_id, 'hotel_type_id' => $hotel_type_id]);
				$response = Http::get(config('app.url') . '/api/hotels', $params);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch hotels']);
			}),

		(new Tool())
			->as('get_hotel_details')
			->for('Get detailed information about a specific hotel including rooms, amenities, location, and reviews')
			->withStringParameter('hotel_id', 'The ID of the hotel')
			->using(function (string $hotel_id) {
				$response = Http::get(config('app.url') . '/api/hotels/details/' . $hotel_id);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch hotel details']);
			}),

		(new Tool())
			->as('get_cheapest_room')
			->for('Get the cheapest available room for a specific hotel')
			->withStringParameter('hotel_id', 'The ID of the hotel')
			->using(function (string $hotel_id) {
				$response = Http::get(config('app.url') . '/api/hotels/cheapest-room/' . $hotel_id);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch cheapest room']);
			}),

		(new Tool())
			->as('get_all_rooms')
			->for('Get list of all available rooms with details like capacity, price, and amenities')
			->withStringParameter('hotel_id', 'Filter rooms by hotel ID (optional)')
			->using(function (string $hotel_id = '') {
				$params = $hotel_id ? ['hotel_id' => $hotel_id] : [];
				$response = Http::get(config('app.url') . '/api/hotels/rooms', $params);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch rooms']);
			}),

		(new Tool())
			->as('get_room_details')
			->for('Get detailed information about a specific room including capacity, amenities, and availability')
			->withStringParameter('room_id', 'The ID of the room')
			->using(function (string $room_id) {
				$response = Http::get(config('app.url') . '/api/hotels/rooms/' . $room_id);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch room details']);
			}),

		(new Tool())
			->as('calculate_room_booking_price')
			->for('Calculate the total price for booking a room based on dates and number of guests')
			->withStringParameter('room_id', 'The ID of the room')
			->withStringParameter('check_in', 'Check-in date in format YYYY-MM-DD')
			->withStringParameter('check_out', 'Check-out date in format YYYY-MM-DD')
			->withStringParameter('guests', 'Number of guests')
			->using(function (string $room_id, string $check_in, string $check_out, string $guests) {
				$params = ['check_in' => $check_in, 'check_out' => $check_out, 'guests' => $guests];
				$response = Http::get(config('app.url') . '/api/hotels/rooms/calculate/booking-room/price/' . $room_id, $params);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to calculate price']);
			}),

		(new Tool())
			->as('get_all_trips')
			->for('Get list of all available trips with destinations, activities, and schedules')
			->withStringParameter('category_id', 'Filter trips by category ID (optional)')
			->withStringParameter('sub_category_id', 'Filter trips by sub-category ID (optional)')
			->using(function (string $category_id = '', string $sub_category_id = '') {
				$params = array_filter(['category_id' => $category_id, 'sub_category_id' => $sub_category_id]);
				$response = Http::get(config('app.url') . '/api/trips', $params);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch trips']);
			}),

		(new Tool())
			->as('get_trip_details')
			->for('Get detailed information about a specific trip including itinerary, included services, and pricing')
			->withStringParameter('trip_id', 'The ID of the trip')
			->using(function (string $trip_id) {
				$response = Http::get(config('app.url') . '/api/trips/' . $trip_id);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch trip details']);
			}),

		(new Tool())
			->as('calculate_trip_booking_price')
			->for('Calculate the total price for booking a trip based on date and number of guests')
			->withStringParameter('trip_id', 'The ID of the trip')
			->withStringParameter('booking_date', 'Booking date in format YYYY-MM-DD')
			->withStringParameter('guests', 'Number of guests')
			->using(function (string $trip_id, string $booking_date, string $guests) {
				$params = ['booking_date' => $booking_date, 'guests' => $guests];
				$response = Http::get(config('app.url') . '/api/trips/calculate/booking-trip/price/' . $trip_id, $params);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to calculate price']);
			}),

		(new Tool())
			->as('get_hotel_types')
			->for('Get list of all hotel types (e.g., resort, apartment, hotel, etc.)')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/hotel-types');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch hotel types']);
			}),

		(new Tool())
			->as('get_cities')
			->for('Get list of all available cities where hotels and trips are offered')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/cities');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch cities']);
			}),

		(new Tool())
			->as('get_categories')
			->for('Get list of all trip categories (e.g., adventure, cultural, beach, etc.)')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/categories');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch categories']);
			}),

		(new Tool())
			->as('get_sub_categories')
			->for('Get list of all trip sub-categories for more specific trip filtering')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/sub-categories');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch sub-categories']);
			}),

		(new Tool())
			->as('get_booking_status')
			->for('Get list of all possible booking status values (e.g., pending, confirmed, cancelled)')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/booking-status');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch booking status']);
			}),

		(new Tool())
			->as('get_faqs')
			->for('Get list of frequently asked questions and their answers')
			->using(function () {
				$response = Http::get(config('app.url') . '/api/chat/faqs');
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch FAQs']);
			}),

		(new Tool())
			->as('get_faq_details')
			->for('Get detailed information about a specific frequently asked question')
			->withStringParameter('faq_id', 'The ID of the FAQ')
			->using(function (string $faq_id) {
				$response = Http::get(config('app.url') . '/api/chat/faqs/' . $faq_id);
				return $response->successful() ? $response->body() : json_encode(['error' => 'Failed to fetch FAQ details']);
			}),
	];

	try {
		$response = Prism::text()
			->using(Provider::Gemini, 'gemini-2.0-flash')
			->withPrompt($message)
			->withTools($tools)
			->asText();

		return response()->json([
			'success' => true,
			'message' => $response->text,
			'usage' => $response->usage ?? null,
			'tool_calls' => $response->toolCalls ?? null,
			'steps' => $response->steps ?? null,
		]);
	} catch (PrismRateLimitedException $e) {
		return response()->json([
			'success' => false,
			'error' => 'Rate limit exceeded. Please try again later.',
			'details' => $e->getMessage(),
		], 429);
	} catch (PrismException $e) {
		return response()->json([
			'success' => false,
			'error' => 'AI service error occurred.',
			'details' => $e->getMessage(),
		], 500);
	} catch (\Exception $e) {
		return response()->json([
			'success' => false,
			'error' => 'An unexpected error occurred.',
			'details' => $e->getMessage(),
		], 500);
	}
});



