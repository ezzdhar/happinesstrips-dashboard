<?php

namespace App\Livewire\Dashboard\MainCategory;

use App\Models\MainCategory;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateMainCategory extends Component
{
    use WithFileUploads;

    public bool $modalUpdate = false;

    public MainCategory $mainCategory;

    public $name_ar;

    public $name_en;

    public $status;

    public $image;

    public function mount(): void
    {
        $this->name_ar = $this->mainCategory->getTranslation('name', 'ar');
        $this->name_en = $this->mainCategory->getTranslation('name', 'en');
        $this->status = $this->mainCategory->status->value;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->mainCategory->update([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'status' => $this->status,
            'image' => FileService::update($this->mainCategory->image, $this->image, 'main_categories'),
        ]);

        $this->modalUpdate = false;
        $this->dispatch('render')->component(MainCategoryData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.main_category')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.main-category.update-main-category');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
