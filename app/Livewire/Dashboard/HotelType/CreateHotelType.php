<?php

namespace App\Livewire\Dashboard\HotelType;

use App\Models\HotelType;
use Livewire\Component;

class CreateHotelType extends Component
{
    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;

    public function render()
    {
        return view('livewire.dashboard.hotel-type.create-hotel-type');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();
        HotelType::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
        ]);

        $this->modalAdd = false;
        $this->dispatch('render')->component(HotelTypeData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.hotel_type')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en']);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
