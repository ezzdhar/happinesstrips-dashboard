<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRating extends Model
{
	protected $fillable = [
		'booking_id',
		'user_id',
		'rating',
	];

	public function booking():BelongsTo
	{
		return $this->belongsTo(Booking::class);
	}

	public function user():BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}
