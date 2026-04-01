<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ToolError;

$errors = ToolError::where('tool', 'ai-keyword-radar')->latest()->take(3)->get();
header('Content-Type: application/json');
echo json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
