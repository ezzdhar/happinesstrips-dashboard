@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-button icon="o-pencil" class="btn-sm btn-ghost" @click="$wire.modalUpdate = true" wire:loading.attr="disabled" tooltip="{{__('lang.edit')}}"/>
	<x-modal wire:model="modalUpdate" title="{{__('lang.update').' '.__('lang.hotel')}}" class="backdrop-blur modal-box-850" persistent>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[70vh] overflow-y-auto">
			<x-select label="{{ __('lang.user') }}" wire:model="user_id" placeholder="{{ __('lang.select') }}" icon="o-user" :options="$users" option-label="name"/>
			<x-choices-offline label="{{ __('lang.city') }}" wire:model="city_id" :options="$cities" single clearable searchable
			                   option-value="id" option-label="name" />
			<x-input label="{{ __('lang.email') }}" wire:model="email" placeholder="{{ __('lang.email') }}" icon="o-envelope" class="col-span-1 md:col-span-2"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>
			<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>
			<x-select label="{{ __('lang.rating') }}" wire:model="rating" placeholder="{{ __('lang.select') }}" icon="o-star" :options="[
				['id' => 1, 'name' => '1'],
				['id' => 2, 'name' => '2'],
				['id' => 3, 'name' => '3'],
				['id' => 4, 'name' => '4'],
				['id' => 5, 'name' => '5'],
			]"/>
			<x-input label="{{ __('lang.phone_key') }}" wire:model="phone_key" placeholder="{{ __('lang.phone_key') }}" icon="o-phone"/>
			<x-input label="{{ __('lang.phone') }}" wire:model="phone" placeholder="{{ __('lang.phone') }}" icon="o-phone"/>
			<x-input label="{{ __('lang.latitude') }}" wire:model="latitude" placeholder="{{ __('lang.latitude') }}" icon="o-map-pin"/>
			<x-input label="{{ __('lang.longitude') }}" wire:model="longitude" placeholder="{{ __('lang.longitude') }}" icon="o-map-pin"/>
			<x-textarea label="{{ __('lang.address').' ('.__('lang.ar').')' }}" wire:model="address_ar" placeholder="{{ __('lang.address').' ('.__('lang.ar').')' }}" rows="2"/>
			<x-textarea label="{{ __('lang.address').' ('.__('lang.en').')' }}" wire:model="address_en" placeholder="{{ __('lang.address').' ('.__('lang.en').')' }}" rows="2"/>
			<x-textarea label="{{ __('lang.facilities').' ('.__('lang.ar').')' }}" wire:model="facilities_ar" placeholder="{{ __('lang.facilities').' ('.__('lang.ar').')' }}" rows="2"/>
			<x-textarea label="{{ __('lang.facilities').' ('.__('lang.en').')' }}" wire:model="facilities_en" placeholder="{{ __('lang.facilities').' ('.__('lang.en').')' }}" rows="2"/>
			<x-textarea label="{{ __('lang.description').' ('.__('lang.ar').')' }}" wire:model="description_ar" placeholder="{{ __('lang.description').' ('.__('lang.ar').')' }}" rows="2"/>
			<x-textarea label="{{ __('lang.description').' ('.__('lang.en').')' }}" wire:model="description_en" placeholder="{{ __('lang.description').' ('.__('lang.en').')' }}" rows="2"/>
			<div class="col-span-1 md:col-span-2">
				<div class="mb-2">
					<label class="block text-sm font-medium mb-1">{{__('lang.current_images')}}</label>
					<div class="flex gap-2 flex-wrap">
						@foreach($hotel->files as $file)
{{--							<img src="{{$file->path ? FileService::get($file->path) : null}}" alt="" class="w-20 h-20 object-cover rounded">--}}
						@endforeach
					</div>
				</div>
				<x-file wire:model="images" label="{{__('lang.add_more_images')}}" accept="image/*" multiple/>
				<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="images"/>
			</div>
		</div>
		<x-slot:actions>
			<x-button label="{{__('lang.cancel')}}" @click="$wire.modalUpdate = false;$wire.resetError()" wire:loading.attr="disabled"/>
			<x-button label="{{__('lang.update')}}" class="btn-primary" wire:click="saveUpdate" wire:loading.attr="disabled" wire:target="saveUpdate" spinner="saveUpdate"/>
		</x-slot:actions>
	</x-modal>
</div>

