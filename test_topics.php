<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$topicsMap = [
    'GENERAL' => 'أخبار اليوم',
    'WORLD' => 'أخبار عالمية',
    'NATION' => 'أخبار محلية',
    'BUSINESS' => 'أعمال واقتصاد',
    'TECHNOLOGY' => 'تكنولوجيا وتقنية',
    'ENTERTAINMENT' => 'فن وترفيه',
    'SPORTS' => 'رياضة',
    'SCIENCE' => 'علوم وفضاء',
    'HEALTH' => 'صحة وطب',
];

foreach ($topicsMap as $key => $term) {
    $url = "https://news.google.com/rss/search?q=" . urlencode($term . " when:6h") . "&hl=ar&gl=EG&ceid=EG:ar";
    
    echo "--- $key ($term) ---\n";
    $response = Http::get($url);
    if ($response->successful()) {
        $xml = @simplexml_load_string($response->body());
        if ($xml && isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                echo (string) $item->title . " | " . (string) $item->pubDate . "\n";
                $count++;
                if ($count >= 2) break;
            }
        } else {
            echo "Failed or empty.\n";
        }
    } else {
        echo "HTTP Request Failed.\n";
    }
    echo "\n";
}
