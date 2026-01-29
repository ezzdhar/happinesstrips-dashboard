<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CalculatesHotelBookingPrice;
use App\Traits\HasPricePeriods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

class Room extends Model
{
	use CalculatesHotelBookingPrice, HasFactory, HasPricePeriods, HasTranslations;

	protected $guarded = ['id', 'created_at', 'updated_at'];

	public array $translatable = ['name', 'includes'];

	protected function casts(): array
	{
		return [
			'status' => Status::class,
			'is_featured' => 'boolean',
			'discount_percentage' => 'decimal:2',
			'adults_count' => 'integer',
			'children_count' => 'integer',
			'adult_age' => 'integer',
		];
	}

	public function hotel(): BelongsTo
	{
		return $this->belongsTo(Hotel::class);
	}

	public function bookingHotels(): HasMany
	{
		return $this->hasMany(BookingHotel::class);
	}

	public function files(): MorphMany
	{
		return $this->morphMany(File::class, 'fileable');
	}

	public function amenities(): BelongsToMany
	{
		return $this->belongsToMany(Amenity::class, 'room_amenity');
	}

	public function childrenPolicies(): HasMany
	{
		return $this->hasMany(RoomChildPolicy::class)->orderBy('child_number');
	}

	/**
	 * علاقة فترات الأسعار
	 */
	public function pricePeriods(): HasMany
	{
		return $this->hasMany(RoomPricePeriod::class);
	}

	/**
	 * فترات أسعار الجنيه المصري
	 */
	public function pricePeriodsEgp(): HasMany
	{
		return $this->pricePeriods()->where('currency', 'egp');
	}

	/**
	 * فترات أسعار الدولار
	 */
	public function pricePeriodsUsd(): HasMany
	{
		return $this->pricePeriods()->where('currency', 'usd');
	}

	public function scopeStatus($query, $status = null)
	{
		return $query->when($status, fn($q) => $q->where('status', $status));
	}

	public function scopeHotelId($query, $hotel_id = null)
	{
		return $query->when($hotel_id, fn($q) => $q->where('hotel_id', $hotel_id));
	}

	public function scopeFilter($query, $search = null)
	{
		return $query->when($search, function ($q) use ($search) {
			$q->where('name->ar', 'like', "%{$search}%")->orWhere('name->en', 'like', "%{$search}%");
		});
	}

	public function scopeIsAvailableRangeCovered($query)
	{
		// تحويل التواريخ للصيغة القياسية
		$startDate = Carbon::parse(request()->start_date)->format('Y-m-d');
		$endDate = Carbon::parse(request()->end_date)->format('Y-m-d');
		$currency = strtolower(request()->attributes->get('currency', 'egp'));

		// استخدام جدول room_price_periods بدلاً من JSON column
		return $query->whereExists(function ($subQuery) use ($startDate, $endDate, $currency) {
			$subQuery->selectRaw('1')
				->from('room_price_periods')
				->whereColumn('room_price_periods.room_id', 'rooms.id')
				->where('room_price_periods.currency', $currency)
				->whereRaw("
					(
						SELECT COALESCE(SUM(
							GREATEST(0, DATEDIFF(
								LEAST(rpp.end_date, ?),
								GREATEST(rpp.start_date, ?)
							))
						), 0)
						FROM room_price_periods rpp
						WHERE rpp.room_id = rooms.id
						AND rpp.currency = ?
						AND rpp.start_date < ?
						AND rpp.end_date > ?
					) >= DATEDIFF(?, ?)
				", [
					$endDate,
					$startDate,
					$currency,
					$endDate,
					$startDate,
					$endDate,
					$startDate,
				]);
		});
	}

	public function scopeFilterByCalculatedPrice($query)
	{
		$startDate = request()->start_date;
		$endDate = request()->end_date;
		$adultsCount = request()->adults_count;
		$childrenAges = request()->childrenAges ?? [];
		$minPrice = request()->min_price;
		$maxPrice = request()->max_price;
		$currency = request()->attributes->get('currency', 'egp');
		// إذا لم يكن هناك فلتر للسعر، نعيد الاستعلام كما هو لتوفير الأداء
		if ($minPrice === null && $maxPrice === null) {
			return $query;
		}

		// 1. استنساخ الاستعلام الحالي لجلب البيانات ومعالجتها (لمنع تداخل الاستعلامات)
		// نحتاج تحميل العلاقة 'hotel' لأن دالتك تستخدم $this->hotel
		$candidates = $query->clone()->with('hotel')->get();

		// 2. استخدام دالتك لحساب السعر وتصفية الـ IDs المقبولة فقط
		$validIds = $candidates->filter(function ($room) use ($startDate, $endDate, $adultsCount, $childrenAges, $minPrice, $maxPrice, $currency) {

			// استدعاء دالتك كما طلبت
			$calculation = $room->calculateBookingPrice(
				$startDate,
				$endDate,
				(int)$adultsCount,
				$childrenAges ?? [],
				$currency ?? 'egp'
			);

			// إذا فشل الحساب (مثلاً التواريخ غير مغطاة)، نستبعد الغرفة
			if (isset($calculation['success']) && !$calculation['success']) {
				return false;
			}

			$grandTotal = $calculation['total_price']; // السعر النهائي

			// التحقق من نطاق السعر
			$min = $minPrice ?? 0;
			$max = $maxPrice ?? PHP_FLOAT_MAX; // رقم كبير جداً في حال عدم وجود حد أقصى

			return $grandTotal >= $min && $grandTotal <= $max;
		})->pluck('id'); // نأخذ الـ IDs فقط

		// 3. إرجاع استعلام SQL يقتصر على الغرف التي اجتازت الفلتر
		return $query->whereIn('id', $validIds);
	}

	public function getPriceAttribute(): float
	{
		$request = request();
		$currency = $request->attributes->get('currency', 'egp');
		$calculation = $this->calculateBookingPrice(
			checkIn: Carbon::parse($request->start_date),
			checkOut: Carbon::parse($request->end_date),
			adultsCount: $request->adults_count,
			childrenAges: $request->childrenAges ?? [],
			currency: $currency
		);
		return (float)$calculation['total_price'];
	}

	public function getPriceBeforeDiscountAttribute(): float
	{
		// استخدام السعر المحسوب بالفعل من getPriceAttribute
		$priceAfterDiscount = $this->price;

		// إذا لم يكن هناك خصم، السعر قبل وبعد الخصم متساوي
		if (!$this->is_featured || $this->discount_percentage <= 0) {
			return $priceAfterDiscount;
		}
		$originalPrice = $priceAfterDiscount + ($priceAfterDiscount * ($this->discount_percentage / 100));
		return (float)round($originalPrice, 2);
	}
}
