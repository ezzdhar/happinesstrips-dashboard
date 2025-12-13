<div>
	<x-card title="{{ __('lang.amenities') }}" shadow class="mb-3">
		<x-slot:menu>
			<livewire:dashboard.amenity.create-amenity wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.amenity.create-amenity>
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
						<th class="text-center">{{__('lang.icon')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($amenities as $amenity)
						<tr class="bg-base-200">
							<th class="text-center">{{$amenities->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">
								{{$amenity->name}}
							</th>
							<th class="text-center">
								<i class="{{ $amenity->icon }} text-primary" style="font-size: 1.5rem;"></i>
							</th>
							<th class="text-center text-nowrap">{{formatDate($amenity->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									<livewire:dashboard.amenity.update-amenity :amenity="$amenity" :key="\Illuminate\Support\Str::random(10)"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$amenity->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$amenity->id}})" spinner="deleteSweetAlert({{$amenity->id}})" tooltip="{{__('lang.delete')}}"/>
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="5" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $amenities->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>
