<div>
	<x-button label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" @click="$wire.modalAdd = true" wire:loading.attr="disabled"/>
	<x-modal wire:model="modalAdd" title="{{__('lang.add').' '.__('lang.city')}}" class="backdrop-blur" persistent>
		<div class="grid grid-cols-1 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
		</div>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false;$wire.resetData()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.save')}}" class="btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
		</x-slot:actions>
	</x-modal>
</div>

