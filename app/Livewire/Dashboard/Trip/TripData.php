<?php

namespace App\Livewire\Dashboard\Trip;

use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('trips')]
class TripData extends Component
{
    use WithPagination;

    public $search;

    public $status_filter;

    public $type_filter;

    public function mount(): void
    {
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.trips'),
                'icon' => 'o-globe-alt',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['trips'] = Trip::filter($this->search)
            ->status($this->status_filter)
            ->type($this->type_filter)
            ->with(['mainCategory', 'subCategory', 'files'])
            ->latest()
            ->paginate(20);

        return view('livewire.dashboard.trip.trip-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.trip')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        Trip::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.trip')]));
    }
}
