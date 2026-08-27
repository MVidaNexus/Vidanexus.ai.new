<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\GoogleNewsRss;
use App\Support\CountryRegistry;
use Illuminate\Support\Facades\Http;

echo "=== TESTING DIRECT GOOGLE NEWS OFFICIAL SOURCE PULL ===\n";

$tests = [
    ['country' => 'EG', 'topic' => 'TECHNOLOGY', 'lang' => 'ar'],
    ['country' => 'EG', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'EG', 'topic' => 'SPORTS',     'lang' => 'ar'],
    ['country' => 'SA', 'topic' => 'NATION',     'lang' => 'ar'],
    ['country' => 'SA', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'AE', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'US', 'topic' => 'TECHNOLOGY', 'lang' => 'en'],
    ['country' => 'US', 'topic' => 'BUSINESS',   'lang' => 'en'],
];

foreach ($tests as $t) {
    $c = $t['country'];
    $topic = $t['topic'];
    $lang = $t['lang'];

    $url = GoogleNewsRss::sectionUrl($topic, $c, $lang);
    $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(5)->get($url);
    $xml = @simplexml_load_string($res->body());

    $items = [];
    if ($xml && isset($xml->channel->item)) {
        foreach ($xml->channel->item as $item) {
            $mapped = GoogleNewsRss::mapRssItem($item);
            if ($mapped) {
                $items[] = $mapped;
            }
        }
    }

    echo "\n----------------------------------------------------\n";
    echo "🌍 [{$c}] - Topic: [{$topic}] - Lang: [{$lang}]\n";
    echo "🔗 URL: {$url}\n";
    echo "📰 Direct Google News Articles: " . count($items) . "\n";
    echo "----------------------------------------------------\n";

    foreach (array_slice($items, 0, 3) as $idx => $it) {
        $num = $idx + 1;
        echo "  {$num}. [{$it['source']}] {$it['title']} ({$it['pubDate']})\n";
    }
}
