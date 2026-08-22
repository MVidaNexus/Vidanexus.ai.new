<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$url = GoogleNewsRss::sectionUrl('HEALTH', 'EG', 'ar');
$response = Http::timeout(15)->withHeaders([
    'User-Agent' => 'Mozilla/5.0 (compatible; VidaNexus/1.0)',
])->get($url);

echo 'URL: '.$url.PHP_EOL;
echo 'HTTP: '.$response->status().PHP_EOL;

$xml = @simplexml_load_string($response->body());
$raw = 0;
$mapped = 0;
$sources = [];
if ($xml && isset($xml->channel->item)) {
    foreach ($xml->channel->item as $item) {
        $raw++;
        $m = GoogleNewsRss::mapRssItem($item);
        if ($m !== null) {
            $mapped++;
        }
        if ($raw <= 5) {
            $sources[] = ($m['source'] ?? '').' | '.($m['source_url'] ?? '');
        }
    }
}

echo "raw_items={$raw} mapped={$mapped}".PHP_EOL;
foreach ($sources as $s) {
    echo "  - {$s}".PHP_EOL;
}

$service = app(\Modules\GlobalNewsMonitor\Services\NewsMonitorService::class);
$filtered = $service->fetchGoogleNews('EG', 'HEALTH', 'ar', '12h', '');
echo 'after full pipeline: '.count($filtered).PHP_EOL;
