<div>
	<x-button label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" @click="$wire.modalAdd = true" wire:loading.attr="disabled"/>
	<x-modal wire:model="modalAdd" title="{{__('lang.add').' '.__('lang.amenity')}}" class="backdrop-blur" persistent>
		<div class="grid grid-cols-1 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
			<x-icon-select 
			label="{{ __('lang.icon') }}" 
			wire:model.live="icon" 
			:value="$icon"
			placeholder="{{ __('lang.select') }} {{ __('lang.icon') }}"
			hint="{{ __('lang.icon_hint') }}"
			required
		/>
		
		@if($icon)
			<div class="flex items-center gap-3 p-4 bg-base-200 rounded-lg border-2 border-primary/20">
				<div class="flex items-center justify-center w-12 h-12 bg-primary/10 rounded-lg">
					<i class="{{ $icon }} text-primary" style="font-size: 1.5rem;"></i>
				</div>
				<div>
					<div class="text-sm font-semibold text-base-content">{{ __('lang.preview') }}</div>
					<div class="text-xs text-base-content/70 font-mono">{{ $icon }}</div>
				</div>
			</div>
		@endif
		</div>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false;$wire.resetData()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.save')}}" class="btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
		</x-slot:actions>
	</x-modal>
</div>
