<?php

namespace App\Models;

use App\Enums\Status;
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

	public array $translatable = ['name', 'address', 'description', 'facilities','include_services'];

	protected function casts(): array
	{
		return [
			'status' => Status::class,
			'rating' => 'integer',
			'latitude' => 'decimal:7',
			'longitude' => 'decimal:7',
		];
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}
	public function city(): BelongsTo
	{
		return $this->belongsTo(City::class, 'city_id');
	}

	public function rooms(): HasMany
	{
		return $this->hasMany(Room::class);
	}

	public function trips(): BelongsToMany
	{
		return $this->belongsToMany(Trip::class, 'hotel_trip');
	}

	public function files(): MorphMany
	{
		return $this->morphMany(File::class, 'fileable');
	}

	public function scopeStatus($query, $status = null)
	{
		return $query->when($status, fn($q) => $q->where('status', $status));
	}

	public function scopeFilter($query, $search = null)
	{
		return $query->when($search, function ($q) use ($search) {
			$q->where('name->ar', 'like', "%{$search}%")
			  ->orWhere('name->en', 'like', "%{$search}%");
		});
	}
}
