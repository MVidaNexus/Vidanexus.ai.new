<?php

/**
 * POST dispatch to dashboard AI Keyword Radar sync route as user #1 (timing).
 * Run: php scripts/debug/verify_speed.php
 */
require __DIR__.'/../bootstrap.php';

use Illuminate\Support\Facades\Route;

header('Content-Type: text/plain; charset=utf-8');

echo "=== SPEED CHECK ===\n";

$user = \App\Models\User::find(1);
if (! $user) {
    exit("User #1 not found.\n");
}
auth()->login($user);

$request = Illuminate\Http\Request::create('/dashboard/ai-keyword-radar/sync', 'POST', [
    'lang' => 'ar',
    'time_filter' => '24h',
]);

$startTime = microtime(true);
$response = Route::dispatch($request);
$elapsed = round(microtime(true) - $startTime, 2);

echo 'Body (truncated): '.substr($response->getContent(), 0, 500)."\n";
echo "Duration: {$elapsed}s\n";

if ($elapsed < 120) {
    echo "\nOK: under 2 minutes.\n";
} else {
    echo "\nSlow: over 2 minutes — check logs.\n";
}
