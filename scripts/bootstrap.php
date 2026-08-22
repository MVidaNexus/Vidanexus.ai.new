<?php

/**
 * Shared bootstrap for CLI / one-off scripts under scripts/.
 * Usage from scripts/maintenance or scripts/debug:
 *   $app = require __DIR__.'/../bootstrap.php';
 */
$projectBase = dirname(__DIR__);

require_once $projectBase.'/vendor/autoload.php';

$app = require_once $projectBase.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

return $app;
