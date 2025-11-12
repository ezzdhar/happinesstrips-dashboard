<?php

namespace App\Livewire\Dashboard\SubCategory;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateSubCategory extends Component
{
    use WithFileUploads;

    public bool $modalUpdate = false;

    public SubCategory $subCategory;

    public $name_ar;

    public $name_en;

    public $status;

    public $main_category_id;

    public $image;

    public $main_categories = [];

    public function mount(): void
    {
        $this->name_ar = $this->subCategory->getTranslation('name', 'ar');
        $this->name_en = $this->subCategory->getTranslation('name', 'en');
        $this->status = $this->subCategory->status->value;
        $this->main_category_id = $this->subCategory->main_category_id;
        $this->main_categories = MainCategory::status(Status::Active)->get(['id', 'name'])->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        })->toArray();
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'main_category_id' => 'required|exists:main_categories,id',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->subCategory->update([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'main_category_id' => $this->main_category_id,
            'status' => $this->status,
            'image' => FileService::update($this->subCategory->image, $this->image, 'sub_categories'),
        ]);

        $this->modalUpdate = false;
        $this->dispatch('render')->component(SubCategoryData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.sub_category')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.sub-category.update-sub-category');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
