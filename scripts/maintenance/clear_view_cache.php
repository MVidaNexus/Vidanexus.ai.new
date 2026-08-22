<?php

/**
 * Remove compiled Blade files under storage/framework/views.
 * Run: php scripts/maintenance/clear_view_cache.php
 *
 * Replaces legacy clear_cache.php (which used the wrong ../storage path from project root).
 */
require __DIR__.'/../bootstrap.php';

$dir = storage_path('framework/views');
$files = glob($dir.DIRECTORY_SEPARATOR.'*.php') ?: [];
foreach ($files as $file) {
    if (is_file($file)) {
        @unlink($file);
    }
}

echo 'View cache cleared ('.count($files).' files in '.$dir.").\n";
