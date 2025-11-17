<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\FileService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('add_room')]
class CreateRoom extends Component
{
    use WithFileUploads;

    public $name_ar;

    public $name_en;

    public $status;

    public $hotel_id;

    public $adults_count = 1;

    public $children_count = 0;

    public $includes_ar;

    public $includes_en;

    public $price_periods = [];

    public $images = [];

    public $hotels = [];

    public $amenities = [];

    public $selected_amenities = [];

    public function mount(): void
    {
        $this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(function ($hotel) {
            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
            ];
        })->toArray();

        $this->amenities = Amenity::get(['id', 'name', 'icon'])->map(function ($amenity) {
            return [
                'id' => $amenity->id,
                'name' => $amenity->name,
                'icon' => $amenity->icon,
            ];
        })->toArray();

        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.rooms'),
                'icon' => 'ionicon.bed-outline',
            ],
            [
                'label' => __('lang.add_room'),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.room.create-room');
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'hotel_id' => 'required|exists:hotels,id',
            'adults_count' => 'required|integer|min:1',
            'children_count' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'includes_ar' => 'required|string',
            'includes_en' => 'required|string',
            'price_periods' => 'required|array|min:1',
            'price_periods.*.start_date' => 'required|date',
            'price_periods.*.end_date' => 'required|date|after:price_periods.*.start_date',
            'price_periods.*.adult_price_egp' => 'required|numeric|min:0',
            'price_periods.*.adult_price_usd' => 'required|numeric|min:0',
            'images.*' => 'required|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
            'selected_amenities' => 'nullable|array',
            'selected_amenities.*' => 'exists:amenities,id',
        ];
    }

    public function saveAdd()
    {
        $this->validate();
        $room = Room::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'hotel_id' => $this->hotel_id,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'status' => $this->status,
            'includes' => [
                'ar' => $this->includes_ar,
                'en' => $this->includes_en,
            ],
            'price_periods' => $this->price_periods,
        ]);

        // Sync amenities
        if (! empty($this->selected_amenities)) {
            $room->amenities()->sync($this->selected_amenities);
        }

        // Save images
        if (! empty($this->images)) {
            foreach ($this->images as $image) {
                $room->files()->create([
                    'path' => FileService::save($image, 'rooms'),
                ]);
            }
        }

        return to_route('rooms')->with('success', __('lang.added_successfully', ['attribute' => __('lang.room')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'status', 'hotel_id', 'adults_count', 'children_count', 'includes_ar', 'includes_en', 'price_periods', 'images']);
        $this->adults_count = 1;
        $this->children_count = 0;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function addPricePeriod(): void
    {
        $this->price_periods[] = [
            'start_date' => '',
            'end_date' => '',
            'adult_price_egp' => 0,
            'adult_price_usd' => 0,
        ];
    }

    public function removePricePeriod($index): void
    {
        unset($this->price_periods[$index]);
        $this->price_periods = array_values($this->price_periods);
    }
}
