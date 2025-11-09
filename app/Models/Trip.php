<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\TripType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Trip extends Model
{
    use HasFactory, HasTranslations;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public array $translatable = ['name', 'notes', 'program'];

    protected function casts(): array
    {
        return [
            'price' => 'array',
            'duration_from' => 'date',
            'duration_to' => 'date',
            'is_featured' => 'boolean',
            'status' => Status::class,
            'type' => TripType::class,
            'people_count' => 'integer',
            'max_people_count' => 'integer',
        ];
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
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

    public function scopeStatus($query, $status = null)
    {
        return $query->when($status, fn($q) => $q->where('status', $status));
    }

    public function scopeType($query, $type = null)
    {
        return $query->when($type, fn($q) => $q->where('type', $type));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeFilter($query, $search = null)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('name->ar', 'like', "%{$search}%")
              ->orWhere('name->en', 'like', "%{$search}%");
        });
    }
}

