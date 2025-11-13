<?php

namespace App\Livewire\Dashboard\BookingTrip;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('trip_bookings')]
class BookingTripData extends Component
{
    use WithPagination;

    public $search;

    public $status_filter;

    public $user_filter;

    public $trip_filter;

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
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['bookings'] = Booking::type('trip')->with(['user', 'trip', 'bookingHotel.hotel', 'bookingHotel.room', 'travelers'])
            ->when($this->search, function ($q) {
                $q->where('booking_number', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->status($this->status_filter)
            ->user($this->user_filter)
            ->trip($this->trip_filter)
            ->latest()
            ->paginate(20);

        $data['users'] = User::get(['id', 'name'])->toArray();
        $data['trips'] = Trip::get(['id', 'name'])->toArray();

        return view('livewire.dashboard.booking-trip.booking-trip-data', $data);
    }

    public function deleteSweetAlert($id): void
    {
        sweetalert()
            ->showDenyButton()
            ->timer(0)
            ->iconColor('#FFA500')
            ->option('confirmButtonText', __('lang.confirm'))
            ->option('denyButtonText', __('lang.cancel'))
            ->option('id', $id)
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.booking')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        Booking::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.booking')]));
    }
}
