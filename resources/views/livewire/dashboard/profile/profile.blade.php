@php use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.personal_info') }}" shadow separator class="mb-3">
		<div class="mb-3">
			@if($image)
				<img src="{{$image->temporaryUrl()}}" alt="img" class="!rounded-lg !w-20" style="width: 150px;margin: auto">
			@else
				<img src="{{FileService::get(auth()->user()->image)}}" alt="img" class="!rounded-lg !w-20 " style="width: 150px;margin: auto">
			@endif
		</div>
		<form wire:submit.prevent="updateProfile" class="flex flex-col gap-4">
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
				<x-input type="email" label="{{__('lang.email')}}" value="{{auth()->user()->email}}" readonly disabled/>
				<x-input label="{{__('lang.username')}}" value="{{auth()->user()->username}}" readonly disabled/>
				<x-input label="{{__('lang.name')}}" wire:model="name"/>
				<div>
					<x-file wire:model="image" label="{{__('lang.image')}}" accept="image/*"/>
					<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="image"/>
				</div>

			</div>
			<div class="text-center">
				<x-button class="btn btn-primary" variant="primary" type="submit" spinner="updateProfile" wire:loading.attr="disabled">{{ __('lang.save') }}</x-button>
			</div>
		</form>
	</x-card>

	<x-card title="{{ __('lang.password') }}" shadow separator class="mb-3">
		<form wire:submit.prevent="updatePassword" class="flex flex-col gap-4">
			<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
				<x-input type="password" label="{{__('lang.old_password')}}" wire:model="old_password"/>
				<x-input type="password" label="{{__('lang.new_password')}}" wire:model="password"/>
				<x-input type="password" label="{{__('lang.confirm_password')}}" wire:model="password_confirmation"/>
			</div>
			<div class="text-center">
				<x-button class="btn btn-primary" variant="primary" type="submit" spinner="updatePassword" wire:loading.attr="disabled">{{ __('lang.save') }}</x-button>
			</div>
		</form>
	</x-card>
</div>