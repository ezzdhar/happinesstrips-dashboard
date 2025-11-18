<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\TripType;
use App\Models\Trip;
use Carbon\Carbon;

trait CalculatesTripBookingPrice
{
    /**
     * Calculate trip booking price based on trip type and passenger configuration
     *
     * @param  Trip  $trip  The trip model
     * @param  string|Carbon  $checkIn  Check-in date
     * @param  string|Carbon  $checkOut  Check-out date
     * @param  int  $adultsCount  Number of adults
     * @param  int  $childrenCount  Number of children (will be categorized by age)
     * @param  array  $childrenAges  Array of children ages for precise calculation
     * @param  string  $currency  Currency (egp/usd)
     * @return array{nights_count: int, base_price: float, adults_price: float, children_breakdown: array, total_price: float, sub_total: float, currency: string}
     */
    public function calculateTripPriceInternal(Trip $trip, string|Carbon $checkIn, string|Carbon $checkOut, int $adultsCount = 1, int $childrenCount = 0, array $childrenAges = [], string $currency = 'egp'): array
    {
        // Parse dates
        $checkInDate = $checkIn instanceof Carbon ? $checkIn : Carbon::parse($checkIn);
        $checkOutDate = $checkOut instanceof Carbon ? $checkOut : Carbon::parse($checkOut);

        // Calculate nights
        $nightsCount = $checkInDate->diffInDays($checkOutDate);
        $nightsCount = (int) max(1, $nightsCount); // Minimum 1 night

        // Get base price based on trip type and currency
        $basePrice = $this->getTripBasePrice($trip, $nightsCount, $currency);

        // Calculate adults price
        $adultsPrice = $this->calculateAdultsPrice($trip, $basePrice, $adultsCount, $nightsCount);

        // Calculate children price with breakdown
        $childrenBreakdown = $this->calculateChildrenPrice(
            trip: $trip,
            basePrice: $basePrice,
            childrenAges: $childrenAges,
            nightsCount: $nightsCount
        );

        // Total calculated price
        $totalPrice = $adultsPrice + $childrenBreakdown['total_children_price'];

        return [
            'nights_count' => $nightsCount,
            'base_price' => $basePrice,
            'adults_price' => $adultsPrice,
            'children_breakdown' => $childrenBreakdown,
            'total_price' => round($totalPrice, 2),
            'sub_total' => round($totalPrice, 2),
            'currency' => $currency,
        ];
    }

    /**
     * Get base price based on trip type
     * - Fixed: price is for entire trip per adult
     * - Flexible: price is per night per adult
     */
    protected function getTripBasePrice(Trip $trip, int $nightsCount, string $currency): float
    {
        // Extract price from JSON based on currency
        $priceData = is_array($trip->price) ? $trip->price : json_decode($trip->price, true);
        $price = (float) ($priceData[$currency] ?? 0);

        // For flexible trips, price is already per night
        // For fixed trips, we need to calculate per-night equivalent for consistency
        if ($trip->type->value === TripType::Fixed) {
            // Price is for entire trip, return as is
            return $price;
        }

        // For flexible trips, price is per night
        return $price;
    }

    /**
     * Calculate total adults price
     */
    protected function calculateAdultsPrice(Trip $trip, float $basePrice, int $adultsCount, int $nightsCount): float
    {
        if ($adultsCount <= 0) {
            return 0;
        }
        if ($trip->type->value === TripType::Fixed) {
            // Fixed trip: basePrice is already the full trip price per adult
            return $basePrice * $adultsCount;
        }

        // Flexible trip: basePrice is per night per adult
        return $basePrice * $adultsCount * $nightsCount;
    }

