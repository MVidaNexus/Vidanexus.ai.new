<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kws = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->where('category', 'Target')->where('lang', 'ar')->get();
$service = app(Modules\AIKeywordRadar\Services\KeywordService::class);

$arr = $kws->map(function($k) {
    return ['id' => $k->id, 'text' => $k->keyword, 'headline_title' => $k->headline_title];
})->toArray();

$kept = $service->filterSimilarKeywords($arr, 0.55, 1);
$keptIds = array_column($kept, 'id');

$deleted = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->where('category', 'Target')->where('lang', 'ar')->whereNotIn('id', $keptIds)->delete();

echo "Deleted redundant duplicates: " . $deleted . ", Remaining unique diverse keywords: " . count($keptIds) . "\n";
