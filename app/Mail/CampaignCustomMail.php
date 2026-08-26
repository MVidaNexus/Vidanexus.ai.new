<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $campaignSubject,
        public string $htmlContent,
        public array $recipientData = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address', 'no.reply@vidanexus.net'),
                config('mail.from.name', 'VidaNexus AI')
            ),
            subject: $this->campaignSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlContent,
        );
    }
}
