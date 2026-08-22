<?php

/**
 * Print AI Keyword Radar-related settings from DB.
 * Run: php scripts/debug/check_settings.php
 */
$app = require __DIR__.'/../bootstrap.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Setting;

echo 'Custom Prompt: '.(Setting::get('ai-keyword-radar_prompt') ?: 'NONE')."\n";
echo 'Provider: '.(Setting::get('ai-keyword-radar_provider') ?: 'NONE')."\n";
echo 'Model: '.(Setting::get('ai-keyword-radar_model') ?: 'NONE')."\n";
