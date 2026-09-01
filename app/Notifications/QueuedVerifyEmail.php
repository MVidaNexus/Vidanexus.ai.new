<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Email verification sent immediately via configured SMTP mailer.
 */
class QueuedVerifyEmail extends VerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $customHtml = \App\Models\Setting::get('template_email_verify_html');

        if (!empty($customHtml)) {
            $parsed = str_replace(
                ['{name}', '{email}', '{url}', '{app_name}', '{{ $name }}', '{{ $email }}', '{{ $url }}'],
                [$notifiable->name ?? 'User', $notifiable->email, $verificationUrl, config('app.name', 'VidaNexus AI'), $notifiable->name ?? 'User', $notifiable->email, $verificationUrl],
                $customHtml
            );

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Activate Your Account | ' . config('app.name', 'VidaNexus AI'))
                ->view('emails.raw-html', ['html' => $parsed]);
        }

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Activate Your Account | ' . config('app.name', 'VidaNexus AI'))
            ->view('emails.auth-verify-email', [
                'user' => $notifiable,
                'url' => $verificationUrl,
            ]);
    }
}
