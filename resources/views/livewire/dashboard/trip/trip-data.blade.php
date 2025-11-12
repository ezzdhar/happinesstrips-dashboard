@php use App\Enums\Status;use App\Enums\TripType;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.trips') }}" shadow class="mb-3">
		<x-slot:menu>
			@can('create_trip')
				<x-button noWireNavigate label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" link="{{route('trips.create')}}"/>
			@endcan
		</x-slot:menu>
		<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
			<x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search') }}" icon="o-magnifying-glass" clearable/>
			<x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}" icon="o-flag" clearable :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
				['id' => Status::Start, 'name' => __('lang.start')],
				['id' => Status::End, 'name' => __('lang.end')],
			]"/>
			<x-select label="{{ __('lang.trip_type') }}" wire:model.live="type_filter" placeholder="{{ __('lang.all') }}" icon="o-bookmark" clearable :options="[
				['id' => TripType::Fixed, 'name' => __('lang.fixed')],
				['id' => TripType::Flexible, 'name' => __('lang.flexible')],
			]"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.main_category')}}</th>
						<th class="text-center">{{__('lang.sub_category')}}</th>
						<th class="text-center">{{__('lang.price')}}</th>
						<th class="text-center">{{__('lang.trip_type')}}</th>
						<th class="text-center">{{__('lang.status')}}</th>
						<th class="text-center">{{__('lang.is_featured')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($trips as $trip)
						<tr class="bg-base-200">
							<th class="text-center">{{$trips->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">{{$trip->name}}</th>
							<th class="text-center text-nowrap">{{$trip->mainCategory->name}}</th>
							<th class="text-center text-nowrap">{{$trip->subCategory->name}}</th>
							<th class="text-center text-nowrap">
								<div class="flex flex-col gap-1">
									<span class="text-sm">{{number_format($trip->price['egp'] ?? 0)}} {{__('lang.price_egp')}}</span>
									<span class="text-sm">{{number_format($trip->price['usd'] ?? 0)}} {{__('lang.price_usd')}}</span>
								</div>
							</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$trip->type->title()" class="bg-{{$trip->type->color()}}"/>
							</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$trip->status->title()" class="bg-{{$trip->status->color()}}"/>
							</th>
							<th class="text-center">
								@if($trip->is_featured)
									<x-icon name="o-star" class="w-5 h-5 text-yellow-400"/>
								@else
									<x-icon name="o-star" class="w-5 h-5 text-gray-300"/>
								@endif
							</th>
							<th class="text-center text-nowrap">{{formatDate($trip->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									@can('update_trip')
										<x-button noWireNavigate icon="o-pencil" class="btn-sm btn-ghost" link="{{route('trips.edit', $trip->id)}}" tooltip="{{__('lang.edit')}}"/>
									@endcan
									@can('delete_trip')
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$trip->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$trip->id}})" spinner="deleteSweetAlert({{$trip->id}})" tooltip="{{__('lang.delete')}}"/>
									@endcan
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="10" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $trips->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>

