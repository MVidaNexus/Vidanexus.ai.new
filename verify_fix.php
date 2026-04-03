<?php
/**
 * Verify the sync lock fix is working correctly
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX VERIFICATION ===\n";
echo "Time: " . now() . "\n\n";

// 1. Check sync locks
echo "--- Current Sync Locks ---\n";
$locks = DB::table('cache')->where('key', 'like', '%sync_lock_%')->get();
echo "Sync lock entries in cache table: " . $locks->count() . "\n";
foreach ($locks as $lock) {
    $expired = $lock->expiration < time() ? '(EXPIRED)' : '(active)';
    echo "  {$lock->key} → expires " . date('Y-m-d H:i:s', $lock->expiration) . " {$expired}\n";
}

// 2. Check queue
echo "\n--- Queue Status ---\n";
$jobs = DB::table('jobs')->get();
echo "Total jobs: " . $jobs->count() . "\n";
foreach ($jobs as $job) {
    $payload = json_decode($job->payload, true);
    echo "  #{$job->id} | {$payload['displayName']} | Attempts: {$job->attempts}\n";
}

// 3. Verify AppServiceProvider has the fix
echo "\n--- AppServiceProvider Check ---\n";
$providerFile = base_path('app/Providers/AppServiceProvider.php');
$content = file_get_contents($providerFile);
if (str_contains($content, 'SyncLock SafetyNet')) {
    echo "✅ AppServiceProvider has Queue::failing() safety net for sync locks\n";
} else {
    echo "❌ AppServiceProvider is MISSING the sync lock safety net\n";
}

// 4. Verify routes/web.php has the fix
echo "\n--- Route Override Check ---\n";
$routeFile = base_path('routes/web.php');
$routeContent = file_get_contents($routeFile);
if (str_contains($routeContent, 'KEYWORD RADAR SYNC LOCK FIX')) {
    echo "✅ routes/web.php has the sync lock fix override\n";
} else {
    echo "❌ routes/web.php is MISSING the sync lock fix\n";
}

// 5. Clear route cache just in case
echo "\n--- Clearing Route Cache ---\n";
$cacheFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cacheFile) && is_writable($cacheFile)) {
    @unlink($cacheFile);
    echo "✅ Route cache cleared\n";
} elseif (file_exists($cacheFile)) {
    echo "⚠️ Route cache exists but cannot be cleared (not writable)\n";
} else {
    echo "ℹ️ No route cache file found (not using route caching)\n";
}

// 6. Test: Simulate a stale lock scenario
echo "\n--- Stale Lock Auto-Clear Test ---\n";
$testLockKey = "sync_lock_999_ar";
Cache::put($testLockKey, true, 60); // Create a test lock
echo "Created test lock: {$testLockKey}\n";
echo "Has lock: " . (Cache::has($testLockKey) ? 'YES' : 'NO') . "\n";

// Check if route fix would detect it as stale (no job in queue)
$hasActiveJob = DB::table('jobs')
    ->where('payload', 'like', '%SyncKeywordsJob%')
    ->exists();
echo "Active SyncKeywordsJob in queue: " . ($hasActiveJob ? 'YES' : 'NO') . "\n";
echo "Route fix would clear this lock: " . ($hasActiveJob ? 'NO (job running)' : 'YES (orphaned)') . "\n";

// Clean up test
Cache::forget($testLockKey);
echo "Test lock cleaned up.\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "</pre>";
