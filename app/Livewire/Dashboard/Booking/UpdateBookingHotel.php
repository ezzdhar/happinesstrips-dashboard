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
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('update_hotel_booking')]
class UpdateBookingHotel extends Component
{
    public Booking $booking;

    public $user_id;

    public $trip_id;

    public $check_in;

    public $check_out;

    public $nights_count = 1;

    public $adults_count = 1;

    public $children_count = 0;

    public $notes;

    public $currency = 'egp';

    public $status;

    // Hotel booking details
    public $selected_hotels = [];

    // Travelers
    public $travelers = [];

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingHotels', 'travelers']);

        $this->user_id = $booking->user_id;
        $this->trip_id = $booking->trip_id;
        $this->check_in = $booking->check_in?->format('Y-m-d');
        $this->check_out = $booking->check_out?->format('Y-m-d');
        $this->nights_count = $booking->nights_count;
        $this->adults_count = $booking->adults_count;
        $this->children_count = $booking->children_count;
        $this->notes = $booking->notes;
        $this->currency = $booking->currency;
        $this->status = $booking->status->value;

        // Load hotel bookings
        foreach ($booking->bookingHotels as $bookingHotel) {
            $this->selected_hotels[] = [
                'id' => $bookingHotel->id,
                'hotel_id' => $bookingHotel->hotel_id,
                'room_id' => $bookingHotel->room_id,
                'rooms_count' => $bookingHotel->rooms_count,
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
                'type' => $traveler->type,
            ];
        }

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
                'label' => __('lang.update_hotel_booking').' - '.$this->booking->booking_number,
            ],
        ];
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

    public function addHotel(): void
    {
        $this->selected_hotels[] = ['hotel_id' => '', 'room_id' => '', 'rooms_count' => 1];
    }

    public function removeHotel($index): void
    {
        if (isset($this->selected_hotels[$index]['id'])) {
            BookingHotel::destroy($this->selected_hotels[$index]['id']);
        }
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
        if (isset($this->travelers[$index]['id'])) {
            BookingTraveler::destroy($this->travelers[$index]['id']);
        }
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
            'status' => 'required|in:'.implode(',', array_column(Status::cases(), 'value')),
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

    public function update(): void
    {
        $this->validate();

        // Calculate total price
        $totalPrice = 0;
        foreach ($this->selected_hotels as $hotelData) {
            $room = Room::find($hotelData['room_id']);
            $roomPrice = $room->weekly_prices[$this->currency] ?? 0;
            $totalPrice += $roomPrice * $hotelData['rooms_count'] * $this->nights_count;
        }

        // Update booking
        $this->booking->update([
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
            'status' => $this->status,
        ]);

        // Update hotel bookings
        $existingIds = [];
        foreach ($this->selected_hotels as $hotelData) {
            $room = Room::find($hotelData['room_id']);

            if (isset($hotelData['id'])) {
                BookingHotel::find($hotelData['id'])->update([
                    'hotel_id' => $hotelData['hotel_id'],
                    'room_id' => $hotelData['room_id'],
                    'room_price' => $room->weekly_prices,
                    'rooms_count' => $hotelData['rooms_count'],
                ]);
                $existingIds[] = $hotelData['id'];
            } else {
                $newBookingHotel = BookingHotel::create([
                    'booking_id' => $this->booking->id,
                    'hotel_id' => $hotelData['hotel_id'],
                    'room_id' => $hotelData['room_id'],
                    'room_price' => $room->weekly_prices,
                    'rooms_count' => $hotelData['rooms_count'],
                ]);
                $existingIds[] = $newBookingHotel->id;
            }
        }

        // Delete removed hotels
        BookingHotel::where('booking_id', $this->booking->id)
            ->whereNotIn('id', $existingIds)
            ->delete();

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
                    'id_number' => $travelerData['id_number'],
                    'type' => $travelerData['type'],
                ]);
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
                    'id_number' => $travelerData['id_number'],
                    'type' => $travelerData['type'],
                ]);
                $existingTravelerIds[] = $newTraveler->id;
            }
        }

        // Delete removed travelers
        BookingTraveler::where('booking_id', $this->booking->id)
            ->whereNotIn('id', $existingTravelerIds)
            ->delete();

        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.booking')]));
        $this->redirectIntended(default: route('bookings.hotels'), navigate: true);
    }

    public function render(): View
    {
        $data['users'] = User::get(['id', 'name'])->toArray();
        $data['trips'] = Trip::status(Status::Active)->get(['id', 'name'])->toArray();
        $data['hotels'] = Hotel::status(Status::Active)->with('rooms')->get();
        $data['statuses'] = Status::cases();

        return view('livewire.dashboard.booking.update-booking-hotel', $data);
    }
}
