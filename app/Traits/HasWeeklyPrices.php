<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasWeeklyPrices
{
	/**
	 * رجّع سعر يوم معيّن بعملة محددة.
	 *
	 * @param string|int|\DateTimeInterface|null $day "sunday" | "الأحد" | 0..6 | تاريخ
	 * @param string $currency "egp" | "usd"
	 * @return float|int|null
	 */
	public function priceForDay($day = null, string $currency = 'egp')
	{
		$currency = $this->normalizeCurrency($currency);
		if (!$currency) {
			return null;
		}

		$dayKey = $this->normalizeDay($day);
		if (!$dayKey) {
			return null;
		}

		$map = $this->weeklyPricesMap();

		if (!isset($map[$dayKey])) {
			return null;
		}

		return $currency === 'egp'
			? ($map[$dayKey]['price_egp'] ?? null)
			: ($map[$dayKey]['price_usd'] ?? null);
	}

	/** حوّل weekly_prices لأيّاً كان شكلها (array of objects أو keyed array) إلى خريطة موحّدة. */
	protected function weeklyPricesMap(): array
	{
		$data = $this->weekly_prices ?? [];

		if (array_is_list($data)) {
			$out = [];
			foreach ($data as $row) {
				if (!isset($row['day_of_week'])) continue;
				$key = $this->normalizeDay($row['day_of_week']);
				if (!$key) continue;

				$out[$key] = [
					'price_egp' => isset($row['price_egp']) ? (float)$row['price_egp'] : null,
					'price_usd' => isset($row['price_usd']) ? (float)$row['price_usd'] : null,
				];
			}
			return $out;
		}

		$out = [];
		foreach ($data as $key => $row) {
			$normKey = $this->normalizeDay($key);
			if (!$normKey) continue;

			$out[$normKey] = [
				'price_egp' => isset($row['price_egp']) ? (float)$row['price_egp'] : null,
				'price_usd' => isset($row['price_usd']) ? (float)$row['price_usd'] : null,
			];
		}
		return $out;
	}

	/** تطبيع اليوم إلى sunday..saturday */
	protected function normalizeDay($day): ?string
	{
		if ($day instanceof \DateTimeInterface || (is_string($day) && strtotime($day))) {
			$c = $day instanceof \DateTimeInterface ? Carbon::instance($day) : Carbon::parse($day);
			return strtolower($c->format('l'));
		}

		if (is_int($day) || ctype_digit((string)$day)) {
			$idx = (int)$day;
			$map = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
			return $map[$idx] ?? null;
		}

		$d = strtolower(trim((string)$day));
		$aliases = [
			'sun' => 'sunday', 'sunday' => 'sunday', 'الأحد' => 'sunday', 'احد' => 'sunday',
			'mon' => 'monday', 'monday' => 'monday', 'الاثنين' => 'monday', 'الإثنين' => 'monday', 'اتنين' => 'monday',
			'tue' => 'tuesday', 'tuesday' => 'tuesday', 'الثلاثاء' => 'tuesday', 'تلات' => 'tuesday',
			'wed' => 'wednesday', 'wednesday' => 'wednesday', 'الأربعاء' => 'wednesday', 'اربع' => 'wednesday',
			'thu' => 'thursday', 'thursday' => 'thursday', 'الخميس' => 'thursday',
			'fri' => 'friday', 'friday' => 'friday', 'الجمعة' => 'friday',
			'sat' => 'saturday', 'saturday' => 'saturday', 'السبت' => 'saturday',
		];

		$dAr = str_replace(['إ', 'أ', 'آ', 'ى'], ['ا', 'ا', 'ا', 'ي'], $d);

		return $aliases[$d] ?? $aliases[$dAr] ?? null;
	}

	/** تطبيع العملة إلى egp | usd */
	protected function normalizeCurrency(string $currency): ?string
	{
		$c = strtolower(trim($currency));
		$aliases = [
			'egp' => 'egp', 'e£' => 'egp', 'le' => 'egp', 'جنيه' => 'egp', 'جنيه مصري' => 'egp', 'pound' => 'egp',
			'usd' => 'usd', '$' => 'usd', 'دولار' => 'usd', 'dollar' => 'usd',
		];
		return $aliases[$c] ?? ($c === 'egp' || $c === 'usd' ? $c : null);
	}
	// سعر اليوم بالجنيه
//echo $room->priceForDay(now(), 'egp');

// سعر الجمعة بالدولار
//echo $room->priceForDay('friday', 'usd');

// سعر يوم 3 (الثلاثاء)
//echo $room->priceForDay(2, 'EGP');

// سعر "الخميس" بالعربي
//echo $room->priceForDay('الخميس', 'دولار');
}
