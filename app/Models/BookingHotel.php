<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHotel extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'room_price' => 'array',
            'rooms_count' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
	    return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function hotel(): BelongsTo
    {
	    return $this->belongsTo(Hotel::class, 'hotel_id')->withDefault(['name' => __('lang.no_data')]);
    }

    public function room(): BelongsTo
    {
	    return $this->belongsTo(Room::class, 'room_id')->withDefault(['name' => __('lang.no_data')]);
    }
}

