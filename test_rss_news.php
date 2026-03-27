<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$urls = [
    "General Normal" => "https://news.google.com/rss?hl=ar&gl=EG&ceid=EG:ar",
    "Tech Normal" => "https://news.google.com/rss/headlines/section/topic/TECHNOLOGY?hl=ar&gl=EG&ceid=EG:ar",
    "General Search When" => "https://news.google.com/rss/search?q=when:1d&hl=ar&gl=EG&ceid=EG:ar",
    "General Search Query" => "https://news.google.com/rss/search?q=أخبار+when:12h&hl=ar&gl=EG&ceid=EG:ar",
];

foreach ($urls as $label => $url) {
    echo "--- $label ---\n";
    $response = Http::get($url);
    if ($response->successful()) {
        $xml = @simplexml_load_string($response->body());
        if ($xml && isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                echo (string) $item->title . " | " . (string) $item->pubDate . "\n";
                $count++;
                if ($count >= 3) break;
            }
        } else {
            echo "Failed to parse XML or empty.\n";
        }
    } else {
        echo "HTTP Request Failed.\n";
    }
    echo "\n";
}
