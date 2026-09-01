<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Password reset link sent immediately via configured SMTP mailer.
 */
class QueuedResetPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $customHtml = \App\Models\Setting::get('template_email_reset_html');

        if (!empty($customHtml)) {
            $parsed = str_replace(
                ['{name}', '{email}', '{url}', '{app_name}', '{{ $name }}', '{{ $email }}', '{{ $url }}'],
                [$notifiable->name ?? 'User', $notifiable->email, $resetUrl, config('app.name', 'VidaNexus AI'), $notifiable->name ?? 'User', $notifiable->email, $resetUrl],
                $customHtml
            );

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Your Password | ' . config('app.name', 'VidaNexus AI'))
                ->view('emails.raw-html', ['html' => $parsed]);
        }

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Reset Your Password | ' . config('app.name', 'VidaNexus AI'))
            ->view('emails.auth-reset-password', [
                'user' => $notifiable,
                'url' => $resetUrl,
            ]);
    }
}
