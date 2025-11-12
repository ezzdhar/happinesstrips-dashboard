<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('update_room')]
class UpdateRoom extends Component
{
    use WithFileUploads;

    public Room $room;

    public $name_ar;

    public $name_en;

    public $status;

    public $hotel_id;

    public $adults_count;

    public $children_count;

    public $includes_ar;

    public $includes_en;

    public $weekly_prices = [];

    public $images = [];

    public $hotels = [];

    public function mount(): void
    {
	    if (auth()->user()->hasRole('hotel')) {
		    if ($this->room->hotel->user_id != auth()->id()) {
			    abort(403);
		    }
	    }
        $this->name_ar = $this->room->getTranslation('name', 'ar');
        $this->name_en = $this->room->getTranslation('name', 'en');
        $this->status = $this->room->status->value;
        $this->hotel_id = $this->room->hotel_id;
        $this->adults_count = $this->room->adults_count;
        $this->children_count = $this->room->children_count;
        $this->includes_ar = $this->room->getTranslation('includes', 'ar');
        $this->includes_en = $this->room->getTranslation('includes', 'en');
	    $this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(function ($hotel) {
		    return [
			    'id' => $hotel->id,
			    'name' => $hotel->name,
		    ];
	    })->toArray();
		$this->loadWeeklyPrices();
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
				'label' => __('lang.update_room'),
			],
		];
	}
    public function loadWeeklyPrices(): void
    {
        $existingPrices = $this->room->weekly_prices ?? [];
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Convert array to keyed format
        $pricesMap = [];
        if (is_array($existingPrices)) {
            foreach ($existingPrices as $price) {
                if (isset($price['day_of_week'])) {
                    $pricesMap[$price['day_of_week']] = $price;
                }
            }
        }

        // Initialize all days
        foreach ($days as $day) {
            $this->weekly_prices[$day] = [
                'day_of_week' => $day,
                'price_egp' => $pricesMap[$day]['price_egp'] ?? 0,
                'price_usd' => $pricesMap[$day]['price_usd'] ?? 0,
            ];
        }
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
            'includes_ar' => 'nullable|string',
            'includes_en' => 'nullable|string',
            'weekly_prices.*.price_egp' => 'required|numeric|min:0',
            'weekly_prices.*.price_usd' => 'required|numeric|min:0',
            'images.*' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

	public function saveUpdate()
    {
        $this->validate();

        $this->room->update([
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

        // Save new images if uploaded
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $this->room->files()->create([
                    'path' => FileService::save($image, 'rooms'),
                ]);
            }
        }

	    return to_route('rooms')->with('success', __('lang.updated_successfully', ['attribute' => __('lang.room')]));

    }

    public function deleteImage($imageId): void
    {
        $file = $this->room->files()->findOrFail($imageId);
        FileService::delete($file->path);
        $file->delete();
        $this->room->refresh();
    }

    public function render(): View
    {
        return view('livewire.dashboard.room.update-room');
    }

    public function resetError(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
}

