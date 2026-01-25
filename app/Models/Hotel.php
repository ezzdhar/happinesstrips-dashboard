<?php

namespace App\Models;

use App\Enums\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Hotel extends Model
{
    use HasFactory, HasTranslations;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public array $translatable = ['name', 'address', 'description', 'facilities'];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
            'rating' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id')->withDefault(['name' => null]);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }

    /**
     * الحصول على أرخص غرفة متاحة بناءً على فترة زمنية محددة ومعايير الحجز
     * Get the cheapest available room based on booking criteria.
     */
    public function getCheapestRoomForPeriod(string $startDate, string $endDate, int $adultsCount, array $childrenAges = [], string $currency = 'egp', $is_featured = false): ?array
    {
        $cheapestRoom = null;
        $lowestPrice = null;
        $cheapestCalculation = null;

        $childrenCount = count($childrenAges);

        // البحث عن الغرف المتاحة للمعايير المطلوبة
        $availableRooms = $this->rooms()
            ->where('status', Status::Active)
            ->where('adults_count', '>=', $adultsCount)
            ->when($childrenCount > 0, fn($q) => $q->where('children_count', '>=', $childrenCount))
            ->when($is_featured, function (Builder $query) use ($is_featured) {
                $isFeatured = filter_var($is_featured, FILTER_VALIDATE_BOOLEAN);

                return $query->where('is_featured', $isFeatured ? 1 : 0);
            })->get();

        foreach ($availableRooms as $room) {
            // حساب السعر الإجمالي للغرفة مع أعمار الأطفال
            $calculation = $room->calculateBookingPrice(
                $startDate,
                $endDate,
                $adultsCount,
                $childrenAges,
                $currency
            );

            // تخطي الغرف غير المتاحة أو التي فشل حساب سعرها
            if (! isset($calculation['success']) || ! $calculation['success']) {
                continue;
            }

            $totalPrice = $calculation['total_price'];

            if ($lowestPrice === null || $totalPrice < $lowestPrice) {
                $lowestPrice = $totalPrice;
                $cheapestRoom = $room;
                $cheapestCalculation = $calculation;
            }
        }

        if ($cheapestRoom === null || $cheapestCalculation === null) {
            return null;
        }

        return [
            'room_id' => $cheapestRoom->id,
            'room_name' => $cheapestRoom->name,
            'adults_count' => $cheapestRoom->adults_count,
            'children_count' => $cheapestRoom->children_count,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'nights_count' => $cheapestCalculation['nights_count'],
            'price_per_night' => $lowestPrice / $cheapestCalculation['nights_count'],
            'total_price' => $lowestPrice,
            'currency' => strtoupper($currency),
            //			'calculation_details' => $cheapestCalculation,
        ];
    }

    /**
     * الحصول على أرخص غرفة متاحة في الفندق بناءً على سعر اليوم الحالي
     * تستخدم لعرض أقل سعر متاح في الفندق اليوم مع بيانات الغرفة
     *
     * Get the cheapest available room based on today's price.
     */
    public function getCheapestRoomForToday(string $currency = 'egp', $is_featured = false): ?array
    {
        $today = now();
        $cheapestRoom = null;
        $lowestPrice = null;

        $rooms = $this->rooms()->where('status', Status::Active)->when($is_featured, function (Builder $query) use ($is_featured) {
            $isFeatured = filter_var($is_featured, FILTER_VALIDATE_BOOLEAN);
            return $query->where('is_featured', $isFeatured ? 1 : 0);
        })->get();

        foreach ($rooms as $room) {
            $todayPrice = $room->priceForDate($today, $currency);
            if ($todayPrice === null) {
                continue;
            }
            if ($lowestPrice === null || $todayPrice < $lowestPrice) {
                $lowestPrice = $todayPrice;
                $cheapestRoom = $room;
            }
        }

        if ($cheapestRoom === null) {
            return [
                'room_id' => null,
                'room_name' => null,
                'adults_count' => null,
                'children_count' => null,
                'price_period_start' => null,
                'price_period_end' => null,
                'price_per_night' => null,
                'currency' => strtoupper($currency),
                'start_date' => null,
                'end_date' => null,
                'price_before_discount' => null,
                'discount_percentage' => null,
            ];
        }

        // الآن findPricePeriodForDate ترجع object (RoomPricePeriod) وتحتاج العملة
        $currentPeriod = $cheapestRoom->findPricePeriodForDate($today, $currency);

        if ($currentPeriod === null) {
            return null;
        }

        // نحسب بكرا ونتأكد إنه جوّه الفترة
        $periodStart = $currentPeriod->start_date; // Carbon object
        $periodEnd = $currentPeriod->end_date;     // Carbon object

        $nextDateInPeriod = null;

        if ($periodStart && $periodEnd) {
            $tomorrow = $today->copy()->addDay();

            if ($tomorrow->betweenIncluded($periodStart, $periodEnd)) {
                $nextDateInPeriod = $tomorrow->toDateString();
            }
        }

        // حساب السعر قبل الخصم ونسبة الخصم
        $priceBeforeDiscount = $lowestPrice;
        $discountPercentage = 0;

        if ($cheapestRoom->is_featured && $cheapestRoom->discount_percentage > 0) {
            $priceBeforeDiscount = $lowestPrice + ($lowestPrice * ($cheapestRoom->discount_percentage / 100));
            $discountPercentage = $cheapestRoom->discount_percentage;
        }

        return [
            'room_id' => $cheapestRoom->id,
            'room_name' => $cheapestRoom->name,
            'adults_count' => $cheapestRoom->adults_count,
            'children_count' => $cheapestRoom->children_count,
            'start_date' => $nextDateInPeriod,
            'end_date' => $nextDateInPeriod ? Carbon::parse($nextDateInPeriod)->addDay()->toDateString() : null,
            'price_period_start' => $periodStart?->format('Y-m-d'),
            'price_period_end' => $periodEnd?->format('Y-m-d'),
            'currency' => strtoupper($currency),
            'price_per_night' => $lowestPrice,
            'price_before_discount' => (float) round($priceBeforeDiscount, 2),
            'discount_percentage' => (float) $discountPercentage,
        ];
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'hotel_trip');
    }

    public function hotelTypes(): BelongsToMany
    {
        return $this->belongsToMany(HotelType::class, 'hotel_hotel_type', 'hotel_id', 'hotel_type_id');
    }

    public function bookingHotels(): HasMany
    {
        return $this->hasMany(BookingHotel::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function scopeStatus($query, $status = null)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeHotelTypeFilter($query, $hotel_type_id = null)
    {
        return $query->when($hotel_type_id, function ($q) use ($hotel_type_id) {
            $q->whereHas('hotelTypes', function ($q2) use ($hotel_type_id) {
                $q2->where('hotel_type_id', $hotel_type_id);
            });
        });
    }

    public function scopeFilter($query, $search = null)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('name->ar', 'like', "%{$search}%")
                ->orWhere('name->en', 'like', "%{$search}%");
        });
    }
}
