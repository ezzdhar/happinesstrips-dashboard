<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasPricePeriods
{
	/**
	 * الحصول على سعر الغرفة في يوم معين بعملة محددة
	 */
	public function priceForDate($date, string $currency = 'egp'): ?float
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) return null;

		$targetDate = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);

		$period = $this->pricePeriods()
			->where('currency', $currency)
			->forDate($targetDate)
			->first();

		return $period ? (float) $period->price : null;
	}

	/**
	 * البحث عن فترة السعر لتاريخ معين
	 */
	public function findPricePeriodForDate(Carbon $date, string $currency = 'egp'): ?object
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) return null;

		return $this->pricePeriods()
			->where('currency', $currency)
			->whereDate('start_date', '<=', $date)
			->whereDate('end_date', '>=', $date)
			->first();
	}

	/**
	 * التحقق من أن نطاق التواريخ مغطى بالكامل لعملة معينة
	 */
	public function isDateRangeCovered($startDate, $endDate, string $currency = 'egp'): bool
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) return false;

		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		if ($start->greaterThanOrEqualTo($end)) return false;

		$totalNightsRequired = $start->diffInDays($end);
		$coveredNights = 0;

		$periods = $this->pricePeriods()->where('currency', $currency)->orderBy('start_date')->get();

		foreach ($periods as $period) {
			$pStart = Carbon::parse($period->start_date)->startOfDay();
			$pEnd = Carbon::parse($period->end_date)->startOfDay();

			$overlapStart = $start->max($pStart);
			$overlapEnd = $end->min($pEnd->copy()->addDay());

			if ($overlapStart->lessThan($overlapEnd)) {
				$coveredNights += $overlapStart->diffInDays($overlapEnd);
			}
		}

		return $coveredNights >= $totalNightsRequired;
	}

	/**
	 * حساب السعر الإجمالي لفترة معينة بعملة محددة
	 */
	public function totalPriceForPeriod($startDate, $endDate, string $currency = 'egp'): float
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) return 0.0;

		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		if ($start->greaterThanOrEqualTo($end)) return 0.0;

		$totalNightsRequired = $start->diffInDays($end);
		$calculatedNights = 0;
		$totalPrice = 0.0;

		$periods = $this->pricePeriods()->where('currency', $currency)->orderBy('start_date')->get();

		foreach ($periods as $period) {
			$pStart = Carbon::parse($period->start_date)->startOfDay();
			$pEnd = Carbon::parse($period->end_date)->startOfDay();

			$overlapStart = $start->max($pStart);
			$overlapEnd = $end->min($pEnd->copy()->addDay());

			if ($overlapStart->lessThan($overlapEnd)) {
				$days = $overlapStart->diffInDays($overlapEnd);
				$totalPrice += ($days * (float) $period->price);
				$calculatedNights += $days;
			}
		}

		if ($calculatedNights < $totalNightsRequired) return 0.0;

		return $totalPrice;
	}

	/**
	 * تفاصيل الأسعار لفترة معينة
	 */
	public function priceBreakdownForPeriod($startDate, $endDate, string $currency = 'egp'): array
	{
		$currency = $this->normalizeCurrency($currency);
		$fallback = ['days' => [], 'total' => 0.0, 'currency' => $currency, 'nights_count' => 0, 'is_covered' => false];

		if (!$currency) return $fallback;

		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		if ($start->greaterThanOrEqualTo($end)) return $fallback;

		$priceMap = $this->buildPriceMap($start, $end, $currency);

		$days = [];
		$total = 0.0;
		$allCovered = true;
		$current = $start->copy();
		$locale = app()->getLocale();

		while ($current->lessThan($end)) {
			$dateStr = $current->format('Y-m-d');
			$price = $priceMap[$dateStr] ?? null;

			if ($price === null) {
				$allCovered = false;
			} else {
				$total += $price;
			}

			$days[] = [
				'date' => $dateStr,
				'day_name' => $current->locale($locale)->translatedFormat('l'),
				'day_name_en' => $current->format('l'),
				'price' => $price ?? 0,
				'currency' => strtoupper($currency),
				'is_covered' => $price !== null,
			];

			$current->addDay();
		}

		return [
			'days' => $days,
			'total' => $total,
			'currency' => strtoupper($currency),
			'nights_count' => count($days),
			'is_covered' => $allCovered,
		];
	}

	/**
	 * التواريخ غير المغطاة
	 */
	public function getUncoveredDates($startDate, $endDate, string $currency = 'egp'): array
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) return [];

		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		$priceMap = $this->buildPriceMap($start, $end, $currency);

		$uncovered = [];
		$current = $start->copy();

		while ($current->lessThan($end)) {
			$dateStr = $current->format('Y-m-d');
			if (!isset($priceMap[$dateStr])) {
				$uncovered[] = $dateStr;
			}
			$current->addDay();
		}

		return $uncovered;
	}

	/**
	 * بناء خريطة الأسعار لفترة معينة
	 */
	private function buildPriceMap(Carbon $start, Carbon $end, string $currency): array
	{
		$map = [];
		$periods = $this->pricePeriods()->where('currency', $currency)->orderBy('start_date')->get();

		foreach ($periods as $period) {
			$pStart = Carbon::parse($period->start_date)->startOfDay();
			$pEnd = Carbon::parse($period->end_date)->startOfDay();

			$overlapStart = $start->max($pStart);
			$overlapEnd = $end->min($pEnd->copy()->addDay());

			if ($overlapStart->lessThan($overlapEnd)) {
				$curr = $overlapStart->copy();
				$price = (float) $period->price;

				while ($curr->lessThan($overlapEnd)) {
					$map[$curr->format('Y-m-d')] = $price;
					$curr->addDay();
				}
			}
		}

		return $map;
	}

	protected function normalizeCurrency(string $currency): ?string
	{
		$c = strtolower(trim($currency));
		$aliases = [
			'egp' => 'egp',
			'e£' => 'egp',
			'le' => 'egp',
			'جنيه' => 'egp',
			'جنيه مصري' => 'egp',
			'pound' => 'egp',
			'usd' => 'usd',
			'$' => 'usd',
			'دولار' => 'usd',
			'dollar' => 'usd',
		];

		return $aliases[$c] ?? ($c === 'egp' || $c === 'usd' ? $c : null);
	}
}
