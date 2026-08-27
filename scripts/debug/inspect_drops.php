<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\GlobalNewsMonitor\Services\NewsMonitorService;
use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$service = app(NewsMonitorService::class);

echo "=== INSPECTING FILTER DROPS FOR [EG] [TECHNOLOGY] ===\n";

$u2 = GoogleNewsRss::sectionUrl('TECHNOLOGY', 'EG', 'ar');
$res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($u2);
$xml = @simplexml_load_string($res->body());

$total = 0;
$droppedCountry = 0;
$droppedTopic = 0;
$droppedAge = 0;
$passed = 0;

$reflector = new ReflectionClass($service);
$mCountry = $reflector->getMethod('articleMatchesCountry');
$mCountry->setAccessible(true);
$mTopic = $reflector->getMethod('articleMatchesTopic');
$mTopic->setAccessible(true);

foreach ($xml->channel->item as $item) {
    $total++;
    $mapped = GoogleNewsRss::mapRssItem($item);
    if (!$mapped) continue;

    $cOk = $mCountry->invoke($service, $mapped, 'EG');
    $tOk = $mTopic->invoke($service, $mapped, 'TECHNOLOGY', 'ar');

    $pubTime = strtotime($mapped['pubDate']);
    $ageHours = (time() - $pubTime) / 3600;

    echo "Article #{$total}: [{$mapped['source']}] {$mapped['title']}\n";
    echo "   -> Country OK: " . ($cOk ? 'YES' : 'NO') . " | Topic OK: " . ($tOk ? 'YES' : 'NO') . " | Age: " . round($ageHours, 1) . "h\n";

    if (!$cOk) $droppedCountry++;
    if (!$tOk) $droppedTopic++;
    if ($ageHours > 24) $droppedAge++;
    if ($cOk && $tOk && $ageHours <= 24) $passed++;
}

echo "\nSummary for Section URL:\n";
echo "Total: {$total}\n";
echo "Dropped by Country: {$droppedCountry}\n";
echo "Dropped by Topic: {$droppedTopic}\n";
echo "Dropped by Age (>24h): {$droppedAge}\n";
echo "Passed: {$passed}\n";
