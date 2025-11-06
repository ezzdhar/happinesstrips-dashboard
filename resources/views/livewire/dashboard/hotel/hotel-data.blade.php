@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.hotels') }}" shadow class="mb-3">
		<x-slot:menu>
			<x-button noWireNavigate label="{{ __('lang.add') }}" icon="o-plus" class="btn-primary btn-sm" link="{{route('hotels.create')}}"/>
		</x-slot:menu>
		<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
			<x-input label="{{ __('lang.search') }}" wire:model.live="search" placeholder="{{ __('lang.search') }}" icon="o-magnifying-glass" clearable/>
			<x-select label="{{ __('lang.status') }}" wire:model.live="status_filter" placeholder="{{ __('lang.all') }}" icon="o-flag" clearable :options="[
				['id' => Status::Active, 'name' => __('lang.active')],
				['id' => Status::Inactive, 'name' => __('lang.inactive')],
			]"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.city')}}</th>
						<th class="text-center">{{__('lang.rating')}}</th>
						<th class="text-center">{{__('lang.status')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($hotels as $hotel)
						<tr class="bg-base-200">
							<th class="text-center">{{$hotels->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">
								{{$hotel->name}}
							</th>
							<th class="text-center text-nowrap">{{$hotel->city->name}}</th>
							<th class="text-center">
								<div class="flex justify-center gap-0.5">
									@for($i = 1; $i <= 5; $i++)
										<x-icon :name="$i <= $hotel->rating ? 'o-star' : 'o-star'" class="w-4 h-4 {{$i <= $hotel->rating ? 'text-yellow-400' : 'text-gray-300'}}"/>
									@endfor
								</div>
							</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$hotel->status->title()" class="bg-{{$hotel->status->color()}}"/>
							</th>
							<th class="text-center text-nowrap">{{formatDate($hotel->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
{{--									<x-button noWireNavigate icon="o-eye" class="btn-sm btn-ghost" link="{{route('hotels.show', $hotel->id)}}" tooltip="{{__('lang.view')}}"/>--}}
									<x-button noWireNavigate icon="o-pencil" class="btn-sm btn-ghost" link="{{route('hotels.edit', $hotel->id)}}" tooltip="{{__('lang.edit')}}"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$hotel->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$hotel->id}})" spinner="deleteSweetAlert({{$hotel->id}})" tooltip="{{__('lang.delete')}}"/>
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="7" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $hotels->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>

