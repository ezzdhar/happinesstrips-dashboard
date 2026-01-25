<?php

namespace App\Traits;

use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait CalculatesHotelBookingPrice
{
	use ApiResponse;

	/**
	 * حساب السعر الإجمالي للحجز مع تطبيق جميع سياسات الفندق (البالغين والأطفال)
	 * تستخدم لحساب السعر الكامل للحجز بما في ذلك البالغين والأطفال حسب أعمارهم وسياسات الفندق
	 * وترجع تفصيل كامل بالأسعار والتواريخ والسياسات
	 *
	 * Calculate total booking price with all policies applied.
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

		// Normalize currency
		$currency = $this->normalizeCurrency($currency);

		if (!$currency) {
			return $this->errorResponse('Invalid currency');
		}

		// Check if date range is covered
		if (!$this->isDateRangeCovered($startDate, $endDate)) {
			$uncoveredDates = $this->getUncoveredDates($startDate, $endDate);
			return $this->errorResponse(
				__('lang.date_range_not_covered'),
				[
					'uncovered_dates' => $uncoveredDates,
				]
			);
		}

		// Get hotel
		$hotel = $this->hotel;

		// Calculate nights count
		$nightsCount = $startDate->diffInDays($endDate);

		if ($nightsCount <= 0) {
			return $this->errorResponse('Invalid date range');
		}

		// Get adult price per person for the entire period
		$adultPricePerPerson = $this->totalPriceForPeriod($startDate, $endDate, $currency);

		if ($adultPricePerPerson === null || $adultPricePerPerson <= 0) {
			return $this->errorResponse('Unable to calculate price');
		}

		// Calculate adults total
		$adultsTotal = $adultPricePerPerson * $adultsCount;

		// Calculate children pricing
		$childrenBreakdown = $this->calculateChildrenPricing(
			$hotel,
			$adultPricePerPerson,
			$childrenAges
		);

		// Calculate grand total
		$grandTotal = $adultsTotal + $childrenBreakdown['total'];

		// Get daily breakdown
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
			'nights_count' => (int)$nightsCount,
			'currency' => strtoupper($currency),

			// Adults
			'adults_count' => $adultsCount,
			'adult_price_per_person' => $adultPricePerPerson,
			'adults_total' => $adultsTotal,

			// Children
			'children_count' => count($childrenAges),
			'children_breakdown' => $childrenBreakdown['breakdown'],
			'children_total' => $childrenBreakdown['total'],

			// Totals
			'subtotal' => $adultsTotal + $childrenBreakdown['total'],
			'total_price' => $grandTotal,

			// Daily breakdown
			'daily_breakdown' => $dailyBreakdown['days'],
			'price_per_night_average' => $nightsCount > 0 ? round($adultPricePerPerson / $nightsCount, 2) : 0,

			// Hotel policies - now using room policy if available
			'policies' => $this->getRoomPolicySummary(),
		];
	}

	/**
	 * حساب أسعار الأطفال بناءً على سياسة الغرفة الجديدة
	 * تستخدم لتطبيق نسب التخفيض على الأطفال حسب أعمارهم ونطاقات العمر المحددة
	 *
	 * Calculate children pricing based on room policy.
	 */
	protected function calculateChildrenPricing($hotel, float $adultPricePerPerson, array $childrenAges): array
	{
		$breakdown = [];
		$total = 0;
		$adultAge = $this->adult_age ?? 12;
		$policies = $this->childrenPolicies ?? collect();

		foreach ($childrenAges as $index => $age) {
			$age = (int)$age;
			$childNumber = $index + 1;
			$category = '';
			$percentage = 0;
			$price = 0;

			// إذا كان السن يساوي أو أكبر من سن البالغ
			if ($age >= $adultAge) {
				$category = 'adult';
				$percentage = 100;
				$price = $adultPricePerPerson;
			} else {
				// البحث عن سياسة الطفل المناسبة من الجدول
				// نبحث عن نطاق العمر المطابق لهذا الطفل
				$childPolicy = $policies
					->where('child_number', $childNumber)
					->first(function ($policy) use ($age) {
						return $age >= $policy->from_age && $age <= $policy->to_age;
					});

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
					// لا توجد سياسة مطابقة - يحسب كبالغ
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

	/**
	 * الحصول على ملخص سياسة الغرفة
	 */
	protected function getRoomPolicySummary(): array
	{
		return [
			'adult_age' => $this->adult_age ?? 12,
			'has_children_policy' => $this->childrenPolicies->isNotEmpty(),
		];
	}

	/**
	 * الحصول على تسمية توضيحية لفئة الطفل
	 *
	 * Get child category label.
	 */
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

	/**
	 * إرجاع رد خطأ موحد عند فشل عملية حساب السعر
	 * تستخدم لإرجاع رسالة خطأ بصيغة موحدة تحتوي على success: false ورسالة الخطأ
	 *
	 * Error response format.
	 */
	protected function errorResponse(string $message, array $data = []): array
	{
		return array_merge([
			'success' => false,
			'error' => $message,
		], $data);
	}

	/**
	 * حساب سعر الحجز البسيط بدون تفصيل الأطفال (حساب سريع مبسط)
	 * تستخدم لحساب السعر بشكل أسرع بدون الدخول في تفاصيل أعمار الأطفال وسياساتهم
	 * مفيدة عند الحاجة لعرض سعر تقريبي سريع
	 *
	 * Simple booking price calculation (without children breakdown).
	 *
	 * @param string|\DateTimeInterface $checkIn
	 * @param string|\DateTimeInterface $checkOut
	 */
	public function calculateSimpleBookingPrice($checkIn, $checkOut, int $adultsCount, int $childrenCount = 0, string $currency = 'egp'): array
	{
		$startDate = $checkIn instanceof \DateTimeInterface
			? Carbon::instance($checkIn)
			: Carbon::parse($checkIn);

		$endDate = $checkOut instanceof \DateTimeInterface
			? Carbon::instance($checkOut)
			: Carbon::parse($checkOut);

		$currency = $this->normalizeCurrency($currency);

		if (!$currency) {
			return $this->errorResponse('Invalid currency');
		}

		if (!$this->isDateRangeCovered($startDate, $endDate)) {
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
