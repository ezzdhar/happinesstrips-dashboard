<?php

namespace App\Livewire\Dashboard\Amenity;

use App\Models\Amenity;
use Livewire\Component;

class CreateAmenity extends Component
{
    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;

    public $icon;

    public function render()
    {
        return view('livewire.dashboard.amenity.create-amenity');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        Amenity::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'icon' => $this->icon,
        ]);

        $this->modalAdd = false;
        $this->dispatch('render')->component(AmenityData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.amenity')]));
        $this->resetData();
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'icon']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
