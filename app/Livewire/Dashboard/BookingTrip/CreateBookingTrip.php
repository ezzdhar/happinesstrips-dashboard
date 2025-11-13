<?php

namespace App\Livewire\Dashboard\BookingTrip;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Trip;
use App\Models\User;
use App\Services\TripPricingService;
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

    public $children_count = 0; // Children at or above threshold age (charged as adults)

    public $free_children_count = 0; // Children below threshold age (free)
    // Threshold age is configurable via CHILD_AGE_THRESHOLD in .env (default: 12)

    public $notes;

    public $currency = 'egp';

    public $calculated_price = 0;

    public $total_price = 0;

    // Travelers
    public $travelers = [];

    // UI State
    public $currentStep = 1;

    public $showReview = false;

    public $users;

    public $trips;

    public function mount(): void
    {
        $this->users = User::role('user')->get(['id', 'name', 'phone'])->toArray();
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
                $this->children_count = 0;
                $this->free_children_count = 0;

                // Sync travelers array
                $this->syncTravelers();

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
            $this->free_children_count = 0;
            $this->travelers = [];
            $this->calculated_price = 0;
            $this->total_price = 0;
        }
    }

    public function nextStep(): void
    {
        // Validate current step before moving forward
        if ($this->currentStep == 1) {
            $this->validate([
                'user_id' => 'required|exists:users,id',
                'trip_id' => 'required|exists:trips,id',
            ]);
            $this->currentStep = 2;
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'check_in' => 'required|date',
                'check_out' => 'required|date|after:check_in',
                'adults_count' => 'required|integer|min:1',
                'currency' => 'required|in:egp,usd',
            ]);
            $this->currentStep = 3;
        } elseif ($this->currentStep == 3) {
            // Validate travelers
            $this->validate([
                'travelers' => 'required|array|min:1',
                'travelers.*.full_name' => 'required|string',
                'travelers.*.phone' => 'required|string',
                'travelers.*.nationality' => 'required|string',
                'travelers.*.age' => 'required|integer|min:1',
                'travelers.*.id_type' => 'required|in:passport,national_id',
                'travelers.*.id_number' => 'required|string',
            ]);
            $this->showReview = true;
        }
    }

    public function previousStep(): void
    {
        if ($this->showReview) {
            $this->showReview = false;
        } elseif ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function editStep(int $step): void
    {
        $this->showReview = false;
        $this->currentStep = $step;
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
        $this->syncTravelers();
        $this->calculatePrice();
    }

    public function updatedChildrenCount(): void
    {
        $this->syncTravelers();
        $this->calculatePrice();
    }

    public function updatedFreeChildrenCount(): void
    {
        $this->syncTravelers();
        // Free children don't affect price
    }

    private function syncTravelers(): void
    {
        $totalTravelers = (int) $this->adults_count + (int) $this->children_count + (int) $this->free_children_count;
        $currentTravelers = count($this->travelers);

        // Add travelers if needed
        if ($totalTravelers > $currentTravelers) {
            $adultsAdded = 0;
            $childrenAdded = 0;
            $freeChildrenAdded = 0;

            for ($i = $currentTravelers; $i < $totalTravelers; $i++) {
                // Determine traveler type based on counts
                if ($adultsAdded < $this->adults_count) {
                    $type = 'adult';
                    $adultsAdded++;
                } elseif ($childrenAdded < $this->children_count) {
                    $type = 'child';
                    $childrenAdded++;
                } else {
                    $type = 'child';
                    $freeChildrenAdded++;
                }

                $this->travelers[] = [
                    'full_name' => '',
                    'phone_key' => '+20',
                    'phone' => '',
                    'nationality' => 'مصر',
                    'age' => '',
                    'id_type' => '',
                    'id_number' => '',
                    'type' => $type,
                ];
            }
        }

        // Remove excess travelers if needed
        if ($totalTravelers < $currentTravelers) {
            $this->travelers = array_slice($this->travelers, 0, $totalTravelers);
        }
    }

    public function getChildAgeThreshold(): int
    {
        return TripPricingService::getChildAgeThreshold();
    }

    public function updatedCurrency(): void
    {
        $this->calculatePrice();
    }

    public function calculatePrice(): void
    {
        if (! $this->trip_id || ! $this->check_in || ! $this->check_out || ! $this->currency) {
            $this->calculated_price = 0;
            $this->total_price = 0;

            return;
        }

        // Get the full Trip model
        $trip = Trip::find($this->trip_id);

        if (! $trip) {
            $this->calculated_price = 0;
            $this->total_price = 0;

            return;
        }

        // Use TripPricingService with all data
        $result = TripPricingService::calculateTripPrice(
            trip: $trip,
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: (int) $this->adults_count,
            childrenCount: (int) $this->children_count,
            freeChildrenCount: (int) $this->free_children_count,
            currency: $this->currency
        );

        // Update component properties from result
        $this->nights_count = $result['nights_count'];
        $this->calculated_price = $result['calculated_price'];
        $this->total_price = $result['total_price'];
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
            'free_children_count' => 'nullable|integer|min:0',
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
            'type' => 'trip',
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
