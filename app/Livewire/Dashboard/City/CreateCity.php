<?php

namespace App\Livewire\Dashboard\City;

use App\Models\City;
use App\Services\FileService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCity extends Component
{
	use WithFileUploads;
    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;
	public $image;

    public function render()
    {
        return view('livewire.dashboard.city.create-city');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255|unique:name->ar',
            'name_en' => 'required|string|max:255',
	        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        City::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
	        'image' => FileService::save($this->image,folder: 'cities'),
        ]);

        $this->modalAdd = false;
        $this->dispatch('render')->component(CityData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.city')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'code']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
