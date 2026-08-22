<?php

require __DIR__.'/../bootstrap.php';

use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use Modules\AIKeywordRadar\Support\KeywordPayload;

set_time_limit(300);
$userId = (int) ($argv[1] ?? 1);

$before = Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar')->count();
$beforeRecent = Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar');
KeywordPayload::applyRetentionScope($beforeRecent);
$beforeRecentCount = $beforeRecent->count();

echo "Before: total={$before}, within retention={$beforeRecentCount}\n";
echo "Starting sync (24h)...\n";

$result = app(KeywordService::class)->syncKeywords(500, 'ar', $userId, '24h');
print_r($result);

$afterRecent = Keyword::where('user_id', $userId)->where('category', 'Target')->where('lang', 'ar');
KeywordPayload::applyRetentionScope($afterRecent);
$afterRecentCount = $afterRecent->count();

echo "After: within retention={$afterRecentCount}\n";
