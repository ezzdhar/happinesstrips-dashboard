@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.rooms') }}" shadow class="mb-3">
		<x-slot:menu>
				<livewire:dashboard.room.create-room wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.room.create-room>
		</x-slot:menu>
		<div class="grid grid-cols-2 sm-only:grid-cols-3 md:grid-cols-3 gap-4 mb-6">
			<x-stat title="{{ __('lang.rooms') }}" value="{{ $rooms->total() }}" icon="fas.list" color="text-primary"/>
			<x-stat title="{{ __('lang.rooms_active') }}" value="{{ $rooms_active }}" icon="fas.check-circle" color="text-green-500"/>
			<x-stat title="{{ __('lang.rooms_inactive') }}" value="{{ $rooms_inactive }}" icon="fas.times-circle" color="text-red-500"/>
		</div>
		<div class="grid grid-cols-1 sm-only:grid-cols-3 md:grid-cols-3 gap-3 mb-3">
			<x-input label="{{ __('lang.search') }}" wire:model.live.debounce="search" placeholder="{{ __('lang.search') }}" icon="o-magnifying-glass" clearable/>
			<x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}" icon="o-flag" clearable :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>
			<x-choices-offline label="{{ __('lang.hotel') }}" wire:model.live="hotel_id_filter" :options="$hotels" single searchable clearable
			                   option-value="id" option-label="name" placeholder="{{ __('lang.select') }}" icon="o-building-office-2"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.hotel')}}</th>
						<th class="text-center">{{__('lang.adults_count')}}</th>
						<th class="text-center">{{__('lang.children_count')}}</th>
						<th class="text-center">{{__('lang.status')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($rooms as $room)
						<tr class="bg-base-200">
							<th class="text-center">{{$rooms->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">{{$room->name}}</th>
							<th class="text-center text-nowrap">{{$room->hotel->name}}</th>
							<th class="text-center text-nowrap">{{$room->adults_count}}</th>
							<th class="text-center text-nowrap">{{$room->children_count}}</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$room->status->title()" class="bg-{{$room->status->color()}}"/>
							</th>
							<th class="text-center text-nowrap">{{formatDate($room->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									<livewire:dashboard.room.update-room :room="$room" :key="\Illuminate\Support\Str::random(10)"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$room->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$room->id}})" spinner="deleteSweetAlert({{$room->id}})" tooltip="{{__('lang.delete')}}"/>
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="8" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $rooms->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>

