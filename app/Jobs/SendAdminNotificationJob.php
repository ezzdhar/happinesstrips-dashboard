<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAdminNotificationJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(public array $title, public array $body, public string $permission, public string $url)
	{

	}

	public function handle()
	{
		User::permission($this->permission)->chunkById(100, function ($admins) {
			foreach ($admins as $admin) {
				$admin->notify(new AdminNotification($this->title, $this->body, $this->url));
			}
		});
	}
}
