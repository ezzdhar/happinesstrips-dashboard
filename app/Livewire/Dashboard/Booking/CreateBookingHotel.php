<?php

namespace App\Livewire\Dashboard\Booking;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('create_hotel_booking')]
class CreateBookingHotel extends Component
{
    public $user_id;

	public $trip_id;
	public $hotel_id;

    public $check_in;

    public $check_out;

    public $nights_count = 1;

    public $adults_count = 1;

    public $children_count = 0;

    public $notes;

    public $currency = 'egp';

    // Hotel booking details
    public $hotels = [];


    // Travelers
	public $travelers = [];
	public $users = [];



    public function mount(): void
    {
	    $this->users = User::get(['id', 'name'])->toArray();
	    $this->hotels = Hotel::status(Status::Active)->with('rooms')->get()->map(function ($hotel) {
		    return [
			    'id' => $hotel->id,
			    'name' => $hotel->name,
			    'rooms' => $hotel->rooms->map(function ($room) {
				    return [
					    'id' => $room->id,
					    'name' => $room->name,
					    'weekly_prices' => $room->weekly_prices,
				    ];
			    })->toArray(),
		    ];
	    })->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.hotel_bookings'),
                'icon' => 'o-calendar',
                'link' => route('bookings.hotels'),
            ],
            [
                'label' => __('lang.create_hotel_booking'),
            ],
        ];
    }

    public function updatedCheckIn(): void
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
            $this->nights_count = $checkIn->diffInDays($checkOut);
        }
    }

    public function updatedCheckOut(): void
    {
        $this->updatedCheckIn();
    }

    public function addHotel(): void
    {
        $this->selected_hotels[] = ['hotel_id' => '', 'room_id' => '', 'rooms_count' => 1];
    }

    public function removeHotel($index): void
    {
        unset($this->selected_hotels[$index]);
        $this->selected_hotels = array_values($this->selected_hotels);
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
            'selected_hotels' => 'required|array|min:1',
            'selected_hotels.*.hotel_id' => 'required|exists:hotels,id',
            'selected_hotels.*.room_id' => 'required|exists:rooms,id',
            'selected_hotels.*.rooms_count' => 'required|integer|min:1',
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

        // Calculate total price
        $totalPrice = 0;
        foreach ($this->selected_hotels as $hotelData) {
            $room = Room::find($hotelData['room_id']);
            $roomPrice = $room->weekly_prices[$this->currency] ?? 0;
            $totalPrice += $roomPrice * $hotelData['rooms_count'] * $this->nights_count;
        }

        // Create booking
        $booking = Booking::create([
            'user_id' => $this->user_id,
            'trip_id' => $this->trip_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'nights_count' => $this->nights_count,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'price' => $totalPrice,
            'total_price' => $totalPrice,
            'currency' => $this->currency,
            'notes' => $this->notes,
            'status' => Status::Pending,
        ]);

        // Create hotel bookings
        foreach ($this->selected_hotels as $hotelData) {
            $room = Room::find($hotelData['room_id']);
            BookingHotel::create([
                'booking_id' => $booking->id,
                'hotel_id' => $hotelData['hotel_id'],
                'room_id' => $hotelData['room_id'],
                'room_price' => $room->weekly_prices,
                'rooms_count' => $hotelData['rooms_count'],
            ]);
        }

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
        $this->redirectIntended(default: route('bookings.hotels'), navigate: true);
    }

    public function render(): View
    {
        $data['users'] = User::get(['id', 'name'])->toArray();
        $data['trips'] = Trip::status(Status::Active)->get(['id', 'name'])->toArray();
        $data['hotels'] = Hotel::status(Status::Active)->with('rooms')->get();

        return view('livewire.dashboard.booking.create-booking-hotel', $data);
    }
}
