<?php

/**
 * Minimal AI pipeline test (Keyword Radar settings + one generate call).
 * Run: php scripts/debug/debug_ai.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/scripts/debug/debug_ai.php';

$app = require __DIR__.'/../bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Core\AI\AIManager;
use App\Models\Setting;

echo "<style>body{font-family:sans-serif;background:#f4f4f4;padding:20px;} pre{background:#eee;padding:10px;border-radius:5px;overflow:auto;} .box{background:#fff;padding:15px;margin-bottom:15px;border-left:5px solid #007bff;}</style>";
echo '<h1>AI Keyword Radar — direct debugger</h1>';

try {
    $aiManager = app(AIManager::class);
    $tool = 'ai-keyword-radar';
    $lang = 'ar';

    echo "<div class='box'><h3>1. Settings</h3>";
    $provider = Setting::get('ai-keyword-radar_provider', 'openrouter');
    $model = Setting::get('ai-keyword-radar_model', 'google/gemini-2.0-flash-001');

    $openrouterKey = Setting::get('openrouter_api_key');
    $geminiKey = Setting::get('gemini_api_key') ?: Setting::get('google_api_key');

    echo '<ul>';
    echo '<li><b>Provider:</b> '.e($provider).'</li>';
    echo '<li><b>Model:</b> '.e($model).'</li>';
    echo '<li><b>OpenRouter key:</b> '.($openrouterKey ? 'SET (ends '.e(substr($openrouterKey, -4)).')' : 'NOT SET').'</li>';
    echo '<li><b>Google/Gemini key:</b> '.($geminiKey ? 'SET (ends '.e(substr($geminiKey, -4)).')' : 'NOT SET').'</li>';
    echo '</ul></div>';

    $headlines = [
        ['title' => 'سعر الذهب اليوم في مصر يرتفع بشكل مفاجئ', 'source' => 'اليوم السابع'],
        ['title' => 'موعد مباراة الأهلي والزمالك في الدوري المصري', 'source' => 'في الجول'],
        ['title' => 'أسعار البنزين والوقود بعد الزيادة الجديدة في مصر', 'source' => 'مباشر'],
    ];

    $titlesText = '';
    foreach ($headlines as $idx => $h) {
        $titlesText .= ($idx + 1).'. ['.$h['source'].'] '.$h['title']."\n";
    }

    $prompt = "You are an SEO expert. Transform these competitor headlines into high-intent 'Target Search Queries'.\n\nHeadlines:\n{$titlesText}\n\nRules:\n1. NO dates or years.\n2. TRANSFORM titles into search queries.\n3. Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";

    echo "<div class='box'><h3>2. Prompt</h3><pre>".e($prompt)."</pre></div>";

    echo "<div class='box'><h3>3. Calling AI</h3>";
    $startTime = microtime(true);

    $result = $aiManager->generate($tool, $prompt, [
        'provider' => ($provider === 'gemini') ? 'google' : $provider,
        'model' => $model,
        'temperature' => 0.1,
        'json_mode' => true,
    ]);

    $duration = round(microtime(true) - $startTime, 2);

    echo '<p>Duration: <b>'.e((string) $duration).'s</b></p></div>';

    echo "<div class='box'><h3>4. Raw result</h3><pre>".e(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))."</pre></div>";

    $response = $result['text'] ?? '';
    echo "<div class='box'><h3>5. Response text</h3><pre>".e($response)."</pre></div>";
} catch (\Exception $e) {
    echo "<div class='box' style='border-left-color:red;'><h3>Error</h3><p style='color:red'><b>".e($e->getMessage())."</b></p><pre>".e(substr($e->getTraceAsString(), 0, 1000))."</pre></div>";
}
