<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCodeNotification extends Notification
{
    public function __construct(public User $user, public string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->user->update(['verification_code' => $this->code]);

        return (new MailMessage)
            ->subject(__('lang.verification_code'))
            ->view('emails.send-code', ['user' => $this->user, 'code' => $this->code]);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
