<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAmenity extends Model
{

	protected $guarded = ['id', 'created_at', 'updated_at'];

	protected $table = 'room_amenity';

	public function room(): BelongsTo
	{
		return $this->belongsTo(Room::class);
	}

	public function amenity(): BelongsTo
	{
		return $this->belongsTo(Amenity::class);
	}

}
