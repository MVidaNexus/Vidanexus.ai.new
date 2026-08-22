<?php

/**
 * Probe Google News RSS by topic (Http client).
 * Run: php scripts/debug/test_topics.php
 */
require __DIR__.'/../bootstrap.php';

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
    $url = 'https://news.google.com/rss/search?q='.urlencode($term.' when:6h').'&hl=ar&gl=EG&ceid=EG:ar';

    echo "--- {$key} ({$term}) ---\n";
    $response = Http::get($url);
    if ($response->successful()) {
        $xml = @simplexml_load_string($response->body());
        if ($xml && isset($xml->channel->item)) {
            $count = 0;
            foreach ($xml->channel->item as $item) {
                echo (string) $item->title.' | '.(string) $item->pubDate."\n";
                $count++;
                if ($count >= 2) {
                    break;
                }
            }
        } else {
            echo "Empty or invalid XML.\n";
        }
    } else {
        echo "HTTP failed.\n";
    }
    echo "\n";
}
