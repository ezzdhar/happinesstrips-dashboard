<?php

namespace App\Livewire\Dashboard\BookingHotel;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('update_hotel_booking')]
class UpdateBookingHotel extends Component
{
    public Booking $booking;

    public $user_id;

    public $hotel_id;

    public $room_id;

    public $selected_room;

    public $check_in;

    public $check_out;

    public $status;

    public $notes;

    public $currency = 'egp';

    public $nights_count = 1;

    public $travelers = [];

    public $hotels = [];

    public $users = [];

    public $rooms = [];

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingHotel', 'travelers']);

        $this->user_id = $booking->user_id;
        $this->check_in = $booking->check_in?->format('Y-m-d');
        $this->check_out = $booking->check_out?->format('Y-m-d');
        $this->nights_count = $booking->nights_count;
        $this->notes = $booking->notes;
        $this->currency = $booking->currency;
        $this->status = $booking->status->value;

        // Load hotel booking (single)
        if ($booking->bookingHotel) {
            $this->hotel_id = $booking->bookingHotel->hotel_id;
            $this->room_id = $booking->bookingHotel->room_id;
            $this->selected_room = $booking->bookingHotel->room;
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

        // Load data for dropdowns
        $this->hotels = Hotel::status(Status::Active)->get()->map(function ($hotel) {
            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
            ];
        })->toArray();
        $this->users = User::role('user')->get(['id', 'name', 'phone'])->toArray();

        // Load rooms for selected hotel
        if ($this->hotel_id) {
            $this->rooms = Room::where('hotel_id', $this->hotel_id)->status(Status::Active)->get();
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

    // get rooms based on selected hotel
    public function updatedHotelId(): void
    {
        $this->room_id = null;
        $this->selected_room = null;
        $this->rooms = [];
        $this->travelers = [];
        if ($this->hotel_id) {
            $this->rooms = Room::where('hotel_id', $this->hotel_id)->status(Status::Active)->get();
        }
    }

    public function updatedRoomId(): void
    {
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            if ($room) {
                $this->selected_room = $room;
                $this->initializeTravelers($room->adults_count, $room->children_count);
            }
        }
    }

    public function initializeTravelers(int $adultsCount, int $childrenCount): void
    {
        $this->travelers = [];
        // Add adults
        for ($i = 0; $i < $adultsCount; $i++) {
            $this->travelers[] = [
                'full_name' => '',
                'phone_key' => '+20',
                'phone' => '',
                'nationality' => '',
                'age' => '',
                'id_type' => '',
                'id_number' => '',
                'type' => 'adult',
            ];
        }

        // Add children
        for ($i = 0; $i < $childrenCount; $i++) {
            $this->travelers[] = [
                'full_name' => '',
                'phone_key' => '+20',
                'phone' => '',
                'nationality' => '',
                'age' => '',
                'id_type' => 'passport',
                'id_number' => '',
                'type' => 'child',
            ];
        }
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

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'nights_count' => 'required|integer|min:1',
            'currency' => 'required|in:egp,usd',
            'notes' => 'nullable|string',
            'travelers' => 'required|array|min:1',
            'travelers.*.full_name' => 'required|string',
            'travelers.*.phone_key' => 'required|string',
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

        // Get room and calculate total price
        $room = Room::find($this->room_id);
        $breakdown = $room->priceBreakdownForPeriod($this->check_in, $this->check_out, $this->currency);

        try {
            DB::beginTransaction();

            // Update booking
            $this->booking->update([
                'user_id' => $this->user_id,
                'check_in' => $this->check_in,
                'check_out' => $this->check_out,
                'nights_count' => $breakdown['nights_count'],
                'adults_count' => $room->adults_count,
                'children_count' => $room->children_count,
                'price' => $breakdown['total'],
                'total_price' => $breakdown['total'],
                'currency' => $this->currency,
                'notes' => $this->notes,
                'status' => $this->status,
            ]);

            // Update or create hotel booking
            $this->booking->bookingHotel()->updateOrCreate(
                ['booking_id' => $this->booking->id],
                [
                    'hotel_id' => $this->hotel_id,
                    'room_id' => $this->room_id,
                    'room_includes' => $room->includes,
                ]
            );

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

            DB::commit();
            flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.booking')]));
            $this->redirectIntended(default: route('bookings.hotels'));
        } catch (\Exception $e) {
            DB::rollBack();
            flash()->error(__('lang.error_occurred'));
            Log::error($e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.booking-hotel.update-booking-hotel');
    }
}
