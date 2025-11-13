<?php

use App\Livewire\Dashboard\BookingTrip\CreateBookingTrip;
use App\Models\Trip;
use App\Models\User;
use Livewire\Livewire;

uses()->group('booking');

beforeEach(function () {
    $this->artisan('migrate:fresh');
});

test('fixed trip calculates price correctly with adults only', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
        'adults_count' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 3)
        ->set('children_count', 0)
        ->set('free_children_count', 0)
        ->assertSet('calculated_price', 1000)
        ->assertSet('total_price', 3000); // 3 adults × 1000
});

test('fixed trip calculates price correctly with adults and paying children', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
        'adults_count' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 2)
        ->set('children_count', 2) // Children 12+ charged as adults
        ->set('free_children_count', 0)
        ->assertSet('calculated_price', 1000)
        ->assertSet('total_price', 4000); // (2 adults + 2 children) × 1000
});

test('fixed trip with free children does not affect price', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
        'adults_count' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 2)
        ->set('children_count', 0)
        ->set('free_children_count', 3) // Free children under 12
        ->assertSet('calculated_price', 1000)
        ->assertSet('total_price', 2000); // 2 adults × 1000, free children don't count
});

test('flexible trip calculates price correctly with nights', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'flexible',
        'price' => ['egp' => 500, 'usd' => 50], // per person per night
        'adults_count' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('nights_count', 3)
        ->set('adults_count', 2)
        ->set('children_count', 1)
        ->set('free_children_count', 0)
        ->assertSet('calculated_price', 500)
        ->assertSet('total_price', 4500); // (2 adults + 1 child) × 500 × 3 nights
});

test('flexible trip with free children calculates correctly', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'flexible',
        'price' => ['egp' => 500, 'usd' => 50],
        'adults_count' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('nights_count', 2)
        ->set('adults_count', 2)
        ->set('children_count', 1) // Charged
        ->set('free_children_count', 2) // Free
        ->assertSet('calculated_price', 500)
        ->assertSet('total_price', 3000); // (2 adults + 1 paying child) × 500 × 2 nights
});

test('child age threshold is configurable', function () {
    expect(config('booking.child_age_threshold'))->toBe(12);
});

test('price updates when adults count changes', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 2)
        ->assertSet('total_price', 2000)
        ->set('adults_count', 4)
        ->assertSet('total_price', 4000);
});

test('price updates when paying children count changes', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 2)
        ->set('children_count', 0)
        ->assertSet('total_price', 2000)
        ->set('children_count', 2)
        ->assertSet('total_price', 4000);
});

test('currency switch updates price correctly', function () {
    $user = User::factory()->create();

    $trip = Trip::factory()->create([
        'type' => 'fixed',
        'price' => ['egp' => 1000, 'usd' => 100],
    ]);

    Livewire::actingAs($user)
        ->test(CreateBookingTrip::class)
        ->set('trip_id', $trip->id)
        ->set('currency', 'egp')
        ->set('adults_count', 2)
        ->assertSet('total_price', 2000)
        ->set('currency', 'usd')
        ->assertSet('total_price', 200);
});
