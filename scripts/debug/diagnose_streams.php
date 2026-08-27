<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;

echo "=== DIAGNOSING GOOGLE NEWS STREAMS ===\n";

$tests = [
    ['country' => 'EG', 'topic' => 'TECHNOLOGY', 'lang' => 'ar', 'topicId' => 'CAAqKAgKIiJDQkFTRXdvSkwyMHZNR1ptZHpWbUVnSmhjaG9DUlVjb0FBUAE'],
    ['country' => 'SA', 'topic' => 'BUSINESS',   'lang' => 'ar', 'topicId' => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx6TVdZU0FtRnlHZ0pGUnlnQVAB'],
    ['country' => 'EG', 'topic' => 'SPORTS',     'lang' => 'ar', 'topicId' => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRFp1ZEdvU0FtRnlHZ0pGUnlnQVAB'],
];

foreach ($tests as $t) {
    $c = $t['country'];
    $topic = $t['topic'];
    $lang = $t['lang'];
    $tid = $t['topicId'];

    $u1 = GoogleNewsRss::topicUrl($tid, $c, $lang);
    $u2 = GoogleNewsRss::sectionUrl($topic, $c, $lang);
    $u3 = GoogleNewsRss::searchUrl("{$topic} when:24h", $c, $lang);

    echo "\n--- [{$c}] [{$topic}] ---\n";
    foreach (['TopicURL' => $u1, 'SectionURL' => $u2, 'SearchURL' => $u3] as $name => $u) {
        $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($u);
        $xml = @simplexml_load_string($res->body());
        $count = $xml && isset($xml->channel->item) ? count($xml->channel->item) : 0;
        echo "  {$name}: {$count} items ({$u})\n";
    }
}
