<?php

namespace App\Livewire\Dashboard\MainCategory;

use App\Enums\Status;
use App\Models\MainCategory;
use App\Services\FileService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateMainCategory extends Component
{
    use WithFileUploads;

    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;

    public $status;

    public $image;

    public function render()
    {
        return view('livewire.dashboard.main-category.create-main-category');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'status' => 'required|in:'.Status::Active.','.Status::Inactive,
            'image' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        MainCategory::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'status' => $this->status,
            'image' => FileService::save($this->image, 'main_categories'),
        ]);

        $this->modalAdd = false;
        $this->dispatch('render')->component(MainCategoryData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.main_category')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'status', 'image']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
