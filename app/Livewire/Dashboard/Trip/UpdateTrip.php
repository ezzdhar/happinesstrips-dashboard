<?php

namespace App\Livewire\Dashboard\Trip;

use App\Enums\TripType;
use App\Models\City;
use App\Models\File;
use App\Models\Hotel;
use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\Trip;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

#[Title('update_trip')]
class UpdateTrip extends Component
{
	use WithFileUploads, WithMediaSync;

	public Trip $trip;

	public $main_category_id;

	public $sub_category_id;

	public $name_ar;

	public $name_en;

	public $price_egp;

	public $price_usd;

	public $duration_from;

	public $duration_to;

	public $nights_count;

	public $people_count;

	public $notes_ar;

	public $notes_en;

	public $program_ar;

	public $program_en;

	public $is_featured;

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

	#[Rule('nullable')]
	public Collection $library;

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

		$this->library = collect();

		// Load existing data
		$this->main_category_id = $this->trip->main_category_id;
		$this->sub_category_id = $this->trip->sub_category_id;
		$this->name_ar = $this->trip->getTranslation('name', 'ar');
		$this->name_en = $this->trip->getTranslation('name', 'en');
		$this->price_egp = $this->trip->price['egp'] ?? 0;
		$this->price_usd = $this->trip->price['usd'] ?? 0;
		$this->duration_from = $this->trip->duration_from?->format('Y-m-d');
		$this->duration_to = $this->trip->duration_to?->format('Y-m-d');
		$this->nights_count = $this->trip->nights_count;
		$this->people_count = $this->trip->people_count;
		$this->city_id = $this->trip->city_id;
		$this->notes_ar = $this->trip->getTranslation('notes', 'ar');
		$this->notes_en = $this->trip->getTranslation('notes', 'en');
		$this->program_ar = $this->trip->getTranslation('program', 'ar');
		$this->program_en = $this->trip->getTranslation('program', 'en');
		$this->is_featured = $this->trip->is_featured;
		$this->discount_percentage = $this->trip->discount_percentage ?? 0;
		$this->type = $this->trip->type->value;
		$this->status = $this->trip->status->value;
		$this->first_child_price_percentage = $this->trip->first_child_price_percentage;
		$this->second_child_price_percentage = $this->trip->second_child_price_percentage;
		$this->third_child_price_percentage = $this->trip->third_child_price_percentage;
		$this->additional_child_price_percentage = $this->trip->additional_child_price_percentage;
		$this->free_child_age = $this->trip->free_child_age;
		$this->adult_age = $this->trip->adult_age;
		$this->selected_hotels = $this->trip->hotels->pluck('id')->toArray();

		// Load sub categories
		if ($this->main_category_id) {
			$this->sub_categories = SubCategory::where('main_category_id', $this->main_category_id)
				->get(['id', 'name'])
				->map(function ($category) {
					return [
						'id' => $category->id,
						'name' => $category->name,
					];
				})->toArray();
		}

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
				'label' => __('lang.update_trip'),
			],
		];
	}

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
		if ($this->duration_to && $this->duration_from && $this->type == TripType::Fixed) {
			$from = Carbon::parse($this->duration_from);
			$to = Carbon::parse($this->duration_to);
			$this->nights_count = $from->diffInDays($to);
		}
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
			'adult_age' => 'required|integer|min:1|max:25|gt:free_child_age', 'notes_ar' => 'required|string',
			'notes_en' => 'required|string',
			'program_ar' => 'required|string',
			'program_en' => 'required|string',
			'is_featured' => 'boolean',
			'discount_percentage' => 'nullable|numeric|min:0|max:100',
			'type' => 'required|in:fixed,flexible',
			'status' => 'required|in:active,inactive,start,end',
			'selected_hotels' => 'nullable|array',
			'selected_hotels.*' => 'exists:hotels,id',
			'images.*' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
		];
	}

	public function saveUpdate(): void
	{
		$this->validate();

		$this->trip->update([
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
			'nights_count' => $this->nights_count,
			'city_id' => $this->city_id,
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

		// Sync hotels
		if ($this->selected_hotels) {
			$this->trip->hotels()->sync($this->selected_hotels);
		} else {
			$this->trip->hotels()->detach();
		}

		// Save new images
		if ($this->images) {
			foreach ($this->images as $image) {
				$this->trip->files()->create([
					'path' => FileService::save($image, 'trips'),
				]);
			}
		}

		flash()->success(__('lang.updated_successfully', ['attribute' => __('lang.trip')]));
		$this->redirectIntended(default: route('trips'));
	}

	public function render(): View
	{
		return view('livewire.dashboard.trip.update-trip');
	}

	public function resetError(): void
	{
		$this->resetErrorBag();
		$this->resetValidation();
	}


	public function delete($id): void
	{
		$file = File::find($id);
		if ($file) {
			FileService::delete($file->path);
			$file->delete();
			$this->trip->refresh();
			flash()->success(__('lang.deleted_successfully', ['attribute' => __('lang.image')]));
		}
	}
}
