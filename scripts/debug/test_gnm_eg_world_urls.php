<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

$country = 'EG';
$topic = 'WORLD';
$lang = 'ar';

$urls = [
    'topic_encoded' => GoogleNewsRss::topicUrl('CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtRnlHZ0pGUnlnQVAB', $country, $lang),
    'section' => GoogleNewsRss::sectionUrl($topic, $country, $lang),
    'top' => GoogleNewsRss::feedUrl($country, $lang),
];

foreach ($urls as $label => $url) {
    $response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
    $xml = @simplexml_load_string($response->body());
    $eg = 0;
    $total = 0;
    foreach ($xml->channel->item ?? [] as $item) {
        $m = GoogleNewsRss::mapRssItem($item);
        if (!$m) continue;
        $total++;
        $hay = strtolower(($m['source_url'] ?? '').' '.($m['source'] ?? '').' '.($m['link'] ?? ''));
        if (str_contains($hay, 'youm7') || str_contains($hay, '.eg') || str_contains($hay, 'almasry') || str_contains($hay, 'masrawy')) {
            $eg++;
        }
    }
    echo "{$label}: items={$total} eg_like={$eg}\n";
}
