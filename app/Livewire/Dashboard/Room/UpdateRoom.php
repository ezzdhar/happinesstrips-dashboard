<?php

namespace App\Livewire\Dashboard\Room;

use App\Enums\Status;
use App\Models\Amenity;
use App\Models\File;
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
    public $images = [];
    public $hotels = [];
    public $amenities = [];
    public $selected_amenities = [];
    public $children_policy = [];
    public $adult_age = 12;

    public $price_periods_egp = [];
    public $price_periods_usd = [];
    public $egp_gaps_warning = '';
    public $usd_gaps_warning = '';

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

        $this->hotels = Hotel::status(Status::Active)->get(['id', 'name'])->map(fn($h) => [
            'id' => $h->id,
            'name' => $h->name,
        ])->toArray();

        $this->amenities = Amenity::get(['id', 'name', 'icon'])->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'icon' => $a->icon,
        ])->toArray();

        $this->selected_amenities = $this->room->amenities()->pluck('amenities.id')->toArray();

        $this->loadPricePeriods();
        $this->loadChildrenPolicy();
        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    protected function loadPricePeriods(): void
    {
        // تحميل فترات الجنيه من الجدول
        $this->price_periods_egp = $this->room->pricePeriodsEgp()->get()->map(fn($p) => [
            'start_date' => $p->start_date->format('Y-m-d'),
            'end_date' => $p->end_date->format('Y-m-d'),
            'price' => $p->price,
        ])->toArray();

        // تحميل فترات الدولار من الجدول
        $this->price_periods_usd = $this->room->pricePeriodsUsd()->get()->map(fn($p) => [
            'start_date' => $p->start_date->format('Y-m-d'),
            'end_date' => $p->end_date->format('Y-m-d'),
            'price' => $p->price,
        ])->toArray();
    }

    public function breadcrumbs(): array
    {
        return [
            ['label' => __('lang.rooms'), 'icon' => 'ionicon.bed-outline'],
            ['label' => __('lang.update_room')],
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

            'price_periods_egp' => 'required|array|min:1',
            'price_periods_egp.*.start_date' => 'required|date',
            'price_periods_egp.*.end_date' => 'required|date|after_or_equal:price_periods_egp.*.start_date',
            'price_periods_egp.*.price' => 'required|numeric|min:0',

            'price_periods_usd' => 'required|array|min:1',
            'price_periods_usd.*.start_date' => 'required|date',
            'price_periods_usd.*.end_date' => 'required|date|after_or_equal:price_periods_usd.*.start_date',
            'price_periods_usd.*.price' => 'required|numeric|min:0',

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
        $this->checkPricePeriodsGaps();

        $this->room->update([
            'name' => ['ar' => $this->name_ar, 'en' => $this->name_en],
            'is_featured' => $this->is_featured,
            'discount_percentage' => $this->is_featured ? $this->discount_percentage : 0,
            'hotel_id' => $this->hotel_id,
            'adults_count' => $this->adults_count,
            'children_count' => $this->children_count,
            'status' => $this->status,
            'includes' => ['ar' => $this->includes_ar, 'en' => $this->includes_en],
            'adult_age' => $this->adult_age,
        ]);

        $this->savePricePeriods($this->room);
        $this->saveChildrenPolicies($this->room);
        $this->room->amenities()->sync($this->selected_amenities ?? []);

        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $this->room->files()->create(['path' => FileService::save($image, 'rooms')]);
            }
        }

        return to_route('rooms')->with('success', __('lang.updated_successfully', ['attribute' => __('lang.room')]));
    }

    protected function savePricePeriods(Room $room): void
    {
        $room->pricePeriods()->delete();

        foreach ($this->price_periods_egp as $period) {
            $room->pricePeriods()->create([
                'currency' => 'egp',
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'price' => $period['price'],
            ]);
        }

        foreach ($this->price_periods_usd as $period) {
            $room->pricePeriods()->create([
                'currency' => 'usd',
                'start_date' => $period['start_date'],
                'end_date' => $period['end_date'],
                'price' => $period['price'],
            ]);
        }
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
        if (empty($periods)) return '';
        usort($periods, fn($a, $b) => strtotime($a['start_date']) <=> strtotime($b['start_date']));

        $gaps = [];
        $previousEnd = null;

        foreach ($periods as $period) {
            if (empty($period['start_date']) || empty($period['end_date'])) continue;
            $startDate = strtotime($period['start_date']);
            $endDate = strtotime($period['end_date']);

            if ($previousEnd !== null) {
                $expectedStart = strtotime('+1 day', $previousEnd);
                if ($startDate > $expectedStart) {
                    $gaps[] = date('Y-m-d', $expectedStart) . ' - ' . date('Y-m-d', strtotime('-1 day', $startDate));
                }
            }
            $previousEnd = $endDate;
        }

        return empty($gaps) ? '' : __('lang.price_periods_gaps_warning') . ': ' . implode(', ', $gaps);
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

        $policies = $this->room->childrenPolicies->groupBy('child_number');

        for ($i = 1; $i <= $this->children_count; $i++) {
            $childPolicies = $policies->get($i, collect());

            if ($childPolicies->isEmpty()) {
                $this->children_policy[] = [
                    'ranges' => [['from_age' => 0, 'to_age' => $this->adult_age - 1, 'price_percentage' => 0]]
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
                'ranges' => [['from_age' => 0, 'to_age' => $this->adult_age - 1, 'price_percentage' => 0]]
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
        $room->childrenPolicies()->delete();

        foreach ($this->children_policy as $childIndex => $child) {
            $childNumber = $childIndex + 1;
            $ranges = $child['ranges'] ?? [];

            foreach ($ranges as $range) {
                $room->childrenPolicies()->create([
                    'child_number' => $childNumber,
                    'from_age' => (int) $range['from_age'],
                    'to_age' => (int) $range['to_age'],
                    'price_percentage' => (float) $range['price_percentage'],
                ]);
            }
        }
    }
}
