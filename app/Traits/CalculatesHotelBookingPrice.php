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
			return $this->errorResponse(__('lang.date_range_not_covered'), [
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

			// Hotel policies
			'hotel_policies' => [
				'free_child_age' => (int)$hotel->free_child_age,
				'adult_age' => (int)$hotel->adult_age,
				'first_child_percentage' => (float)$hotel->first_child_price_percentage,
				'second_child_percentage' => (float)$hotel->second_child_price_percentage,
				'third_child_percentage' => (float)$hotel->third_child_price_percentage,
				'additional_child_percentage' => (float)$hotel->additional_child_price_percentage,
			],
		];
	}

	/**
	 * حساب أسعار الأطفال بناءً على سياسات الفندق (طفل مجاني، طفل مخفض، أو كبالغ)
	 * تستخدم لتطبيق نسب التخفيض على الأطفال حسب أعمارهم وترتيبهم (أول طفل، ثاني طفل، إلخ)
	 * وتصنيفهم إلى (مجاني، طفل بتخفيض، أو يُحسب كبالغ)
	 *
	 * Calculate children pricing based on hotel policy.
	 */
	protected function calculateChildrenPricing($hotel, float $adultPricePerPerson, array $childrenAges): array
	{
		$breakdown = [];
		$total = 0;

		foreach ($childrenAges as $index => $age) {
			// Ensure age is integer
			$age = (int)$age;

			$childNumber = $index + 1;
			$category = '';
			$percentage = 0;
			$price = 0;

			// Free child (under free_child_age)
			if ($age < $hotel->free_child_age) {
				$category = 'free';
				$percentage = 0;
				$price = 0;
			} // Considered as adult (adult_age or above)
			elseif ($age >= $hotel->adult_age) {
				$category = 'adult';
				$percentage = 100;
				$price = $adultPricePerPerson;
			} // Child pricing (between free_child_age and adult_age)
			else {
				$category = 'child';

				if ($childNumber == 1) {
					$percentage = $hotel->first_child_price_percentage;
				} elseif ($childNumber == 2) {
					$percentage = $hotel->second_child_price_percentage;
				} elseif ($childNumber == 3) {
					$percentage = $hotel->third_child_price_percentage;
				} else {
					$percentage = $hotel->additional_child_price_percentage;
				}

				$price = ($adultPricePerPerson * $percentage) / 100;
			}

			$breakdown[] = [
				'child_number' => $childNumber,
				'age' => $age,
				'category' => $category,
				'category_label' => $this->getChildCategoryLabel($category, $age, $hotel),
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
	 * الحصول على تسمية توضيحية لفئة الطفل (مجاني، طفل بتخفيض، أو كبالغ)
	 * تستخدم لعرض وصف نصي واضح للمستخدم عن سبب تسعير الطفل بهذه الطريقة
	 *
	 * Get child category label.
	 */
	protected function getChildCategoryLabel(string $category, int $age, $hotel): string
	{
		return match ($category) {
			'free' => __('lang.free') . " ({$age} " . __('lang.years') . ", < {$hotel->free_child_age})",
			'adult' => __('lang.charged_as_adult') . " ({$age} " . __('lang.years') . ", ≥ {$hotel->adult_age})",
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
