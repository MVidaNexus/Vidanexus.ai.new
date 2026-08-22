<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$service = app(\Modules\GlobalNewsMonitor\Services\NewsMonitorService::class);
$ref = new ReflectionClass($service);
$matchesCountry = $ref->getMethod('articleMatchesCountry');
$matchesCountry->setAccessible(true);
$matchesTopic = $ref->getMethod('articleMatchesTopic');
$matchesTopic->setAccessible(true);

$url = GoogleNewsRss::feedUrl('EG', 'ar');
$response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$xml = @simplexml_load_string($response->body());
$cutoff = time() - 43200;

$pass = 0;
foreach ($xml->channel->item ?? [] as $item) {
    $m = GoogleNewsRss::mapRssItem($item);
    if (!$m) continue;
    $pubTime = strtotime($m['pubDate']);
    if (!$pubTime || $pubTime < $cutoff) continue;

    $cOk = $matchesCountry->invoke($service, $m, 'EG');
    $tOk = $matchesTopic->invoke($service, $m, 'WORLD', 'ar');
    if (!$cOk || !$tOk) {
        echo ($cOk ? 'C+' : 'C-').' '.($tOk ? 'T+' : 'T-').' | '.$m['source'].' | '.parse_url($m['link'], PHP_URL_HOST).PHP_EOL;
    } else {
        $pass++;
    }
}

echo "pass_both={$pass}\n";
