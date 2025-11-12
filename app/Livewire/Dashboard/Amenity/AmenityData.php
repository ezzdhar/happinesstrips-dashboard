<?php

namespace App\Livewire\Dashboard\Amenity;

use App\Models\Amenity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('amenities')]
class AmenityData extends Component
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
                'label' => __('lang.amenities'),
                'icon' => 'o-sparkles',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['amenities'] = Amenity::when($this->search, function ($q) {
            $q->where('name->ar', 'like', "%{$this->search}%")
                ->orWhere('name->en', 'like', "%{$this->search}%");
        })
            ->latest()
            ->paginate(25);

        return view('livewire.dashboard.amenity.amenity-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.amenity')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        Amenity::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.amenity')]));
    }
}
