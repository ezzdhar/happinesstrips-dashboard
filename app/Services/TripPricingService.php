<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Trip;
use App\Traits\CalculatesTripBookingPrice;

class TripPricingService
{
    use CalculatesTripBookingPrice;

    /**
     * Calculate trip pricing with all necessary data using children ages.
     *
     * @param  Trip  $trip  The trip model
     * @param  string  $checkIn  Check-in date (Y-m-d format)
     * @param  string  $checkOut  Check-out date (Y-m-d format)
     * @param  int  $adultsCount  Number of adults
     * @param  array  $childrenAges  Array of children ages for precise calculation
     * @param  string  $currency  Currency (egp or usd)
     * @return array Result containing all pricing details
     */
    public static function calculateTripPriceWithAges(Trip $trip, string $checkIn, string $checkOut, int $adultsCount, array $childrenAges = [], string $currency = 'egp'): array
    {
        $instance = new self;

        return $instance->calculateTripPriceInternal(
            trip: $trip,
            checkIn: $checkIn,
            checkOut: $checkOut,
            adultsCount: $adultsCount,
            childrenCount: count($childrenAges),
            childrenAges: $childrenAges,
            currency: $currency
        );
    }

    /**
     * Calculate trip pricing with all necessary data (legacy method for backward compatibility).
     *
     * @param  Trip  $trip  The trip model
     * @param  string  $checkIn  Check-in date (Y-m-d format)
     * @param  string  $checkOut  Check-out date (Y-m-d format)
     * @param  int  $adultsCount  Number of adults
     * @param  int  $childrenCount  Number of children at or above threshold age (charged as adults)
     * @param  int  $freeChildrenCount  Number of children below threshold age (free)
     * @param  string  $currency  Currency (egp or usd)
     * @return array Result containing all pricing details
     */
    public static function calculateTripPrice(Trip $trip, string $checkIn, string $checkOut, int $adultsCount, int $childrenCount = 0, int $freeChildrenCount = 0, string $currency = 'egp'): array
    {
        // Build children ages array based on counts
        // This is a simplified approach - ideally we should receive actual ages
        $childrenAges = [];
        $adultAge = (int) $trip->adult_age;
        $freeChildAge = (int) $trip->free_child_age;
        $midAge = (int) (($adultAge + $freeChildAge) / 2);

        // Add paid children (above free age but below adult age)
        for ($i = 0; $i < $childrenCount; $i++) {
            $childrenAges[] = $midAge;
        }

        // Add free children (below free age)
        for ($i = 0; $i < $freeChildrenCount; $i++) {
            $childrenAges[] = $freeChildAge - 1;
        }

        // Use the new calculation method
        $instance = new self;
        $result = $instance->calculateTripPriceInternal(
            trip: $trip,
            checkIn: $checkIn,
            checkOut: $checkOut,
            adultsCount: $adultsCount,
            childrenCount: count($childrenAges),
            childrenAges: $childrenAges,
            currency: $currency
        );

        return [
            'trip_id' => $trip->id,
            'trip_name' => $trip->name,
            'trip_type' => $trip->type->value,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights_count' => $result['nights_count'],
            'adults_count' => $adultsCount,
            'children_count' => $childrenCount,
            'free_children_count' => $freeChildrenCount,
            'total_paying_people' => $adultsCount + $childrenCount,
            'total_people' => $adultsCount + $childrenCount + $freeChildrenCount,
            'currency' => $currency,
            'base_price' => round($result['base_price'], 2),
            'calculated_price' => round($result['calculated_price'], 2),
            'total_price' => round($result['total_price'], 2),
            'child_age_threshold' => self::getChildAgeThreshold(),
        ];
    }

    /**
     * Calculate total price for a fixed trip (backward compatibility).
     */
    public static function calculateFixedTripPrice(
        float $basePrice,
        int $adultsCount,
        int $childrenCount = 0
    ): array {
        $totalPayingPeople = $adultsCount + $childrenCount;
        $calculatedPrice = $basePrice;
        $totalPrice = $totalPayingPeople * $basePrice;

        return [
            'calculated_price' => round($calculatedPrice, 2),
            'total_price' => round($totalPrice, 2),
        ];
    }

    /**
     * Calculate total price for a flexible trip (backward compatibility).
     */
    public static function calculateFlexibleTripPrice(
        float $basePricePerNight,
        int $nightsCount,
        int $adultsCount,
        int $childrenCount = 0
    ): array {
        if ($nightsCount < 1) {
            return [
                'calculated_price' => 0,
                'total_price' => 0,
            ];
        }

        $totalPayingPeople = $adultsCount + $childrenCount;
        $calculatedPrice = $basePricePerNight;
        $totalPrice = $totalPayingPeople * $basePricePerNight * $nightsCount;

        return [
            'calculated_price' => round($calculatedPrice, 2),
            'total_price' => round($totalPrice, 2),
        ];
    }

    /**
     * Get empty result for invalid calculations.
     */
    private static function getEmptyResult(Trip $trip, string $checkIn, string $checkOut, int $nightsCount, int $adultsCount, int $childrenCount, int $freeChildrenCount, string $currency): array
    {
        return [
            'trip_id' => $trip->id,
            'trip_name' => $trip->name,
            'trip_type' => $trip->type->value,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights_count' => $nightsCount,
            'adults_count' => $adultsCount,
            'children_count' => $childrenCount,
            'free_children_count' => $freeChildrenCount,
            'total_paying_people' => 0,
            'total_people' => 0,
            'currency' => $currency,
            'base_price' => 0,
            'calculated_price' => 0,
            'total_price' => 0,
            'child_age_threshold' => self::getChildAgeThreshold(),
        ];
    }

    /**
     * Get the child age threshold from config.
     */
    public static function getChildAgeThreshold(): int
    {
        return (int) config('booking.child_age_threshold', 12);
    }
}
