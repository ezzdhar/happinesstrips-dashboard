@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-button icon="o-pencil" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true" wire:loading.attr="disabled" tooltip="{{__('lang.edit')}}"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update').' '.__('lang.sub_category')}}" class="backdrop-blur" persistent>
		<div class="grid grid-cols-1 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
			<x-choices-offline label="{{ __('lang.main_category') }}" wire:model="main_category_id" :options="$main_categories" single searchable
			                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-rectangle-stack"/>
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
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false;$wire.resetError()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.update')}}" class="btn-primary" wire:click="saveUpdate" wire:loading.attr="disabled" wire:target="saveUpdate" spinner="saveUpdate"/>
		</x-slot:actions>
	</x-modal>
</div>

