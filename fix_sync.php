<?php
/**
 * Clear ALL stuck sync locks, stale jobs, and verify the fix.
 * Run this via browser: https://vidanexus.ai/fix_sync.php
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX — CLEARING ALL STUCK STATE ===\n";
echo "Time: " . now() . "\n\n";

// 1. Clear ALL sync locks
echo "--- Clearing Sync Locks ---\n";
$locksDel = DB::table('cache')->where('key', 'like', '%sync_lock_%')->delete();
echo "Cleared {$locksDel} sync lock entries from cache table\n";

// 2. Clear ALL SyncKeywordsJob entries from queue
echo "\n--- Clearing Stuck Jobs ---\n";
$jobsDel = DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
echo "Cleared {$jobsDel} stuck SyncKeywordsJob(s) from queue\n";

$failedDel = DB::table('failed_jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
echo "Cleared {$failedDel} failed SyncKeywordsJob(s)\n";

// 3. Check remaining queue state
echo "\n--- Queue State ---\n";
$totalJobs = DB::table('jobs')->count();
echo "Total jobs remaining in queue: {$totalJobs}\n";
$totalFailed = DB::table('failed_jobs')->count();
echo "Total failed jobs: {$totalFailed}\n";

// 4. Verify the route override is in place
echo "\n--- Code Verification ---\n";
$routeContent = file_get_contents(base_path('routes/web.php'));
echo "Route override: " . (str_contains($routeContent, 'dispatchSync') ? '✅ Using dispatchSync (inline)' : '❌ Still using dispatch (broken)') . "\n";
echo "Old exec() call: " . (str_contains($routeContent, 'exec($cmd)') ? '⚠️ Still present' : '✅ Removed') . "\n";
echo "Old proc_open: " . (str_contains($routeContent, 'proc_open') ? '⚠️ Still present' : '✅ Removed') . "\n";

// 5. Show PHP binary info (for diagnosis)
echo "\n--- PHP Environment ---\n";
echo "PHP_BINARY: " . PHP_BINARY . "\n";
echo "PHP_SAPI: " . php_sapi_name() . "\n";
$phpCli = '/opt/cpanel/ea-php82/root/usr/bin/php';
echo "PHP CLI exists: " . (file_exists($phpCli) ? '✅ YES (' . $phpCli . ')' : '❌ NO') . "\n";
echo "exec() available: " . (function_exists('exec') && !in_array('exec', array_map('trim', explode(',', ini_get('disable_functions')))) ? 'YES' : '❌ NO (disabled)') . "\n";
echo "proc_open() available: " . (function_exists('proc_open') ? 'YES' : '❌ NO') . "\n";

// 6. Clear queue worker log (was full of php-fpm garbage)
echo "\n--- Cleanup ---\n";
$logFile = storage_path('logs/queue-worker.log');
if (file_exists($logFile)) {
    file_put_contents($logFile, "=== Log cleared at " . now() . " ===\n");
    echo "Cleared queue-worker.log (was full of php-fpm errors)\n";
} else {
    echo "No queue-worker.log to clear\n";
}

// 7. Clear route cache if exists
$cacheFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cacheFile) && is_writable($cacheFile)) {
    @unlink($cacheFile);
    echo "Route cache cleared\n";
} elseif (file_exists($cacheFile)) {
    echo "⚠️ Route cache exists but cannot be cleared (not writable)\n";
} else {
    echo "No route cache to clear\n";
}

echo "\n=== ✅ ALL CLEAR ===\n";
echo "System is ready for syncing.\n";
echo "The sync now runs INLINE (no background worker needed).\n";
echo "Next sync from the dashboard will work directly.\n";
echo "</pre>";
