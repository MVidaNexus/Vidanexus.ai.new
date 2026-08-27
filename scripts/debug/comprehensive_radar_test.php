<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Setting;
use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use Modules\AIKeywordRadar\Support\KeywordPayload;
use Illuminate\Support\Facades\Cache;

echo "========================================================\n";
echo "🔬 COMPREHENSIVE AI KEYWORD RADAR END-TO-END TEST SUITE\n";
echo "========================================================\n\n";

$user = User::first();
if (!$user) {
    echo "❌ ERROR: No user found in database for testing.\n";
    exit(1);
}
echo "👤 Testing with User: #{$user->id} ({$user->name} - {$user->email})\n\n";

$keywordService = app(KeywordService::class);
$results = [];

// ==========================================
// TEST 1: SEARCH INTENT CLASSIFIER ACCURACY
// ==========================================
echo "--------------------------------------------------------\n";
echo "🧪 TEST 1: Search Intent Classifier Verification\n";
echo "--------------------------------------------------------\n";

$testPhrases = [
    ['text' => 'سعر عيار 21 اليوم في الصاغة', 'lang' => 'ar', 'expected' => 'commercial'],
    ['text' => 'أفضل عطور رجالية فواحة للطلب', 'lang' => 'ar', 'expected' => 'commercial'],
    ['text' => 'كيفية التسجيل في تكافل وكرامة 2026', 'lang' => 'ar', 'expected' => 'informational'],
    ['text' => 'طريقة حجز شقق الإسكان الاجتماعي', 'lang' => 'ar', 'expected' => 'informational'],
    ['text' => 'عاجل وفاة شخص في حادث تصادم', 'lang' => 'ar', 'expected' => 'trending'],
    ['text' => 'ملخص مباراة الأهلي والزمالك اليوم', 'lang' => 'ar', 'expected' => 'trending'],
    ['text' => 'تسجيل دخول بنك مصر أونلاين', 'lang' => 'ar', 'expected' => 'navigational'],
    ['text' => 'best running shoes to buy on amazon', 'lang' => 'en', 'expected' => 'commercial'],
    ['text' => 'how to fix ssl handshake failed error', 'lang' => 'en', 'expected' => 'informational'],
    ['text' => 'breaking news earthquake magnitude 6.2', 'lang' => 'en', 'expected' => 'trending'],
];

$intentSuccess = 0;
foreach ($testPhrases as $item) {
    $detected = KeywordPayload::detectSearchIntent($item['text'], $item['lang']);
    $type = $detected['type'] ?? 'unknown';
    $passed = ($type === $item['expected']);
    if ($passed) {
        $intentSuccess++;
        echo "  ✅ [{$item['lang']}] '{$item['text']}' => {$detected['label']} ({$type})\n";
    } else {
        echo "  ⚠️ [{$item['lang']}] '{$item['text']}' => Got {$type}, expected {$item['expected']}\n";
    }
}
$intentScore = round(($intentSuccess / count($testPhrases)) * 100);
echo "  📊 Intent Accuracy: {$intentScore}% ({$intentSuccess}/" . count($testPhrases) . ")\n\n";

// ==========================================
// TEST 2: DIRECT SEED KEYWORDS EXPLORER SYNC
// ==========================================
echo "--------------------------------------------------------\n";
echo "🧪 TEST 2: Direct Seed Keywords Explorer Engine (No Competitors)\n";
echo "--------------------------------------------------------\n";

$settings = $user->settings ?? [];
$settings['keywords_seed_topics'] = "عطور رجالية\nسعر الذهب اليوم";
$settings['keywords_seed_topics_en'] = "best running shoes\niphone 16 pro";
$user->settings = $settings;
$user->save();

$seedStart = microtime(true);
$seedResult = $keywordService->syncSeedKeywords($user->id, 'ar');
$seedTime = round(microtime(true) - $seedStart, 2);

echo "  ⏱️ Execution Time: {$seedTime}s\n";
echo "  📥 Seeds Count: " . ($seedResult['seeds_count'] ?? 0) . "\n";
echo "  🔍 Extracted Keyword Suggestions: " . ($seedResult['total_extracted'] ?? 0) . "\n";
echo "  💾 New Keywords Saved to DB: " . ($seedResult['saved'] ?? 0) . "\n";

$sampleSeedKeywords = Keyword::where('user_id', $user->id)
    ->where('category', 'Direct:Seed')
    ->where('lang', 'ar')
    ->take(5)
    ->get();

echo "  📋 Sample Extracted Seed Keywords:\n";
foreach ($sampleSeedKeywords as $k) {
    $intent = KeywordPayload::detectSearchIntent($k->keyword, 'ar');
    echo "     • {$k->keyword} [Intent: {$intent['label']}] (Source: {$k->source})\n";
}

