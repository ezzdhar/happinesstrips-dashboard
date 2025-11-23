<?php

namespace App\Notifications;

use DevKandil\NotiFire\Enums\MessagePriority;
use DevKandil\NotiFire\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminNotification extends Notification
{
	use Queueable;

	public function __construct(public $title = [], public $body = [], public $url)
	{
		//
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}

	public function toArray(object $notifiable): array
	{
		return [
			'title' => $this->title,
			'body' => $this->body,
			'url' => $this->url,
		];
	}


}
