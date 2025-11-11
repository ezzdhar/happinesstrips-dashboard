<?php

namespace App\Livewire\Dashboard\Booking;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('create_trip_booking')]
class CreateBookingTrip extends Component
{
    public $user_id;

    public $trip_id;

    public $check_in;

    public $check_out;

    public $nights_count = 1;

    public $adults_count = 1;

    public $children_count = 0;

    public $notes;

    public $currency = 'egp';

    // Travelers
    public $travelers = [];

    public function mount(): void
    {
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
            $trip = Trip::find($this->trip_id);
            if ($trip) {
                $this->check_in = $trip->duration_from;
                $this->check_out = $trip->duration_to;
                $this->updatedCheckIn();
            }
        }
    }

    public function updatedCheckIn(): void
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = \Carbon\Carbon::parse($this->check_in);
            $checkOut = \Carbon\Carbon::parse($this->check_out);
            $this->nights_count = $checkIn->diffInDays($checkOut);
        }
    }

    public function updatedCheckOut(): void
    {
        $this->updatedCheckIn();
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

        // Get trip and calculate price
        $trip = Trip::find($this->trip_id);
        $tripPrice = $trip->price[$this->currency] ?? 0;
        $totalPrice = $tripPrice * ($this->adults_count + ($this->children_count * 0.5));

        // Create booking
        $booking = Booking::create([
            'user_id' => $this->user_id,
            'trip_id' => $this->trip_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights_count' => $this->nights_count,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'price' => $tripPrice,
            'total_price' => $totalPrice,
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
        $data['users'] = User::get(['id', 'name'])->toArray();
        $data['trips'] = Trip::status(Status::Active)->get(['id', 'name', 'duration_from', 'duration_to', 'price'])->toArray();

        return view('livewire.dashboard.booking.create-booking-trip', $data);
    }
}
