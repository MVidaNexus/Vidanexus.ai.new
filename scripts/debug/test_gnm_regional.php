<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$service = app(\Modules\GlobalNewsMonitor\Services\NewsMonitorService::class);
$ref = new ReflectionClass($service);
$collect = $ref->getMethod('collectFreshArticles');
$collect->setAccessible(true);
$apply = $ref->getMethod('applyRelevanceFilter');
$apply->setAccessible(true);
$fetch = $ref->getMethod('fetchFromUrl');
$fetch->setAccessible(true);

$raw = [];
$url = GoogleNewsRss::topicUrl('CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtRnlHZ0pGUnlnQVAB', 'EG', 'ar');
$raw = $fetch->invoke($service, $url, $raw);
$regional = $apply->invoke($service, array_values($raw), 'EG', 'WORLD', 'ar', true);
$fresh48 = $collect->invoke($service, $regional, 172800, now()->toIso8601String());
echo 'raw='.count($raw).' regional='.count($regional).' fresh48='.count($fresh48).PHP_EOL;
