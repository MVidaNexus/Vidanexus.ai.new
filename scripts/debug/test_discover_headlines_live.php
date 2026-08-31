<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\DiscoverHeadlines\Services\HeadlineService;
use App\Models\Setting;
use App\Models\User;

$service = app(HeadlineService::class);
$user = User::first();
if (!$user) {
    $user = User::factory()->create();
}

echo "=== TESTING DISCOVER HEADLINES PROMPT GENERATION ===\n";

$tests = [
    'سعر الذهب اليوم',
    'محمد صلاح',
    'الذكاء الاصطناعي',
];

foreach ($tests as $kw) {
    echo "\n----------------------------------------------------\n";
    echo "🔍 Topic: {$kw}\n";
    echo "----------------------------------------------------\n";

    $res = $service->generate($user->id, [
        'keyword' => $kw,
        'type' => 'keyword',
        'country' => 'EG',
        'variants' => 5,
    ]);

    if (!empty($res['scored'])) {
        foreach ($res['scored'] as $idx => $item) {
            $num = $idx + 1;
            $hl = $item['headline'];
            $score = $item['score'];
            $len = mb_strlen($hl);
            echo "  {$num}. [Score: {$score}% | {$len} chars] {$hl}\n";
            if (!empty($item['visual_concepts'])) {
                echo "     📸 Visual 1: " . ($item['visual_concepts'][0]['description'] ?? '') . "\n";
            }
        }
    } else {
        echo "  ⚠️ No scored headlines returned.\n";
        echo "  Raw: " . json_encode($res) . "\n";
    }
}
