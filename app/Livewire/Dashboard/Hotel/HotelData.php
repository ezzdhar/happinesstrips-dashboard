<?php

namespace App\Livewire\Dashboard\Hotel;

use App\Models\Hotel;
use App\Models\HotelType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('hotels')]
class HotelData extends Component
{
    use WithPagination;

    public $search;

    public $status_filter;
	public $hotel_types = [];
	public $hotel_type_filter;

    public function mount(): void
    {
	    $this->hotel_types = HotelType::get(['id', 'name'])->map(function ($type) {
		    return [
			    'id' => $type->id,
			    'name' => $type->name,
		    ];
	    })->toArray();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.hotels'),
                'icon' => 'o-building-office-2',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['hotels'] = Hotel::filter($this->search)
	        ->status($this->status_filter)
	        ->hotelTypeFilter($this->hotel_type_filter)
	        ->with(['city', 'hotelTypes'])->withCount('rooms')->latest()->paginate(20);

        return view('livewire.dashboard.hotel.hotel-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.hotel')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        Hotel::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.hotel')]));
    }
}
