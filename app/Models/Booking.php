<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'status' => Status::class,
            'adults_count' => 'integer',
            'children_count' => 'integer',
            'nights_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->booking_number) {
                $booking->booking_number = 'BK-' . strtoupper(uniqid());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function bookingHotels(): HasMany
    {
        return $this->hasMany(BookingHotel::class);
    }

    public function travelers(): HasMany
    {
        return $this->hasMany(BookingTraveler::class);
    }

    public function scopeStatus($query, $status = null)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeUser($query, $userId = null)
    {
        return $query->when($userId, fn($q) => $q->where('user_id', $userId));
    }

    public function scopeTrip($query, $tripId = null)
    {
        return $query->when($tripId, fn($q) => $q->where('trip_id', $tripId));
    }
}

