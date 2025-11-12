<?php

namespace App\Livewire\Dashboard\SubCategory;

use App\Models\SubCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('sub_categories')]
class SubCategoryData extends Component
{
    use WithPagination;

    public $search;

    public $status_filter;

    public function mount(): void
    {
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.sub_categories'),
                'icon' => 'o-squares-2x2',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['sub_categories'] = SubCategory::filter($this->search)
            ->status($this->status_filter)
            ->latest()
            ->with(['mainCategory'])
            ->paginate(25);

        return view('livewire.dashboard.sub-category.sub-category-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.sub_category')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        SubCategory::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.sub_category')]));
    }
}
