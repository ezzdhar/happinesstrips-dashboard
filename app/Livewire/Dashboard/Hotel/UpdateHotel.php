<?php

namespace App\Livewire\Dashboard\Hotel;

use App\Enums\Status;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

#[Title('update_hotels')]
class UpdateHotel extends Component
{
	use WithFileUploads, WithMediaSync;

	public bool $modalUpdate = false;

	public Hotel $hotel;

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

	public $description_ar;

	public $description_en;

	public $address_ar;

	public $address_en;

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

		$this->user_id = $this->hotel->user_id;
		$this->city_id = $this->hotel->city_id;
		$this->email = $this->hotel->email;
		$this->name_ar = $this->hotel->getTranslation('name', 'ar');
		$this->name_en = $this->hotel->getTranslation('name', 'en');
		$this->status = $this->hotel->status->value;
		$this->rating = $this->hotel->rating;
		$this->phone_key = $this->hotel->phone_key;
		$this->phone = $this->hotel->phone;
		$this->latitude = $this->hotel->latitude;
		$this->longitude = $this->hotel->longitude;
		$this->description_ar = $this->hotel->getTranslation('description', 'ar');
		$this->description_en = $this->hotel->getTranslation('description', 'en');
		$this->address_ar = $this->hotel->getTranslation('address', 'ar');
		$this->address_en = $this->hotel->getTranslation('address', 'en');
		$this->facilities_ar = $this->hotel->getTranslation('facilities', 'ar');
		$this->facilities_en = $this->hotel->getTranslation('facilities', 'en');
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
				'label' => __('lang.update_hotel'),
			],
		];
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
			'description_ar' => 'nullable|string',
			'description_en' => 'nullable|string',
			'address_ar' => 'required|string',
			'address_en' => 'required|string',
			'facilities_ar' => 'required|string',
			'facilities_en' => 'required|string',
			'images.*' => 'nullable|image|max:5000|mimes:jpg,jpeg,png,gif,webp,svg',
		];
	}

	public function saveUpdate()
	{
		$this->validate();

		$this->hotel->update([
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

		// Save new images
		if ($this->images) {
			foreach ($this->images as $image) {
				$this->hotel->files()->create([
					'path' => FileService::save($image, 'hotels'),
				]);
			}
		}
		return to_route('hotels')->with('success', __('lang.updated_successfully', ['attribute' => __('lang.hotel')]));
	}

	public function render(): View
	{
		return view('livewire.dashboard.hotel.update-hotel');
	}

	public function resetError(): void
	{
		$this->resetErrorBag();
		$this->resetValidation();
	}
}

