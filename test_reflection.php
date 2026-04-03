<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
$keywordService = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
$reflection = new ReflectionClass(get_class($keywordService));
if ($reflection->hasMethod('extractKeywordsWithAI')) {
    echo "✅ Reflection works: Method exists.\n";
    $method = $reflection->getMethod('extractKeywordsWithAI');
    $method->setAccessible(true);
    echo "✅ Method accessibility set to true.\n";
} else {
    echo "❌ Method NOT found.\n";
}
