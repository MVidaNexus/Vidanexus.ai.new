<?php
/**
 * Debug script to check sync lock state, queue jobs, and fix stuck locks
 */
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC LOCK DEBUGGER ===\n";
echo "Time: " . now() . "\n\n";

// Check cache driver
echo "Cache driver: " . config('cache.default') . "\n\n";

// Get all users
$users = DB::table('users')->get(['id', 'name']);
echo "--- Checking sync locks for all users ---\n";
$foundLocks = [];
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            $foundLocks[] = $key;
            echo "🔒 LOCKED: {$key} (User: {$user->name})\n";
        }
    }
    // Also check custom boxes
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
                    $foundLocks[] = $key;
                    echo "🔒 LOCKED: {$key} (User: {$user->name}, Box: {$boxId})\n";
                }
            }
        }
    }
}

if (empty($foundLocks)) {
    echo "✅ No sync locks found!\n";
}

echo "\n--- Jobs Queue ---\n";
try {
    $jobs = DB::table('jobs')->get();
    echo "Pending jobs: " . $jobs->count() . "\n";
    foreach ($jobs as $job) {
        $payload = json_decode($job->payload, true);
        echo "  Job #{$job->id} | Queue: {$job->queue} | Attempts: {$job->attempts} | Created: " . date('Y-m-d H:i:s', $job->created_at) . " | Class: " . ($payload['displayName'] ?? 'unknown') . "\n";
    }
} catch (Exception $e) {
    echo "Error querying jobs: " . $e->getMessage() . "\n";
}

echo "\n--- Failed Jobs ---\n";
try {
    $failed = DB::table('failed_jobs')->orderBy('id', 'desc')->limit(5)->get();
    echo "Recent failed jobs: " . $failed->count() . "\n";
    foreach ($failed as $fj) {
        echo "  Failed #{$fj->id} | {$fj->failed_at} | Exception: " . substr($fj->exception, 0, 300) . "\n\n";
    }
} catch (Exception $e) {
    echo "Error querying failed_jobs: " . $e->getMessage() . "\n";
}

// Check queue worker log
echo "\n--- Recent Queue Worker Logs ---\n";
$logFile = storage_path('logs/queue-worker.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $tail = array_slice($lines, -30);
    echo implode("", $tail);
} else {
    echo "No queue worker log found at: {$logFile}\n";
}

echo "\n--- Recent Laravel Logs (SyncKeywordsJob) ---\n";
$laravelLog = storage_path('logs/laravel.log');
if (file_exists($laravelLog)) {
    $lines = file($laravelLog);
    $relevant = [];
    foreach ($lines as $line) {
        if (stripos($line, 'SyncKeywordsJob') !== false || stripos($line, 'Keyword Radar') !== false) {
            $relevant[] = $line;
        }
    }
    $tail = array_slice($relevant, -20);
    echo implode("", $tail);
} else {
    echo "No laravel.log found.\n";
}

echo "\n</pre>";
