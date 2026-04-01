<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);

$errors = \App\Models\ToolError::where('tool_slug', 'ai-keyword-radar')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

header('Content-Type: application/json');
echo json_encode($errors);
