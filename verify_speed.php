<?php
/**
 * Verify Speed Improvement
 * Run via: https://vidanexus.ai/verify_speed.php
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== SPEED VERIFICATION ===\n";
echo "Testing sync for User #1 (AR) - Last 24 Hours\n\n";

$startTime = microtime(true);

// Mock a request to the sync route
$request = Illuminate\Http\Request::create('/dashboard/ai-keyword-radar/sync', 'POST', [
    'lang' => 'ar',
    'time_filter' => '24h'
]);

// Authenticate as user #1
$user = \App\Models\User::find(1);
auth()->login($user);

echo "Starting Sync logic...\n";
$response = Route::dispatch($request);
$elapsed = round(microtime(true) - $startTime, 2);

echo "Response: " . $response->getContent() . "\n";
echo "Total Duration: {$elapsed} seconds\n";

if ($elapsed < 120) {
    echo "\n✅ SUCCESS: Sync finished in under 2 minutes (Actual: {$elapsed}s)\n";
} else {
    echo "\n⚠️ WARNING: Sync took {$elapsed}s. Still faster than 12m, but check logs.\n";
}
