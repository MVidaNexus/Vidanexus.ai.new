<?php
/**
 * Debug + Fix: Clear stuck sync locks and stale jobs
 */
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX TOOL ===\n";
echo "Time: " . now() . "\n\n";

// Show current auth user info
echo "--- User Info ---\n";
$users = DB::table('users')->get(['id', 'name', 'email']);
foreach ($users as $u) {
    echo "  User #{$u->id}: {$u->name} ({$u->email})\n";
}

// 1. Clear ALL sync locks
echo "\n--- Clearing ALL sync locks ---\n";
$cleared = 0;
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            Cache::forget($key);
            echo "🔓 Cleared: {$key}\n";
            $cleared++;
        }
    }
    // Also clear custom box locks
    $settings = DB::table('users')->where('id', $user->id)->value('settings');
    if ($settings) {
        $settings = json_decode($settings, true);
        $boxes = $settings['keywords_custom_boxes'] ?? [];
        foreach ($boxes as $box) {
            $boxId = $box['id'] ?? '';
            if (empty($boxId)) continue;
            foreach (['ar', 'en'] as $lang) {
                $key = "sync_lock_{$user->id}_{$lang}_{$boxId}";
                if (Cache::has($key)) {
                    Cache::forget($key);
                    echo "🔓 Cleared: {$key}\n";
                    $cleared++;
                }
            }
        }
    }
}
echo "Total locks cleared: {$cleared}\n";

// 2. Clear stale SyncKeywordsJob entries from the queue
echo "\n--- Clearing stale SyncKeywordsJob from queue ---\n";
$deleted = DB::table('jobs')
    ->where('payload', 'like', '%SyncKeywordsJob%')
    ->delete();
echo "Deleted {$deleted} stale job(s) from queue.\n";

// 3. Verify locks are cleared
echo "\n--- Verification ---\n";
$stillLocked = false;
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            echo "⚠️ Still locked: {$key}\n";
            $stillLocked = true;
        }
    }
}
if (!$stillLocked) {
    echo "✅ All sync locks cleared successfully!\n";
}

// 4. Show remaining jobs
$remainingJobs = DB::table('jobs')->count();
echo "Remaining jobs in queue: {$remainingJobs}\n";

// 5. Show keyword counts
echo "\n--- Keyword Counts ---\n";
$kwCounts = DB::table('keywords')
    ->select('user_id', 'lang', 'category', DB::raw('count(*) as cnt'), DB::raw('MAX(created_at) as latest'))
    ->groupBy('user_id', 'lang', 'category')
    ->get();
foreach ($kwCounts as $kw) {
    echo "  User #{$kw->user_id} | {$kw->lang} | {$kw->category} | Count: {$kw->cnt} | Latest: {$kw->latest}\n";
}

echo "\n✅ Fix complete! You can now try syncing again.\n";
echo "</pre>";
