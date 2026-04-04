<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$user = App\Models\User::first();
auth()->login($user);

// Test the Global News Monitor page
$request = Illuminate\Http\Request::create('/dashboard/global-news-monitor', 'GET');
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);
$time = microtime(true) - $start;

echo "✅ Page Load Time: {$time} seconds\n";
echo "✅ Status: " . $response->getStatusCode() . "\n";

$content = $response->getContent();
// Check for new features
echo "\n--- Feature Checks ---\n";
echo "Has 'opportunity_level': " . (str_contains($content, 'HIGH OPPORTUNITY') || str_contains($content, 'MODERATE') ? '✅ YES' : '❌ NO') . "\n";
echo "Has 'trend_direction': " . (str_contains($content, 'Surging') || str_contains($content, 'Rising') || str_contains($content, 'Stable') ? '✅ YES' : '❌ NO') . "\n";
echo "Has 'AI Deep Analysis' button: " . (str_contains($content, 'AI Deep Analysis') ? '✅ YES' : '❌ NO') . "\n";
echo "Has 'Breaking Now' filter: " . (str_contains($content, 'Breaking Now') ? '✅ YES' : '❌ NO') . "\n";
echo "Has 'High Opportunity' filter: " . (str_contains($content, 'High Opportunity') ? '✅ YES' : '❌ NO') . "\n";
echo "Has auto-refresh countdown: " . (str_contains($content, 'autoRefreshCountdown') ? '✅ YES' : '❌ NO') . "\n";
echo "Has scoring breakdown: " . (str_contains($content, 'virality_score') || str_contains($content, 'freshness_score') ? '✅ YES' : '❌ NO') . "\n";
echo "Has stats bar: " . (str_contains($content, 'high opportunity') ? '✅ YES' : '❌ NO') . "\n";

// Count news items
preg_match_all('/HIGH OPPORTUNITY|MODERATE|class="news-card/', $content, $matches);
echo "\n--- News Items ---\n";
echo "Total news cards found in HTML: ~" . count($matches[0]) . "\n";
