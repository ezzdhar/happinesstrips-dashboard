<?php

namespace App\Livewire\Dashboard\BookingHotel;

use App\Enums\Status;
use App\Models\Booking;
use App\Models\BookingHotel;
use App\Models\BookingTraveler;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('create_hotel_booking')]
class CreateBookingHotel extends Component
{
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

    public $pricing_result = null;

    public $adults_count = 2;

    public $children_ages = [];

    public $hotels = [];

    public $users = [];

    public $rooms = [];

    public function mount(): void
    {
        $this->hotels = Hotel::status(Status::Active)->get()->map(function ($hotel) {
            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
            ];
        })->toArray();
        $this->users = User::role('user')->get(['id', 'name', 'phone'])->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    // get rooms based on selected hotel
    public function updatedHotelId(): void
    {
        $this->room_id = null;
        $this->travelers = [];
        $this->rooms = [];
        $this->selected_room = null;
        if ($this->hotel_id) {
            $this->rooms = Room::where('hotel_id', $this->hotel_id)->status(Status::Active)->get();
        }
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
        $this->calculatePrice();
    }

    public function updatedCheckOut(): void
    {
        $this->updatedCheckIn();
    }

    public function updatedRoomId(): void
    {
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            if ($room) {
                $this->selected_room = $room;
                $this->adults_count = $room->adults_count;
                $this->pricing_result = null;
                $this->travelers = [];
            }
        }
    }

    public function calculatePrice(): void
    {
        if (! $this->room_id || ! $this->check_in || ! $this->check_out) {
            $this->pricing_result = null;
            flash()->error(__('lang.please_select_room_checkin_checkout'));

            return;
        }

        $room = Room::find($this->room_id);
        if (! $room) {
            $this->pricing_result = null;
            return;
        }

        $this->pricing_result = $room->calculateBookingPrice(
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: $this->adults_count,
            childrenAges: $this->children_ages,
            currency: $this->currency
        );

        if ($this->pricing_result['success']) {
            $this->nights_count = $this->pricing_result['nights_count'];
            // تهيئة المسافرين بناءً على العدد
            $this->initializeTravelers(
                $this->adults_count,
                count($this->children_ages)
            );
        }
    }

    public function addChild(): void
    {
        if ($this->selected_room->children_count <= count($this->children_ages)) {
            flash()->error(__('lang.children_count_exceeded_maximum', ['max' => $this->selected_room->children_count]));

            return;
        }
        $this->children_ages[] = 0;
        $this->calculatePrice();
    }

    public function removeChild($index): void
    {
        unset($this->children_ages[$index]);
        $this->children_ages = array_values($this->children_ages);
        $this->calculatePrice();
    }

    public function updatedAdultsCount(): void
    {
        if ($this->selected_room->adults_count < $this->adults_count) {
            $this->adults_count = $this->selected_room->adults_count;
            flash()->error(__('lang.adults_count_exceeded_maximum', ['max' => $this->selected_room->adults_count]));

            return;
        }
        $this->calculatePrice();
    }

    public function updatedChildrenAges(): void
    {
        $this->calculatePrice();
    }

    public function updatedCurrency(): void
    {
        $this->calculatePrice();
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

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'adults_count' => 'required|integer|min:1',
            'children_ages' => 'nullable|array',
            'children_ages.*' => 'required|integer|min:0|max:18',
            'nights_count' => 'required|integer|min:1',
            'currency' => 'required|in:egp,usd',
            'status' => 'required|in:pending,under_payment,under_cancellation,cancelled,completed',
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

    public function save()
    {
        $this->validate();

        // Get room and calculate total price using new system
        $room = Room::find($this->room_id);

        $pricingResult = $room->calculateBookingPrice(
            checkIn: $this->check_in,
            checkOut: $this->check_out,
            adultsCount: $this->adults_count,
            childrenAges: $this->children_ages,
            currency: $this->currency
        );

        // Check if pricing calculation was successful
        if (! $pricingResult['success']) {
            flash()->error($pricingResult['error']);

            return;
        }

        try {
            DB::beginTransaction();

            // Create booking
            $booking = Booking::create([
                'user_id' => $this->user_id,
                'check_in' => $this->check_in,
                'check_out' => $this->check_out,
                'nights_count' => $pricingResult['nights_count'],
                'adults_count' => $this->adults_count,
                'children_count' => count($this->children_ages),
                'price' => $pricingResult['grand_total'],
                'total_price' => $pricingResult['grand_total'],
                'currency' => strtoupper($this->currency),
                'notes' => $this->notes,
                'status' => $this->status,
            ]);

            // Create hotel booking with pricing details
	        BookingHotel::create([
                'booking_id' => $booking->id,
                'hotel_id' => $this->hotel_id,
                'room_id' => $this->room_id,
                'room_includes' => $room->includes,
                'adults_price' => $pricingResult['adults_total'],
                'children_price' => $pricingResult['children_total'],
                'children_breakdown' => $pricingResult['children_breakdown'],
                'pricing_details' => $pricingResult,
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

            DB::commit();
            flash()->success(__('lang.created_successfully', ['attribute' => __('lang.booking')]));
            $this->redirectIntended(default: route('bookings.hotels.show', $booking->id));
        } catch (\Exception $e) {
            DB::rollBack();
            flash()->error(__('lang.error_occurred'));
            Log::error('Booking creation error: '.$e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.booking-hotel.create-booking-hotel');
    }
}
