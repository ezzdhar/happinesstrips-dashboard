<?php

namespace App\Livewire\Dashboard\City;

use App\Models\City;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpdateCity extends Component
{
    public bool $modalUpdate = false;

    public City $city;

    public $name_ar;

    public $name_en;


    public function mount(): void
    {
        $this->name_ar = $this->city->getTranslation('name', 'ar');
        $this->name_en = $this->city->getTranslation('name', 'en');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ];
    }

    public function saveUpdate(): void
    {
        $this->validate();
        $this->city->update([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
        ]);

        $this->modalUpdate = false;
        $this->dispatch('render')->component(CityData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.city')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.city.update-city');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

