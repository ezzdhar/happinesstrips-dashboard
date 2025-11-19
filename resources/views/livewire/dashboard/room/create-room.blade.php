@php use App\Enums\Status; @endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
	<x-card title="{{ __('lang.add_room') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveAdd">
			<div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-4 gap-3">
				<x-input required label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
				<x-input required label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>

				<x-choices-offline required label="{{ __('lang.hotel') }}" wire:model="hotel_id" :options="$hotels" single searchable clearable
				                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-building-office-2"/>

				<x-select required label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
					['id' => Status::Active, 'name' => __('lang.active')],
					['id' => Status::Inactive, 'name' => __('lang.inactive')],
				]"/>

				<x-input required label="{{ __('lang.adults_count') }}" wire:model="adults_count" type="number" min="1" placeholder="{{ __('lang.adults_count') }}" icon="o-users"/>
				<x-input required label="{{ __('lang.children_count') }}" wire:model="children_count" type="number" min="0" placeholder="{{ __('lang.children_count') }}" icon="o-user-group"/>
				<x-checkbox label="{{ __('lang.is_featured') }}" wire:model="is_featured"/>
			</div>

			<div class="mt-3">
				<x-choices-offline label="{{ __('lang.amenities') }}" wire:model="selected_amenities" :options="$amenities" searchable
				                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-sparkles" hint="{{__('lang.select').' '.__('lang.amenities')}}"/>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
				<x-trix required wire:model="includes_ar" label="{{ __('lang.includes').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
				<x-trix dir="ltr" required wire:model="includes_en" label="{{ __('lang.includes').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
			</div>

			{{-- Price Periods Section --}}
			<div class="mt-3">
				<div class="flex justify-between items-center mb-3">
					<h3 class="font-bold text-lg">{{ __('lang.price_periods') }}</h3>
					<x-button wire:click="addPricePeriod" icon="o-plus" class="btn-sm btn-primary" spinner="addPricePeriod">
						{{ __('lang.add_price_period') }}
					</x-button>
				</div>

				@if(empty($price_periods))
					<div class="alert alert-warning">
						<x-icon name="o-exclamation-triangle" class="w-5 h-5"/>
						<span>{{ __('lang.no_price_periods_added') }}</span>
					</div>
				@endif

				@foreach($price_periods as $index => $period)
					<div class="card bg-base-200 p-4 mb-3">
						<div class="flex justify-between items-center mb-3">
							<h4 class="font-semibold">{{ __('lang.price_period') }} #{{ $index + 1 }}</h4>
							<x-button wire:click="removePricePeriod({{ $index }})" icon="o-trash" class="btn-sm btn-error btn-circle"/>
						</div>

						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
							<x-input required label="{{ __('lang.start_date') }}" wire:model="price_periods.{{ $index }}.start_date" type="date" icon="o-calendar"/>
							<x-input required label="{{ __('lang.end_date') }}" wire:model="price_periods.{{ $index }}.end_date" type="date" icon="o-calendar"/>
							<x-input required label="{{ __('lang.adult_price_egp') }}" wire:model="price_periods.{{ $index }}.adult_price_egp" type="number" step="0.01" min="0" placeholder="0" icon="o-currency-dollar" hint="{{ __('lang.price_per_person_per_night') }}"/>
							<x-input required label="{{ __('lang.adult_price_usd') }}" wire:model="price_periods.{{ $index }}.adult_price_usd" type="number" step="0.01" min="0" placeholder="0" icon="o-currency-dollar" hint="{{ __('lang.price_per_person_per_night') }}"/>
						</div>
					</div>
				@endforeach
			</div>

			<div class="mt-3">
				<x-file required wire:model="images" label="{{__('lang.images')}}" accept="image/*" multiple/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="images"/>
			</div>

			<div class="mt-6 flex justify-end gap-2 px-4 pb-4">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('hotels')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="saveAdd,images" spinner="saveAdd"/>
			</div>
		</form>
	</x-card>
</div>
