<?php

namespace App\Traits;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

trait HasPricePeriods
{
	/**
	 * الحصول على سعر الغرفة في يوم معين بعملة محددة
	 * (تم تحسين الأداء باستخدام Timestamp للمقارنة الأسرع)
	 */
	public function priceForDate($date, string $currency = 'egp'): ?float
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) {
			return null;
		}

		// نستخدم الدالة المحسنة للبحث
		$period = $this->findPricePeriodForDate(
			$date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date)
		);

		if (!$period) {
			return null;
		}

		return $currency === 'egp'
			? ($period['adult_price_egp'] ?? null)
			: ($period['adult_price_usd'] ?? null);
	}

	/**
	 * البحث عن فترة السعر (تم تحسينها لتتوقف فور العثور على النتيجة)
	 */
	public function findPricePeriodForDate(Carbon $date): ?array
	{
		$periods = $this->price_periods ?? [];
		$targetTimestamp = $date->startOfDay()->timestamp;

		foreach ($periods as $period) {
			// تحقق سريع قبل عمل Parse للتواريخ لتوفير الأداء
			if (!isset($period['start_date']) || !isset($period['end_date'])) {
				continue;
			}

			// تحويل التواريخ لـ Timestamp للمقارنة الرقمية السريعة
			$startTs = Carbon::parse($period['start_date'])->startOfDay()->timestamp;
			$endTs = Carbon::parse($period['end_date'])->endOfDay()->timestamp;

			if ($targetTimestamp >= $startTs && $targetTimestamp <= $endTs) {
				return $period;
			}
		}

		return null;
	}

	/**
	 * التحقق من التغطية (تم تحسينها باستخدام الرياضيات بدلاً من اللوب)
	 * Time Complexity: O(M) where M is number of periods (Fast)
	 */
	public function isDateRangeCovered($startDate, $endDate): bool
	{
		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		if ($start->greaterThanOrEqualTo($end)) {
			return false;
		}

		$totalNightsRequired = $start->diffInDays($end);
		$coveredNights = 0;

		$periods = $this->price_periods ?? [];

		foreach ($periods as $period) {
			if (!isset($period['start_date']) || !isset($period['end_date'])) {
				continue;
			}

			$pStart = Carbon::parse($period['start_date'])->startOfDay();
			$pEnd = Carbon::parse($period['end_date'])->startOfDay(); // نجعلها StartOfDay لتوحيد الحساب

			// حساب التقاطع بين فترة الحجز وفترة السعر
			// Intersection Start = Max(ReqStart, PeriodStart)
			// Intersection End   = Min(ReqEnd, PeriodEnd + 1 Day) -> لأن الـ End في الفترات عادة شامل، بينما في الحجز هو Check-out

			// تصحيح: فترة السعر شاملة لليوم الأخير، لكن الحجز ينتهي عند الـ check-out
			// إذن الليلة الأخيرة في فترة السعر هي pEnd.
			// الحساب الدقيق:

			$overlapStart = $start->max($pStart);
			// نأخذ الأصغر بين: (تاريخ الخروج) و (تاريخ نهاية الفترة + 1 يوم) لأن فترة السعر تشمل ليلة النهاية
			$overlapEnd = $end->min($pEnd->copy()->addDay());

			if ($overlapStart->lessThan($overlapEnd)) {
				$days = $overlapStart->diffInDays($overlapEnd);
				$coveredNights += $days;
			}
		}

		// إذا كان مجموع الأيام المغطاة يساوي أو أكبر من المطلوب، فالنطاق مغطى
		// ملاحظة: هذا يفترض عدم تداخل الفترات في قاعدة البيانات، وهو الطبيعي
		return $coveredNights >= $totalNightsRequired;
	}

	/**
	 * حساب السعر الإجمالي (تم تحسينها بضرب عدد الأيام في السعر بدلاً من الجمع المتكرر)
	 * Time Complexity: O(M) (Fast)
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

		// مفتاح السعر المطلوب
		$priceKey = $currency === 'usd' ? 'adult_price_usd' : 'adult_price_egp';
		$periods = $this->price_periods ?? [];

		foreach ($periods as $period) {
			if (!isset($period[$priceKey])) continue;

			$pStart = Carbon::parse($period['start_date'])->startOfDay();
			$pEnd = Carbon::parse($period['end_date'])->startOfDay();

			// منطق التقاطع (Intersection Logic)
			$overlapStart = $start->max($pStart);
			$overlapEnd = $end->min($pEnd->copy()->addDay());

			if ($overlapStart->lessThan($overlapEnd)) {
				$days = $overlapStart->diffInDays($overlapEnd);

				$totalPrice += ($days * (float)$period[$priceKey]);
				$calculatedNights += $days;
			}
		}

		// إذا لم نغطي كامل المدة، نرجع 0 كما في الدالة الأصلية
		if ($calculatedNights < $totalNightsRequired) {
			return 0.0;
		}

		return $totalPrice;
	}

	/**
	 * تفاصيل الأسعار (تم تحسينها باستخدام Lookup Map)
	 * Time Complexity: O(N) (Fast lookup inside loop)
	 */
	public function priceBreakdownForPeriod($startDate, $endDate, string $currency = 'egp'): array
	{
		$currency = $this->normalizeCurrency($currency);

		// الرد الافتراضي للفشل
		$fallback = [
			'days' => [], 'total' => 0.0, 'currency' => $currency,
			'nights_count' => 0, 'is_covered' => false
		];

		if (!$currency) return $fallback;

		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		if ($start->greaterThanOrEqualTo($end)) return $fallback;

		// 1. بناء خريطة أسعار (Cache) للفترة المطلوبة فقط
		// هذا يمنع البحث المتكرر داخل اللوب
		$priceMap = $this->buildPriceMap($start, $end, $currency);

		$days = [];
		$total = 0.0;
		$allCovered = true;
		$current = $start->copy();

		// Locale مرة واحدة خارج اللوب
		$locale = app()->getLocale();

		// اللوب الآن سريع جداً لأنه مجرد قراءة من المصفوفة
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
	 * (تستخدم نفس منطق الـ Map للسرعة)
	 */
	public function getUncoveredDates($startDate, $endDate): array
	{
		$start = Carbon::parse($startDate)->startOfDay();
		$end = Carbon::parse($endDate)->startOfDay();

		// نستخدم خريطة وهمية للتحقق من الوجود فقط
		// بما أننا لا نحتاج السعر، نمرر 'egp' كقيمة افتراضية
		$priceMap = $this->buildPriceMap($start, $end, 'egp');

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
	 * دالة مساعدة خاصة لبناء خريطة الأسعار بسرعة
	 * تحول الفترات إلى مصفوفة: ['2025-01-01' => 100, '2025-01-02' => 100]
	 */
	private function buildPriceMap(Carbon $start, Carbon $end, string $currency): array
	{
		$map = [];
		$priceKey = $currency === 'usd' ? 'adult_price_usd' : 'adult_price_egp';
		$periods = $this->price_periods ?? [];

		foreach ($periods as $period) {
			if (!isset($period['start_date'], $period['end_date'], $period[$priceKey])) {
				continue;
			}

			// تحسين: نفحص فقط الفترات التي تتقاطع مع طلب العميل
			$pStart = Carbon::parse($period['start_date'])->startOfDay();
			$pEnd = Carbon::parse($period['end_date'])->startOfDay(); // نهاية الفترة تشمل الليلة

			// التقاطع
			$overlapStart = $start->max($pStart);
			$overlapEnd = $end->min($pEnd->copy()->addDay()); // +1 لأن الـ Loop يتوقف قبل الـ End

			if ($overlapStart->lessThan($overlapEnd)) {
				$curr = $overlapStart->copy();
				$price = (float)$period[$priceKey];

				// ملء الخريطة للأيام المتقاطعة فقط
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
			'egp' => 'egp', 'e£' => 'egp', 'le' => 'egp', 'جنيه' => 'egp', 'جنيه مصري' => 'egp', 'pound' => 'egp',
			'usd' => 'usd', '$' => 'usd', 'دولار' => 'usd', 'dollar' => 'usd',
		];

		return $aliases[$c] ?? ($c === 'egp' || $c === 'usd' ? $c : null);
	}
}