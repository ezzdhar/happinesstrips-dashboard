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
	 * المنطق الجديد:
	 * - الأطفال >= adult_age يُحاسبون كبالغين
	 * - الأطفال الزائدون عن children_count للغرفة يُحاسبون كبالغين (الأكبر سناً أولاً)
	 * - باقي الأطفال يُحاسبون حسب سياسة الغرفة
	 */
	protected function calculateChildrenPricing(float $adultPricePerPerson, array $childrenAges): array
	{
		$adultAge = $this->adult_age ?? 12;
		$policies = $this->childrenPolicies ?? collect();
		$roomChildrenCapacity = $this->children_count ?? 0;

		$childrenWithIndex = collect($childrenAges)
			->map(fn($age, $index) => ['age' => (int)$age, 'original_index' => $index])
			->values();

		$adultAgeChildren = $childrenWithIndex->filter(fn($c) => $c['age'] > $adultAge);
		$underAgeChildren = $childrenWithIndex->filter(fn($c) => $c['age'] <= $adultAge);
		$sortedUnderAge = $underAgeChildren->sortByDesc('age')->values();

		// Helper to calculate price for a given set of children
		$calculate = function ($asChildren, $asAdults) use ($policies, $adultPricePerPerson) {
			$breakdown = [];
			$total = 0;

			// As Adults
			foreach ($asAdults as $child) {
				$price = round($adultPricePerPerson, 2);
				$breakdown[] = [
					'child_number' => $child['original_index'] + 1,
					'age' => $child['age'],
					'category' => 'adult',
					'category_label' => __('lang.charged_as_adult') . ' (' . __('lang.overflow_child') . ')',
					'percentage' => 100,
					'price' => $price,
				];
				$total += $price;
			}

			// As Children (Sorted by age ascending for policy matching: Child 1, Child 2...)
			// Or should we match Child 1 to the oldest inside the "As Children" group?
			// Usually: Child 1 policy applies to the first child slot. The slot is usually filled by age priority.
			// If we selected specific children to be "children", we should sort them to map to Child 1, Child 2 policies?
			// Let's assume Child 1 policy applies to the oldest among the "children group", and Child 2 to the next.
			// So we sort $asChildren descending before applying policies.
			$sortedAsChildren = $asChildren->sortByDesc('age')->values();

			foreach ($sortedAsChildren as $index => $child) {
				$age = $child['age'];
				$childPolicyNumber = $index + 1;
				$childPolicy = $policies
					->where('child_number', $childPolicyNumber)
					->first(fn($policy) => $age >= $policy->from_age && $age <= $policy->to_age);

				if ($childPolicy) {
					$percentage = $childPolicy->price_percentage ?? 0;
					$price = ($percentage == 0) ? 0 : ($adultPricePerPerson * $percentage) / 100;
					$category = ($percentage == 0) ? 'free' : 'child';
				} else {
					$percentage = 100;
					$price = $adultPricePerPerson;
					$category = 'adult';
				}

				$breakdown[] = [
					'child_number' => $child['original_index'] + 1,
					'age' => $age,
					'category' => $category,
					'category_label' => $this->getChildCategoryLabel($category, $age),
					'percentage' => (float)$percentage,
					'price' => round($price, 2),
				];
				$total += $price;
			}
			return ['breakdown' => $breakdown, 'total' => $total];
		};

		// Scenario 1: Top-Down (Oldest get children slots)
		$s1_Children = $sortedUnderAge->slice(0, $roomChildrenCapacity);
		$s1_Adults = $sortedUnderAge->slice($roomChildrenCapacity);
		$scenario1 = $calculate($s1_Children, $s1_Adults);

		// Scenario 2: Bottom-Up (Youngest get children slots)
		// Only try if distinct from S1
		if ($sortedUnderAge->count() > $roomChildrenCapacity) {
			$s2_Children = $sortedUnderAge->slice(-$roomChildrenCapacity);
			$s2_Adults = $sortedUnderAge->slice(0, $sortedUnderAge->count() - $roomChildrenCapacity);
			$scenario2 = $calculate($s2_Children, $s2_Adults);
		} else {
			$scenario2 = $scenario1;
		}

		// Add definitely adults (>= adultAge) to both scenarios totals for fair comparison?
		// No, they are static. Just add them to the winner.

		// Decide Winner (Less Total is better. If equal, S1 (Top-Down) is preferred standard)
		$winner = ($scenario2['total'] < $scenario1['total']) ? $scenario2 : $scenario1;

		// Add fixed adults (over age limit)
		foreach ($adultAgeChildren as $child) {
			$winner['breakdown'][] = [
				'child_number' => $child['original_index'] + 1,
				'age' => $child['age'],
				'category' => 'adult',
				'category_label' => $this->getChildCategoryLabel('adult', $child['age']),
				'percentage' => 100,
				'price' => round($adultPricePerPerson, 2),
			];
			$winner['total'] += round($adultPricePerPerson, 2);
		}

		// Final Sort by original input index
		usort($winner['breakdown'], fn($a, $b) => $a['child_number'] <=> $b['child_number']);

		return [
			'breakdown' => $winner['breakdown'],
			'total' => round($winner['total'], 2),
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
			'adult' => __('lang.charged_as_adult') . " ({$age} " . __('lang.years') . ", > {$adultAge})",
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
