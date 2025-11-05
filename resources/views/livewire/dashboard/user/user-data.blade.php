@php use App\Enums\Status;use App\Services\FileService; @endphp
<div>
	<x-card title="{{ __('lang.users') }}" shadow class="mb-3">
		<x-slot:menu>
			<livewire:dashboard.user.create-user wire:key="{{\Illuminate\Support\Str::random(20)}}"></livewire:dashboard.user.create-user>
		</x-slot:menu>
		<div class="w-64 mb-3">
			<x-choices-offline label="{{ __('lang.users') }}" wire:model.live="search_user_id" :options="$all_user" single clearable searchable
			                   option-value="id" option-label="name" option-sub-label="username" placeholder="{{ __('lang.search') }}"/>
		</div>
		<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
			<div class="overflow-x-auto">
				<table class="table">
					<thead class="min-w-full divide-y bg-base-300 text-base-content">
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">{{__('lang.name')}}</th>
						<th class="text-center">{{__('lang.email')}}</th>
						<th class="text-center">{{__('lang.created_at')}}</th>
						<th class="text-center">{{__('lang.action')}}</th>
					</tr>
					</thead>
					<tbody>
					@forelse($users as $user)
						<tr class="bg-base-200">
							<th class="text-center">{{$users->firstItem() + $loop->index}}</th>
							<th class="text-nowrap">
								<x-avatar :image="FileService::get($user->image)" :title="$user->name" :subtitle="$user->username" class="!w-10"/>
							</th>
							<th class="text-center text-nowrap">{{$user->email}}</th>
							<th class="text-center text-nowrap">{{formatDate($user->created_at,true) }}</th>
							<td>
								<div class="flex gap-2 justify-center">
									<livewire:dashboard.user.update-user :user="$user" :key="\Illuminate\Support\Str::random(10)"/>
									<x-button icon="o-trash" class="btn-sm btn-ghost" wire:click="deleteSweetAlert({{$user->id}})" wire:loading.attr="disabled"
									          wire:target="deleteSweetAlert({{$user->id}})" spinner="deleteSweetAlert({{$user->id}})" tooltip="{{__('lang.delete')}}"/>
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
							{{ $users->links() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</x-card>
</div>