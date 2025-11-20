<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasPricePeriods
{
    /**
     * الحصول على سعر الغرفة في يوم معين بعملة محددة
     * تستخدم للحصول على سعر البالغ في يوم واحد فقط
     *
     * Get the price for a specific date and currency.
     *
     * @param  string|\DateTimeInterface  $date
     * @param  string  $currency  "egp" | "usd"
     */
    public function priceForDate($date, string $currency = 'egp'): ?float
    {
        $currency = $this->normalizeCurrency($currency);
        if (! $currency) {
            return null;
        }

        $searchDate = $date instanceof \DateTimeInterface
            ? Carbon::instance($date)
            : Carbon::parse($date);

        $period = $this->findPricePeriodForDate($searchDate);

        if (! $period) {
            return null;
        }

        return $currency === 'egp'
            ? ($period['adult_price_egp'] ?? null)
            : ($period['adult_price_usd'] ?? null);
    }

    /**
     * البحث عن فترة السعر التي تحتوي على التاريخ المحدد
     * تستخدم للعثور على price period الذي يحتوي على اليوم المطلوب من جدول price_periods
     *
     * Find price period that contains the given date.
     */
    public function findPricePeriodForDate(Carbon $date): ?array
    {
        $periods = $this->price_periods ?? [];

        foreach ($periods as $period) {
            if (! isset($period['start_date']) || ! isset($period['end_date'])) {
                continue;
            }

            $start = Carbon::parse($period['start_date'])->startOfDay();
            $end = Carbon::parse($period['end_date'])->endOfDay();

            if ($date->between($start, $end)) {
                return $period;
            }
        }

        return null;
    }

    /**
     * التحقق من أن جميع الأيام في فترة الحجز لها أسعار محددة
     * تستخدم للتأكد من تغطية كل ليلة في الحجز بسعر من price_periods قبل السماح بالحجز
     * ملحوظة: تاريخ الخروج لا يُحسب (يُحسب فقط عدد الليالي)
     *
     * Check if a date range is fully covered by price periods.
     * Note: endDate is the checkout date and is NOT included in the calculation (only nights count).
     */
    public function isDateRangeCovered($startDate, $endDate): bool
    {
        $start = $startDate instanceof \DateTimeInterface
            ? Carbon::instance($startDate)->startOfDay()
            : Carbon::parse($startDate)->startOfDay();

        $end = $endDate instanceof \DateTimeInterface
            ? Carbon::instance($endDate)->startOfDay()
            : Carbon::parse($endDate)->startOfDay();

        if ($start->greaterThanOrEqualTo($end)) {
            return false;
        }

        $current = $start->copy();

        // Only check nights (days before checkout)
        while ($current->lessThan($end)) {
            if (! $this->findPricePeriodForDate($current)) {
                return false;
            }
            $current->addDay();
        }

        return true;
    }

    /**
     * حساب إجمالي السعر لفترة زمنية (مجموع أسعار كل الليالي)
     * تستخدم لحساب السعر الإجمالي للشخص البالغ الواحد خلال فترة الحجز كاملة
     * ملحوظة: تاريخ الخروج لا يُحسب (يُحسب فقط عدد الليالي)
     *
     * Calculate total price for a date range.
     * Note: endDate is the checkout date and is NOT included (only nights count).
     */
    public function totalPriceForPeriod($startDate, $endDate, string $currency = 'egp'): float
    {
        $currency = $this->normalizeCurrency($currency);
        if (! $currency) {
            return 0.0;
        }

        $start = $startDate instanceof \DateTimeInterface
            ? Carbon::instance($startDate)->startOfDay()
            : Carbon::parse($startDate)->startOfDay();

        $end = $endDate instanceof \DateTimeInterface
            ? Carbon::instance($endDate)->startOfDay()
            : Carbon::parse($endDate)->startOfDay();

        if ($start->greaterThanOrEqualTo($end)) {
            return 0.0;
        }

        $total = 0.0;
        $current = $start->copy();

        // Only count nights (days before checkout)
        while ($current->lessThan($end)) {
            $price = $this->priceForDate($current, $currency);
            if ($price === null) {
                return 0.0; // If any day is not covered, return 0
            }
            $total += $price;
            $current->addDay();
        }

        return $total;
    }

    /**
     * الحصول على تفصيل الأسعار لكل يوم في فترة الحجز
     * تستخدم لعرض سعر كل ليلة على حدة مع اسم اليوم والتاريخ في نتيجة حساب السعر
     * ملحوظة: تاريخ الخروج لا يُحسب (يُحسب فقط عدد الليالي)
     *
     * Get price breakdown for a period.
     * Note: endDate is the checkout date and is NOT included (only nights count).
     */
    public function priceBreakdownForPeriod($startDate, $endDate, string $currency = 'egp'): array
    {
        $currency = $this->normalizeCurrency($currency);
        if (! $currency) {
            return [
                'days' => [],
                'total' => 0.0,
                'currency' => $currency,
                'nights_count' => 0,
                'is_covered' => false,
            ];
        }

        $start = $startDate instanceof \DateTimeInterface
            ? Carbon::instance($startDate)->startOfDay()
            : Carbon::parse($startDate)->startOfDay();

        $end = $endDate instanceof \DateTimeInterface
            ? Carbon::instance($endDate)->startOfDay()
            : Carbon::parse($endDate)->startOfDay();

        if ($start->greaterThanOrEqualTo($end)) {
            return [
                'days' => [],
                'total' => 0.0,
                'currency' => $currency,
                'nights_count' => 0,
                'is_covered' => false,
            ];
        }

        $days = [];
        $total = 0.0;
        $current = $start->copy();
        $allCovered = true;

        // Only include nights (days before checkout)
        while ($current->lessThan($end)) {
            $price = $this->priceForDate($current, $currency);

            if ($price === null) {
                $allCovered = false;
            }

            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day_name' => $current->locale(app()->getLocale())->translatedFormat('l'),
                'day_name_en' => $current->format('l'),
                'price' => $price ?? 0,
                'currency' => strtoupper($currency),
                'is_covered' => $price !== null,
            ];

            if ($price !== null) {
                $total += $price;
            }

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
     * الحصول على قائمة بالتواريخ التي ليس لها أسعار محددة في فترة الحجز
     * تستخدم لإظهار للمستخدم الأيام التي لا يمكن الحجز فيها لعدم وجود أسعار لها
     * ملحوظة: تاريخ الخروج لا يُحسب (يُحسب فقط عدد الليالي)
     *
     * Get all uncovered dates in a range.
     * Note: endDate is the checkout date and is NOT included (only nights count).
     */
    public function getUncoveredDates($startDate, $endDate): array
    {
        $start = $startDate instanceof \DateTimeInterface
            ? Carbon::instance($startDate)->startOfDay()
            : Carbon::parse($startDate)->startOfDay();

        $end = $endDate instanceof \DateTimeInterface
            ? Carbon::instance($endDate)->startOfDay()
            : Carbon::parse($endDate)->startOfDay();

        $uncovered = [];
        $current = $start->copy();

        // Only check nights (days before checkout)
        while ($current->lessThan($end)) {
            if (! $this->findPricePeriodForDate($current)) {
                $uncovered[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $uncovered;
    }

    /**
     * تحويل العملة إلى الصيغة الموحدة (egp أو usd)
     * تستخدم لقبول العملة بأشكال مختلفة (جنيه، EGP، دولار، USD، إلخ) وتحويلها للشكل الموحد
     *
     * Normalize currency to egp | usd.
     */
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