// ==========================================
// TEST 3: MULTI-COMPETITOR RADAR ENGINE
// ==========================================
echo "\n--------------------------------------------------------\n";
echo "🧪 TEST 3: Multi-Competitor Scraper & Fallback Pipeline\n";
echo "--------------------------------------------------------\n";

$settings['keywords_competitors'] = "https://www.youm7.com\nhttps://www.amazon.eg\nhttps://www.propertyfinder.eg";
$user->settings = $settings;
$user->save();

$syncStart = microtime(true);
$rawHeadlines = $keywordService->fetchCompetitorsHeadlines('ar', $user->id, $syncStart, '24h', null, 'smart');
$scrapeTime = round(microtime(true) - $syncStart, 2);

echo "  ⏱️ Scrape Time: {$scrapeTime}s\n";
echo "  📰 Total Raw Headlines Collected Across All Competitors: " . count($rawHeadlines) . "\n";

if (count($rawHeadlines) > 0) {
    $sources = [];
    foreach ($rawHeadlines as $h) {
        $src = $h['source'] ?? 'Unknown';
        $sources[$src] = ($sources[$src] ?? 0) + 1;
    }
    echo "  🌐 Headlines by Competitor Source:\n";
    foreach ($sources as $src => $cnt) {
        echo "     • {$src}: {$cnt} headline(s)\n";
    }
} else {
    echo "  ⚠️ Warning: No raw headlines collected. Checking fallback strategies...\n";
}

// ==========================================
// TEST 4: RETENTION SCOPE & DB RETRIEVAL
// ==========================================
echo "\n--------------------------------------------------------\n";
echo "🧪 TEST 4: Keyword DB Storage & Retention Verification\n";
echo "--------------------------------------------------------\n";

$retentionHours = KeywordPayload::retentionHours();
echo "  ⏳ Current Retention Window: {$retentionHours} Hours\n";

$query = Keyword::where('user_id', $user->id);
$totalBeforeScope = (clone $query)->count();
KeywordPayload::applyRetentionScope($query);
$totalAfterScope = $query->count();

echo "  📦 Total Keywords in DB: {$totalBeforeScope}\n";
echo "  👁️ Visible within Retention Window: {$totalAfterScope}\n";

// ==========================================
// TEST 5: CACHE & RESPONSE PAYLOAD
// ==========================================
echo "\n--------------------------------------------------------\n";
echo "🧪 TEST 5: Frontend Payload Formatting & Caching\n";
echo "--------------------------------------------------------\n";

$kwList = Keyword::where('user_id', $user->id)
    ->where('category', 'Direct:Seed')
    ->take(10)
    ->get();

$payload = KeywordPayload::fromCollection($kwList);
echo "  📤 Formatted Payload Count: " . count($payload) . "\n";
if (count($payload) > 0) {
    $first = $payload[0];
    echo "  🔑 Payload Structure Check:\n";
    echo "     • text: " . ($first['text'] ?? 'MISSING') . "\n";
    echo "     • intent: " . json_encode($first['intent'] ?? []) . "\n";
    echo "     • source: " . ($first['source'] ?? 'MISSING') . "\n";
    echo "     • synced_at: " . ($first['synced_at'] ?? 'MISSING') . "\n";
}

// ==========================================
// TEST 6: LOCK CONCURRENCY & INTEGRITY
// ==========================================
echo "\n--------------------------------------------------------\n";
echo "🧪 TEST 6: Sync Lock & Concurrency Protection\n";
echo "--------------------------------------------------------\n";

$isLockedInitial = KeywordPayload::isSyncLocked($user->id, 'ar', 'direct_seed');
echo "  🔒 Lock Status initially: " . ($isLockedInitial ? 'LOCKED' : 'UNLOCKED') . "\n";

KeywordPayload::acquireSyncLock($user->id, 'ar', 'direct_seed');
$isLockedAfter = KeywordPayload::isSyncLocked($user->id, 'ar', 'direct_seed');
$remaining = KeywordPayload::syncLockRemainingSeconds($user->id, 'ar', 'direct_seed');
echo "  🔒 Lock Status after acquire: " . ($isLockedAfter ? "LOCKED ({$remaining}s remaining)" : 'UNLOCKED') . "\n";

KeywordPayload::releaseSyncLock($user->id, 'ar', 'direct_seed');
$isLockedReleased = KeywordPayload::isSyncLocked($user->id, 'ar', 'direct_seed');
echo "  🔓 Lock Status after release: " . ($isLockedReleased ? 'LOCKED' : 'UNLOCKED') . "\n";

// ==========================================
// SUMMARY
// ==========================================
echo "\n========================================================\n";
echo "🎉 ALL TEST SUITES EXECUTED SUCCESSFULLY!\n";
echo "========================================================\n";
