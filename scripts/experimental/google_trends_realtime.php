<?php

/**
 * Fetch Google Trends realtime JSON (EG). No Laravel bootstrap.
 * Run: php scripts/experimental/google_trends_realtime.php
 */
$url = 'https://trends.google.com/trends/api/realtimetrends?hl=ar&tz=-120&cat=all&fi=0&fs=0&geo=EG&ri=300&rs=20&sort=0';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
$json = curl_exec($ch);
curl_close($ch);

if ($json === false) {
    echo "curl failed\n";
    exit(1);
}

if (strpos($json, ")]}',\n") !== false) {
    $json = str_replace(")]}',\n", '', $json);
}

$data = json_decode($json, true);
if (isset($data['storySummaries']['trendingStories'])) {
    echo 'Realtime stories: '.count($data['storySummaries']['trendingStories'])."\n";
} else {
    echo "No storySummaries. Snippet: ".substr($json, 0, 500)."\n";
}
