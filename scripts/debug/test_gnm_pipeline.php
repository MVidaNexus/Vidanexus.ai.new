<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;

$service = app(\Modules\GlobalNewsMonitor\Services\NewsMonitorService::class);
$ref = new ReflectionClass($service);
$fetchFromUrl = $ref->getMethod('fetchFromUrl');
$fetchFromUrl->setAccessible(true);
$applyFilter = $ref->getMethod('applyRelevanceFilter');
$applyFilter->setAccessible(true);

$country = 'EG';
$topic = 'WORLD';
$lang = 'ar';

$raw = [];
$topUrl = GoogleNewsRss::feedUrl($country, $lang);
$raw = $fetchFromUrl->invoke($service, $topUrl, $raw);
echo 'raw after top fetch: '.count($raw).PHP_EOL;

$topRelevant = $applyFilter->invoke($service, array_values($raw), $country, $topic, $lang);
echo 'topRelevant: '.count($topRelevant).PHP_EOL;

$cutoff = time() - min(43200, 172800);
$fresh = 0;
foreach ($topRelevant as $item) {
    $pubTime = strtotime($item['pubDate']);
    echo 'title='.mb_substr($item['title'], 0, 40).' pubDate='.$item['pubDate'].' ts='.$pubTime.' fresh='.($pubTime && $pubTime >= $cutoff ? 'yes' : 'no').PHP_EOL;
    if ($pubTime && $pubTime >= $cutoff) {
        $fresh++;
    }
}
echo "fresh after cutoff: {$fresh}".PHP_EOL;
