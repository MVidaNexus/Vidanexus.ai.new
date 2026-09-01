<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t0 = microtime(true);
$service = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
$res = $service->syncKeywords(500, 'ar', 1, '60m', null, 'smart');
$dur = round(microtime(true) - $t0, 2);

echo "Sync finished in {$dur}s!\n";
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
$cnt = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', 1)->where('category', 'Target')->where('lang', 'ar')->count();
echo "Keywords in Target/ar for User 1: {$cnt}\n";
