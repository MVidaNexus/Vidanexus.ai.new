<?php

/**
 * Internal HTTP GET to Global News Monitor dashboard (timing + HTML substring checks).
 * Run: php scripts/debug/test_radar.php
 */
require __DIR__.'/../bootstrap.php';

$start = microtime(true);
$user = App\Models\User::first();
if (! $user) {
    exit("No users in database.\n");
}
auth()->login($user);

$request = Illuminate\Http\Request::create('/dashboard/global-news-monitor', 'GET');
$httpKernel = app()->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);
$time = microtime(true) - $start;

echo "Page load: {$time}s\n";
echo 'Status: '.$response->getStatusCode()."\n";

$content = $response->getContent();
echo "\n--- Feature checks ---\n";
echo 'opportunity markers: '.(str_contains($content, 'HIGH OPPORTUNITY') || str_contains($content, 'MODERATE') ? 'yes' : 'no')."\n";
echo 'trend_direction: '.(str_contains($content, 'Surging') || str_contains($content, 'Rising') || str_contains($content, 'Stable') ? 'yes' : 'no')."\n";
echo 'AI Deep Analysis: '.(str_contains($content, 'AI Deep Analysis') ? 'yes' : 'no')."\n";
