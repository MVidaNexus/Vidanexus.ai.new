<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Setting;

echo "Custom Prompt: " . (Setting::get('ai-keyword-radar_prompt') ?: 'NONE') . "\n";
echo "Provider: " . (Setting::get('ai-keyword-radar_provider') ?: 'NONE') . "\n";
echo "Model: " . (Setting::get('ai-keyword-radar_model') ?: 'NONE') . "\n";