    /**
     * Calculate children price with detailed breakdown
     */
    protected function calculateChildrenPrice(Trip $trip, float $basePrice, array $childrenAges, int $nightsCount): array
    {
        $breakdown = [
            'free_children' => [],
            'first_child' => null,
            'second_child' => null,
            'third_child' => null,
            'additional_children' => [],
            'total_children_price' => 0,
        ];

        if (empty($childrenAges)) {
            return $breakdown;
        }

        // Sort ages to process them in order
        sort($childrenAges);

        $freeChildAge = (int) $trip->free_child_age;
        $adultAge = (int) $trip->adult_age;

        $paidChildrenCount = 0;

        foreach ($childrenAges as $age) {
            $age = (int) $age;

            // Free children (below free_child_age)
            if ($age < $freeChildAge) {
                $breakdown['free_children'][] = [
                    'age' => $age,
                    'price' => 0,
                ];

                continue;
            }

            // Adults (>= adult_age) - should be counted as adults, not children
            if ($age >= $adultAge) {
                // This child should actually be counted as an adult
                // For now, we'll treat them as additional child with full price
                $childPrice = $this->getChildPrice(
                    trip: $trip,
                    basePrice: $basePrice,
                    childPosition: 4, // Additional child
                    nightsCount: $nightsCount
                );

                $breakdown['additional_children'][] = [
                    'age' => $age,
                    'price' => $childPrice,
                    'note' => 'Charged as adult (age >= '.$adultAge.')',
                ];

                $breakdown['total_children_price'] += $childPrice;

                continue;
            }

            // Paid children (between free_child_age and adult_age)
            $paidChildrenCount++;

            $childPrice = $this->getChildPrice(
                trip: $trip,
                basePrice: $basePrice,
                childPosition: $paidChildrenCount,
                nightsCount: $nightsCount
            );

            $childData = [
                'age' => $age,
                'price' => $childPrice,
            ];

            if ($paidChildrenCount === 1) {
                $breakdown['first_child'] = $childData;
            } elseif ($paidChildrenCount === 2) {
                $breakdown['second_child'] = $childData;
            } elseif ($paidChildrenCount === 3) {
                $breakdown['third_child'] = $childData;
            } else {
                $breakdown['additional_children'][] = $childData;
            }

            $breakdown['total_children_price'] += $childPrice;
        }

        return $breakdown;
    }

    /**
     * Get price for a specific child based on their position
     */
    protected function getChildPrice(Trip $trip, float $basePrice, int $childPosition, int $nightsCount): float
    {
        $percentage = match ($childPosition) {
            1 => (float) $trip->first_child_price_percentage,
            2 => (float) $trip->second_child_price_percentage,
            3 => (float) $trip->third_child_price_percentage,
            default => (float) $trip->additional_child_price_percentage,
        };

        $pricePerChild = $basePrice * ($percentage / 100);

        if ($trip->type->value === TripType::Fixed) {
            // Fixed trip: basePrice is already full trip price
            return $pricePerChild;
        }

        // Flexible trip: multiply by nights
        return $pricePerChild * $nightsCount;
    }

    /**
     * Get simplified pricing summary for UI display
     */
    public function getTripPricingSummary(Trip $trip, string|Carbon $checkIn, string|Carbon $checkOut, int $adultsCount, array $childrenAges, string $currency = 'egp'): array
    {
        $calculation = $this->calculateTripPriceInternal(
            trip: $trip,
            checkIn: $checkIn,
            checkOut: $checkOut,
            adultsCount: $adultsCount,
            childrenCount: count($childrenAges),
            childrenAges: $childrenAges,
            currency: $currency
        );

        return [
            'trip_name' => $trip->name,
            'trip_type' => $trip->type->value,
            'nights' => $calculation['nights_count'],
            'adults' => $adultsCount,
            'children' => count($childrenAges),
            'base_price_label' => $trip->type->value === TripType::Fixed
                ? 'السعر الأساسي للرحلة الكاملة'
                : 'السعر الأساسي لليلة الواحدة',
            'base_price' => $calculation['base_price'],
            'adults_total' => $calculation['adults_price'],
            'children_total' => $calculation['children_breakdown']['total_children_price'],
            'sub_total' => $calculation['sub_total'],
            'total' => $calculation['total_price'],
            'currency' => strtoupper($currency),
            'currency_symbol' => $currency === 'usd' ? '$' : 'ج.م',
        ];
    }
}
