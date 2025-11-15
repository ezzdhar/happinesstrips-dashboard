<?php

use App\Models\User;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
	use WithPagination;

	public $unread_notifications, $per_page = 5;

	public function mount(): void
	{
		$user = auth()->user();
		$this->unread_notifications = $user->unreadNotifications()->count();
	}

	public function getNotificationsProperty()
	{
		return auth()->user()->notifications()->latest()->paginate($this->per_page);
	}

	public function readAllNotifications(): void
	{
		auth()->user()->unreadNotifications->markAsRead();
		$this->unread_notifications = 0;
		$this->render();
	}

	public function loadMore(): void
	{
		$this->per_page += 4;
	}

	public function readNotification($notificationId): void
	{
		$notification = auth()->user()->notifications()->find($notificationId);
		if ($notification) {
			$notification->markAsRead();
//			$this->unread_notifications = auth()->user()->unreadNotifications()->count();
			$this->redirectIntended(default: $notification->data['url']);
		}
	}
};
?>


<div>

	<x-dropdown>
		<x-slot:trigger>
			<div class="indicator relative">
				<x-button icon="o-bell" class="btn-ghost btn-sm btn-circle" responsive/>
				@if($unread_notifications > 0)
					<small class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-[10px] font-bold rounded-full px-1 py-0.4" style="font-size: 12px;">
						{{ $unread_notifications > 9 ? '9+' : $unread_notifications }}
					</small>
				@endif
			</div>
		</x-slot:trigger>

		<div>
			<div class="flex items-center justify-between p-2 py-0 border-b border-base-200 ">
				<h5 class="font-bold">{{__('lang.notifications')}} </h5>
				<x-button label="{{__('lang.read_all_notifications')}}" @click.stop="" wire:click="readAllNotifications"
				          class="btn-sm border-none hover:bg-inherit btn-ghost" wire:target="readAllNotifications" spinner="readAllNotifications"/>
			</div>
			<div style="max-height: 295px; overflow-y: auto;max-width: 260px">
				@forelse($this->notifications as $notification)
					<div class="block border-b border-base-200 rounded-lg p-2 hover:bg-base-200 transition mb-1 {{ $notification->read_at ?? 'bg-base-300' }}">
						<a  class="cursor-pointer" wire:click="readNotification('{{ $notification->id }}')">
							<div class="font-bold">{{ \Illuminate\Support\Str::limit($notification->data['title'],20) }}</div>
							<div class="text-sm">{{ \Illuminate\Support\Str::limit($notification->data['body'],60)  }}</div>
						</a>
						<div class="text-xs mt-1" style="text-align: end">
							<small>{{ $notification->created_at->diffForHumans() }}</small>
						</div>
					</div>
				@empty
					<div class="text-center text-sm py-4">{{__('lang.not_notifications')}}</div>
				@endforelse

				@if($this->notifications->hasMorePages())
					<div class="text-center mt-2" @click.stop="" wire:loading.attr="disabled">
						<x-button label="{{__('lang.load_more')}}" wire:click="loadMore" class="btn-sm border-none hover:bg-inherit btn-ghost" wire:target="loadMore" spinner="loadMore"/>
					</div>
				@endif
			</div>

		</div>
	</x-dropdown>
</div>
