<?php

/**
 * JSON export of recent ToolError rows (model uses tool_slug column).
 * Query: ?tool=ai-keyword-radar&limit=10
 *
 * Run: php scripts/debug/tool_errors.php
 */
$app = require __DIR__.'/../bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\ToolError;

$slug = $_GET['tool'] ?? 'ai-keyword-radar';
$limit = min(50, max(1, (int) ($_GET['limit'] ?? 5)));

$errors = ToolError::query()
    ->where('tool_slug', $slug)
    ->orderByDesc('created_at')
    ->limit($limit)
    ->get();

header('Content-Type: application/json; charset=utf-8');
echo $errors->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
