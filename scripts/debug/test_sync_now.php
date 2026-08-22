<?php

/**
 * Pick first user with competitors; clear locks/jobs; run SyncKeywordsJob::dispatchSync; report counts.
 * Run: php scripts/debug/test_sync_now.php
 */
require __DIR__.'/../bootstrap.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

set_time_limit(300);
ini_set('max_execution_time', '300');

header('Content-Type: text/plain; charset=utf-8');
ob_implicit_flush(true);
if (ob_get_level()) {
    ob_end_flush();
}

echo "=== SYNC TEST ===\n";
echo 'Started: '.now()."\n\n";

$testUser = null;
foreach (\App\Models\User::whereNotNull('settings')->get() as $u) {
    $settings = $u->settings ?? [];
    if (! empty($settings['keywords_competitors'])) {
        $testUser = $u;
        break;
    }
}

if (! $testUser) {
    echo "No user with keywords_competitors in settings.\n";
    exit(1);
}

$settings = $testUser->settings ?? [];
$competitors = $settings['keywords_competitors'] ?? '';
$competitorList = array_filter(explode("\n", $competitors), fn ($c) => ! empty(trim($c)));
echo "User: #{$testUser->id} ({$testUser->email})\n";
echo 'Competitors: '.count($competitorList)."\n";

try {
    DB::table('cache')->where('key', 'like', '%sync_lock_%')->delete();
} catch (\Throwable $e) {
}
try {
    DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
} catch (\Throwable $e) {
}
echo "Locks / stuck jobs cleared (best effort).\n";

$retentionVal = (int) \App\Models\Setting::get('ai-keyword-radar_retention_hours', 24);
$retentionLimit = now()->subHours($retentionVal);
$beforeCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $testUser->id)
    ->where('category', 'Target')
    ->where('lang', 'ar')
    ->where(function ($q) use ($retentionLimit) {
        $q->where('published_at', '>=', $retentionLimit)
            ->orWhere(function ($q2) use ($retentionLimit) {
                $q2->whereNull('published_at')
                    ->where('created_at', '>=', $retentionLimit);
            });
    })
    ->count();
echo "Keywords before: {$beforeCount}\n";

echo "\n--- dispatchSync (24h) ---\n";
$startTime = microtime(true);

try {
    $syncCredits = (int) \App\Models\Setting::get('ai-keyword-radar_sync_credits', 1);
    \Modules\AIKeywordRadar\Jobs\SyncKeywordsJob::dispatchSync($testUser->id, 'ar', $syncCredits, '24h', null);
    $elapsed = round(microtime(true) - $startTime, 2);

    $afterCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $testUser->id)
        ->where('category', 'Target')
        ->where('lang', 'ar')
        ->where(function ($q) use ($retentionLimit) {
            $q->where('published_at', '>=', $retentionLimit)
                ->orWhere(function ($q2) use ($retentionLimit) {
                    $q2->whereNull('published_at')
                        ->where('created_at', '>=', $retentionLimit);
                });
        })
        ->count();

    $newKw = max(0, $afterCount - $beforeCount);
    echo "\n=== RESULTS ===\n";
    echo "Duration: {$elapsed}s\n";
    echo "Before: {$beforeCount} | After: {$afterCount} | New: {$newKw}\n";
    echo 'Lock cleared: '.(Cache::has("sync_lock_{$testUser->id}_ar") ? 'NO' : 'YES')."\n";
    echo 'Stuck jobs: '.DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->count()."\n";
} catch (\Throwable $e) {
    echo "\nFAILED: ".$e->getMessage()."\n";
    Cache::forget("sync_lock_{$testUser->id}_ar");
}

echo "\nFinished: ".now()."\n";
