<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $plainPassword,
        public string $roleName,
        public ?string $managerName = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $loginUrl = url('/login');
        $installAppUrl = url('/install-app');
        $appName = config('app.name');

        return (new MailMessage)
            ->subject('Welcome to ' . $appName . ' – Your account details')
            ->view('emails.new-user-welcome', [
                'user' => $this->user,
                'plainPassword' => $this->plainPassword,
                'roleName' => $this->roleName,
                'managerName' => $this->managerName,
                'loginUrl' => $loginUrl,
                'installAppUrl' => $installAppUrl,
                'appName' => $appName,
            ]);
    }
}
