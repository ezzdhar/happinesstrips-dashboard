@php use App\Enums\Status;use App\Enums\TripType; @endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
	<x-card title="{{ __('lang.add_trip') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveAdd">
			{{-- Basic Information Section --}}
			<div class="border-b py-3">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-information-circle" class="w-5 h-5 inline"/> {{ __('lang.trip_information') }}
				</h3>
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
					<x-choices-offline required label="{{ __('lang.main_category') }}" wire:model.live="main_category_id" :options="$main_categories" single clearable searchable
					                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
					<x-choices-offline required label="{{ __('lang.sub_category') }}" wire:model="sub_category_id" :options="$sub_categories" single clearable searchable
					                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
					<x-choices-offline required label="{{ __('lang.city') }}" wire:model="city_id" :options="$cities" single clearable searchable
					                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
					<x-input required label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
					<x-input required label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" dir="ltr" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
					<x-select required label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
							['id' => Status::Active, 'name' => __('lang.active')],
							['id' => Status::Inactive, 'name' => __('lang.inactive')],
							['id' => Status::Start, 'name' => __('lang.start')],
							['id' => Status::End, 'name' => __('lang.end')],
						]"/>
					<x-select required label="{{ __('lang.trip_type') }}" wire:model.live="type" placeholder="{{ __('lang.select') }}" icon="o-bookmark" :options="[
							['id' => TripType::Fixed, 'name' => __('lang.fixed')],
							['id' => TripType::Flexible, 'name' => __('lang.flexible')],
						]"/>1
					<x-checkbox label="{{ __('lang.is_featured') }}" wire:model.live="is_featured"/>
					@if($is_featured)
						<x-input type="number" step="0.01" min="0" max="100" label="{{ __('lang.discount_percentage') }}" wire:model="discount_percentage" placeholder="0.00" suffix="%" icon="o-tag" />
					@endif
				</div>
			</div>

			{{-- Price Information Section --}}
			<div class="border-b py-4">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-currency-dollar" class="w-5 h-5 inline"/> {{ __('lang.price_information') }}
				</h3>
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
					<x-input required type="number" step="0.01" label="{{ __('lang.price_egp') }}" wire:model="price_egp" placeholder="0.00" icon="o-banknotes"
					         hint="{{ $type === TripType::Flexible ? __('lang.for_one_person_per_night') : __('lang.for_one_person_per_trip') }}"/>
					<x-input required type="number" step="0.01" label="{{ __('lang.price_usd') }}" wire:model="price_usd" placeholder="0.00" icon="o-banknotes"
					         hint="{{ $type === TripType::Flexible ? __('lang.for_one_person_per_night') : __('lang.for_one_person_per_trip') }}"/>
					<x-input required type="date" label="{{ __('lang.duration_from') }}" wire:model.live="duration_from" icon="o-calendar"/>
					@if($type === TripType::Fixed)
						<x-input type="date" label="{{ __('lang.duration_to') }}" wire:model.live="duration_to" icon="o-calendar"/>
						<x-input type="number" label="{{ __('lang.nights_count') }}" wire:model="nights_count" placeholder="0" icon="o-moon"/>
					@endif
				</div>
			</div>

			{{-- Children Policy Section --}}
			<div class="border-b py-4">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-user-group" class="w-5 h-5 inline"/> {{ __('lang.children_policy') }}
				</h3>

				{{-- Age Policy --}}
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
					<x-input required label="{{ __('lang.free_child_age') }}" wire:model="free_child_age" type="number" min="0" max="18" placeholder="4" icon="o-user" hint="{{ __('lang.children_under_this_age_free') }}"/>
					<x-input required label="{{ __('lang.adult_age') }}" wire:model="adult_age" type="number" min="1" max="25" placeholder="12" icon="o-user-circle" hint="{{ __('lang.age_considered_adult') }}"/>
				</div>

				{{-- Price Percentages --}}
				<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
					<x-input suffix="%" required label="{{ __('lang.first_child_percentage') }}" wire:model="first_child_price_percentage" type="number" step="0.01" min="0" max="100" placeholder="0" icon="o-percent-badge"
					         hint="{{ __('lang.percentage_of_adult_price') }}"/>
					<x-input suffix="%" required label="{{ __('lang.second_child_percentage') }}" wire:model="second_child_price_percentage" type="number" step="0.01" min="0" max="100" placeholder="0" icon="o-percent-badge"
					         hint="{{ __('lang.percentage_of_adult_price') }}"/>
					<x-input suffix="%" required label="{{ __('lang.third_child_percentage') }}" wire:model="third_child_price_percentage" type="number" step="0.01" min="0" max="100" placeholder="0" icon="o-percent-badge"
					         hint="{{ __('lang.percentage_of_adult_price') }}"/>
					<x-input suffix="%" required label="{{ __('lang.additional_child_percentage') }}" wire:model="additional_child_price_percentage" type="number" step="0.01" min="0" max="100" placeholder="0" icon="o-percent-badge"
					         hint="{{ __('lang.percentage_of_adult_price') }}"/>
				</div>
			</div>

			{{-- Hotels Section --}}
			<div class="border-b py-4">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-building-office-2" class="w-5 h-5 inline"/> {{ __('lang.trip_hotels') }}
				</h3>
				<div class="grid grid-cols-1 gap-4">
					<x-choices-offline label="{{ __('lang.select_hotels') }}" wire:model="selected_hotels" :options="$hotels" searchable
					                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}"/>
				</div>
			</div>

			{{-- Notes Section --}}
			<div class="overflow-auto border-b py-3">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-document-text" class="w-5 h-5 inline"/> {{ __('lang.notes') }}
				</h3>
				<div class="overflow-auto grid grid-cols-1 md:grid-cols-2 gap-4">
					<x-trix wire:model="notes_ar" label="{{ __('lang.notes').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
					<x-trix wire:model="notes_en" dir="ltr" label="{{ __('lang.notes').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
				</div>
			</div>

			{{-- Program Section --}}
			<div class="overflow-auto border-b py-3">
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-clipboard-document-list" class="w-5 h-5 inline"/> {{ __('lang.program') }}
				</h3>
				<div class="overflow-auto grid grid-cols-1 md:grid-cols-2 gap-4">
					<x-trix wire:model="program_ar" label="{{ __('lang.program').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
					<x-trix wire:model="program_en" dir="ltr" label="{{ __('lang.program').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
				</div>
			</div>

			{{-- Images Section --}}
			<div>
				<h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">
					<x-icon name="o-photo" class="w-5 h-5 inline"/> {{ __('lang.images') }}
				</h3>
				<x-image-library wire:model="images" wire:library="library" :preview="$library" label="{{__('lang.images')}}"/>
			</div>

			<div class="mt-6 flex justify-end gap-2 px-4 py-3">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('trips')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
			</div>
		</form>
	</x-card>
</div>

