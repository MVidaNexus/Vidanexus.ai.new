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

    public array $cleanDetails;

    public function __construct(
        public User $user,
        public array $paymentDetails
    ) {
        $this->cleanDetails = $paymentDetails;
        if (isset($this->cleanDetails['credits_added'])) {
            $this->cleanDetails['credits_added'] = (int) round((float) $this->cleanDetails['credits_added']);
        }
        if (isset($this->cleanDetails['new_balance'])) {
            $this->cleanDetails['new_balance'] = (int) round((float) $this->cleanDetails['new_balance']);
        }
    }

    public function envelope(): Envelope
    {
        $ref = $this->cleanDetails['reference'] ?? 'Receipt';
        $itemName = $this->cleanDetails['item_name'] ?? 'Credit Package';
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
                'details' => $this->cleanDetails,
                'toolsUrl' => config('app.url', 'https://vidanexus.ai') . '/dashboard#subscriptions',
                'siteUrl' => config('app.url', 'https://vidanexus.ai'),
            ],
        );
    }
}
