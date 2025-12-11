<?php

namespace App\Traits;

trait TripFilter
{
	public function scopeType($query, $type = null)
	{
		return $query->when($type, fn($q) => $q->where('type', $type));
	}

	public function scopeStatus($query, $status = null)
	{
		return $query->when($status, fn($q) => $q->where('status', $status));
	}

	public function scopeFeatured($query)
	{
		return $query->where('is_featured', true);
	}

	public function scopeHotelFilter($query, $hotelId = null)
	{
		return $query->when($hotelId, function ($q) use ($hotelId) {
			$q->whereHas('hotels', function ($q2) use ($hotelId) {
				$q2->where('hotel_id', $hotelId);
			});
		});
	}


	public function scopeNameFilter($query, $search = null)
	{
		return $query->when($search, function ($q) use ($search) {
			$q->where('name->ar', 'like', "%{$search}%")->orWhere('name->en', 'like', "%{$search}%");
		});
	}

	public function scopeCityFilter($query, $cityId = null)
	{
		return $query->when($cityId, fn($q) => $q->where('city_id', $cityId));
	}
	public function scopeMainCategoryFilter($query, $mainCategoryId = null)
	{
		return $query->when($mainCategoryId, fn($q) => $q->where('main_category_id', $mainCategoryId));
	}

	public function scopeSubCategoryFilter($query, $subCategoryId = null)
	{
		return $query->when($subCategoryId, fn($q) => $q->where('sub_category_id', $subCategoryId));
	}
	public function scopeDurationFrom($query, $date = null)
	{
		return $query->when($date, fn($q) => $q->whereDate('duration_from', '>=', $date));
	}
	public function scopeDurationTo($query, $date = null)
	{
		return $query->when($date, fn($q) => $q->whereDate('duration_to', '<=', $date));
	}
}