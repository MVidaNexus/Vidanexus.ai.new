<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$n = count(app(\Modules\GlobalNewsMonitor\Services\NewsMonitorService::class)->fetchGoogleNews('EG', 'WORLD', 'ar', '12h', ''));
echo "EG/WORLD count={$n}\n";
