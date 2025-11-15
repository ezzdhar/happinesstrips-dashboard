<?php

namespace App\Services;

use App\Models\Trip;
use Carbon\Carbon;

class TripPricingService
{
    /**
     * Calculate trip pricing with all necessary data.
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
        // Calculate nights
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nightsCount = $checkInDate->diffInDays($checkOutDate);

        // Get base price from trip
        $basePrice = $trip->price[$currency] ?? 0;

        // Calculate total paying people (adults + children at/above threshold age)
        $totalPayingPeople = $adultsCount + $childrenCount;

        // Calculate total people including free children
        $totalPeople = $adultsCount + $childrenCount + $freeChildrenCount;

        if ($trip->type->value === 'fixed') {
            // Fixed trip: base price is per person for the entire trip duration
            $calculatedPrice = $basePrice;
            $totalPrice = $totalPayingPeople * $basePrice;
        } else {
            // Flexible trip: base price is per person per night
            if ($nightsCount < 1) {
                return self::getEmptyResult($trip, $checkIn, $checkOut, $nightsCount, $adultsCount, $childrenCount, $freeChildrenCount, $currency);
            }

            $calculatedPrice = $basePrice;
            $totalPrice = $totalPayingPeople * $basePrice * $nightsCount;
        }

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
            'total_paying_people' => $totalPayingPeople,
            'total_people' => $totalPeople,
            'currency' => $currency,
            'base_price' => round($basePrice, 2),
            'calculated_price' => round($calculatedPrice, 2),
            'total_price' => round($totalPrice, 2),
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
        return config('booking.child_age_threshold', 12);
    }
}
