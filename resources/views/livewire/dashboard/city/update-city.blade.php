@php use App\Services\FileService; @endphp
<div>
	<x-button icon="o-pencil" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true" wire:loading.attr="disabled" tooltip="{{__('lang.edit')}}"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update').' '.__('lang.city')}}" class="backdrop-blur" persistent>
		<div class="mb-4 m-auto">
			<img src="{{$image ? $image->temporaryUrl() : FileService::get($city->image)}}" alt="{{__('lang.image')}}" class="w-32 h-32 object-cover rounded-md mx-auto"/>
		</div>
		<div class="grid grid-cols-1 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
			<div>
				<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
			</div>
		</div>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false;$wire.resetError()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.update')}}" class="btn-primary" wire:click="saveUpdate" wire:loading.attr="disabled" spinner="saveUpdate"/>
		</x-slot:actions>
	</x-modal>
</div>

