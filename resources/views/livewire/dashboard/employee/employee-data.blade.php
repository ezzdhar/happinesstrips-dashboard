@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.employees') }} ({{$employees->total()}})" shadow class="mb-3">
		<x-slot:menu>
			@if(auth()->user()->hasPermissionTo('create_employee'))
				<livewire:dashboard.employee.create-employee wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.employee.create-employee>
			@endif
		</x-slot:menu>
		<div class="w-64 mb-3">
			<x-choices-offline label="{{ __('lang.employees') }}" wire:model.live="search_employee_id" :options="$all_employees" single clearable searchable
			                   option-value="id" option-label="name" option-sub-label="phone" placeholder="{{ __('lang.search') }}"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.phone')}}</th>
						<th class="text-center">{{__('lang.email')}}</th>
						<th class="text-center">{{__('lang.status')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($employees as $employee)
						<tr class="bg-base-200">
							<th class="text-center">{{$employees->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">
								{{$employee->name}}
							</th>
							<th class="text-center text-nowrap">
								<span dir="ltr">{{$employee->full_phone}}</span>
							</th>
							<th class="text-center text-nowrap">{{$employee->email}}</th>
							<th class="text-center text-nowrap">
								<x-badge :value="$employee->status->title()" class="bg-{{$employee->status->color()}}"/>
							</th>
							<th class="text-center text-nowrap">{{formatDate($employee->created_at,true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									@if(auth()->user()->hasPermissionTo('update_employee'))
										<livewire:dashboard.employee.update-employee :employee="$employee" :key="\Illuminate\Support\Str::random(10)"/>
									@endif
									@if(auth()->user()->hasPermissionTo('delete_employee'))
										<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$employee->id}})" wire:loading.attr="disabled"
										          wire:target="deleteSweetAlert({{$employee->id}})" spinner="deleteSweetAlert({{$employee->id}})" tooltip="{{__('lang.delete')}}"/>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr class="bg-base-200">
							<th colspan="9" class="text-center">{{__('lang.no_data')}}</th>
						</tr>
					@endforelse
					</tbody>
				</table>
				<div class="flex items-center justify-between px-4 py-3 bg-base-300 text-base-content sm:px-6 min-w-">
					<div class="flex w-full items-center justify-between">
						<div class="w-full flex-none">
							{{ $employees->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>