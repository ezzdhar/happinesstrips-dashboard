<?php

namespace App\Models;

use App\Enums\Status;
use App\Observers\BookingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(BookingObserver::class)]
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
			'is_special' => 'boolean',
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

	public function createdBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function trip(): BelongsTo
	{
		return $this->belongsTo(Trip::class)->withDefault(['name' => null]);
	}

	public function bookingHotel(): HasOne
	{
		return $this->hasOne(BookingHotel::class, 'booking_id', 'id');
	}

	public function bookingTrip(): HasOne
	{
		return $this->hasOne(BookingTrip::class, 'booking_id', 'id');
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

	public function scopeHotel($query, $hotelId = null)
	{
		return $query->when($hotelId, fn($q) => $q->whereHas('bookingHotel', fn($q2) => $q2->where('hotel_id', $hotelId)));
	}

	public function scopeBookingNumber($query, $bookingNumber = null)
	{
		return $query->when($bookingNumber, fn($q) => $q->where('booking_number', 'like', "%{$bookingNumber}%"));
	}

	public function scopeType($query, $type = 'hotel')// hotel or trip
	{
		return $query->when($type, fn($q) => $q->where('type', $type));
	}

	public function scopeIsSpecial($query, $isSpecial = true)
	{
		return $query->when($isSpecial, fn($q) => $q->where('is_special', $isSpecial));
	}

}
