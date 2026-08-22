<?php

require __DIR__.'/../bootstrap.php';

use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Support\KeywordPayload;

$userId = (int) ($argv[1] ?? 1);
$retention = KeywordPayload::retentionLimit();

$total = Keyword::where('user_id', $userId)->count();
$arTarget = Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar')->count();
$arRecent = Keyword::where('user_id', $userId)
    ->where('category', 'Target')
    ->where('lang', 'ar')
    ->where(function ($q) use ($retention) {
        $q->where('published_at', '>=', $retention)
            ->orWhere(function ($q2) use ($retention) {
                $q2->whereNull('published_at')->where('created_at', '>=', $retention);
            });
    })
    ->count();

echo "User #{$userId}\n";
echo "Retention since: {$retention}\n";
echo "Total keywords: {$total}\n";
echo "AR Target (all time): {$arTarget}\n";
echo "AR Target (within retention): {$arRecent}\n";

$sample = Keyword::where('user_id', $userId)
    ->where('category', 'Target')
    ->where('lang', 'ar')
    ->orderByDesc('created_at')
    ->first();
if ($sample) {
    echo "Latest: {$sample->keyword} | created={$sample->created_at} | published=".($sample->published_at ?? 'null')."\n";
}
