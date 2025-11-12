<?php

namespace App\Livewire\Dashboard\BookingTrip;

use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('booking_details')]
class ShowBookingTrip extends Component
{
    public Booking $booking;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['user', 'trip', 'bookingHotel.hotel', 'bookingHotel.room', 'travelers']);
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        $isHotelBooking = $this->booking->bookingHotel->count() > 0;

        return [
            [
                'label' => $isHotelBooking ? __('lang.hotel_bookings') : __('lang.trip_bookings'),
                'icon' => $isHotelBooking ? 'o-calendar' : 'o-map',
                'link' => $isHotelBooking ? route('bookings.hotels') : route('bookings.trips'),
            ],
            [
                'label' => __('lang.booking_details').' - '.$this->booking->booking_number,
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.booking-trip.show-booking-trip');
    }
}
