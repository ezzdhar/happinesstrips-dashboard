<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('rooms')]
class RoomData extends Component
{
	use WithPagination;

	public $search;

	public $status_filter;
	public $hotels = [];
	#[Url]
	public $hotel_id_filter;


	public function mount(): void
	{
		$this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(function ($hotel) {
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
				'label' => __('lang.rooms'),
				'icon' => 'o-home',
			],
		];
	}

	#[On('render')]
	public function render(): View
	{
		$rooms = Room::with(['hotel'])->filter($this->search)->status($this->status_filter)->hotelId($this->hotel_id_filter);
		$data['rooms_active'] = (clone $rooms)->where('status', Status::Active)->count();
		$data['rooms_inactive'] = (clone $rooms)->where('status', Status::Inactive)->count();
		$data['rooms'] = $rooms->with(['files'])->latest()->paginate(25);
		return view('livewire.dashboard.room.room-data', $data);
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
			->info(__('lang.confirm_delete', ['attribute' => __('lang.room')]));
	}

	#[On('sweetalert:confirmed')]
	public function delete(array $payload): void
	{
		$id = $payload['envelope']['options']['id'];
		Room::findOrFail($id)->delete();
		flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.room')]));
	}
}

