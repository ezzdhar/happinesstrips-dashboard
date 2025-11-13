<?php

namespace App\Livewire\Dashboard\BookingTrip;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('create_trip_booking')]
class CreateBookingTrip extends Component
{
    public $user_id;

    public $trip_id;

    public $selectedTrip;

    public $check_in;

    public $check_out;

    public $nights_count = 1;

    public $adults_count = 1;

    public $children_count = 0;

    public $notes;

    public $currency = 'egp';

    public $calculated_price = 0;

    public $total_price = 0;

    // Travelers
    public $travelers = [];

    public $users;

    public $trips;

    public function mount(): void
    {
        $this->users = User::role('user')->get(['id', 'name'])->toArray();
        $this->trips = Trip::status(Status::Active)->get()->map(function ($trip) {
            return [
                'id' => $trip->id,
                'name' => $trip->name,
                'type' => $trip->type->value,
                'duration_from' => $trip->duration_from?->format('Y-m-d'),
                'duration_to' => $trip->duration_to?->format('Y-m-d'),
                'price' => $trip->price,
                'adults_count' => $trip->adults_count,
                'children_count' => $trip->children_count,
            ];
        })->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.trip_bookings'),
                'icon' => 'o-map',
                'link' => route('bookings.trips'),
            ],
            [
                'label' => __('lang.create_trip_booking'),
            ],
        ];
    }

    public function updatedTripId(): void
    {
        if ($this->trip_id) {
            $this->selectedTrip = collect($this->trips)->firstWhere('id', $this->trip_id);

            if ($this->selectedTrip) {
                // For Fixed trips, set the dates automatically
                if ($this->selectedTrip['type'] === 'fixed') {
                    $this->check_in = $this->selectedTrip['duration_from'];
                    $this->check_out = $this->selectedTrip['duration_to'];
                    $this->updatedCheckIn();
                } else {
                    // For Flexible trips, clear dates and set minimum date
                    $this->check_in = null;
                    $this->check_out = null;
                    $this->nights_count = 1;
                }

                // Set default people count from trip
                $this->adults_count = $this->selectedTrip['adults_count'] ?? 1;
                $this->children_count = $this->selectedTrip['children_count'] ?? 0;

                // Calculate price
                $this->calculatePrice();
            }
        } else {
            $this->selectedTrip = null;
            $this->check_in = null;
            $this->check_out = null;
            $this->nights_count = 1;
            $this->adults_count = 1;
            $this->children_count = 0;
            $this->calculated_price = 0;
            $this->total_price = 0;
        }
    }

    public function updatedCheckIn(): void
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            $this->nights_count = $checkIn->diffInDays($checkOut);
            $this->calculatePrice();
        }
    }

    public function updatedCheckOut(): void
    {
        $this->updatedCheckIn();
    }

    public function updatedAdultsCount(): void
    {
        $this->calculatePrice();
    }

    public function updatedChildrenCount(): void
    {
        $this->calculatePrice();
    }

    public function updatedCurrency(): void
    {
        $this->calculatePrice();
    }

    public function calculatePrice(): void
    {
        if (! $this->selectedTrip || ! $this->currency) {
            $this->calculated_price = 0;
            $this->total_price = 0;

            return;
        }

        $basePrice = $this->selectedTrip['price'][$this->currency] ?? 0;
        $baseAdultsCount = $this->selectedTrip['adults_count'] ?? 1;

        // Get child discount settings
        $childDiscountPercentage = config('booking.child_discount_percentage', 50);
        $maxDiscountedChildren = config('booking.max_discounted_children', 2);

        if ($this->selectedTrip['type'] === 'fixed') {
            // Fixed trip: price is for base adults count for the trip duration
            $pricePerAdult = $basePrice / $baseAdultsCount;

            // Calculate adults cost
            $adultsCost = $this->adults_count * $pricePerAdult;

            // Calculate children cost
            $childrenCost = 0;
            for ($i = 0; $i < $this->children_count; $i++) {
                if ($i < $maxDiscountedChildren) {
                    // First 2 children get discount
                    $childrenCost += $pricePerAdult * ($childDiscountPercentage / 100);
                } else {
                    // 3rd child and beyond pay full adult rate
                    $childrenCost += $pricePerAdult;
                }
            }

            $this->total_price = $adultsCost + $childrenCost;
            $this->calculated_price = $basePrice;

        } else {
            // Flexible trip: price per night for base adults count
            if (! $this->nights_count || $this->nights_count < 1) {
                $this->calculated_price = 0;
                $this->total_price = 0;

                return;
            }

            $pricePerAdultPerNight = $basePrice / $baseAdultsCount;

            // Calculate adults cost
            $adultsCost = $this->adults_count * $pricePerAdultPerNight * $this->nights_count;

            // Calculate children cost
            $childrenCost = 0;
            for ($i = 0; $i < $this->children_count; $i++) {
                if ($i < $maxDiscountedChildren) {
                    // First 2 children get discount
                    $childrenCost += ($pricePerAdultPerNight * $this->nights_count) * ($childDiscountPercentage / 100);
                } else {
                    // 3rd child and beyond pay full adult rate
                    $childrenCost += $pricePerAdultPerNight * $this->nights_count;
                }
            }

            $this->total_price = $adultsCost + $childrenCost;
            $this->calculated_price = $basePrice;
        }

        // Round to 2 decimals
        $this->calculated_price = round($this->calculated_price, 2);
        $this->total_price = round($this->total_price, 2);
    }

    public function addTraveler(): void
    {
        $this->travelers[] = [
            'full_name' => '',
            'phone_key' => '+20',
            'phone' => '',
            'nationality' => '',
            'age' => '',
            'id_type' => 'passport',
            'id_number' => '',
            'type' => 'adult',
        ];
    }

    public function removeTraveler($index): void
    {
        unset($this->travelers[$index]);
        $this->travelers = array_values($this->travelers);
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'trip_id' => 'required|exists:trips,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'nights_count' => 'required|integer|min:1',
            'adults_count' => 'required|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
            'currency' => 'required|in:egp,usd',
            'notes' => 'nullable|string',
            'travelers' => 'required|array|min:1',
            'travelers.*.full_name' => 'required|string',
            'travelers.*.phone' => 'required|string',
            'travelers.*.nationality' => 'required|string',
            'travelers.*.age' => 'required|integer|min:1',
            'travelers.*.id_type' => 'required|in:passport,national_id',
            'travelers.*.id_number' => 'required|string',
            'travelers.*.type' => 'required|in:adult,child',
        ];
    }

    public function save(): void
    {
        $this->validate();

        // Create booking with calculated prices
        $booking = Booking::create([
            'user_id' => $this->user_id,
            'trip_id' => $this->trip_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights_count' => $this->nights_count,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'price' => $this->calculated_price,
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => Status::Pending,
        ]);

        // Create travelers
        foreach ($this->travelers as $travelerData) {
            BookingTraveler::create([
                'booking_id' => $booking->id,
                'full_name' => $travelerData['full_name'],
                'phone_key' => $travelerData['phone_key'] ?? '+20',
                'phone' => $travelerData['phone'],
                'nationality' => $travelerData['nationality'],
                'age' => $travelerData['age'],
                'id_type' => $travelerData['id_type'],
                'id_number' => $travelerData['id_number'],
                'type' => $travelerData['type'],
            ]);
        }

        flash()->success(__('lang.created_successfully', ['attribute' => __('lang.booking')]));
        $this->redirectIntended(default: route('bookings.trips'), navigate: true);
    }

    public function render(): View
    {

        return view('livewire.dashboard.booking-trip.create-booking-trip');
    }
}
