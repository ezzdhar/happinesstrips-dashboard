<?php

namespace App\Livewire\Dashboard\HotelType;

use App\Models\HotelType;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UpdateHotelType extends Component
{
    public bool $modalUpdate = false;

    public HotelType $hotelType;

    public $name_ar;

    public $name_en;

    public function mount(): void
    {
        $this->name_ar = $this->hotelType->getTranslation('name', 'ar');
        $this->name_en = $this->hotelType->getTranslation('name', 'en');
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
        $this->hotelType->update([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
        ]);

        $this->modalUpdate = false;
        $this->dispatch('render')->component(HotelTypeData::class);
        flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.hotel_type')]));
    }

    public function render(): View
    {
        return view('livewire.dashboard.hotel-type.update-hotel-type');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
