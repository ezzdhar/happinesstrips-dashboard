<?php

namespace App\Livewire\Dashboard\City;

use App\Models\City;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('cities')]
class CityData extends Component
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
                'label' => __('lang.cities'),
                'icon' => 'o-map-pin',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['cities'] = City::filter($this->search)
            ->latest()
            ->paginate(25);

        return view('livewire.dashboard.city.city-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.city')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        City::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.city')]));
    }
}

