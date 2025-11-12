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
	    $this->users = User::role('user')->get(['id', 'name','phone'])->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

	//get rooms based on selected hotel
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

	public function save()
    {
        $this->validate();
	    // Get room and calculate total price
	    $room = Room::find($this->room_id);
	    $breakdown = $room->priceBreakdownForPeriod($this->check_in, $this->check_out, $this->currency);

	    try {
		    DB::beginTransaction();
		    // Create booking
		    $booking = Booking::create([
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

		    // Create hotel booking
		    BookingHotel::create([
			    'booking_id' => $booking->id,
			    'hotel_id' => $this->hotel_id,
			    'room_id' => $this->room_id,
			    'room_includes' => $room->includes,
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
		    $this->redirectIntended(default: route('bookings.hotels'));
	    } catch (\Exception $e) {
		    DB::rollBack();
		    flash()->error(__('lang.error_occurred'));
		    // Optionally log the error
		    Log::error($e->getMessage());
	    }
    }

    public function render(): View
    {
	    return view('livewire.dashboard.booking-hotel.create-booking-hotel');
    }
}
