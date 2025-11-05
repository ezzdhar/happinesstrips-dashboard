<?php

namespace App\Livewire\Dashboard\MainCategory;

use App\Enums\Status;
use App\Models\MainCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('main_categories')]
class MainCategoryData extends Component
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
                'label' => __('lang.main_categories'),
                'icon' => 'o-rectangle-stack',
            ],
        ];
    }

    #[On('render')]
    public function render(): View
    {
        $data['main_categories'] = MainCategory::filter($this->search)
            ->status($this->status_filter)
            ->latest()
            ->paginate(25);

        return view('livewire.dashboard.main-category.main-category-data', $data);
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
            ->info(__('lang.confirm_delete', ['attribute' => __('lang.main_category')]));
    }

    #[On('sweetalert:confirmed')]
    public function delete(array $payload): void
    {
        $id = $payload['envelope']['options']['id'];
        MainCategory::findOrFail($id)->delete();
        flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.main_category')]));
    }
}

