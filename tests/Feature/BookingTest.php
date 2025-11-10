<?php

use App\Enums\Status;
use App\Enums\TripType;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Trip;
use App\Models\User;
use App\Services\BookingPriceCalculator;

test('can create a booking for fixed trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create([
        'type' => TripType::Fixed,
        'price' => ['egp' => 10000, 'usd' => 500],
    ]);

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $trip->id,
        'trip_price' => $trip->price,
        'total_price' => $trip->price,
        'status' => Status::Pending,
    ]);

    expect($booking)->toBeInstanceOf(Booking::class)
        ->and($booking->user_id)->toBe($user->id)
        ->and($booking->trip_id)->toBe($trip->id)
        ->and($booking->status)->toBe(Status::Pending)
        ->and($booking->booking_number)->toStartWith('BK-');
});

test('can create a booking for flexible trip', function () {
    $user = User::factory()->create();
    $trip = Trip::factory()->create([
        'type' => TripType::Flexible,
        'price' => ['egp' => 1000, 'usd' => 50],
    ]);

    $checkIn = now();
    $checkOut = now()->addDays(7);

    $booking = Booking::factory()->flexible()->create([
        'user_id' => $user->id,
        'trip_id' => $trip->id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'nights_count' => 7,
    ]);

    expect($booking->nights_count)->toBe(7)
        ->and($booking->check_in)->not->toBeNull()
        ->and($booking->check_out)->not->toBeNull();
});

test('can add hotels and rooms to booking', function () {
    $booking = Booking::factory()->create();
    $hotel = Hotel::factory()->create();
    $room = Room::factory()->create(['hotel_id' => $hotel->id]);

    $bookingHotel = BookingHotel::create([
        'booking_id' => $booking->id,
        'hotel_id' => $hotel->id,
        'room_id' => $room->id,
        'room_price' => ['egp' => 2000, 'usd' => 100],
        'rooms_count' => 2,
    ]);

    expect($booking->bookingHotels)->toHaveCount(1)
        ->and($bookingHotel->hotel_id)->toBe($hotel->id)
        ->and($bookingHotel->room_id)->toBe($room->id);
});

test('can add travelers to booking', function () {
    $booking = Booking::factory()->create([
        'adults_count' => 2,
        'children_count' => 1,
    ]);

    BookingTraveler::factory()->count(2)->create([
        'booking_id' => $booking->id,
        'type' => 'adult',
    ]);

    BookingTraveler::factory()->child()->create([
        'booking_id' => $booking->id,
    ]);

    expect($booking->travelers)->toHaveCount(3)
        ->and($booking->travelers->where('type', 'adult'))->toHaveCount(2)
        ->and($booking->travelers->where('type', 'child'))->toHaveCount(1);
});

test('booking price calculator works for fixed trip', function () {
    $trip = Trip::factory()->create([
        'type' => TripType::Fixed,
        'price' => ['egp' => 10000, 'usd' => 500],
    ]);

    $calculator = new BookingPriceCalculator();
    $result = $calculator->calculate($trip);

    expect($result['trip_price'])->toBe($trip->price)
        ->and($result['total_price']['egp'])->toBe(10000.0)
        ->and($result['total_price']['usd'])->toBe(500.0)
        ->and($result['nights'])->toBeNull();
});

test('booking price calculator works for flexible trip', function () {
    $trip = Trip::factory()->create([
        'type' => TripType::Flexible,
        'price' => ['egp' => 1000, 'usd' => 50],
    ]);

    $checkIn = now();
    $checkOut = now()->addDays(7);

    $calculator = new BookingPriceCalculator();
    $result = $calculator->calculate($trip, [], $checkIn, $checkOut);

    expect($result['total_price']['egp'])->toBe(7000.0)
        ->and($result['total_price']['usd'])->toBe(350.0)
        ->and($result['nights'])->toBe(7);
});

test('booking scopes work correctly', function () {
    $user = User::factory()->create();

    Booking::factory()->count(3)->create(['user_id' => $user->id]);
    Booking::factory()->count(2)->pending()->create();
    Booking::factory()->confirmed()->create();

    expect(Booking::user($user->id)->get())->toHaveCount(3)
        ->and(Booking::status(Status::Pending)->get())->toHaveCount(2)
        ->and(Booking::status(Status::Confirmed)->get())->toHaveCount(1);
});

test('trip has bookings relationship', function () {
    $trip = Trip::factory()->create();
    Booking::factory()->count(3)->create(['trip_id' => $trip->id]);

    expect($trip->bookings)->toHaveCount(3);
});

test('user has bookings relationship', function () {
    $user = User::factory()->create();
    Booking::factory()->count(2)->create(['user_id' => $user->id]);

    expect($user->bookings)->toHaveCount(2);
});

