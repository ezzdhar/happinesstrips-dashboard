@php use App\Enums\Status;use App\Services\FileService; @endphp
@assets()
<link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
<script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
@endassets()
<div>
	<x-card title="{{ __('lang.add_room') }}" shadow class="mb-3">
		<form wire:submit.prevent="saveUpdate">

		<div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-3 gap-3">
			<x-input label="{{ __('lang.name').' ('.__('lang.ar').')' }}" wire:model="name_ar" placeholder="{{ __('lang.name').' ('.__('lang.ar').')' }}" icon="o-language"/>
			<x-input label="{{ __('lang.name').' ('.__('lang.en').')' }}" wire:model="name_en" placeholder="{{ __('lang.name').' ('.__('lang.en').')' }}" icon="o-language"/>

			<x-choices-offline label="{{ __('lang.hotel') }}" wire:model="hotel_id" :options="$hotels" single searchable clearable
			                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-building-office-2"/>

			<x-select label="{{ __('lang.status') }}" wire:model="status" placeholder="{{ __('lang.select') }}" icon="o-flag" :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>

			<x-input label="{{ __('lang.adults_count') }}" wire:model="adults_count" type="number" min="1" placeholder="{{ __('lang.adults_count') }}" icon="o-users"/>
			<x-input label="{{ __('lang.children_count') }}" wire:model="children_count" type="number" min="0" placeholder="{{ __('lang.children_count') }}" icon="o-user-group"/>
		</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
				<x-trix required wire:model="includes_ar" label="{{ __('lang.includes').' ('.__('lang.ar').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
				<x-trix dir="ltr" required wire:model="includes_en" label="{{ __('lang.includes').' ('.__('lang.en').')' }}" key="{{\Illuminate\Support\Str::random(20)}}"></x-trix>
			</div>

			<div class="mt-3">
				<x-choices-offline label="{{ __('lang.amenities') }}" wire:model="selected_amenities" :options="$amenities" searchable
				                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-sparkles" hint="{{__('lang.select').' '.__('lang.amenities')}}"/>
			</div>

		<div class="mt-4">
			<h3 class="font-bold text-lg mb-3">{{ __('lang.weekly_prices') }}</h3>
			<div class="grid grid-cols-1 sm-only:grid-cols-2 md:grid-cols-4 gap-3">
				@foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
					<div class="card bg-base-200 p-3">
						<h4 class="font-semibold mb-2">{{ __('lang.'.$day) }}</h4>
						<div class="grid grid-cols-2 gap-2">
							<x-input label="{{__('lang.price_egp')}}" wire:model="weekly_prices.{{$day}}.price_egp" type="number" step="0.01" min="0" placeholder="0"/>
							<x-input label="{{__('lang.price_usd')}}" wire:model="weekly_prices.{{$day}}.price_usd" type="number" step="0.01" min="0" placeholder="0"/>
						</div>
					</div>
				@endforeach
			</div>
		</div>

		@if($room->files->isNotEmpty())
			<div class="mt-4">
				<h4 class="font-semibold mb-2">{{ __('lang.current_images') }}</h4>
				<div class="grid grid-cols-2 md:grid-cols-4 gap-2">
					@foreach($room->files as $file)
						<div class="relative">
							<img src="{{FileService::get($file->path)}}" alt="Room image" class="w-full h-32 object-cover rounded">
							<button type="button" wire:click="deleteImage({{$file->id}})" class="absolute top-1 right-1 btn btn-xs btn-error">
								<x-icon name="o-trash" class="w-4 h-4"/>
							</button>
						</div>
					@endforeach
				</div>
			</div>
		@endif

		<div class="mt-3">
			<x-file wire:model="images" label="{{__('lang.add_more_images')}}" accept="image/*" multiple/>
			<x-progress class="progress-primary h-0.5" indeterminate wire:loading wire:target="images"/>
		</div>

			<div class="mt-6 flex justify-end gap-2 px-4 pb-4">
				<x-button label="{{__('lang.cancel')}}" @click="window.location='{{route('hotels')}}'" wire:loading.attr="disabled"/>
				<x-button label="{{__('lang.save')}}" class="btn-primary" type="submit" wire:loading.attr="disabled" wire:target="saveAdd,images" spinner="saveAdd"/>
			</div>
		</form>
	</x-card>
</div>
