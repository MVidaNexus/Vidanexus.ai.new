<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Satisfy Laravel environment
$_SERVER['HTTP_HOST'] = 'vidanexusai.ai';
$_SERVER['SERVER_NAME'] = 'vidanexusai.ai';
$_SERVER['REQUEST_URI'] = '/inspect_headlines.php';

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

use Modules\AIKeywordRadar\Services\KeywordService;

echo "<style>body{font-family:sans-serif; background:#f4f4f4; padding:20px;} .box{background:#fff; padding:15px; margin-bottom:15px; border-left:5px solid #007bff; border-radius:5px;} .short{color:#999; font-style:italic;} .good{color:green; font-weight:bold;}</style>";
echo "<h1>AI Keyword Radar - Headline Inspector</h1>";

try {
    $service = app(KeywordService::class);
    $lang = $_GET['lang'] ?? 'ar';
    $userId = auth()->id() ?: 14; // Default to user 14 if not logged in

    echo "<div class='box'><h3>Fetching headlines for user #{$userId} in [{$lang}]...</h3>";
    $startTime = microtime(true);
    
    // Use reflection to call the underlying scraper methods to ensure we get a detailed trace
    $headlines = $service->fetchCompetitorsHeadlines($lang, $userId, $startTime);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);

    echo "<p>Scanned: <b>" . count($headlines) . "</b> headlines in <b>{$duration}s</b></p></div>";

    if (empty($headlines)) {
        echo "<div class='box' style='border-left-color:red;'><h3>No headlines found.</h3><p>Verify your competitor website links in Radar Settings. If they are subreddits or sitemaps, they might be blocked.</p></div>";
    } else {
        echo "<div class='box'><h3>Headline List:</h3><ol>";
        foreach ($headlines as $idx => $h) {
            $title = $h['title'] ?? 'N/A';
            $source = $h['source'] ?? 'Unknown';
            $len = mb_strlen($title);
            $class = ($len < 15) ? 'short' : 'good';
            echo "<li>[<small>{$source}</small>] <span class='{$class}'>" . htmlspecialchars($title) . "</span> <small>({$len} chars)</small></li>";
        }
        echo "</ol></div>";
    }

} catch (\Exception $e) {
    echo "<div class='box' style='border-left-color:red;'><h3>ERROR:</h3><p style='color:red'>" . $e->getMessage() . "</p><pre>" . $e->getTraceAsString() . "</pre></div>";
}
