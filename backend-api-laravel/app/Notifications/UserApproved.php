<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Hash;

class UserApproved extends Notification
{
    use Queueable;

    protected $password;

    public function __construct($password = null)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('🎓 Account Approved – Smart Discussion Forum')
            ->greeting("Hello {$notifiable->name}!")
            ->line('Your account has been approved by the administrator.')
            ->line('You can now log in and start participating in discussions.')
            ->action('Login Now', url('/login'));

        // If we have a password (from registration), include it
        if ($this->password) {
            $message->line('Your temporary login credentials are:')
                    ->line("**Email:** {$notifiable->email}")
                    ->line("**Password:** {$this->password}")
                    ->line('Please change your password after logging in.');
        }

        return $message
            ->line('Thank you for joining the Smart Discussion Forum community!');
    }
}