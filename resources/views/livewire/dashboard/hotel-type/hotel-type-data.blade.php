<div>
	<x-card title="{{ __('lang.hotel_types') }}" shadow class="mb-3">
		<x-slot:menu>
			<livewire:dashboard.hotel-type.create-hotel-type wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.hotel-type.create-hotel-type>
		</x-slot:menu>
		<div class="grid grid-cols-1 mb-3">
			<x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search') }}" icon="o-magnifying-glass" clearable/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($hotel_types as $hotel_type)
						<tr class="bg-base-200">
							<th class="text-center">{{$hotel_types->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">{{$hotel_type->name}}</th>
							<th class="text-center text-nowrap">{{formatDate($hotel_type->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									<livewire:dashboard.hotel-type.update-hotel-type :hotelType="$hotel_type" :key="\Illuminate\Support\Str::random(10)"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$hotel_type->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$hotel_type->id}})" spinner="deleteSweetAlert({{$hotel_type->id}})" tooltip="{{__('lang.delete')}}"/>
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="4" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $hotel_types->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>
