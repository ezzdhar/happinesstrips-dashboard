<?php

namespace App\Livewire\Dashboard\BookingHotel;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('hotel_bookings')]
class BookingHotelData extends Component
{
    use WithPagination;

    public $search;

    public $status_filter;

	public $user_filter;
	public $users;
	public $hotel_filter;
	public $hotels;


    public function mount(): void
    {
	    $this->users = User::get(['id', 'name','phone'])->toArray();
	    $this->hotels = Hotel::get(['id', 'name'])->map(function ($hotel) {
		    return [
			    'id' => $hotel->id,
			    'name' => $hotel->name,
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
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
	    $data['bookings'] = Booking::type('hotel')->bookingNumber($this->search)->status($this->status_filter)->user($this->user_filter)->hotel($this->hotel_filter)
		    ->select('id', 'user_id', 'booking_number', 'check_in', 'check_out', 'status', 'total_price')
            ->latest()
		    ->with(['user','bookingHotel.hotel', 'bookingHotel.room', 'travelers'])
		    ->paginate(20);
	    return view('livewire.dashboard.booking-hotel.booking-hotel-data', $data);
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
