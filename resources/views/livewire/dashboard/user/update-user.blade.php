@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-button icon="o-pencil" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true" tooltip="{{__('lang.update')}}" wire:click="resetError"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update')}}" box-class="modal-box-600">
		<x-form wire:submit="saveUpdate">
			<div class="text-center mb-3 mx-auto">
				<x-avatar :image="FileService::get($user->image)" class="w-20 h-20"/>
			</div>
			<x-input label="{{__('lang.name')}}" wire:model="name"/>
			<x-input label="{{__('lang.email')}}" wire:model="email" type="email"/>
			<x-input label="{{__('lang.username')}}" wire:model="username"/>
			<x-input label="{{__('lang.password')}}" wire:model="password" type="password"/>
			<x-input label="{{__('lang.password_confirmation')}}" wire:model="password_confirmation" type="password"/>
			<div>
				<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
			</div>
			<x-slot:actions>
				<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false"/>
				<x-button label="{{__('lang.update')}}" class="btn btn-primary" wire:loading.attr="disabled" type="submit" spinner="saveUpdate"/>
			</x-slot:actions>
		</x-form>
	</x-modal>
</div>