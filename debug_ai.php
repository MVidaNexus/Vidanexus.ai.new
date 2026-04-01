<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set fake headers to satisfy some middleware
$_SERVER['HTTP_HOST'] = 'vidanexus.ai';
$_SERVER['SERVER_NAME'] = 'vidanexus.ai';
$_SERVER['REQUEST_URI'] = '/debug_ai.php';

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use App\Core\AI\AIManager;
use App\Models\Setting;

echo "<style>body{font-family:sans-serif; background:#f4f4f4; padding:20px;} pre{background:#eee; padding:10px; border-radius:5px; overflow:auto;} .box{background:#fff; padding:15px; margin-bottom:15px; border-left:5px solid #007bff;}</style>";
echo "<h1>AI Keyword Radar - Direct Debugger</h1>";

try {
    $aiManager = app(AIManager::class);
    $tool = 'ai-keyword-radar';
    $lang = 'ar';
    
    echo "<div class='box'><h3>1. Setting Check</h3>";
    $provider = Setting::get('ai-keyword-radar_provider', 'openrouter');
    $model = Setting::get('ai-keyword-radar_model', 'google/gemini-2.0-flash-001');
    
    $openrouterKey = Setting::get('openrouter_api_key');
    $geminiKey = Setting::get('gemini_api_key') ?: Setting::get('google_api_key');
    
    echo "<ul>";
    echo "<li><b>Current Provider:</b> $provider</li>";
    echo "<li><b>Current Model:</b> $model</li>";
    echo "<li><b>OpenRouter Key:</b> " . ($openrouterKey ? "SET (Ends in: " . substr($openrouterKey, -4) . ")" : "NOT SET") . "</li>";
    echo "<li><b>Google/Gemini Key:</b> " . ($geminiKey ? "SET (Ends in: " . substr($geminiKey, -4) . ")" : "NOT SET") . "</li>";
    echo "</ul></div>";

    $headlines = [
        ['title' => 'سعر الذهب اليوم في مصر يرتفع بشكل مفاجئ', 'source' => 'اليوم السابع'],
        ['title' => 'موعد مباراة الأهلي والزمالك في الدوري المصري', 'source' => 'في الجول'],
        ['title' => 'أسعار البنزين والوقود بعد الزيادة الجديدة في مصر', 'source' => 'مباشر'],
    ];

    $titlesText = "";
    foreach ($headlines as $idx => $h) {
        $titlesText .= ($idx + 1) . ". [" . $h['source'] . "] " . $h['title'] . "\n";
    }

    $prompt = "You are an SEO expert. Transform these competitor headlines into high-intent 'Target Search Queries'.\n\nHeadlines:\n{$titlesText}\n\nRules:\n1. NO dates or years.\n2. TRANSFORM titles into search queries.\n3. Return ONLY a JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";

    echo "<div class='box'><h3>2. Prompt</h3><pre>" . htmlspecialchars($prompt) . "</pre></div>";

    echo "<div class='box'><h3>3. Calling AI...</h3>";
    $startTime = microtime(true);
    
    $result = $aiManager->generate($tool, $prompt, [
        'provider' => ($provider === 'gemini') ? 'google' : $provider,
        'model' => $model,
        'temperature' => 0.1,
        'json_mode' => true,
    ]);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo "<p>AI Process took: <b>{$duration}s</b></p></div>";

    echo "<div class='box'><h3>4. Raw AI Result</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre></div>";

    echo "<div class='box'><h3>5. Parsed Keywords (Simulated)</h3>";
    $response = $result['text'] ?? '';
    // Use the actual service method to handle parsing if possible
    $keywordService = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
    // Use reflection to access protected method if needed, or just manually for now
    echo "<p><i>Resulting Text:</i></p><pre>" . htmlspecialchars($response) . "</pre></div>";


} catch (\Exception $e) {
    echo "<div class='box' style='border-left-color: red;'><h3>ERROR:</h3>";
    echo "<p style='color:red'><b>" . $e->getMessage() . "</b></p>";
    echo "<i>Trace Snippet:</i><pre>" . substr($e->getTraceAsString(), 0, 1000) . "</pre></div>";
}
