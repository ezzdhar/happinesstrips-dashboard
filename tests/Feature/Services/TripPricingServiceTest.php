<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Enums\TripType;
use App\Models\Trip;
use App\Services\TripPricingService;
use Carbon\Carbon;

beforeEach(function () {
    // Create a fixed trip for testing
    $this->fixedTrip = Trip::factory()->create([
        'type' => TripType::Fixed,
        'status' => Status::Active,
        'duration_from' => now()->addDays(10),
        'duration_to' => now()->addDays(17),
        'price' => 5000, // EGP per adult for entire trip
        'free_child_age' => 5,
        'adult_age' => 12,
        'first_child_price_percentage' => 50,
        'second_child_price_percentage' => 40,
        'third_child_price_percentage' => 30,
        'additional_child_price_percentage' => 25,
    ]);

    // Create a flexible trip for testing
    $this->flexibleTrip = Trip::factory()->create([
        'type' => TripType::Flexible,
        'status' => Status::Active,
        'price' => 800, // EGP per adult per night
        'free_child_age' => 5,
        'adult_age' => 12,
        'first_child_price_percentage' => 50,
        'second_child_price_percentage' => 40,
        'third_child_price_percentage' => 30,
        'additional_child_price_percentage' => 25,
    ]);
});

test('calculates fixed trip price for adults only', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [],
        currency: 'egp'
    );

    expect($result['nights_count'])->toBe(7);
    expect($result['adults_price'])->toBe(10000.0); // 2 adults * 5000
    expect($result['children_breakdown']['total_children_price'])->toBe(0.0);
    expect($result['total_price'])->toBe(10000.0);
});

test('calculates fixed trip price with one child', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [8], // One child age 8 (50% of adult price)
        currency: 'egp'
    );

    expect($result['adults_price'])->toBe(10000.0); // 2 adults * 5000
    expect($result['children_breakdown']['total_children_price'])->toBe(2500.0); // 1 child * 5000 * 50%
    expect($result['total_price'])->toBe(12500.0);
    expect($result['children_breakdown']['first_child'])->not->toBeNull();
    expect($result['children_breakdown']['first_child']['age'])->toBe(8);
    expect($result['children_breakdown']['first_child']['price'])->toBe(2500.0);
});

test('calculates fixed trip price with multiple children', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [8, 6, 10], // Three children with different percentages
        currency: 'egp'
    );

    expect($result['adults_price'])->toBe(10000.0);
    // First child: 5000 * 50% = 2500
    // Second child: 5000 * 40% = 2000
    // Third child: 5000 * 30% = 1500
    // Total children: 6000
    expect($result['children_breakdown']['total_children_price'])->toBe(6000.0);
    expect($result['total_price'])->toBe(16000.0);
});

test('calculates fixed trip price with free children', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [3, 8], // One free child (age 3), one paid child (age 8)
        currency: 'egp'
    );

    expect($result['adults_price'])->toBe(10000.0);
    expect($result['children_breakdown']['total_children_price'])->toBe(2500.0); // Only the 8-year-old
    expect($result['total_price'])->toBe(12500.0);
    expect(count($result['children_breakdown']['free_children']))->toBe(1);
    expect($result['children_breakdown']['first_child']['age'])->toBe(8);
});

test('calculates flexible trip price for adults only', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->flexibleTrip,
        checkIn: now()->format('Y-m-d'),
        checkOut: now()->addDays(5)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [],
        currency: 'egp'
    );

    expect($result['nights_count'])->toBe(5);
    expect($result['adults_price'])->toBe(8000.0); // 2 adults * 800 * 5 nights
    expect($result['children_breakdown']['total_children_price'])->toBe(0.0);
    expect($result['total_price'])->toBe(8000.0);
});

test('calculates flexible trip price with children', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->flexibleTrip,
        checkIn: now()->format('Y-m-d'),
        checkOut: now()->addDays(5)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [8, 6], // Two children
        currency: 'egp'
    );

    expect($result['nights_count'])->toBe(5);
    expect($result['adults_price'])->toBe(8000.0); // 2 adults * 800 * 5 nights
    // First child: 800 * 50% * 5 = 2000
    // Second child: 800 * 40% * 5 = 1600
    expect($result['children_breakdown']['total_children_price'])->toBe(3600.0);
    expect($result['total_price'])->toBe(11600.0);
});

test('converts to USD currency', function () {
    config(['booking.exchange_rate' => 30.0]);

    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 2,
        childrenAges: [],
        currency: 'usd'
    );

    expect($result['currency'])->toBe('usd');
    expect($result['total_price'])->toBe(round(10000 / 30, 2)); // Convert to USD
});

test('handles additional children beyond third child', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 1,
        childrenAges: [8, 7, 6, 9], // Four children
        currency: 'egp'
    );

    expect($result['adults_price'])->toBe(5000.0);
    // First: 5000 * 50% = 2500
    // Second: 5000 * 40% = 2000
    // Third: 5000 * 30% = 1500
    // Fourth: 5000 * 25% = 1250 (additional)
    expect($result['children_breakdown']['total_children_price'])->toBe(7250.0);
    expect($result['total_price'])->toBe(12250.0);
    expect(count($result['children_breakdown']['additional_children']))->toBe(1);
});

test('treats children at adult age as additional with full price', function () {
    $result = TripPricingService::calculateTripPriceWithAges(
        trip: $this->fixedTrip,
        checkIn: now()->addDays(10)->format('Y-m-d'),
        checkOut: now()->addDays(17)->format('Y-m-d'),
        adultsCount: 1,
        childrenAges: [12], // Child at adult_age threshold
        currency: 'egp'
    );

    expect($result['adults_price'])->toBe(5000.0);
    // Child at adult age gets additional_child_price_percentage
    expect($result['children_breakdown']['total_children_price'])->toBe(1250.0); // 5000 * 25%
    expect(count($result['children_breakdown']['additional_children']))->toBe(1);
});
