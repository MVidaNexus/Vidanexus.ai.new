<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";

// Check which directories/files are writable
$paths = [
    'app/Providers/AppServiceProvider.php',
    'app/Providers/',
    'routes/web.php',
    'routes/',
    'config/',
    'bootstrap/',
    'bootstrap/app.php',
    'bootstrap/providers.php',
];

foreach ($paths as $p) {
    $full = base_path($p);
    $stat = @stat($full);
    if (!$stat) { echo "NOT FOUND: {$p}\n"; continue; }
    $owner = posix_getpwuid($stat['uid']);
    echo sprintf("%-50s owner=%-12s writable=%s\n",
        $p,
        $owner['name'] ?? $stat['uid'],
        is_writable($full) ? 'YES' : 'NO'
    );
}

echo "</pre>";
