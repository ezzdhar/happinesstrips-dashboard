<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
	use HasFactory, HasTranslations;

	public $translatable = ['name'];

	protected $guarded = ['id', 'created_at', 'updated_at'];

	public function hotels(): HasMany
	{
		return $this->hasMany(Hotel::class, 'city_id');
	}

	public function scopeFilter($query, $search = null)
	{
		return $query->when($search, function ($q) use ($search) {
			$q->where('name->ar', 'like', "%{$search}%")
			  ->orWhere('name->en', 'like', "%{$search}%")
			  ->orWhere('code', 'like', "%{$search}%");
		});
	}
}
