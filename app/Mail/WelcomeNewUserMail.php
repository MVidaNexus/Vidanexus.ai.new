<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public float $welcomeCredits = 0.0
    ) {}

    public function envelope(): Envelope
    {
        $name = $this->user->name ?: 'Creator';
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no.reply@vidanexus.net'),
                config('mail.from.name', 'VidaNexus AI')
            ),
            subject: "Welcome to VidaNexus AI, {$name}! 🚀 Your AI Workspace is Ready",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.welcome-user',
            with: [
                'user' => $this->user,
                'welcomeCredits' => $this->welcomeCredits,
                'siteUrl' => config('app.url', 'https://vidanexus.ai'),
            ],
        );
    }
}
