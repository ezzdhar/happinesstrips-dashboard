@php use App\Enums\Status; @endphp
<div>
	<x-button label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" @click="$wire.modalAdd = true" wire:loading.attr="disabled"/>
	<x-modal wire:model="modalAdd" title="{{__('lang.add').' '.__('lang.room')}}" box-class="modal-box-700">
		<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
			<x-input required label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input required label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>

			<x-choices-offline label="{{ __('lang.hotel') }}" wire:model="hotel_id" :options="$hotels" single searchable clearable
			                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-building-office-2"/>

			<x-select required label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>

			<x-input required label="{{ __('lang.adults_count') }}" wire:model="adults_count" type="number" min="1" placeholder="{{ __('lang.adults_count') }}" icon="o-users"/>
			<x-input required label="{{ __('lang.children_count') }}" wire:model="children_count" type="number" min="0" placeholder="{{ __('lang.children_count') }}" icon="o-user-group"/>
		</div>

		<div class="grid grid-cols-1 gap-3 mt-3">
			<x-textarea required label="{{ __('lang.includes').' ('.__('lang.ar').')' }}" wire:model="includes_ar" placeholder="{{ __('lang.includes').' ('.__('lang.ar').')' }}" rows="3"/>
			<x-textarea required label="{{ __('lang.includes').' ('.__('lang.en').')' }}" wire:model="includes_en" placeholder="{{ __('lang.includes').' ('.__('lang.en').')' }}" rows="3"/>
		</div>

		<div class="mt-4">
			<h3 class="font-bold text-lg mb-3">{{ __('lang.weekly_prices') }}</h3>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
				@foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
					<div class="card bg-base-200 p-3">
						<h4 class="font-semibold mb-2">{{ __('lang.'.$day) }}</h4>
						<div class="grid grid-cols-2 gap-2">
							<x-input required label="{{__('lang.price_egp')}}" wire:model="weekly_prices.{{$day}}.price_egp" type="number" step="0.01" min="0" placeholder="0"/>
							<x-input required label="{{__('lang.price_usd')}}" wire:model="weekly_prices.{{$day}}.price_usd" type="number" step="0.01" min="0" placeholder="0"/>
						</div>
					</div>
				@endforeach
			</div>
		</div>

		<div class="mt-3">
			<x-file wire:model="images" label="{{__('lang.images')}}" accept="image/*" multiple/>
			<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="images"/>
		</div>

		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false;$wire.resetData()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.save')}}" class="btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
		</x-slot:actions>
	</x-modal>
</div>

