<?php

use App\Enums\Status;
use App\Models\Booking;
use App\Models\User;

test('booking can be filtered by status', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $pendingBooking = Booking::factory()->create([
        'status' => Status::Pending,
        'user_id' => $user1->id,
    ]);
    $completedBooking = Booking::factory()->create([
        'status' => Status::Completed,
        'user_id' => $user2->id,
    ]);

    $bookings = Booking::status(Status::Pending)->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->id)->toBe($pendingBooking->id);
});

test('booking can be filtered by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $booking1 = Booking::factory()->create(['user_id' => $user1->id]);
    $booking2 = Booking::factory()->create(['user_id' => $user2->id]);

    $bookings = Booking::user($user1->id)->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->id)->toBe($booking1->id);
});

test('booking can be filtered by trip', function () {
    $user = User::factory()->create();
    $trip1 = \App\Models\Trip::factory()->create();
    $trip2 = \App\Models\Trip::factory()->create();

    $booking1 = Booking::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $trip1->id,
    ]);
    $booking2 = Booking::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $trip2->id,
    ]);

    $bookings = Booking::trip($trip1->id)->get();

    expect($bookings)->toHaveCount(1)
        ->and($bookings->first()->id)->toBe($booking1->id);
});

test('booking number is automatically generated', function () {
    $user = User::factory()->create();
    $trip = \App\Models\Trip::factory()->create();

    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'trip_id' => $trip->id,
    ]);

    expect($booking->booking_number)
        ->toBeString()
        ->toStartWith('BK-');
});


