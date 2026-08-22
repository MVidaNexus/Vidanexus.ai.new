<?php

/**
 * Verify sync locks, queue, AppServiceProvider safety net, and dashboard route structure.
 * Run: php scripts/maintenance/verify_fix.php
 */
require __DIR__.'/../bootstrap.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX VERIFICATION ===\n";
echo 'Time: '.now()."\n\n";

echo "--- Sync Locks (database cache) ---\n";
try {
    $locks = DB::table('cache')->where('key', 'like', '%sync_lock_%')->get();
    echo 'Sync lock rows: '.$locks->count()."\n";
    foreach ($locks as $lock) {
        $expired = $lock->expiration < time() ? '(EXPIRED)' : '(active)';
        echo "  {$lock->key} → expires ".date('Y-m-d H:i:s', (int) $lock->expiration)." {$expired}\n";
    }
} catch (\Throwable $e) {
    echo 'Skip: '.$e->getMessage()."\n";
}

echo "\n--- Queue ---\n";
try {
    $jobs = DB::table('jobs')->get();
    echo 'Total jobs: '.$jobs->count()."\n";
    foreach ($jobs as $job) {
        $payload = json_decode($job->payload, true);
        $name = is_array($payload) ? ($payload['displayName'] ?? 'unknown') : 'unknown';
        echo "  #{$job->id} | {$name} | Attempts: {$job->attempts}\n";
    }
} catch (\Throwable $e) {
    echo $e->getMessage()."\n";
}

echo "\n--- AppServiceProvider ---\n";
$providerFile = base_path('app/Providers/AppServiceProvider.php');
$content = @file_get_contents($providerFile) ?: '';
echo str_contains($content, 'SyncLock') || str_contains($content, 'sync_lock') || str_contains($content, 'Queue::failing')
    ? "✅ Contains queue/sync lock related handling (review file for details)\n"
    : "ℹ️ No obvious SyncLock marker (may be OK if handled elsewhere)\n";

echo "\n--- Dashboard routes ---\n";
$routeFile = base_path('routes/web/dashboard.php');
$routeContent = @file_get_contents($routeFile) ?: '';
echo str_contains($routeContent, 'AiKeywordRadarSyncController')
    ? "✅ AiKeywordRadarSyncController registered\n"
    : "❌ AiKeywordRadarSyncController not found in routes/web/dashboard.php\n";

echo "\n--- Route cache ---\n";
$cacheFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cacheFile) && is_writable($cacheFile)) {
    @unlink($cacheFile);
    echo "✅ Route cache cleared\n";
} elseif (file_exists($cacheFile)) {
    echo "⚠️ Route cache exists but not writable\n";
} else {
    echo "ℹ️ No route cache file\n";
}

echo "\n--- Stale lock probe (Cache facade) ---\n";
$testLockKey = 'sync_lock_999_ar';
Cache::put($testLockKey, true, 60);
echo 'Test lock set: '.(Cache::has($testLockKey) ? 'YES' : 'NO')."\n";
$hasActiveJob = DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->exists();
echo 'SyncKeywordsJob in queue: '.($hasActiveJob ? 'YES' : 'NO')."\n";
Cache::forget($testLockKey);
echo "Test lock removed.\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "</pre>\n";
