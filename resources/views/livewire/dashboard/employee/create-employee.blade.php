<div>
	<x-button icon="o-plus" class="btn-primary btn-sm mt-2 md:mt-0" label="{{__('lang.add')}}" @click="$wire.modalAdd = true" wire:click="resetData"/>

	{{--modalAdd--}}
	<x-modal wire:model="modalAdd" title="{{__('lang.add')}}" box-class="modal-box-700">
		<x-form wire:submit="saveAdd">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<x-input required label="{{__('lang.name')}}" wire:model="name"/>
				<x-input label="{{__('lang.email')}}" type="email" wire:model="email"/>

				<x-phone-input
					required
					label="{{__('lang.phone')}}"
					phoneProperty="phone"
					keyProperty="phone_key"
				/>
				<div>
					<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
					<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
				</div>
				<x-input required label="{{__('lang.password')}}" type="password" wire:model="password"/>
				<x-input required label="{{__('lang.password_confirmation')}}" type="password" wire:model="password_confirmation"/>
			</div>
			<x-choices clearable  label="{{__('lang.roles')}}" wire:model="selected_roles" :options="$get_roles" allow-all />
			<x-slot:actions>
				<x-button label="{{__('lang.cancel')}}" @click="$wire.modalAdd = false"/>
				<x-button label="{{__('lang.save')}}" class="btn btn-primary" wire:loading.attr="disabled" type="submit" spinner="saveAdd"/>
			</x-slot:actions>
		</x-form>
	</x-modal>
</div>