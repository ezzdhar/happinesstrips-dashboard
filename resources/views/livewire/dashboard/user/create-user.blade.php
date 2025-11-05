<div>
	<x-button icon="o-plus" class="btn-primary btn-sm mt-2 md:mt-0" label="{{__('lang.add')}}" @click="$wire.modalAdd = true" wire:click="resetData"/>
	{{--modalAdd--}}
	<x-modal wire:model="modalAdd" title="{{__('lang.add')}}" box-class="modal-box-600">
		<x-form wire:submit="saveAdd">
			<x-input label="{{__('lang.name')}}" wire:model="name"/>
			<x-input label="{{__('lang.email')}}" type="email" wire:model="email"/>
			<x-input label="{{__('lang.password')}}" type="password" wire:model="password"/>
			<x-input label="{{__('lang.password_confirmation')}}" type="password" wire:model="password_confirmation"/>
			<div>
				<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
			</div>
			<x-slot:actions>
				<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false"/>
				<x-button label="{{__('lang.save')}}" class="btn btn-primary" wire:loading.attr="disabled" type="submit" spinner="saveAdd"/>
			</x-slot:actions>
		</x-form>
	</x-modal>
</div>
