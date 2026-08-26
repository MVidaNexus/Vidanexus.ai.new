<?php

namespace App\Jobs;

use App\Mail\CampaignCustomMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMassEmailCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600;

    /**
     * @param array<int, array{email: string, name: string, balance?: float|int}> $recipients
     */
    public function __construct(
        public array $recipients,
        public string $subject,
        public string $templateHtml
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $siteUrl = config('app.url', 'https://vidanexus.ai');
        $appName = config('app.name', 'VidaNexus AI');

        foreach ($this->recipients as $recipient) {
            $email = trim($recipient['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $name = $recipient['name'] ?? 'User';
            $balance = isset($recipient['balance']) ? number_format((float)$recipient['balance'], 2) : '0.00';

            // Replace dynamic placeholders
            $parsedHtml = str_replace(
                ['{name}', '{email}', '{balance}', '{site_url}', '{app_name}', '{{ $name }}', '{{ $email }}', '{{ $balance }}'],
                [$name, $email, $balance, $siteUrl, $appName, $name, $email, $balance],
                $this->templateHtml
            );

            $parsedSubject = str_replace(
                ['{name}', '{balance}', '{app_name}'],
                [$name, $balance, $appName],
                $this->subject
            );

            try {
                Mail::to($email)->send(new CampaignCustomMail($parsedSubject, $parsedHtml, $recipient));
                // Small sleep to ensure provider rate limits are respected
                usleep(50000); // 50ms pause
            } catch (\Throwable $e) {
                Log::error("Failed sending campaign email to {$email}: " . $e->getMessage(), [
                    'recipient' => $recipient,
                ]);
            }
        }
    }
}
