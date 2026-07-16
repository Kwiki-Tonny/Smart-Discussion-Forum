<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountBlacklisted extends Notification
{
    use Queueable;

    protected $expiresAt;

    public function __construct($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $expiryDate = $this->expiresAt ? $this->expiresAt->format('F d, Y') : 'the configured duration';

        return (new MailMessage)
            ->subject('Account Blacklisted – Smart Discussion Forum')
            ->greeting("Hello {$notifiable->name},")
            ->line('We regret to inform you that your account has been **blacklisted** due to prolonged inactivity.')
            ->line("Your account will be locked until **{$expiryDate}**.")
            ->line('After this date, you will be able to log in again.')
            ->line('If you have any questions, please contact the administrator.')
            ->line('Thank you for your understanding.');
    }
}