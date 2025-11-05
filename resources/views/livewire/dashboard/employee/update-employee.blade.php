@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-button icon="o-pencil" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true" tooltip="{{__('lang.update')}}" wire:click="resetError"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update')}}" box-class="modal-box-600">
		<x-form wire:submit="saveUpdate">
			<div class="text-center mb-3 mx-auto">
				<x-avatar :image="FileService::get($employee->image)" class="w-20 h-20"/>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<x-input required label="{{__('lang.name')}}" wire:model="name"/>
			<x-input  label="{{__('lang.email')}}" type="email" wire:model="email"/>

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
				<x-select required label="{{__('lang.status')}}" wire:model="status" :options="[
						['id' => null,'name' => __('lang.select')],
						['id' => Status::Active,'name' => __('lang.active')],
						['id' => Status::Inactive,'name' => __('lang.inactive')]]"/>
				<x-input  label="{{__('lang.password')}}" type="password" wire:model="password"/>
				<x-input  label="{{__('lang.password_confirmation')}}" type="password" wire:model="password_confirmation"/>
			</div>
			<x-choices allow-all clearable label="{{__('lang.roles')}}" wire:model="selected_roles" :options="$roles" >

			</x-choices>
			<x-slot:actions>
				<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false"/>
				<x-button label="{{__('lang.update')}}" class="btn btn-primary" wire:loading.attr="disabled" type="submit" spinner="saveUpdate"/>
			</x-slot:actions>
		</x-form>
	</x-modal>
</div>