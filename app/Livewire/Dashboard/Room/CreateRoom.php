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

    public $is_featured = false;

    public $discount_percentage = 0;

    public $hotel_id;

    public $adults_count = 1;

    public $children_count = 0;

    public $includes_ar;

    public $includes_en;

    public $images = [];

    public $hotels = [];

    public $amenities = [];

    public $selected_amenities = [];

    public $children_policy = [];

    public $adult_age = 12;

    // فترات الأسعار المنفصلة لكل عملة
    public $price_periods_egp = [];

    public $price_periods_usd = [];

    // تحذيرات الفجوات
    public $egp_gaps_warning = '';

    public $usd_gaps_warning = '';

    // حماية من الـ double-click
    public $isProcessing = false;

    // Clone mode
    public ?Room $sourceRoom = null;

    public bool $isCloneMode = false;

    public function mount(?Room $room = null): void
    {
        $this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(fn ($h) => [
            'id' => $h->id,
            'name' => $h->name,
        ])->toArray();

        $this->amenities = Amenity::get(['id', 'name', 'icon'])->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'icon' => $a->icon,
        ])->toArray();

        // If cloning from an existing room
        if ($room) {
            $this->isCloneMode = true;
            $this->sourceRoom = $room;
            $this->loadFromSourceRoom($room);
        }

        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    protected function loadFromSourceRoom(Room $room): void
    {
        // Basic info - add "نسخة من" prefix to name
        $this->name_ar = __('lang.copy_of').' '.$room->getTranslation('name', 'ar');
        $this->name_en = 'Copy of '.$room->getTranslation('name', 'en');
        $this->hotel_id = $room->hotel_id;
        $this->status = $room->status->value;
        $this->is_featured = $room->is_featured;
        $this->discount_percentage = $room->discount_percentage ?? 0;
        $this->adults_count = $room->adults_count;
        $this->children_count = $room->children_count;
        $this->includes_ar = $room->getTranslation('includes', 'ar');
        $this->includes_en = $room->getTranslation('includes', 'en');
        $this->adult_age = $room->adult_age ?? 12;

        // Selected amenities
        $this->selected_amenities = $room->amenities->pluck('id')->toArray();

        // Price periods EGP
        $this->price_periods_egp = $room->pricePeriodsEgp()->get()->map(fn ($p) => [
            'start_date' => $p->start_date->format('Y-m-d'),
            'end_date' => $p->end_date->format('Y-m-d'),
            'price' => $p->price,
        ])->toArray();

        // Price periods USD
        $this->price_periods_usd = $room->pricePeriodsUsd()->get()->map(fn ($p) => [
            'start_date' => $p->start_date->format('Y-m-d'),
            'end_date' => $p->end_date->format('Y-m-d'),
            'price' => $p->price,
        ])->toArray();

        // Children policy - Load all at once to avoid N+1
        $this->children_policy = [];
        $allPolicies = $room->childrenPolicies()->get()->groupBy('child_number');

        for ($i = 0; $i < $this->children_count; $i++) {
            $childPolicies = $allPolicies->get($i + 1, collect());
            $this->children_policy[$i] = [
                'ranges' => $childPolicies->map(fn ($p) => [
                    'from_age' => $p->from_age,
                    'to_age' => $p->to_age,
                    'price_percentage' => $p->price_percentage,
                ])->toArray() ?: [['from_age' => 0, 'to_age' => 11, 'price_percentage' => 0]],
            ];
        }
    }

    public function breadcrumbs(): array
    {
        return [
            ['label' => __('lang.rooms'), 'icon' => 'ionicon.bed-outline'],
            ['label' => __('lang.add_room')],
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
            'includes_ar' => 'nullable|string',
            'includes_en' => 'nullable|string',
            'is_featured' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',

            'price_periods_egp' => 'required|array|min:1',
            'price_periods_egp.*.start_date' => 'required|date',
            'price_periods_egp.*.end_date' => 'required|date|after_or_equal:price_periods_egp.*.start_date',
            'price_periods_egp.*.price' => 'required|numeric|min:0',

            'price_periods_usd' => 'required|array|min:1',
            'price_periods_usd.*.start_date' => 'required|date',
            'price_periods_usd.*.end_date' => 'required|date|after_or_equal:price_periods_usd.*.start_date',
            'price_periods_usd.*.price' => 'required|numeric|min:0',

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
        // منع الـ double-click
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        try {
            $this->validate();
            $this->checkPricePeriodsGaps();

            $room = \DB::transaction(function () {
                $room = Room::create([
                    'name' => ['ar' => $this->name_ar, 'en' => $this->name_en],
                    'hotel_id' => $this->hotel_id,
                    'is_featured' => $this->is_featured,
                    'discount_percentage' => $this->is_featured ? $this->discount_percentage : 0,
                    'adults_count' => $this->adults_count,
                    'children_count' => $this->children_count,
                    'status' => $this->status,
                    'includes' => ['ar' => $this->includes_ar, 'en' => $this->includes_en],
                    'adult_age' => $this->adult_age,
                ]);

                // حفظ فترات الأسعار في الجدول
                $this->savePricePeriods($room);

                // حفظ سياسات الأطفال
                $this->saveChildrenPolicies($room);

                if (! empty($this->selected_amenities)) {
                    $room->amenities()->sync($this->selected_amenities);
                }

                if (! empty($this->images)) {
                    foreach ($this->images as $image) {
                        $room->files()->create(['path' => FileService::save($image, 'rooms')]);
                    }
                }

                return $room;
            });

            return to_route('rooms')->with('success', __('lang.added_successfully', ['attribute' => __('lang.room')]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->isProcessing = false;
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    flash()->error($message);
                }
            }
        }
    }

    protected function savePricePeriods(Room $room): void
    {
        // حذف الفترات القديمة
        $room->pricePeriods()->delete();

        // حفظ فترات الجنيه
        foreach ($this->price_periods_egp as $period) {
            $room->pricePeriods()->create([
                'currency' => 'egp',
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'price' => $period['price'],
            ]);
        }

        // حفظ فترات الدولار
        foreach ($this->price_periods_usd as $period) {
            $room->pricePeriods()->create([
                'currency' => 'usd',
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'price' => $period['price'],
            ]);
        }
    }

    public function resetData(): void
    {
        $this->reset(['name_ar', 'name_en', 'status', 'hotel_id', 'adults_count', 'children_count', 'includes_ar', 'includes_en', 'price_periods_egp', 'price_periods_usd', 'images']);
        $this->adults_count = 1;
        $this->children_count = 0;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function addPricePeriodEgp(): void
    {
        $this->price_periods_egp[] = ['start_date' => '', 'end_date' => '', 'price' => 0];
    }

    public function removePricePeriodEgp($index): void
    {
        unset($this->price_periods_egp[$index]);
        $this->price_periods_egp = array_values($this->price_periods_egp);
    }

    public function addPricePeriodUsd(): void
    {
        $this->price_periods_usd[] = ['start_date' => '', 'end_date' => '', 'price' => 0];
    }

    public function removePricePeriodUsd($index): void
    {
        unset($this->price_periods_usd[$index]);
        $this->price_periods_usd = array_values($this->price_periods_usd);
    }

    public function checkPricePeriodsGaps(): void
    {
        $this->egp_gaps_warning = $this->detectGaps($this->price_periods_egp);
        $this->usd_gaps_warning = $this->detectGaps($this->price_periods_usd);
    }

    protected function detectGaps(array $periods): string
    {
        if (empty($periods)) {
            return '';
        }

        usort($periods, fn ($a, $b) => strtotime($a['start_date']) <=> strtotime($b['start_date']));

        $gaps = [];
        $previousEnd = null;

        foreach ($periods as $period) {
            if (empty($period['start_date']) || empty($period['end_date'])) {
                continue;
            }

            $startDate = strtotime($period['start_date']);
            $endDate = strtotime($period['end_date']);

            if ($previousEnd !== null) {
                $expectedStart = strtotime('+1 day', $previousEnd);
                if ($startDate > $expectedStart) {
                    $gaps[] = date('Y-m-d', $expectedStart).' - '.date('Y-m-d', strtotime('-1 day', $startDate));
                }
            }
            $previousEnd = $endDate;
        }

        return empty($gaps) ? '' : __('lang.price_periods_gaps_warning').': '.implode(', ', $gaps);
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
                'ranges' => [['from_age' => 0, 'to_age' => $this->adult_age - 1, 'price_percentage' => 0]],
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
        // حذف السياسات القديمة أولاً (في حالة الـ clone mode)
        $room->childrenPolicies()->delete();

        if (empty($this->children_policy)) {
            return;
        }

        foreach ($this->children_policy as $childIndex => $child) {
            $childNumber = $childIndex + 1;
            $ranges = $child['ranges'] ?? [];

            if (empty($ranges)) {
                continue;
            }

            foreach ($ranges as $range) {
                // استخدام updateOrCreate لتجنب الـ duplicate entries
                $room->childrenPolicies()->updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'child_number' => $childNumber,
                        'from_age' => (int) $range['from_age'],
                    ],
                    [
                        'to_age' => (int) $range['to_age'],
                        'price_percentage' => (float) $range['price_percentage'],
                    ]
                );
            }
        }
    }
}
