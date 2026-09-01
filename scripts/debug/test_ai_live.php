<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
echo "Starting test extraction...\n";
$res = $service->getTargetKeywordsFromCompetitors('ar', 1, microtime(true), '60m');
echo "Extracted keywords: " . count($res['keywords']) . " from " . ($res['headlines_count'] ?? 0) . " headlines\n";
echo json_encode(array_slice($res['keywords'], 0, 10), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
