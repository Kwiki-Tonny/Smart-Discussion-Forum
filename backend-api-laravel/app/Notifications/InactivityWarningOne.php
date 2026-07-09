<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InactivityWarningOne extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('First Warning: Inactivity Notice')
            ->greeting("Hello {$notifiable->name},")
            ->line("We've noticed you haven't posted in the forum for 7 days.")
            ->line('Continued inactivity may lead to further warnings and eventual account suspension.')
            ->line('Please log in and participate in discussions to keep your account in good standing.');
    }
}