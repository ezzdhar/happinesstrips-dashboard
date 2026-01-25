<?php

namespace App\Traits;

use Carbon\Carbon;

trait CalculatesHotelBookingPrice
{
	use ApiResponse;

	/**
	 * حساب السعر الإجمالي للحجز مع تطبيق جميع سياسات الغرفة (البالغين والأطفال)
	 *
	 * @param string|\DateTimeInterface $checkIn
	 * @param string|\DateTimeInterface $checkOut
	 * @param array $childrenAges Array of children ages
	 * @param string $currency "egp" | "usd"
	 */
	public function calculateBookingPrice($checkIn, $checkOut, int $adultsCount, array $childrenAges = [], string $currency = 'egp'): array
	{
		$startDate = $checkIn instanceof \DateTimeInterface ? Carbon::instance($checkIn) : Carbon::parse($checkIn);
		$endDate = $checkOut instanceof \DateTimeInterface ? Carbon::instance($checkOut) : Carbon::parse($checkOut);

		$currency = $this->normalizeCurrency($currency);
		if (!$currency) {
			return $this->errorResponse('Invalid currency');
		}

		// التحقق من التغطية لهذه العملة المحددة
		if (!$this->isDateRangeCovered($startDate, $endDate, $currency)) {
			$uncoveredDates = $this->getUncoveredDates($startDate, $endDate, $currency);
			return $this->errorResponse(
				__('lang.date_range_not_covered'),
				['uncovered_dates' => $uncoveredDates]
			);
		}

		$hotel = $this->hotel;
		$nightsCount = $startDate->diffInDays($endDate);

		if ($nightsCount <= 0) {
			return $this->errorResponse('Invalid date range');
		}

		// حساب السعر للعملة المحددة
		$adultPricePerPerson = $this->totalPriceForPeriod($startDate, $endDate, $currency);

		if ($adultPricePerPerson === null || $adultPricePerPerson <= 0) {
			return $this->errorResponse('Unable to calculate price');
		}

		$adultsTotal = $adultPricePerPerson * $adultsCount;

		// حساب أسعار الأطفال
		$childrenBreakdown = $this->calculateChildrenPricing($adultPricePerPerson, $childrenAges);

		$grandTotal = $adultsTotal + $childrenBreakdown['total'];

		$dailyBreakdown = $this->priceBreakdownForPeriod($startDate, $endDate, $currency);

		return [
			'success' => true,
			'room_id' => $this->id,
			'room_name' => $this->name,
			'hotel_id' => $hotel->id,
			'hotel_name' => $hotel->name,
			'city' => $hotel->city->name,
			'rating' => (int) $hotel->rating,
			'check_in' => $startDate->format('Y-m-d'),
			'check_out' => $endDate->format('Y-m-d'),
			'nights_count' => (int) $nightsCount,
			'currency' => strtoupper($currency),

			'adults_count' => $adultsCount,
			'adult_price_per_person' => $adultPricePerPerson,
			'adults_total' => $adultsTotal,

			'children_count' => count($childrenAges),
			'children_breakdown' => $childrenBreakdown['breakdown'],
			'children_total' => $childrenBreakdown['total'],

			'subtotal' => $adultsTotal + $childrenBreakdown['total'],
			'total_price' => $grandTotal,

			'daily_breakdown' => $dailyBreakdown['days'],
			'price_per_night_average' => $nightsCount > 0 ? round($adultPricePerPerson / $nightsCount, 2) : 0,

			'policies' => $this->getRoomPolicySummary(),
		];
	}

	/**
	 * حساب أسعار الأطفال بناءً على سياسة الغرفة
	 */
	protected function calculateChildrenPricing(float $adultPricePerPerson, array $childrenAges): array
	{
		$breakdown = [];
		$total = 0;
		$adultAge = $this->adult_age ?? 12;
		$policies = $this->childrenPolicies ?? collect();

		foreach ($childrenAges as $index => $age) {
			$age = (int) $age;
			$childNumber = $index + 1;
			$category = '';
			$percentage = 0;
			$price = 0;

			if ($age >= $adultAge) {
				$category = 'adult';
				$percentage = 100;
				$price = $adultPricePerPerson;
			} else {
				$childPolicy = $policies
					->where('child_number', $childNumber)
					->first(fn($policy) => $age >= $policy->from_age && $age <= $policy->to_age);

				if ($childPolicy) {
					$percentage = $childPolicy->price_percentage ?? 0;

					if ($percentage == 0) {
						$category = 'free';
						$price = 0;
					} else {
						$category = 'child';
						$price = ($adultPricePerPerson * $percentage) / 100;
					}
				} else {
					$category = 'adult';
					$percentage = 100;
					$price = $adultPricePerPerson;
				}
			}

			$breakdown[] = [
				'child_number' => $childNumber,
				'age' => $age,
				'category' => $category,
				'category_label' => $this->getChildCategoryLabel($category, $age),
				'percentage' => $percentage,
				'price' => round($price, 2),
			];

			$total += $price;
		}

		return [
			'breakdown' => $breakdown,
			'total' => round($total, 2),
		];
	}

	protected function getRoomPolicySummary(): array
	{
		return [
			'adult_age' => $this->adult_age ?? 12,
			'has_children_policy' => $this->childrenPolicies->isNotEmpty(),
		];
	}

	protected function getChildCategoryLabel(string $category, int $age): string
	{
		$adultAge = $this->adult_age ?? 12;

		return match ($category) {
			'free' => __('lang.free') . " ({$age} " . __('lang.years') . ")",
			'adult' => __('lang.charged_as_adult') . " ({$age} " . __('lang.years') . ", ≥ {$adultAge})",
			'child' => __('lang.child_price') . " ({$age} " . __('lang.years') . ')',
			default => __('lang.unknown'),
		};
	}

	protected function errorResponse(string $message, array $data = []): array
	{
		return array_merge([
			'success' => false,
			'error' => $message,
		], $data);
	}

	/**
	 * حساب سعر الحجز البسيط بدون تفصيل الأطفال
	 */
	public function calculateSimpleBookingPrice($checkIn, $checkOut, int $adultsCount, int $childrenCount = 0, string $currency = 'egp'): array
	{
		$startDate = $checkIn instanceof \DateTimeInterface ? Carbon::instance($checkIn) : Carbon::parse($checkIn);
		$endDate = $checkOut instanceof \DateTimeInterface ? Carbon::instance($checkOut) : Carbon::parse($checkOut);

		$currency = $this->normalizeCurrency($currency);

		if (!$currency) {
			return $this->errorResponse('Invalid currency');
		}

		if (!$this->isDateRangeCovered($startDate, $endDate, $currency)) {
			return $this->errorResponse(__('lang.date_range_not_covered'));
		}

		$nightsCount = $startDate->diffInDays($endDate);
		$adultPricePerPerson = $this->totalPriceForPeriod($startDate, $endDate, $currency);
		$adultsTotal = $adultPricePerPerson * $adultsCount;

		return [
			'success' => true,
			'nights_count' => $nightsCount,
			'adults_count' => $adultsCount,
			'children_count' => $childrenCount,
			'adult_price_per_person' => $adultPricePerPerson,
			'adults_total' => $adultsTotal,
			'price_per_night' => $nightsCount > 0 ? $adultPricePerPerson / $nightsCount : 0,
			'currency' => strtoupper($currency),
		];
	}
}
