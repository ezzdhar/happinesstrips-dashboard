<?php

namespace App\Livewire\Dashboard\SubCategory;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Services\FileService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateSubCategory extends Component
{
    use WithFileUploads;

    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;

    public $status;

    public $main_category_id;

    public $image;

    public $main_categories = [];

    public function mount(): void
    {
        $this->main_categories = MainCategory::status(Status::Active)->get(['id', 'name'])->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.sub-category.create-sub-category');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'main_category_id' => 'required|exists:main_categories,id',
            'status' => 'required|in:' . Status::Active->value . ',' . Status::Inactive->value,
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        SubCategory::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'main_category_id' => $this->main_category_id,
            'status' => $this->status,
            'image' => FileService::save($this->image, 'sub_categories'),
        ]);

        $this->modalAdd = false;
        $this->dispatch('render')->component(SubCategoryData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.sub_category')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'status', 'main_category_id', 'image']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

