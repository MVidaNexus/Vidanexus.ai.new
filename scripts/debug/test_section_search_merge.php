<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

echo "=== TESTING SECTION FEED + TOPIC SEARCH MERGE ===\n";

$topicNames = [
    'TECHNOLOGY'    => ['ar' => 'تكنولوجيا', 'en' => 'Technology'],
    'BUSINESS'      => ['ar' => 'اقتصاد', 'en' => 'Business'],
    'SPORTS'        => ['ar' => 'رياضة', 'en' => 'Sports'],
    'HEALTH'        => ['ar' => 'صحة', 'en' => 'Health'],
    'SCIENCE'       => ['ar' => 'علوم', 'en' => 'Science'],
    'ENTERTAINMENT' => ['ar' => 'فن وترفيه', 'en' => 'Entertainment'],
    'NATION'        => ['ar' => '', 'en' => ''],
    'WORLD'         => ['ar' => 'أخبار العالم', 'en' => 'World'],
];

$tests = [
    ['country' => 'EG', 'topic' => 'TECHNOLOGY', 'lang' => 'ar'],
    ['country' => 'EG', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'EG', 'topic' => 'SPORTS',     'lang' => 'ar'],
    ['country' => 'SA', 'topic' => 'NATION',     'lang' => 'ar'],
    ['country' => 'SA', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'AE', 'topic' => 'BUSINESS',   'lang' => 'ar'],
    ['country' => 'US', 'topic' => 'TECHNOLOGY', 'lang' => 'en'],
];

foreach ($tests as $t) {
    $c = $t['country'];
    $topic = $t['topic'];
    $lang = $t['lang'];
    $tName = $topicNames[$topic][$lang] ?? '';

    $urls = [
        GoogleNewsRss::sectionUrl($topic, $c, $lang),
    ];
    if ($tName !== '') {
        $urls[] = GoogleNewsRss::searchUrl($tName, $c, $lang);
    }

    $raw = [];
    foreach ($urls as $u) {
        $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(5)->get($u);
        $xml = @simplexml_load_string($res->body());
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $mapped = GoogleNewsRss::mapRssItem($item);
                if ($mapped && !isset($raw[$mapped['link']])) {
                    $raw[$mapped['link']] = $mapped;
                }
            }
        }
    }

    echo "🌍 [{$c}] [{$topic}]: Total Articles = " . count($raw) . "\n";
}
