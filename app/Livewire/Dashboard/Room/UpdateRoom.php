<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\File;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomChildPolicy;
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

    public $children_policy = [];

    public $adult_age = 12;

    public function mount(): void
    {
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
        $this->loadChildrenPolicy();
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
            'adult_age' => 'required|integer|min:1|max:25',
            'children_policy' => 'nullable|array',
            'children_policy.*.ranges' => 'nullable|array',
            'children_policy.*.ranges.*.from_age' => 'required|integer|min:0|max:18',
            'children_policy.*.ranges.*.to_age' => 'required|integer|min:0|max:18|gte:children_policy.*.ranges.*.from_age',
            'children_policy.*.ranges.*.price_percentage' => 'required|numeric|min:0|max:100',
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
            'adult_age' => $this->adult_age,
        ]);

        // حفظ سياسات الأطفال
        $this->saveChildrenPolicies($this->room);

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

    public function delete($id): void
    {
        $file = File::find($id);
        if ($file) {
            FileService::delete($file->path);
            $file->delete();
            $this->room->refresh();
            flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.image')]));
        }
    }

    public function updatedChildrenCount(): void
    {
        $this->initializeChildrenPolicy();
    }

    public function loadChildrenPolicy(): void
    {
        $this->adult_age = $this->room->adult_age ?? 12;
        $this->children_policy = [];

        // تجميع السياسات حسب child_number
        $policies = $this->room->childrenPolicies->groupBy('child_number');

        for ($i = 1; $i <= $this->children_count; $i++) {
            $childPolicies = $policies->get($i, collect());

            if ($childPolicies->isEmpty()) {
                $this->children_policy[] = [
                    'ranges' => [
                        [
                            'from_age' => 0,
                            'to_age' => $this->adult_age - 1,
                            'price_percentage' => 0,
                        ]
                    ]
                ];
            } else {
                $ranges = [];
                foreach ($childPolicies as $policy) {
                    $ranges[] = [
                        'from_age' => $policy->from_age,
                        'to_age' => $policy->to_age,
                        'price_percentage' => $policy->price_percentage,
                    ];
                }
                $this->children_policy[] = ['ranges' => $ranges];
            }
        }
    }

    public function initializeChildrenPolicy(): void
    {
        $count = (int) $this->children_count;
        $this->children_policy = [];

        for ($i = 0; $i < $count; $i++) {
            $this->children_policy[] = [
                'ranges' => [
                    [
                        'from_age' => 0,
                        'to_age' => $this->adult_age - 1,
                        'price_percentage' => 0,
                    ]
                ]
            ];
        }
    }

    public function addAgeRange(int $childIndex): void
    {
        $this->children_policy[$childIndex]['ranges'][] = [
            'from_age' => 0,
            'to_age' => $this->adult_age - 1,
            'price_percentage' => 0,
        ];
    }

    public function removeAgeRange(int $childIndex, int $rangeIndex): void
    {
        if (count($this->children_policy[$childIndex]['ranges']) > 1) {
            unset($this->children_policy[$childIndex]['ranges'][$rangeIndex]);
            $this->children_policy[$childIndex]['ranges'] = array_values($this->children_policy[$childIndex]['ranges']);
        }
    }

    protected function saveChildrenPolicies(Room $room): void
    {
        // حذف السياسات القديمة وإضافة الجديدة
        $room->childrenPolicies()->delete();

        foreach ($this->children_policy as $childIndex => $child) {
            $childNumber = $childIndex + 1;

            // تطبيع وملء الفجوات في نطاقات العمر
            $normalizedRanges = $this->normalizeAndFillAgeRanges($child['ranges']);

            foreach ($normalizedRanges as $range) {
                $room->childrenPolicies()->create([
                    'child_number' => $childNumber,
                    'from_age' => (int) $range['from_age'],
                    'to_age' => (int) $range['to_age'],
                    'price_percentage' => (float) $range['price_percentage'],
                ]);
            }
        }
    }

    /**
     * تطبيع نطاقات العمر وملء الفجوات المفقودة بنسبة 100%
     */
    protected function normalizeAndFillAgeRanges(array $ranges): array
    {
        $maxAge = $this->adult_age - 1;

        usort($ranges, fn($a, $b) => $a['from_age'] <=> $b['from_age']);

        $normalizedRanges = [];
        $currentAge = 0;

        foreach ($ranges as $range) {
            $fromAge = (int) $range['from_age'];
            $toAge = (int) $range['to_age'];
            $percentage = (float) $range['price_percentage'];

            if ($fromAge > $currentAge) {
                $normalizedRanges[] = [
                    'from_age' => $currentAge,
                    'to_age' => $fromAge - 1,
                    'price_percentage' => 100,
                ];
            }

            $normalizedRanges[] = [
                'from_age' => max($fromAge, $currentAge),
                'to_age' => min($toAge, $maxAge),
                'price_percentage' => $percentage,
            ];

            $currentAge = $toAge + 1;
        }

        if ($currentAge <= $maxAge) {
            $normalizedRanges[] = [
                'from_age' => $currentAge,
                'to_age' => $maxAge,
                'price_percentage' => 100,
            ];
        }

        return $normalizedRanges;
    }
}
