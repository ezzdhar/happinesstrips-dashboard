<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomChildPolicy;
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

    public $is_featured = false;

    public $discount_percentage = 0;

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

    // سياسة كل طفل: مصفوفة من نطاقات العمر
    public $children_policy = [];

    public $adult_age = 12;

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
            'is_featured' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'price_periods' => 'required|array|min:1',
            'price_periods.*.start_date' => 'required|date',
            'price_periods.*.end_date' => 'required|date|after:price_periods.*.start_date',
            'price_periods.*.adult_price_egp' => 'required|numeric|min:0',
            'price_periods.*.adult_price_usd' => 'required|numeric|min:0',
            'images.*' => 'required|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
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

    public function saveAdd()
    {
        $this->validate();
        $room = Room::create([
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'hotel_id' => $this->hotel_id,
            'is_featured' => $this->is_featured,
            'discount_percentage' => $this->is_featured ? $this->discount_percentage : 0,
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
        $this->saveChildrenPolicies($room);

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

    public function updatedChildrenCount(): void
    {
        $this->initializeChildrenPolicy();
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
     * - ترتيب النطاقات حسب from_age
     * - ملء أي فجوات بين النطاقات بنسبة 100%
     * - إضافة نطاق حتى adult_age - 1 إذا لم يكن موجوداً
     */
    protected function normalizeAndFillAgeRanges(array $ranges): array
    {
        $maxAge = $this->adult_age - 1;

        // ترتيب النطاقات حسب from_age
        usort($ranges, fn($a, $b) => $a['from_age'] <=> $b['from_age']);

        $normalizedRanges = [];
        $currentAge = 0;

        foreach ($ranges as $range) {
            $fromAge = (int) $range['from_age'];
            $toAge = (int) $range['to_age'];
            $percentage = (float) $range['price_percentage'];

            // إذا كان هناك فجوة قبل هذا النطاق، نملأها بنسبة 100%
            if ($fromAge > $currentAge) {
                $normalizedRanges[] = [
                    'from_age' => $currentAge,
                    'to_age' => $fromAge - 1,
                    'price_percentage' => 100,
                ];
            }

            // إضافة النطاق الحالي (مع ضمان أن يبدأ من العمر الصحيح)
            $normalizedRanges[] = [
                'from_age' => max($fromAge, $currentAge),
                'to_age' => min($toAge, $maxAge),
                'price_percentage' => $percentage,
            ];

            $currentAge = $toAge + 1;
        }

        // إذا لم تغطِ النطاقات حتى adult_age - 1، نضيف نطاقاً بنسبة 100%
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
