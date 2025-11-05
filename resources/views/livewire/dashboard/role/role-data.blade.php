<div>
	<x-card title="{{ __('lang.roles') }}" shadow class="mb-3">
		<x-slot:menu>
			@if(auth()->user()->hasPermissionTo('create_role'))
				<livewire:dashboard.role.create-role wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.role.create-role>
			@endif
		</x-slot:menu>
		<div class="w-64 mb-3">
			<x-choices-offline label="{{ __('lang.search_name') }}" wire:model.live="search_role_id" :options="$all_roles" single clearable searchable
			                   option-value="id" option-label="name" placeholder="{{ __('lang.search') }}"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.number_of_users')}}</th>
						<th class="text-center">{{__('lang.number_of_permissions')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($roles as $role)
						<tr class="bg-base-200">
							<th class="text-center">{{$roles->firstItem() + $loop->index}}</th>
							<th class="text-center text-nowrap">{{$role->name}}</th>
							<th class="text-center text-nowrap">{{$role->users_count}}</th>
							<th class="text-center text-nowrap">{{$role->permissions_count}}</th>
							<th class="text-center text-nowrap">{{formatDate($role->created_at,true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									@if(auth()->user()->hasPermissionTo('update_role'))
										<livewire:dashboard.role.update-role :role="$role" :key="\Illuminate\Support\Str::random(10)"/>
									@endif
									@if(auth()->user()->hasPermissionTo('delete_role') && !$role->is_main)
										<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$role->id}})" wire:loading.attr="disabled"
										          wire:target="deleteSweetAlert({{$role->id}})" spinner="deleteSweetAlert({{$role->id}})" tooltip="{{__('lang.delete')}}"/>
									@endif
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
							{{ $roles->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>