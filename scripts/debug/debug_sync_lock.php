<?php

/**
 * Inspect sync locks, jobs, failed_jobs, and log tails (read-only).
 * Run: php scripts/debug/debug_sync_lock.php
 */
require __DIR__.'/../bootstrap.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC LOCK DEBUGGER ===\n";
echo 'Time: '.now()."\n\n";

echo 'Cache driver: '.config('cache.default')."\n\n";

$users = DB::table('users')->get(['id', 'name']);
echo "--- Sync locks (Cache facade) ---\n";
$foundLocks = [];
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            $foundLocks[] = $key;
            echo "🔒 LOCKED: {$key} (User: {$user->name})\n";
        }
    }
    $settings = DB::table('users')->where('id', $user->id)->value('settings');
    if ($settings) {
        $settings = json_decode($settings, true);
        $boxes = $settings['keywords_custom_boxes'] ?? [];
        foreach ($boxes as $box) {
            $boxId = $box['id'] ?? '';
            if (empty($boxId)) {
                continue;
            }
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
    echo "✅ No sync locks in current cache driver.\n";
}

echo "\n--- Jobs queue ---\n";
try {
    $jobs = DB::table('jobs')->get();
    echo 'Pending jobs: '.$jobs->count()."\n";
    foreach ($jobs as $job) {
        $payload = json_decode($job->payload, true);
        $name = is_array($payload) ? ($payload['displayName'] ?? 'unknown') : 'unknown';
        echo "  Job #{$job->id} | Queue: {$job->queue} | Attempts: {$job->attempts} | Class: {$name}\n";
    }
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\n--- Failed jobs (latest 5) ---\n";
try {
    $failed = DB::table('failed_jobs')->orderBy('id', 'desc')->limit(5)->get();
    foreach ($failed as $fj) {
        echo "  Failed #{$fj->id} | {$fj->failed_at} | ".substr((string) $fj->exception, 0, 300)."\n\n";
    }
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}

echo "\n--- queue-worker.log tail ---\n";
$logFile = storage_path('logs/queue-worker.log');
if (file_exists($logFile)) {
    $lines = file($logFile) ?: [];
    echo implode('', array_slice($lines, -30));
} else {
    echo "No file: {$logFile}\n";
}

echo "\n--- laravel.log (Keyword Radar lines, last 20) ---\n";
$laravelLog = storage_path('logs/laravel.log');
if (file_exists($laravelLog)) {
    $lines = file($laravelLog) ?: [];
    $relevant = [];
    foreach ($lines as $line) {
        if (stripos($line, 'SyncKeywordsJob') !== false || stripos($line, 'Keyword Radar') !== false) {
            $relevant[] = $line;
        }
    }
    echo implode('', array_slice($relevant, -20));
} else {
    echo "No laravel.log\n";
}

echo "\n</pre>\n";
