<?php

namespace App\Notifications;

use App\Services\NotificationFirebaseHelper;
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
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        // Send FCM notification here after storing in database
        $this->sendFcmNotification($notifiable);

        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }

    protected function sendFcmNotification(object $notifiable): void
    {
        try {
            $lang = $notifiable->language ?? $notifiable->lang ?? 'en';

            NotificationFirebaseHelper::send($notifiable, [
                'title' => $this->title[$lang] ?? $this->title['en'] ?? '',
                'body' => $this->body[$lang] ?? $this->body['en'] ?? '',
                'type' => $this->data,
            ]);

            \Log::info('FCM Notification sent via NotificationFirebaseHelper', [
                'user_id' => $notifiable->id,
                'title' => $this->title[$lang] ?? $this->title['en'] ?? '',
                'body' => $this->body[$lang] ?? $this->body['en'] ?? '',
                'data' => $this->data,
            ]);
        } catch (\Exception $e) {
            \Log::error('FCM Notification via NotificationFirebaseHelper failed', [
                'user_id' => $notifiable->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Silently fail to prevent breaking the notification system
        }
    }
}
