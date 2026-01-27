<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class SubCategory extends Model
{
    use HasFactory, HasTranslations;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'status' => Status::class,
        ];
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class)->withDefault(['name' => null]);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::Active);
    }

    public function scopeStatus($query, $status = null)
    {
        return $query->when($status, fn ($q) => $q->where('status', $status));
    }

    public function scopeFilter($query, $search = null)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('name->ar', 'like', "%{$search}%")
                ->orWhere('name->en', 'like', "%{$search}%");
        });
    }
}
