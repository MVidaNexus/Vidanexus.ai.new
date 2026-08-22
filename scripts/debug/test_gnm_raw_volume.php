<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

foreach (['EG', 'SA', 'US'] as $country) {
    $lang = CountryRegistry::langFor($country);
    $url = GoogleNewsRss::feedUrl($country, $lang);
    $response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
    $xml = @simplexml_load_string($response->body());
    $n = ($xml && isset($xml->channel->item)) ? count($xml->channel->item) : 0;
    echo "top stories {$country}: HTTP {$response->status()} items={$n}\n";
}
