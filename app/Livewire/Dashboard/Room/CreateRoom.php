<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\FileService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateRoom extends Component
{
    use WithFileUploads;

    public bool $modalAdd = false;

    public $name_ar;

    public $name_en;

    public $status;

    public $hotel_id;

    public $adults_count = 1;

    public $children_count = 0;

    public $includes_ar;

    public $includes_en;

    public $weekly_prices = [];

    public $images = [];

    public $hotels = [];

    public function mount(): void
    {
        $this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(function ($hotel) {
	        return [
		        'id' => $hotel->id,
		        'name' => $hotel->name,
	        ];
        })->toArray();
        $this->initializeWeeklyPrices();
    }

    public function initializeWeeklyPrices(): void
    {
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        foreach ($days as $day) {
            $this->weekly_prices[$day] = [
                'day_of_week' => $day,
                'price_egp' => 0,
                'price_usd' => 0,
            ];
        }
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
            'weekly_prices.*.price_egp' => 'required|numeric|min:0',
            'weekly_prices.*.price_usd' => 'required|numeric|min:0',
            'images.*' => 'required|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveAdd(): void
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
            'weekly_prices' => array_values($this->weekly_prices),
        ]);

        // Save images
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $room->files()->create([
                    'path' => FileService::save($image, 'rooms'),
                ]);
            }
        }

        $this->modalAdd = false;
        $this->dispatch('render')->component(RoomData::class);
        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.room')]));
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'status', 'hotel_id', 'adults_count', 'children_count', 'includes_ar', 'includes_en', 'images']);
        $this->adults_count = 1;
        $this->children_count = 0;
        $this->initializeWeeklyPrices();
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

