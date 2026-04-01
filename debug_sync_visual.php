<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set fake headers to satisfy some middleware
$_SERVER['HTTP_HOST'] = 'vidanexusai.ai';
$_SERVER['SERVER_NAME'] = 'vidanexusai.ai';
$_SERVER['REQUEST_URI'] = '/debug_sync_visual.php';

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Modules\AIKeywordRadar\Services\KeywordService;
use App\Models\Setting;
use App\Models\User;

echo "<style>body{font-family:sans-serif; background:#0f172a; color:#f1f5f9; padding:20px;} .box{background:#1e293b; padding:15px; margin-bottom:15px; border-left:5px solid #0ea5e9; border-radius:8px;} pre{background:#000; padding:10px; border-radius:5px; overflow:auto; color:#bef264; font-size:0.85rem;} .meta{color:#94a3b8; font-size:0.8rem; margin-bottom:5px;} .keyword-item{display:inline-block; margin:2px; padding:4px 10px; background:#0ea5e922; border:1px solid #0ea5e944; border-radius:15px; color:#0ea5e9; font-size:0.85rem; font-weight:bold;}</style>";
echo "<h1>AI Keyword Radar - ⚡ LIVE Visual Sync Debugger</h1>";

try {
    $sessionPath = __DIR__ . '/storage/framework/sessions';
    // Bootstrap full laravel session to get logged in user
    $kernel->handle(Illuminate\Http\Request::capture());
    
    $userId = auth()->id() ?: 1; 
    $lang = $_GET['lang'] ?? 'ar';
    $user = User::find($userId);

    if ($user) {
        auth()->login($user);
    }

    echo "<div class='box'><h3>1. Environment Setup</h3>";
    echo "USER: #{$userId} (" . ($user->name ?? 'N/A') . ") | LANG: [{$lang}] | PROVIDER: " . Setting::get('ai-keyword-radar_provider', 'openrouter') . "</div>";

    $service = app(KeywordService::class);
    $syncStart = microtime(true);

    echo "<div class='box'><h3>2. Scraping Headlines...</h3>";
    $headlinesRaw = $service->fetchCompetitorsHeadlines($lang, $userId, $syncStart);
    echo "<p>Found <b>" . count($headlinesRaw) . "</b> headlines from competitors.</p></div>";

    if (empty($headlinesRaw)) {
        die("<div class='box' style='border-left-color:red;'>FAILED: No headlines scanned. Check competitor sites in settings.</div>");
    }

    $batches = array_chunk($headlinesRaw, 20);
    echo "<div class='box'><h3>3. AI Extraction (Batch by Batch)</h3>";
    echo "<p>Splitting into <b>" . count($batches) . "</b> batches of 20.</p>";

    $allKeywords = [];
    foreach ($batches as $idx => $batch) {
        $batchNum = $idx + 1;
        echo "<div style='margin-left:20px; border-left:1px dashed #475569; padding-left:15px; margin-bottom:20px;'>";
        echo "<h4>Batch #{$batchNum} (" . count($batch) . " headlines)</h4>";
        
        $titlesText = "";
        foreach ($batch as $i => $h) {
            $titlesText .= ($i + 1) . ". [" . ($h['source'] ?? 'Site') . "] " . $h['title'] . "\n";
        }
        
        $langInstruction = ($lang === 'en') ? "English" : "Arabic";
        $dbPrompt = Setting::get('ai-keyword-radar_prompt');
        $prompt = "You are an SEO expert. Transform these competitor headlines into high-intent 'Target Search Queries' in {$langInstruction}.\n\nHeadlines:\n{$titlesText}\n\nRules:\n1. NO dates or years.\n2. TRANSFORM titles into search queries.\n3. Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";
        if ($dbPrompt) {
            $prompt = str_replace(['[Headlines]', '[headlines]', '[lang]'], [$titlesText, $titlesText, $langInstruction], $dbPrompt);
            if (!str_contains($prompt, 'Return ONLY a JSON array')) {
                $prompt .= "\n\nCRITICAL: Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";
            }
        }

        echo "<div class='meta'>Connecting to AI...</div>";
        $aiManager = app(\App\Core\AI\AIManager::class);
        $provider = Setting::get('ai-keyword-radar_provider', 'openrouter');
        $model = Setting::get('ai-keyword-radar_model', 'google/gemini-2.0-flash-001');

        $startTime = microtime(true);
        $aiResult = $aiManager->generate('ai-keyword-radar', $prompt, [
            'provider' => ($provider === 'gemini') ? 'google' : $provider,
            'model' => $model,
            'temperature' => 0.1,
            'json_mode' => true,
        ]);
        $duration = round(microtime(true) - $startTime, 2);

        $responseText = $aiResult['text'] ?? '';
        echo "<div class='meta'>RAW RESPONSE (in {$duration}s):</div>";
        echo "<pre>" . htmlspecialchars($responseText ?: 'EMPTY_TEXT_RESPONSE') . "</pre>";
        
        if (empty($responseText)) {
            echo "<p style='color:red;'>⚠️ AI returned nothing for this batch.</p>";
        }

        $keywords = $service->parseKeywordsResponse($responseText, $batch);
        echo "<p>Extracted: <b>" . count($keywords) . "</b> valid keywords.</p>";
        foreach ($keywords as $kw) {
            echo "<span class='keyword-item'>" . htmlspecialchars($kw['keyword'] ?? 'No text') . "</span>";
        }

        $allKeywords = array_merge($allKeywords, $keywords);
        echo "</div>";
        
        // Flush output buffer to browser if possible
        if (ob_get_level() > 0) ob_flush();
        flush();
    }
    echo "</div>";

    echo "<div class='box'><h3>4. Final Consolidation</h3>";
    echo "<p>Total Keywords found across all batches: <b>" . count($allKeywords) . "</b></p>";
    echo "</div>";

} catch (\Exception $e) {
    echo "<div class='box' style='border-left-color:red;'><h3>CRITICAL ERROR:</h3>";
    echo "<p style='color:red'><b>" . $e->getMessage() . "</b></p></div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
