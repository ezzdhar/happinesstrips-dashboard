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

#[Title('update_trip_booking')]
class UpdateBookingTrip extends Component
{
    public Booking $booking;

    public $user_id;

    public $trip_id;

    public $selectedTrip;

    public $check_in;

    public $check_out;

    public $nights_count = 1;

    public $adults_count = 1;

    public $children_count = 0; // Children 12+ (charged as adults)

    public $free_children_count = 0; // Children under 12 (free)

    public $notes;

    public $currency = 'egp';

    public $status;

    public $calculated_price = 0;

    public $total_price = 0;

    // Travelers
    public $travelers = [];

    public $users;

    public $trips;

    public function mount(Booking $booking): void
    {
        $this->users = User::get(['id', 'name', 'phone'])->toArray();
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
        $this->booking = $booking->load(['travelers', 'trip']);

        $this->user_id = $booking->user_id;
        $this->trip_id = $booking->trip_id;
        $this->check_in = $booking->check_in?->format('Y-m-d');
        $this->check_out = $booking->check_out?->format('Y-m-d');
        $this->nights_count = $booking->nights_count;
        $this->adults_count = $booking->adults_count;
        $this->children_count = $booking->children_count;
        $this->free_children_count = $booking->free_children_count ?? 0;
        $this->notes = $booking->notes;
        $this->currency = $booking->currency;
        $this->status = $booking->status->value;
        $this->calculated_price = $booking->price;
        $this->total_price = $booking->total_price;

        // Load selected trip
        if ($booking->trip) {
            $this->selectedTrip = [
                'id' => $booking->trip->id,
                'name' => $booking->trip->name,
                'type' => $booking->trip->type->value,
                'price' => $booking->trip->price,
                'duration_from' => $booking->trip->duration_from,
                'duration_to' => $booking->trip->duration_to,
            ];
        }

        // Load travelers
        foreach ($booking->travelers as $traveler) {
            $this->travelers[] = [
                'id' => $traveler->id,
                'full_name' => $traveler->full_name,
                'phone_key' => $traveler->phone_key,
                'phone' => $traveler->phone,
                'nationality' => $traveler->nationality,
                'age' => $traveler->age,
                'id_type' => $traveler->id_type,
                'id_number' => $traveler->id_number,
            ];
        }

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
                'label' => __('lang.update_trip_booking') . ' - ' . $this->booking->booking_number,
            ],
        ];
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

    public function updatedTripId(): void
    {
        if ($this->trip_id) {
            $trip = Trip::find($this->trip_id);
            if ($trip) {
                $this->selectedTrip = [
                    'id' => $trip->id,
                    'name' => $trip->name,
                    'type' => $trip->type->value,
                    'price' => $trip->price,
                    'duration_from' => $trip->duration_from,
                    'duration_to' => $trip->duration_to,
                ];
                $this->calculatePrice();
            }
        }
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
    }

    public function updatedCurrency(): void
    {
        $this->calculatePrice();
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
                $this->travelers[] = [
                    'full_name' => '',
                    'phone_key' => '+20',
                    'phone' => '',
                    'nationality' => 'مصر',
                    'age' => '',
                    'id_type' => 'passport',
                    'id_number' => '',
                    // 'type' => $type, // Removed
                ];
            }
        }

        // Remove excess travelers if needed
        if ($totalTravelers < $currentTravelers) {
            // Delete removed travelers from database
            for ($i = $totalTravelers; $i < $currentTravelers; $i++) {
                if (isset($this->travelers[$i]['id'])) {
                    BookingTraveler::destroy($this->travelers[$i]['id']);
                }
            }
            $this->travelers = array_slice($this->travelers, 0, $totalTravelers);
        }
    }

    public function calculatePrice(): void
    {
        if (! $this->trip_id || ! $this->check_in || ! $this->check_out || ! $this->currency) {
            $this->calculated_price = 0;
            $this->total_price = 0;

            return;
        }

        $trip = Trip::find($this->trip_id);

        if (! $trip) {
            $this->calculated_price = 0;
            $this->total_price = 0;

            return;
        }

        $result = TripPricingService::calculateTripPrice(
            trip: $trip,
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: (int) $this->adults_count,
            childrenCount: (int) $this->children_count,
            freeChildrenCount: (int) $this->free_children_count,
            currency: $this->currency
        );

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
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'travelers' => 'required|array|min:1',
            'travelers.*.full_name' => 'required|string',
            'travelers.*.phone' => 'required|string',
            'travelers.*.nationality' => 'required|string',
            'travelers.*.age' => 'required|integer|min:1',
            'travelers.*.id_type' => 'required|in:passport,national_id',
            'travelers.*.id_number' => 'required|string',
        ];
    }

    public function update(): void
    {
        $this->validate();

        // Update booking with calculated prices
        $this->booking->update([
            'user_id' => $this->user_id,
            'trip_id' => $this->trip_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights_count' => $this->nights_count,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'free_children_count' => $this->free_children_count,
            'price' => $this->calculated_price,
            'total_price' => $this->total_price,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => $this->status,
        ]);

        // Update travelers
        $existingTravelerIds = [];
        foreach ($this->travelers as $travelerData) {
            if (isset($travelerData['id'])) {
                BookingTraveler::find($travelerData['id'])->update([
                    'full_name' => $travelerData['full_name'],
                    'phone_key' => $travelerData['phone_key'] ?? '+20',
                    'phone' => $travelerData['phone'],
                    'nationality' => $travelerData['nationality'],
                    'age' => $travelerData['age'],
                    'id_type' => $travelerData['id_type'],
                    'id_number' => $travelerData['id_number'],                ]);
                $existingTravelerIds[] = $travelerData['id'];
            } else {
                $newTraveler = BookingTraveler::create([
                    'booking_id' => $this->booking->id,
                    'full_name' => $travelerData['full_name'],
                    'phone_key' => $travelerData['phone_key'] ?? '+20',
                    'phone' => $travelerData['phone'],
                    'nationality' => $travelerData['nationality'],
                    'age' => $travelerData['age'],
                    'id_type' => $travelerData['id_type'],
                    'id_number' => $travelerData['id_number'],                ]);
                $existingTravelerIds[] = $newTraveler->id;
            }
        }

        // Delete removed travelers
        BookingTraveler::where('booking_id', $this->booking->id)
            ->whereNotIn('id', $existingTravelerIds)
            ->delete();

        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.booking')]));
        $this->redirectIntended(default: route('bookings.trips'));
    }

    public function render(): View
    {

        return view('livewire.dashboard.booking-trip.update-booking-trip');
    }
}
