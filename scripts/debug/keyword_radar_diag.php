<?php

require __DIR__.'/../bootstrap.php';

use App\Models\Setting;
use App\Models\User;
use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;

$user = User::first();
if (! $user) {
    echo "No users\n";
    exit(1);
}

$settings = $user->settings ?? [];
echo "User #{$user->id} ({$user->email})\n";
echo 'AR competitors: '.(trim($settings['keywords_competitors'] ?? '') ?: '(empty)')."\n";
echo 'Global competitors: '.(trim(Setting::get('ai-keyword-radar_competitors', '')) ?: '(empty)')."\n";
echo 'Provider: '.(Setting::get('ai-keyword-radar_provider') ?: 'default')."\n";
echo 'Model: '.(Setting::get('ai-keyword-radar_model') ?: 'default')."\n";
echo 'Keywords in DB: '.Keyword::where('user_id', $user->id)->count()."\n\n";

$service = app(KeywordService::class);
$urls = $service->getMergedCompetitorUrls($user->id, 'ar');
echo 'Merged URLs: '.count($urls)."\n";
foreach (array_slice($urls, 0, 5) as $u) {
    echo "  - {$u}\n";
}

if (empty($urls)) {
    echo "\nSTOP: No competitor URLs — add them in Radar Settings.\n";
    exit(1);
}

echo "\n--- fetchCompetitorsHeadlines (24h) ---\n";
$headlines = $service->fetchCompetitorsHeadlines('ar', $user->id, microtime(true), '24h');
echo 'Headlines: '.count($headlines)."\n";
if (! empty($headlines)) {
    echo 'Sample: '.($headlines[0]['title'] ?? '')."\n";
}

if (count($headlines) > 0) {
    echo "\n--- syncKeywords (24h) ---\n";
    $result = $service->syncKeywords(500, 'ar', $user->id, '24h');
    print_r($result);
}
