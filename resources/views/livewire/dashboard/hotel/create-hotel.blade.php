@php use App\Enums\Status; @endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
	<x-card title="{{ __('lang.add_hotel') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveAdd">
			<div class="max-h-[75vh] overflow-y-auto p-4 space-y-6">

				{{-- Basic Information Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-information-circle" class="w-5 h-5 inline"/> {{ __('lang.basic_information') }}
					</h3>
					<div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
						<x-select label="{{ __('lang.owner') }}" wire:model="user_id" placeholder="{{ __('lang.select') }}" icon="o-user" :options="$users" option-label="name"/>
						<x-choices-offline label="{{ __('lang.city') }}" wire:model="city_id" :options="$cities" single clearable searchable
						                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
						<x-input label="{{ __('lang.email') }}" wire:model="email" placeholder="{{ __('lang.email') }}" icon="o-envelope"/>
						<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
						<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
						<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
							['id' => Status::Active, 'name' => __('lang.active')],
							['id' => Status::Inactive, 'name' => __('lang.inactive')],
						]"/>
						<x-select label="{{ __('lang.rating') }}" wire:model="rating" placeholder="{{ __('lang.select') }}" icon="o-star" :options="[
							['id' => 1, 'name' => '1'],
							['id' => 2, 'name' => '2'],
							['id' => 3, 'name' => '3'],
							['id' => 4, 'name' => '4'],
							['id' => 5, 'name' => '5'],
						]"/>
						<x-phone-input
								required
								label="{{__('lang.phone')}}"
								phoneProperty="phone"
								keyProperty="phone_key"
						/>
					</div>
				</div>

				{{-- Location & Map Section --}}
				<div class="border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-map-pin" class="w-5 h-5 inline"/> {{ __('lang.location_information') }}
					</h3>

					{{-- استخدام الـ Google Map Component --}}
					<x-google-map :latitude="$latitude" :longitude="$longitude" :address-ar="$address_ar" :address-en="$address_en" latitude-property="latitude" longitude-property="longitude"
					              address-ar-property="address_ar" address-en-property="address_en" height="500px" map-id="map" search-input-id="pac-input"/>
				</div>

				{{-- Facilities Section --}}
				<div class="overflow-auto border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-building-office" class="w-5 h-5 inline"/> {{ __('lang.facilities') }}
					</h3>
					<div class="overflow-auto grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-trix wire:model="facilities_ar" label="{{ __('lang.facilities').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
						<x-trix wire:model="facilities_en" label="{{ __('lang.facilities').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
					</div>
				</div>

				{{-- Services Section --}}
				<div class="overflow-auto border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-sparkles" class="w-5 h-5 inline"/> {{ __('lang.include_services') }}
					</h3>
					<div class="overflow-auto grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-trix wire:model="include_services_ar" label="{{ __('lang.include_services').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
						<x-trix wire:model="include_services_en" label="{{ __('lang.include_services').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
					</div>
				</div>

				{{-- Description Section --}}
				<div class="overflow-auto border-b pb-4">
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-document-text" class="w-5 h-5 inline"/> {{ __('lang.description') }}
					</h3>
					<div class="overflow-auto grid grid-cols-1 md:grid-cols-2 gap-4">
						<x-trix wire:model="description_ar" label="{{ __('lang.description').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
						<x-trix wire:model="description_en" label="{{ __('lang.description').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
					</div>
				</div>

				{{-- Images Section --}}
				<div>
					<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
						<x-icon name="o-photo" class="w-5 h-5 inline"/> {{ __('lang.images') }}
					</h3>
					<x-image-library wire:model="images" wire:library="library" :preview="$library" label="{{__('lang.project_images')}}"/>
				</div>

			</div>


			<div class="mt-6 flex justify-end gap-2 px-4 pb-4">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('hotels')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
			</div>
		</form>
	</x-card>
</div>