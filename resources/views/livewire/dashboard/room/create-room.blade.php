@php use App\Enums\Status; @endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
	<x-card title="{{ __('lang.add_room') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveAdd">
			<div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-3 gap-3">
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
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
				<x-trix required wire:model="includes_ar" label="{{ __('lang.includes').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
				<x-trix dir="ltr" required wire:model="includes_en" label="{{ __('lang.includes').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
			</div>

			<div class="mt-3">
				<x-choices-offline label="{{ __('lang.amenities') }}" wire:model="selected_amenities" :options="$amenities" searchable
				                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-sparkles" hint="{{__('lang.select').' '.__('lang.amenities')}}"/>
			</div>

			<div class="mt-3">
				<h3 class="font-bold text-lg mb-3">{{ __('lang.weekly_prices') }}</h3>
				<div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-4 gap-3">
				@foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
					<div class="card bg-base-200 p-3">
						<h4 class="font-semibold mb-2">{{ __('lang.'.$day) }}</h4>
							<x-input required label="{{__('lang.price_egp')}}" wire:model="weekly_prices.{{$day}}.price_egp" type="number" step="0.01" min="0" placeholder="0"/>
							<x-input required label="{{__('lang.price_usd')}}" wire:model="weekly_prices.{{$day}}.price_usd" type="number" step="0.01" min="0" placeholder="0"/>
					</div>
				@endforeach
				</div>
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
