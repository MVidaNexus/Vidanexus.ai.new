<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$url = GoogleNewsRss::feedUrl('EG', 'ar');
$response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$xml = @simplexml_load_string($response->body());
$cutoff = time() - 43200;
$noDate = 0;
$old = 0;
$fresh = 0;
foreach ($xml->channel->item ?? [] as $item) {
    $m = GoogleNewsRss::mapRssItem($item);
    if (!$m) continue;
    $pubTime = strtotime($m['pubDate']);
    if (!$pubTime) {
        $noDate++;
        echo 'NO_DATE: '.$m['title'].' | pubDate='.$m['pubDate'].PHP_EOL;
        continue;
    }
    if ($pubTime < $cutoff) {
        $old++;
    } else {
        $fresh++;
    }
}
echo "fresh={$fresh} old={$old} no_date={$noDate}".PHP_EOL;
