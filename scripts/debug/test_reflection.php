<?php

/**
 * Verify KeywordService::extractKeywordsWithAI exists via Reflection.
 * Run: php scripts/debug/test_reflection.php
 */
require __DIR__.'/../bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');

$keywordService = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
$reflection = new ReflectionClass(get_class($keywordService));
if ($reflection->hasMethod('extractKeywordsWithAI')) {
    echo "OK: extractKeywordsWithAI exists.\n";
    $method = $reflection->getMethod('extractKeywordsWithAI');
    $method->setAccessible(true);
    echo "OK: method accessible.\n";
} else {
    echo "FAIL: method missing.\n";
}
