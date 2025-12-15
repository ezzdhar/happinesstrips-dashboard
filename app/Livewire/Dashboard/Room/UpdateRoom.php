<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Amenity;
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

    public $is_featured;

    public $discount_percentage = 0;

    public $hotel_id;

    public $adults_count;

    public $children_count;

    public $includes_ar;

    public $includes_en;

    public $price_periods = [];

    public $images = [];

    public $hotels = [];

    public $amenities = [];

    public $selected_amenities = [];

    public function mount(): void
    {
        if (auth()->user()->hasRole('hotel')) {
            if ($this->room->hotel->user_id != auth()->id()) {
                abort(403);
            }
        }
        $this->name_ar = $this->room->getTranslation('name', 'ar');
        $this->name_en = $this->room->getTranslation('name', 'en');
        $this->is_featured = $this->room->is_featured;
        $this->discount_percentage = $this->room->discount_percentage ?? 0;
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

        $this->amenities = Amenity::get(['id', 'name', 'icon'])->map(function ($amenity) {
            return [
                'id' => $amenity->id,
                'name' => $amenity->name,
                'icon' => $amenity->icon,
            ];
        })->toArray();

        $this->selected_amenities = $this->room->amenities()->pluck('amenities.id')->toArray();

        $this->price_periods = $this->room->price_periods ?? [];
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

    public function rules(): array
    {
        return [
            'is_featured' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'hotel_id' => 'required|exists:hotels,id',
            'adults_count' => 'required|integer|min:1',
            'children_count' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'includes_ar' => 'nullable|string',
            'includes_en' => 'nullable|string',
            'price_periods' => 'required|array|min:1',
            'price_periods.*.start_date' => 'required|date',
            'price_periods.*.end_date' => 'required|date|after:price_periods.*.start_date',
            'price_periods.*.adult_price_egp' => 'required|numeric|min:0',
            'price_periods.*.adult_price_usd' => 'required|numeric|min:0',
            'images.*' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
            'selected_amenities' => 'nullable|array',
            'selected_amenities.*' => 'exists:amenities,id',
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
            'is_featured' => $this->is_featured,
            'discount_percentage' => $this->is_featured ? $this->discount_percentage : 0,
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
        $this->room->amenities()->sync($this->selected_amenities ?? []);

        // Save new images if uploaded
        if (! empty($this->images)) {
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
