<?php
/**
 * Quick Sync Test — lightweight test with minimal processing.
 * Run via browser: https://vidanexus.ai/test_sync_now.php
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Prevent browser timeout
set_time_limit(300);
ini_set('max_execution_time', 300);

// Flush output immediately
header('Content-Type: text/plain; charset=utf-8');
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

echo "=== SYNC TEST ===\n";
echo "Started: " . now() . "\n\n";

// Find user with competitors
$users = \App\Models\User::whereNotNull('settings')->get();
$testUser = null;
foreach ($users as $u) {
    $settings = $u->settings ?? [];
    if (!empty($settings['keywords_competitors'])) {
        $testUser = $u;
        break;
    }
}

if (!$testUser) {
    echo "No user with competitors found.\n";
    exit;
}

$settings = $testUser->settings ?? [];
$competitors = $settings['keywords_competitors'] ?? '';
$competitorList = array_filter(explode("\n", $competitors), fn($c) => !empty(trim($c)));
echo "User: #{$testUser->id} ({$testUser->email})\n";
echo "Competitors: " . count($competitorList) . "\n";
foreach ($competitorList as $c) {
    echo "  - " . trim($c) . "\n";
}

// Clear locks & jobs
DB::table('cache')->where('key', 'like', '%sync_lock_%')->delete();
DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->delete();
echo "\nLocks & stuck jobs cleared.\n";

// Count before
$retentionVal = (int)\App\Models\Setting::get('ai-keyword-radar_retention_hours', 24);
$retentionLimit = now()->subHours($retentionVal);
$beforeCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $testUser->id)
    ->where('category', 'Target')
    ->where('lang', 'ar')
    ->where(function($q) use ($retentionLimit) {
        $q->where('published_at', '>=', $retentionLimit)
          ->orWhere(function($q2) use ($retentionLimit) {
              $q2->whereNull('published_at')
                 ->where('created_at', '>=', $retentionLimit);
          });
    })
    ->count();
echo "Keywords before: {$beforeCount}\n";

echo "\n--- Running dispatchSync (time_filter=24h) ---\n";
$startTime = microtime(true);

try {
    $syncCredits = (int)\App\Models\Setting::get('ai-keyword-radar_sync_credits', 1);
    \Modules\AIKeywordRadar\Jobs\SyncKeywordsJob::dispatchSync($testUser->id, 'ar', $syncCredits, '24h', null);
    $elapsed = round(microtime(true) - $startTime, 2);
    
    $afterCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $testUser->id)
        ->where('category', 'Target')
        ->where('lang', 'ar')
        ->where(function($q) use ($retentionLimit) {
            $q->where('published_at', '>=', $retentionLimit)
              ->orWhere(function($q2) use ($retentionLimit) {
                  $q2->whereNull('published_at')
                     ->where('created_at', '>=', $retentionLimit);
              });
        })
        ->count();
    
    $newKw = max(0, $afterCount - $beforeCount);
    echo "\n=== RESULTS ===\n";
    echo "Status:      SUCCESS\n";
    echo "Duration:    {$elapsed}s\n";
    echo "Before:      {$beforeCount} keywords\n";
    echo "After:       {$afterCount} keywords\n";
    echo "New:         {$newKw} keywords\n";
    echo "Lock clear:  " . (Cache::has("sync_lock_{$testUser->id}_ar") ? 'NO' : 'YES') . "\n";
    echo "Stuck jobs:  " . DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->count() . "\n";

} catch (\Throwable $e) {
    $elapsed = round(microtime(true) - $startTime, 2);
    echo "\n=== FAILED ===\n";
    echo "Duration: {$elapsed}s\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    Cache::forget("sync_lock_{$testUser->id}_ar");
}

echo "\nFinished: " . now() . "\n";
