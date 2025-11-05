<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
	use  HasTranslations;

	public $translatable = ['name'];

	protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

	public function hotels(): HasMany
	{
		return $this->hasMany(Hotel::class, 'city_id');
	}


}
