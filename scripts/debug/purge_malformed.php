<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Delete any keywords ending with prepositions, colons, dots, or dashes
$badKeywords = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->get()->filter(function($k) {
    $text = trim($k->keyword);
    if (preg_match('/(\s+(في|من|على|إلى|عن|مع|بين|بخصوص|حول|بسبب|أن|إن|أو|و|the|in|on|at|to|for|with|by|from|about)\s*|[.:|–—\-…]+)$/ui', $text)) {
        return true;
    }
    if (mb_strlen($text, 'UTF-8') < 8 || count(explode(' ', $text)) < 2) {
        return true;
    }
    return false;
});

$deletedBad = 0;
foreach ($badKeywords as $bk) {
    $bk->delete();
    $deletedBad++;
}

// 2. Filter near-duplicates
$kws = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->where('category', 'Target')->where('lang', 'ar')->get();
$service = app(Modules\AIKeywordRadar\Services\KeywordService::class);

$arr = $kws->map(function($k) {
    return ['id' => $k->id, 'text' => $k->keyword, 'headline_title' => $k->headline_title];
})->toArray();

$kept = $service->filterSimilarKeywords($arr, 0.50, 1);
$keptIds = array_column($kept, 'id');

$deletedDuplicates = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)
    ->where('category', 'Target')
    ->where('lang', 'ar')
    ->whereNotIn('id', $keptIds)
    ->delete();

$remaining = Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->where('category', 'Target')->where('lang', 'ar')->count();

echo "Cleaned malformed: {$deletedBad}, Cleaned duplicate angles: {$deletedDuplicates}, Remaining distinct keywords: {$remaining}\n";
