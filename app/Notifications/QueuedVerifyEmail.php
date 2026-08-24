<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Email verification sent via the queue (retries, failure logging via Laravel queue).
 */
class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
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
