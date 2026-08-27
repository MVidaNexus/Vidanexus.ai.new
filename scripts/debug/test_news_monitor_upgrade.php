<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\GlobalNewsMonitor\Services\NewsMonitorService;

echo "========================================================\n";
echo "🔬 GLOBAL NEWS MONITOR UPGRADE VERIFICATION SUITE\n";
echo "========================================================\n\n";

$service = app(NewsMonitorService::class);

$testMatrix = [
    ['country' => 'EG', 'topic' => 'TECHNOLOGY', 'lang' => 'ar', 'desc' => 'Egypt Technology (Should be 100% tech, NO weather/crime)'],
    ['country' => 'EG', 'topic' => 'BUSINESS',   'lang' => 'ar', 'desc' => 'Egypt Business / Economy'],
    ['country' => 'EG', 'topic' => 'SPORTS',     'lang' => 'ar', 'desc' => 'Egypt Sports'],
    ['country' => 'SA', 'topic' => 'NATION',     'lang' => 'ar', 'desc' => 'Saudi Arabia Nation (Should be Saudi news, NO Egyptian local news)'],
    ['country' => 'SA', 'topic' => 'BUSINESS',   'lang' => 'ar', 'desc' => 'Saudi Arabia Business & Energy'],
    ['country' => 'AE', 'topic' => 'BUSINESS',   'lang' => 'ar', 'desc' => 'UAE Business & Economy'],
    ['country' => 'US', 'topic' => 'TECHNOLOGY', 'lang' => 'en', 'desc' => 'US Technology (English)'],
];

foreach ($testMatrix as $test) {
    $c = $test['country'];
    $t = $test['topic'];
    $lang = $test['lang'];
    $desc = $test['desc'];

    echo "--------------------------------------------------------\n";
    echo "🧪 Testing: {$c} | Topic: {$t} [{$lang}]\n";
    echo "📋 Description: {$desc}\n";
    echo "--------------------------------------------------------\n";

    $start = microtime(true);
    $articles = $service->fetchGoogleNews($c, $t, $lang, '24h');
    $elapsed = round(microtime(true) - $start, 2);

    $count = count($articles);
    echo "  ⏱️ Fetch Time: {$elapsed}s\n";
    echo "  📰 Total Articles Returned: {$count}\n";

    if ($count > 0) {
        $sample = array_slice($articles, 0, 4);
        echo "  📄 Sample Headings:\n";
        foreach ($sample as $s) {
            $src = $s['source'] ?? 'Unknown';
            $title = $s['title'] ?? '';
            $timeAgo = $s['time_ago'] ?? 'N/A';
            $score = $s['seo_score'] ?? 0;
            echo "     • [{$src}] {$title} ({$timeAgo}) [SEO: {$score}%]\n";
        }
    } else {
        echo "  ⚠️ Warning: No articles returned!\n";
    }
    echo "\n";
}

echo "========================================================\n";
echo "🎉 GLOBAL NEWS MONITOR TEST COMPLETED!\n";
echo "========================================================\n";
