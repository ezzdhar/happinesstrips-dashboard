<?php

namespace App\Livewire\Dashboard\Hotel;

use App\Enums\Status;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

class CreateHotel extends Component
{
	use WithFileUploads, WithMediaSync;

	public bool $modalAdd = false;

	public $user_id;

	public $city_id;

	public $email;

	public $name_ar;

	public $name_en;

	public $status;

	public $rating;

	public $phone_key;

	public $phone;

	public $latitude;

	public $longitude;

	public $include_services_ar;

	public $include_services_en;

	public $description_ar;

	public $description_en;

	public $address_ar;

	public $address_en;

	public $address; // For temporary address from map

	public $facilities_ar;

	public $facilities_en;

	public $images = [];

	public $cities = [];

	public $users = [];

	#[Rule('required')]
	public Collection $library;
	public function mount(): void
	{
		$this->cities = City::get(['id', 'name'])->map(function ($city) {
			return [
				'id' => $city->id,
				'name' => $city->name,
			];
		})->toArray();
		$this->library = collect();
		$this->users = User::role('hotel')->get(['id', 'name'])->toArray();
		view()->share('breadcrumbs', $this->breadcrumbs());

	}

	public function breadcrumbs(): array
	{
		return [
			[
				'label' => __('lang.hotels'),
				'icon' => 'o-building-office-2',
			],
			[
				'label' => __('lang.add_hotel'),
			],
		];
	}


	public function render()
	{
		return view('livewire.dashboard.hotel.create-hotel');
	}

	public function rules(): array
	{
		return [
			'user_id' => 'required|exists:users,id',
			'city_id' => 'required|exists:cities,id',
			'email' => 'required|email|max:255',
			'name_ar' => 'required|string|max:255',
			'name_en' => 'required|string|max:255',
			'status' => 'required|in:active,inactive',
			'rating' => 'required|in:1,2,3,4,5',
			'phone_key' => 'nullable|string|max:10',
			'phone' => 'nullable|string|max:20',
			'latitude' => 'nullable|numeric',
			'longitude' => 'nullable|numeric',
			'include_services_ar' => 'nullable|string',
			'include_services_en' => 'nullable|string',
			'description_ar' => 'nullable|string',
			'description_en' => 'nullable|string',
			'address_ar' => 'required|string',
			'address_en' => 'required|string',
			'facilities_ar' => 'required|string',
			'facilities_en' => 'required|string',
			'images.*' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
		];
	}

	public function saveAdd(): void
	{
		$this->validate();

		$hotel = Hotel::create([
			'user_id' => $this->user_id,
			'city_id' => $this->city_id,
			'email' => $this->email,
			'name' => [
				'ar' => $this->name_ar,
				'en' => $this->name_en,
			],
			'status' => $this->status,
			'rating' => $this->rating,
			'phone_key' => $this->phone_key,
			'phone' => $this->phone,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'include_services' => [
				'ar' => $this->include_services_ar,
				'en' => $this->include_services_en,
			],
			'description' => [
				'ar' => $this->description_ar,
				'en' => $this->description_en,
			],
			'address' => [
				'ar' => $this->address_ar,
				'en' => $this->address_en,
			],
			'facilities' => [
				'ar' => $this->facilities_ar,
				'en' => $this->facilities_en,
			],
		]);

		// Save images
		if ($this->images) {
			foreach ($this->images as $image) {
				$hotel->files()->create([
					'path' => FileService::save($image, 'hotels'),
				]);
			}
		}

		$this->modalAdd = false;
		$this->dispatch('render')->component(HotelData::class);
		flash()->success(__('lang.added_successfully', ['attribute' => __('lang.hotel')]));
	}

	public function resetData(): void
	{
		$this->reset([
			'user_id', 'city_id', 'email', 'name_ar', 'name_en', 'status', 'rating',
			'phone_key', 'phone', 'latitude', 'longitude', 'include_services_ar',
			'include_services_en', 'description_ar', 'description_en', 'address_ar',
			'address_en', 'facilities_ar', 'facilities_en', 'images'
		]);
		$this->resetErrorBag();
		$this->resetValidation();
	}
}

