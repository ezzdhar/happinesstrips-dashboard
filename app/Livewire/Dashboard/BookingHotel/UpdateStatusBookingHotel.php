<?php

namespace App\Livewire\Dashboard\BookingHotel;

use App\Models\Booking;
use Livewire\Component;

class UpdateStatusBookingHotel extends Component
{
	public bool $modalUpdate = false;

	public Booking $booking;
	public $status;

	public function mount(): void
	{
		$this->status = $this->booking->status->value;
	}

	public function rules(): array
	{
		return [
			'status' => 'required|in:pending,under_payment,under_cancellation,cancelled,completed',
		];
	}

	public function saveUpdate(): void
	{
		$this->validate();
		$this->booking->update(['status' => $this->status]);
		$this->modalUpdate = false;
		$this->dispatch('render')->component(BookingHotelData::class);
		$this->dispatch('render')->component(ShowBookingHotel::class);
		flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.booking')]));
	}

	public function render()
	{
		return view('livewire.dashboard.booking-hotel.update-status-booking-hotel');
	}

	public function resetError(): void
	{
		$this->resetErrorBag();
		$this->resetValidation();
	}
}
