<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $paymentDetails
    ) {}

    public function envelope(): Envelope
    {
        $ref = $this->paymentDetails['reference'] ?? 'Receipt';
        $itemName = $this->paymentDetails['item_name'] ?? 'Credit Package';
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'no.reply@vidanexus.net'),
                config('mail.from.name', 'VidaNexus AI')
            ),
            subject: "🎉 Payment Confirmed: {$itemName} [{$ref}]",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.payment-receipt',
            with: [
                'user' => $this->user,
                'details' => $this->paymentDetails,
                'dashboardUrl' => config('app.url', 'https://vidanexus.ai') . '/dashboard',
                'siteUrl' => config('app.url', 'https://vidanexus.ai'),
            ],
        );
    }
}
