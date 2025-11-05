@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.main_categories') }}" shadow class="mb-3">
		<x-slot:menu>
			<livewire:dashboard.main-category.create-main-category wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.main-category.create-main-category>
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
						<th class="text-center">{{__('lang.status')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($main_categories as $main_category)
						<tr class="bg-base-200">
							<th class="text-center">{{$main_categories->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">
								<x-avatar :image="FileService::get($main_category->image)" :title="$main_category->name" class="!w-10"/>
							</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$main_category->status->value" class="bg-{{$main_category->status->color()}}"/>
							</th>
							<th class="text-center text-nowrap">{{formatDate($main_category->created_at, true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									<livewire:dashboard.main-category.update-main-category :mainCategory="$main_category" :key="\Illuminate\Support\Str::random(10)"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$main_category->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$main_category->id}})" spinner="deleteSweetAlert({{$main_category->id}})" tooltip="{{__('lang.delete')}}"/>
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
							{{ $main_categories->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>

