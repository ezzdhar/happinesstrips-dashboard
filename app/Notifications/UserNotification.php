<?php

namespace App\Notifications;

use DevKandil\NotiFire\Enums\MessagePriority;
use DevKandil\NotiFire\FcmMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

	public function __construct(public $title = [], public $body = [], public $data = [], $lang = 'en')
    {
        //
    }

    public function via(object $notifiable): array
    {
	    return ['database', 'fcm'];
    }

    public function toArray(object $notifiable): array
    {
        return [
	        'title' => $this->title,
	        'body' => $this->body,
	        'data' => $this->data,
        ];
    }

	public function toFcm($notifiable)
	{
		try {
			$message = FcmMessage::create($this->title[$notifiable->lang ?? 'en'], $this->body[$notifiable->lang ?? 'en'])
				->image(public_path('logo.svg'))
				->sound('default')
				->clickAction('OPEN_ACTIVITY')
				->icon(public_path('favicon.ico'))
				->color('#FF5733')
				->priority(MessagePriority::HIGH)
				->data($this->data);

			\Log::info('FCM Notification prepared successfully', [
				'user_id' => $notifiable->id,
				'title' => $this->title[$notifiable->lang ?? 'en'],
				'body' => $this->body[$notifiable->lang ?? 'en'],
				'data' => $this->data,
			]);

			return $message;
		} catch (\Exception $e) {
			\Log::error('FCM Notification preparation failed', [
				'user_id' => $notifiable->id,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
			]);

			throw $e;
		}
	}
}
