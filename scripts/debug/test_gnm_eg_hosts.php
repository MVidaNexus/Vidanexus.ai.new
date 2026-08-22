<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$url = GoogleNewsRss::feedUrl('EG', 'ar');
$response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
$xml = @simplexml_load_string($response->body());
$hosts = [];
foreach ($xml->channel->item ?? [] as $item) {
    $m = GoogleNewsRss::mapRssItem($item);
    if (!$m) continue;
    $host = parse_url($m['link'], PHP_URL_HOST) ?: '';
    $hosts[$host] = ($hosts[$host] ?? 0) + 1;
}
arsort($hosts);
echo "EG top stories host distribution:\n";
foreach (array_slice($hosts, 0, 15, true) as $h => $c) {
    echo "  {$c}x {$h}\n";
}
