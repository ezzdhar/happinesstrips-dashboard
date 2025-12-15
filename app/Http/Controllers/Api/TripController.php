<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Enums\TripType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalculateBookingTripPriceRequest;
use App\Http\Resources\TripResource;
use App\Http\Resources\TripSimpleResource;
use App\Models\Trip;
use App\Services\TripPricingService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TripController extends Controller
{
	use ApiResponse;

	public function trips(Request $request)
	{
		$currency = $request->attributes->get('currency', 'egp');
		$trips = Trip::status(Status::Active)
			->when($request->name, fn($q, $name) => $q->nameFilter($name))
			->when($request->is_featured, function (Builder $query) use ($request) {
				$isFeatured = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);

				return $query->where('is_featured', $isFeatured ? 1 : 0);
			})
			->when($request->city_id, fn($q, $city_id) => $q->CityFilter($city_id))
			->when($request->hotel_id, fn($q, $hotel_id) => $q->HotelFilter($hotel_id))
			->when($request->main_category_id, fn($q, $main_category_id) => $q->mainCategoryFilter($main_category_id))
			->when($request->sub_category_id, fn($q, $sub_category_id) => $q->subCategoryFilter($sub_category_id))
			->when($request->duration_from, fn($q, $duration_from) => $q->durationFrom($duration_from))
			->when($request->duration_to, fn($q, $duration_to) => $q->durationTo($duration_to))
			->when($request->rating, fn(Builder $query, $rating) => $query->orderBy('rating', $rating))
			->when($request->price, function (Builder $query, $direction) use ($currency) {
				return $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.{$currency}')) AS DECIMAL(10,2)) {$direction}");
			})->paginate($request->per_page ?? 15);

		return $this->responseOk(message: __('lang.trips'), data: TripSimpleResource::collection($trips), paginate: true);
	}

	public function tripDetails(Trip $trip)
	{
		$trip->load(['mainCategory', 'subCategory', 'city', 'hotels.files', 'files']);

		return $this->responseOk(message: __('lang.trip_details'), data: new TripResource($trip));
	}

	public function calculateBookingTripPrice(CalculateBookingTripPriceRequest $request, Trip $trip)
	{
		$currency = $request->attributes->get('currency', 'egp');
		if ($trip->type->value === TripType::Fixed) {
			$request->check_in = $trip->duration_from;
			$request->check_out = $trip->duration_to;
		}
		$pricingResult = TripPricingService::calculateTripPriceWithAges(
			trip: $trip,
			checkIn: $request->check_in,
			checkOut: $request->check_out,
			adultsCount: (int)$request->adults_count,
			childrenAges: $request->children_ages,
			currency: $currency
		);
		$trip = new TripResource(resource: $trip);
		$data = array_merge($pricingResult, ['trip' => $trip]);
        return $this->responseOk(message: __('lang.trip_details'), data: $data);
    }
}
