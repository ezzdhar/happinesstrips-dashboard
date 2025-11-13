<?php

namespace App\Livewire\Dashboard\BookingTrip;

use App\Models\Booking;
use App\Services\TripPricingService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('booking_details')]
class ShowBookingTrip extends Component
{
    public Booking $booking;

    public $calculated_price = 0;

    public $total_price = 0;

    public $child_age_threshold = 12;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['user', 'trip', 'travelers']);

        $this->calculated_price = $booking->price;
        $this->total_price = $booking->total_price;
        $this->child_age_threshold = TripPricingService::getChildAgeThreshold();

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
                'label' => __('lang.booking_details').' - '.$this->booking->booking_number,
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.dashboard.booking-trip.show-booking-trip');
    }
}
