<?php

namespace App\Livewire\Dashboard\Amenity;

use App\Models\Amenity;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpdateAmenity extends Component
{
    public bool $modalUpdate = false;

    public Amenity $amenity;

    public $name_ar;

    public $name_en;

    public $icon;

    public function mount(): void
    {
        $this->name_ar = $this->amenity->getTranslation('name', 'ar');
        $this->name_en = $this->amenity->getTranslation('name', 'en');
        $this->icon = $this->amenity->icon;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->amenity->update([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'icon' => $this->icon,
        ]);

        $this->modalUpdate = false;
        $this->dispatch('render')->component(AmenityData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.amenity')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.amenity.update-amenity');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
