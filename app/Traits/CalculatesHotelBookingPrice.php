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
	 * - الأطفال الزائدون عن children_count للغرفة يُحاسبون كبالغين (Bottom-Up: الأطفال الأصغر يحصلون على أماكن الأطفال والأكبر يرحلون)
	 * - باقي الأطفال يُحاسبون حسب سياسة الغرفة
	 */
	protected function calculateChildrenPricing(float $adultPricePerPerson, array $childrenAges): array
	{
		$breakdown = [];
		$total = 0;
		$adultAge = $this->adult_age ?? 12;
		$policies = $this->childrenPolicies ?? collect();
		$roomChildrenCapacity = $this->children_count ?? 0;

		$childrenWithIndex = collect($childrenAges)
			->map(fn($age, $index) => ['age' => (int)$age, 'original_index' => $index])
			->values();

		$adultAgeChildren = $childrenWithIndex->filter(fn($c) => $c['age'] > $adultAge);
		$underAgeChildren = $childrenWithIndex->filter(fn($c) => $c['age'] <= $adultAge);

		// ترتيب الأطفال الأقل من adult_age تنازلياً (الأكبر سناً أولاً)
		$sortedUnderAge = $underAgeChildren->sortByDesc('age')->values();

		// توزيع الأطفال حسب القاعدة: "الطفل اللي سنه أكبر يتم ترحيله للبالغين"
		// هذا يعني أن الأطفال الأصغر سناً لهم الأولوية في أماكن الأطفال (Bottom-Up)
		if ($roomChildrenCapacity > 0 && $sortedUnderAge->isNotEmpty()) {
			// نأخذ آخر N عناصر (الأصغر سناً) ليكونوا أطفالاً
			$takeCount = min($roomChildrenCapacity, $sortedUnderAge->count());
			$childrenAsChildren = $sortedUnderAge->slice(-$takeCount)->values();

			// الأكبر سناً (في بداية المصفوفة) يرحلون
			$childrenAsAdults = $sortedUnderAge->slice(0, $sortedUnderAge->count() - $takeCount)->values();
		} else {
			$childrenAsChildren = collect();
			$childrenAsAdults = $sortedUnderAge;
		}

		// 1. معالجة الأطفال >= adult_age كبالغين
		foreach ($adultAgeChildren as $child) {
			$breakdown[] = [
				'child_number' => $child['original_index'] + 1,
				'age' => $child['age'],
				'category' => 'adult',
				'category_label' => $this->getChildCategoryLabel('adult', $child['age']),
				'percentage' => 100,
				'price' => round($adultPricePerPerson, 2),
			];
			$total += $adultPricePerPerson;
		}

		// 2. معالجة الأطفال المرحلين (الأكبر سناً) كبالغين
		foreach ($childrenAsAdults as $child) {
			$breakdown[] = [
				'child_number' => $child['original_index'] + 1,
				'age' => $child['age'],
				'category' => 'adult',
				'category_label' => __('lang.charged_as_adult') . ' (' . __('lang.overflow_child') . ')',
				'percentage' => 100,
				'price' => round($adultPricePerPerson, 2),
			];
			$total += $adultPricePerPerson;
		}

		// 3. معالجة الأطفال المقبولين (الأصغر سناً) حسب السياسات
		// نرتبهم تنازلياً ليتم مطابقة "الطفل الأول" (في السياسة) مع "أكبر الأطفال المقبولين"
		$sortedChildrenAsChildren = $childrenAsChildren->sortByDesc('age')->values();

		foreach ($sortedChildrenAsChildren as $policyIndex => $child) {
			$age = $child['age'];
			$childPolicyNumber = $policyIndex + 1; // الترتيب في السياسة (1, 2, 3...)

			$childPolicy = $policies
				->where('child_number', $childPolicyNumber)
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

		// ترتيب نهائي
		usort($breakdown, fn($a, $b) => $a['child_number'] <=> $b['child_number']);

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
