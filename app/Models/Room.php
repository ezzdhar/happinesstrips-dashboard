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
            'price_periods' => 'array',
            'status' => Status::class,
            'is_featured' => 'boolean',
            'discount_percentage' => 'decimal:2',
            'adults_count' => 'integer',
            'children_count' => 'integer',
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

    public function scopeStatus($query, $status = null)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    public function scopeHotelId($query, $hotel_id = null)
    {
        return $query->when($hotel_id, fn ($q) => $q->where('hotel_id', $hotel_id));
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

        return $query->whereRaw("
        (
            SELECT COALESCE(SUM(
                GREATEST(0, DATEDIFF(
                    -- تحديد نهاية التقاطع (الأصغر بين نهاية الفترة ونهاية الحجز)
                    LEAST(periods.p_end, ?), 
                    -- تحديد بداية التقاطع (الأكبر بين بداية الفترة وبداية الحجز)
                    GREATEST(periods.p_start, ?)
                ))
            ), 0)
            FROM JSON_TABLE(price_periods, '$[*]' COLUMNS(
                p_start DATE PATH '$.start_date',
                p_end DATE PATH '$.end_date'
            )) as periods
            -- تحسين للأداء: نفحص فقط الفترات التي تتقاطع مبدئياً
            WHERE periods.p_start < ? 
            AND periods.p_end > ?
        ) >= DATEDIFF(?, ?) -- مقارنة المجموع بالفترة المطلوبة
    ", [
            // للـ Sum (حساب التقاطع)
            $endDate, $startDate,
            // للـ Where الداخلي (تحسين الأداء)
            $endDate, $startDate,
            // للمقارنة النهائية (حساب عدد ليالي الحجز)
            $endDate, $startDate,
        ]);
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
                (int) $adultsCount,
                $childrenAges ?? [],
                $currency ?? 'egp'
            );

            // إذا فشل الحساب (مثلاً التواريخ غير مغطاة)، نستبعد الغرفة
            if (isset($calculation['success']) && ! $calculation['success']) {
                return false;
            }

            $grandTotal = $calculation['grand_total']; // السعر النهائي

            // التحقق من نطاق السعر
            $min = $minPrice ?? 0;
            $max = $maxPrice ?? PHP_FLOAT_MAX; // رقم كبير جداً في حال عدم وجود حد أقصى

            return $grandTotal >= $min && $grandTotal <= $max;

        })->pluck('id'); // نأخذ الـ IDs فقط

        // 3. إرجاع استعلام SQL يقتصر على الغرف التي اجتازت الفلتر
        return $query->whereIn('id', $validIds);
    }
}
