<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\CountryRegistry;
use Modules\GlobalNewsMonitor\Services\NewsMonitorService;

$service = app(NewsMonitorService::class);

$cases = [
    ['EG', 'HEALTH'],
    ['EG', 'WORLD'],
    ['SA', 'WORLD'],
    ['SA', 'SPORTS'],
    ['US', 'WORLD'],
    ['PL', 'TECHNOLOGY'],
];

foreach ($cases as [$country, $topic]) {
    $lang = CountryRegistry::langFor($country);
    $news = $service->fetchGoogleNews($country, $topic, $lang, '12h', '');
    echo "{$country}/{$topic} lang={$lang} count=".count($news).PHP_EOL;
}
