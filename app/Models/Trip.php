<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\TripType;
use App\Traits\TripFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Trip extends Model
{
    use HasFactory, HasTranslations, TripFilter;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public array $translatable = ['name', 'notes', 'program'];

    protected function casts(): array
    {
        return [
            'price' => 'array',
            'duration_from' => 'date',
            'duration_to' => 'date',
            'is_featured' => 'boolean',
            'discount_percentage' => 'decimal:2',
            'status' => Status::class,
            'type' => TripType::class,
            'nights_count' => 'integer',
        ];
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class)->withDefault(['name' => null]);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class)->withDefault(['name' => null]);
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'hotel_trip');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class)->withDefault(['name' => null]);
    }

    public function bookingRatings()
    {
        return $this->hasManyThrough(
            BookingRating::class,
            Booking::class,
            'trip_id',     // foreign key on bookings
            'booking_id',  // foreign key on booking_ratings
            'id',          // local key on trips
            'id'           // local key on bookings
        );
    }
}
