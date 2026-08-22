<?php

/**
 * Full KeywordService headline fetch + batched AI extraction (HTML output).
 * WARNING: When run via web, may call auth()->login() for user id 1 (or ?user_id=).
 *
 * Run: php scripts/debug/debug_sync_visual.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/scripts/debug/debug_sync_visual.php';

$app = require __DIR__.'/../bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Core\AI\AIManager;
use App\Models\Setting;
use App\Models\User;
use Modules\AIKeywordRadar\Services\KeywordService;

echo "<style>body{font-family:sans-serif;background:#0f172a;color:#f1f5f9;padding:20px;} .box{background:#1e293b;padding:15px;margin-bottom:15px;border-left:5px solid #0ea5e9;border-radius:8px;} pre{background:#000;padding:10px;border-radius:5px;overflow:auto;color:#bef264;font-size:0.85rem;} .meta{color:#94a3b8;font-size:0.8rem;margin-bottom:5px;} .keyword-item{display:inline-block;margin:2px;padding:4px 10px;background:#0ea5e922;border:1px solid #0ea5e944;border-radius:15px;color:#0ea5e9;font-size:0.85rem;font-weight:bold;}</style>";
echo '<h1>AI Keyword Radar — visual sync debugger</h1>';

try {
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (auth()->id() ?: 1);
    $lang = $_GET['lang'] ?? 'ar';
    $user = User::find($userId);

    if ($user) {
        auth()->login($user);
    }

    echo "<div class='box'><h3>1. Environment</h3>";
    echo 'USER: #'.$userId.' ('.e($user->name ?? 'N/A').") | LANG: [{$lang}] | PROVIDER: ".e((string) Setting::get('ai-keyword-radar_provider', 'openrouter')).'</div>';

    $service = app(KeywordService::class);
    $syncStart = microtime(true);

    echo "<div class='box'><h3>2. Headlines</h3>";
    $headlinesRaw = $service->fetchCompetitorsHeadlines($lang, $userId, $syncStart);
    echo '<p>Found <b>'.count($headlinesRaw).'</b> headlines.</p></div>';

    if (empty($headlinesRaw)) {
        exit("<div class='box' style='border-left-color:red;'>No headlines — check competitors in settings.</div>");
    }

    $batches = array_chunk($headlinesRaw, 20);
    echo "<div class='box'><h3>3. AI batches</h3><p><b>".count($batches).'</b> batches.</p>';

    $allKeywords = [];
    foreach ($batches as $idx => $batch) {
        $batchNum = $idx + 1;
        echo "<div style='margin-left:20px;border-left:1px dashed #475569;padding-left:15px;margin-bottom:20px;'>";
        echo '<h4>Batch #'.$batchNum.' ('.count($batch).' headlines)</h4>';

        $titlesText = '';
        foreach ($batch as $i => $h) {
            $titlesText .= ($i + 1).'. ['.($h['source'] ?? 'Site').'] '.$h['title']."\n";
        }

        $langInstruction = ($lang === 'en') ? 'English' : 'Arabic';
        $dbPrompt = Setting::get('ai-keyword-radar_prompt');
        $prompt = "You are an SEO expert. Transform these competitor headlines into high-intent 'Target Search Queries' in {$langInstruction}.\n\nHeadlines:\n{$titlesText}\n\nRules:\n1. NO dates or years.\n2. TRANSFORM titles into search queries.\n3. Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";
        if ($dbPrompt) {
            $prompt = str_replace(['[Headlines]', '[headlines]', '[lang]'], [$titlesText, $titlesText, $langInstruction], $dbPrompt);
            if (! str_contains($prompt, 'Return ONLY a JSON array')) {
                $prompt .= "\n\nCRITICAL: Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";
            }
        }

        echo "<div class='meta'>Calling AI…</div>";
        $aiManager = app(AIManager::class);
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
        echo '<div class="meta">RAW ('.$duration.'s)</div>';
        echo '<pre>'.e($responseText ?: 'EMPTY_TEXT_RESPONSE').'</pre>';

        $keywords = $service->parseKeywordsResponse($responseText, $batch);
        echo '<p>Extracted: <b>'.count($keywords).'</b></p>';
        foreach ($keywords as $kw) {
            echo '<span class="keyword-item">'.e($kw['keyword'] ?? 'No text').'</span>';
        }

        $allKeywords = array_merge($allKeywords, $keywords);
        echo '</div>';

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
    echo '</div>';

    echo "<div class='box'><h3>4. Total keywords</h3><p><b>".count($allKeywords).'</b></p></div>';
} catch (\Exception $e) {
    echo "<div class='box' style='border-left-color:red;'><h3>Error</h3><p style='color:red'><b>".e($e->getMessage())."</b></p></div>";
    echo '<pre>'.e($e->getTraceAsString()).'</pre>';
}
