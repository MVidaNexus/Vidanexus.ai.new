<?php

/**
 * Check writability of common paths (Linux/macOS: owner name via POSIX; Windows: uid only).
 * Run: php scripts/maintenance/check_perms.php
 */
require __DIR__.'/../bootstrap.php';

$html = PHP_SAPI !== 'cli';
if ($html) {
    echo "<pre>\n";
}

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
    if (! $stat) {
        echo "NOT FOUND: {$p}\n";
        continue;
    }
    $ownerLabel = isset($stat['uid']) ? (string) $stat['uid'] : 'n/a';
    if (function_exists('posix_getpwuid') && isset($stat['uid'])) {
        $owner = @posix_getpwuid($stat['uid']);
        if (is_array($owner) && isset($owner['name'])) {
            $ownerLabel = $owner['name'];
        }
    }
    echo sprintf(
        "%-50s owner=%-12s writable=%s\n",
        $p,
        $ownerLabel,
        is_writable($full) ? 'YES' : 'NO'
    );
}

if ($html) {
    echo "</pre>\n";
}
