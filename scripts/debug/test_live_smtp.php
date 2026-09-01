<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

echo "=== DIAGNOSING EMAIL DELIVERY SYSTEM ===\n";

echo "1. Mailer Config:\n";
echo "   - Driver: " . config('mail.default') . "\n";
echo "   - Host: " . config('mail.mailers.smtp.host') . "\n";
echo "   - Port: " . config('mail.mailers.smtp.port') . "\n";
echo "   - Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "   - Username: " . config('mail.mailers.smtp.username') . "\n";
echo "   - From Address: " . config('mail.from.address') . "\n";
echo "   - From Name: " . config('mail.from.name') . "\n";

echo "\n2. Queue Config:\n";
echo "   - Queue Driver: " . config('queue.default') . "\n";
echo "   - Pending Jobs in DB: " . DB::table('jobs')->count() . "\n";
echo "   - Failed Jobs in DB: " . DB::table('failed_jobs')->count() . "\n";

$user = User::where('role', 'admin')->first() ?: User::first();
if (!$user) {
    echo "❌ No user found in database.\n";
    exit;
}

echo "\n3. Testing Direct SMTP Transport to ({$user->email}):\n";
try {
    Mail::raw('VidaNexus SMTP Health Check', function ($msg) use ($user) {
        $msg->to($user->email)
            ->subject('VidaNexus Direct SMTP Health Check ' . date('Y-m-d H:i:s'));
    });
    echo "   ✅ Direct Mail::raw() sent successfully!\n";
} catch (\Throwable $e) {
    echo "   ❌ Direct Mail::raw() failed: " . $e->getMessage() . "\n";
}

echo "\n4. Testing Verification Notification Render & Send:\n";
try {
    $notification = new QueuedVerifyEmail();
    $mailMessage = $notification->toMail($user);
    echo "   ✅ Verification MailMessage rendered successfully! Subject: {$mailMessage->subject}\n";

    // Test sending notification directly (synchronously)
    $user->notifyNow($notification);
    echo "   ✅ notifyNow(QueuedVerifyEmail) delivered successfully to {$user->email}!\n";
} catch (\Throwable $e) {
    echo "   ❌ Verification Notification failed: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
