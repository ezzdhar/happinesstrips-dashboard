<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CalculatesBookingPrice;
use App\Traits\HasPricePeriods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Room extends Model
{
    use CalculatesBookingPrice, HasFactory, HasPricePeriods, HasTranslations;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public array $translatable = ['name', 'includes'];

    protected function casts(): array
    {
        return [
            'price_periods' => 'array',
            'status' => Status::class,
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
}
