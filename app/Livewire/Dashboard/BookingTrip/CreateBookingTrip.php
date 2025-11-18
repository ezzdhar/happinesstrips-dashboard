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

    public $children_ages = [];

    public $notes;

    public $currency = 'egp';

    public $sub_total = 0;

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
                'nights_count' => $trip->nights_count,
                'free_child_age' => $trip->free_child_age,
                'adult_age' => $trip->adult_age,
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

                // Set default people count - price is always for 1 adult
                $this->adults_count = 1;
                $this->children_ages = [];

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
            $this->children_ages = [];
            $this->travelers = [];
            $this->sub_total = 0;
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

    public function addChild(): void
    {
        $this->children_ages[] = 0;
        $this->syncTravelers();
        $this->calculatePrice();
    }

    public function removeChild($index): void
    {
        unset($this->children_ages[$index]);
        $this->children_ages = array_values($this->children_ages);
        $this->syncTravelers();
        $this->calculatePrice();
    }

    public function updatedChildrenAges(): void
    {
        $this->calculatePrice();
    }

    private function syncTravelers(): void
    {
        $totalTravelers = (int) $this->adults_count + count($this->children_ages);
        $currentTravelers = count($this->travelers);

        // Add travelers if needed
        if ($totalTravelers > $currentTravelers) {
            $adultsAdded = 0;
            $childrenAdded = 0;

            for ($i = $currentTravelers; $i < $totalTravelers; $i++) {
                // Determine traveler type based on counts
                if ($adultsAdded < $this->adults_count) {
                    $type = 'adult';
                    $adultsAdded++;
                } else {
                    $type = 'child';
                    $childrenAdded++;
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

    public function updatedTravelers(): void
    {
        // Recalculate price when traveler ages change
        $this->calculatePrice();
    }

    public function calculatePrice(): void
    {
        if (! $this->trip_id || ! $this->check_in || ! $this->check_out || ! $this->currency) {
            $this->sub_total = 0;
            $this->total_price = 0;
            return;
        }

        // Get the full Trip model
        $trip = Trip::find($this->trip_id);

        if (! $trip) {
            $this->sub_total = 0;
            $this->total_price = 0;
            return;
        }

        // Use TripPricingService with children ages
        $result = TripPricingService::calculateTripPriceWithAges(
            trip: $trip,
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: (int) $this->adults_count,
            childrenAges: $this->children_ages,
            currency: $this->currency
        );



        // Update component properties from result
        $this->nights_count = $result['nights_count'];
        $this->sub_total = $result['sub_total'];
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
            'children_ages' => 'nullable|array',
            'children_ages.*' => 'required|integer|min:0|max:18',
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
            'children_count' => count($this->children_ages),
            'price' => $this->sub_total,
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
        $this->redirectIntended(default: route('bookings.trips'));
    }

    public function render(): View
    {

        return view('livewire.dashboard.booking-trip.create-booking-trip');
    }
}
