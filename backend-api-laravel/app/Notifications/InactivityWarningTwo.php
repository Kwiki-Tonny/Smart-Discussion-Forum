<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InactivityWarningTwo extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Final Warning: Account at Risk of Blacklisting')
            ->greeting("Hello {$notifiable->name},")
            ->line("It's been 14 days since your last activity on the forum.")
            ->line('This is your final warning. If you remain inactive for 21 days total, your account will be automatically blacklisted and you will be temporarily blocked from logging in.')
            ->line('Please log in and participate in discussions to keep your account in good standing.')
            ->action('Login Now', url('/login'));
    }
    
}
