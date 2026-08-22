<?php

/**
 * Aggressive cleanup: DB cache rows for sync_lock_*, SyncKeywordsJob queue + failed_jobs,
 * optional route cache, queue-worker log trim. Verifies dashboard sync route wiring.
 *
 * Run: php scripts/maintenance/fix_sync.php
 */
require __DIR__.'/../bootstrap.php';

use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX — CLEARING ALL STUCK STATE ===\n";
echo 'Time: '.now()."\n\n";

echo "--- Clearing Sync Locks (database cache store) ---\n";
try {
    $locksDel = DB::table('cache')->where('key', 'like', '%sync_lock_%')->delete();
    echo "Cleared {$locksDel} sync lock entries from cache table\n";
} catch (\Throwable $e) {
    echo 'Cache table skip: '.$e->getMessage()."\n";
}

echo "\n--- Clearing Stuck Jobs ---\n";
try {
    $jobsDel = DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
    echo "Cleared {$jobsDel} stuck SyncKeywordsJob(s) from queue\n";
    $failedDel = DB::table('failed_jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
    echo "Cleared {$failedDel} failed SyncKeywordsJob(s)\n";
} catch (\Throwable $e) {
    echo 'Jobs table: '.$e->getMessage()."\n";
}

echo "\n--- Queue State ---\n";
try {
    echo 'Total jobs remaining: '.DB::table('jobs')->count()."\n";
    echo 'Total failed jobs: '.DB::table('failed_jobs')->count()."\n";
} catch (\Throwable $e) {
    echo $e->getMessage()."\n";
}

echo "\n--- Route wiring (routes/web/dashboard.php) ---\n";
$dashboard = @file_get_contents(base_path('routes/web/dashboard.php')) ?: '';
echo 'AiKeywordRadarSyncController: '.(str_contains($dashboard, 'AiKeywordRadarSyncController') ? '✅' : '❌ missing')."\n";
echo 'DiscoverHeadlinesGenerateController: '.(str_contains($dashboard, 'DiscoverHeadlinesGenerateController') ? '✅' : '❌ missing')."\n";
echo 'Legacy exec() in dashboard routes: '.(str_contains($dashboard, 'exec($cmd)') ? '⚠️ present' : '✅ none')."\n";
echo 'Legacy proc_open in dashboard routes: '.(str_contains($dashboard, 'proc_open') ? '⚠️ present' : '✅ none')."\n";

echo "\n--- PHP Environment ---\n";
echo 'PHP_BINARY: '.PHP_BINARY."\n";
echo 'PHP_SAPI: '.php_sapi_name()."\n";
$phpCli = '/opt/cpanel/ea-php82/root/usr/bin/php';
echo 'cPanel PHP82 path exists: '.(file_exists($phpCli) ? 'YES ('.$phpCli.')' : 'NO')."\n";
$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
echo 'exec() usable: '.(function_exists('exec') && ! in_array('exec', $disabled, true) ? 'YES' : 'NO')."\n";
echo 'proc_open() usable: '.(function_exists('proc_open') && ! in_array('proc_open', $disabled, true) ? 'YES' : 'NO')."\n";

echo "\n--- Cleanup ---\n";
$logFile = storage_path('logs/queue-worker.log');
if (file_exists($logFile)) {
    file_put_contents($logFile, '=== Log cleared at '.now()." ===\n");
    echo "Truncated queue-worker.log\n";
} else {
    echo "No queue-worker.log\n";
}

$cacheFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cacheFile) && is_writable($cacheFile)) {
    @unlink($cacheFile);
    echo "Route cache cleared\n";
} elseif (file_exists($cacheFile)) {
    echo "⚠️ Route cache not writable\n";
} else {
    echo "No route cache file\n";
}

echo "\n=== DONE ===\n";
echo "</pre>\n";
