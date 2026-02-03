<?php

namespace App\Livewire\Dashboard\Trip;

use App\Enums\TripType;
use App\Models\City;
use App\Models\Hotel;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Trip;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

#[Title('add_trip')]
class CreateTrip extends Component
{
    use WithFileUploads, WithMediaSync;

    public $main_category_id;

    public $sub_category_id;

    public $name_ar;

    public $name_en;

    public $price_egp;

    public $price_usd;

    public $duration_from;

    public $duration_to;

    public $nights_count;

    public $people_count = 1;

    public $notes_ar;

    public $notes_en;

    public $program_ar;

    public $program_en;

    public $is_featured = false;

    public $discount_percentage = 0;

    public $type;

    public $status;

    public $first_child_price_percentage = 0;

    public $second_child_price_percentage = 0;

    public $third_child_price_percentage = 0;

    public $additional_child_price_percentage = 0;

    public $free_child_age = 4;

    public $adult_age = 12;

    public $selected_hotels = [];

    public $images = [];

    public $main_categories = [];

    public $sub_categories = [];

    public $hotels = [];

    public $cities = [];

    public $city_id;

    public function mount(): void
    {
        $this->main_categories = MainCategory::get(['id', 'name'])->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        })->toArray();
        $this->cities = City::get(['id', 'name'])->map(function ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
            ];
        })->toArray();
        $this->hotels = Hotel::get(['id', 'name'])->map(function ($hotel) {
            return [
                'id' => $hotel->id,
                'name' => $hotel->name,
            ];
        })->toArray();

        view()->share('breadcrumbs', $this->breadcrumbs());
    }

    public function breadcrumbs(): array
    {
        return [
            [
                'label' => __('lang.trips'),
                'icon' => 'o-globe-alt',
                'link' => route('trips'),
            ],
            [
                'label' => __('lang.add_trip'),
            ],
        ];
    }

    public function updatedMainCategoryId($value): void
    {
        if ($value) {
            $this->sub_categories = SubCategory::where('main_category_id', $value)
                ->get(['id', 'name'])
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                    ];
                })->toArray();
            $this->sub_category_id = null;
        } else {
            $this->sub_categories = [];
            $this->sub_category_id = null;
        }
    }

    // calculate nights count
    public function updatedDurationFrom(): void
    {
        if ($this->duration_to && $this->duration_from && $this->type == TripType::Fixed) {
            $from = Carbon::parse($this->duration_from);
            $to = Carbon::parse($this->duration_to);
            $this->nights_count = $from->diffInDays($to);
        }
    }

    public function updatedDurationTo(): void
    {
        $this->updatedDurationFrom();
    }

    public function render()
    {
        return view('livewire.dashboard.trip.create-trip');
    }

    public function rules(): array
    {
        return [
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'price_egp' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'city_id' => 'required|exists:cities,id',
            'duration_from' => 'required|date|after_or_equal:to_day',
            'duration_to' => 'nullable|required_if:type,fixed|date|after_or_equal:duration_from',
            'nights_count' => 'nullable|integer|min:1',
            'first_child_price_percentage' => 'required|numeric|min:0|max:100',
            'second_child_price_percentage' => 'required|numeric|min:0|max:100',
            'third_child_price_percentage' => 'required|numeric|min:0|max:100',
            'additional_child_price_percentage' => 'required|numeric|min:0|max:100',
            'free_child_age' => 'required|integer|min:0|max:18',
            'adult_age' => 'required|integer|min:1|max:25|gt:free_child_age',
            'notes_ar' => 'required|string',
            'notes_en' => 'required|string',
            'program_ar' => 'required|string',
            'program_en' => 'required|string',
            'is_featured' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'type' => 'required|in:fixed,flexible',
            'status' => 'required|in:active,inactive,start,end',
            'selected_hotels' => 'nullable|array',
            'selected_hotels.*' => 'exists:hotels,id',
            'images.*' => 'required|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
        ];
    }

    public function saveAdd(): void
    {
        $this->validate();

        $trip = Trip::create([
            'main_category_id' => $this->main_category_id,
            'sub_category_id' => $this->sub_category_id,
            'name' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'price' => [
                'egp' => $this->price_egp,
                'usd' => $this->price_usd,
            ],
            'duration_from' => $this->duration_from,
            'duration_to' => $this->duration_to,
            'city_id' => $this->city_id,
            'nights_count' => $this->nights_count,
            'people_count' => 1, // $this->people_count
            'notes' => [
                'ar' => $this->notes_ar,
                'en' => $this->notes_en,
            ],
            'program' => [
                'ar' => $this->program_ar,
                'en' => $this->program_en,
            ],
            'is_featured' => $this->is_featured,
            'discount_percentage' => $this->is_featured ? $this->discount_percentage : 0,
            'type' => $this->type,
            'status' => $this->status,
            'first_child_price_percentage' => $this->first_child_price_percentage,
            'second_child_price_percentage' => $this->second_child_price_percentage,
            'third_child_price_percentage' => $this->third_child_price_percentage,
            'additional_child_price_percentage' => $this->additional_child_price_percentage,
            'free_child_age' => $this->free_child_age,
            'adult_age' => $this->adult_age,
        ]);

        // Attach hotels to trip
        if ($this->selected_hotels) {
            $trip->hotels()->sync($this->selected_hotels);
        }

        // Save images
        if ($this->images) {
            foreach ($this->images as $image) {
                $trip->files()->create([
                    'path' => FileService::save($image, 'trips'),
                ]);
            }
        }

        flash()->success(__('lang.added_successfully', ['attribute' => __('lang.trip')]));
        $this->redirectIntended(default: route('trips'));
    }
}
