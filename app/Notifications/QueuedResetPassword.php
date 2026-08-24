<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Password reset link sent via the queue.
 */
class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
        ];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Reset Your Password | VidaNexus AI')
            ->view('emails.auth-reset-password', [
                'user' => $notifiable,
                'url' => $resetUrl,
            ]);
    }
}
