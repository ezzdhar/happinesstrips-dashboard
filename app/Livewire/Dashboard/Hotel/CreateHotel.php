<?php

namespace App\Livewire\Dashboard\Hotel;

use App\Models\City;
use App\Models\Hotel;
use App\Models\HotelType;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;

#[Title('add_hotel')]
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

    public $description_ar;

    public $description_en;

    public $address_ar;

    public $address_en;

    public $facilities_ar;

    public $facilities_en;

    public $images = [];

	public $cities = [];
	public $hotel_types = [];
	public $hotel_type_ids = [];

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
	    $this->hotel_types = HotelType::get(['id', 'name'])->map(function ($type) {
		    return [
			    'id' => $type->id,
			    'name' => $type->name,
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
	        'hotel_type_ids' => 'required|array|min:1',
	        'hotel_type_ids.*' => 'exists:hotel_types,id',
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

    public function saveAdd()
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
	    if ($this->hotel_type_ids) {
		    $hotel->hotelTypes()->attach($this->hotel_type_ids);
	    }


        return to_route('hotels')->with('success', __('lang.added_successfully', ['attribute' => __('lang.hotel')]));
    }

    public function resetData(): void
    {
        $this->reset([
            'user_id', 'city_id', 'email', 'name_ar', 'name_en', 'status', 'rating',
            'phone_key', 'phone', 'latitude', 'longitude', 'description_ar', 'description_en', 'address_ar',
            'address_en', 'facilities_ar', 'facilities_en', 'images',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }
}
