<?php

namespace App\Livewire\Dashboard\HotelType;

use App\Models\HotelType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('hotel_types')]
class HotelTypeData extends Component
{
    use WithPagination;

    public $search;

    public function mount(): void
    {
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.hotel_types'),
                'icon' => 'o-building-office',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['hotel_types'] = HotelType::filter($this->search)
            ->latest()
            ->paginate(25);

        return view('livewire.dashboard.hotel-type.hotel-type-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.hotel_type')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        HotelType::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.hotel_type')]));
    }
}
