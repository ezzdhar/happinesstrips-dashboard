@php use App\Enums\Status; @endphp
<div>
	<x-button label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" @click="$wire.modalAdd = true" wire:loading.attr="disabled"/>
	<x-modal wire:model="modalAdd" title="{{__('lang.add').' '.__('lang.main_category')}}" class="backdrop-blur" persistent>
		<div class="grid grid-cols-1 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
			<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>
			<div>
				<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
			</div>
		</div>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false;$wire.resetData()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.save')}}" class="btn-primary" wire:click="saveAdd" wire:loading.attr="disabled" wire:target="saveAdd" spinner="saveAdd"/>
		</x-slot:actions>
	</x-modal>
</div>