<?php

namespace App\Services\Logging;

use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;

class EmailLogService
{
    public function logSuccess(?int $userId, string $toEmail, ?string $subject = null, ?array $meta = null): void
    {
        EmailLog::create([
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'status' => 'sent',
            'meta' => $meta,
        ]);

        Log::channel('mail')->info('Email sent', [
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
        ]);
    }

    public function logFailure(?int $userId, string $toEmail, ?string $subject, string $error, ?array $meta = null): void
    {
        EmailLog::create([
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'status' => 'failed',
            'error_message' => $error,
            'meta' => $meta,
        ]);

        Log::channel('mail')->error('Email failed', [
            'user_id' => $userId,
            'to_email' => $toEmail,
            'subject' => $subject,
            'error' => $error,
        ]);
    }
}
