<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowCreditBalanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public float $currentBalance = 0.0
    ) {}

    public function envelope(): Envelope
    {
        $formattedBalance = number_format($this->currentBalance, 2);
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no.reply@vidanexus.net'),
                config('mail.from.name', 'VidaNexus AI')
            ),
            subject: "⚠️ Balance Reminder: {$formattedBalance} Credits Remaining on VidaNexus AI",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.low-balance-alert',
            with: [
                'user' => $this->user,
                'currentBalance' => $this->currentBalance,
                'pricingUrl' => config('app.url', 'https://vidanexus.ai') . '/pricing',
                'siteUrl' => config('app.url', 'https://vidanexus.ai'),
            ],
        );
    }
}
