<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$user = App\Models\User::first();
auth()->login($user);
$request = Illuminate\Http\Request::create('/dashboard/ai-keyword-radar', 'GET');
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);
$time = microtime(true) - $start;
echo "Execution Time: {$time} seconds\n";
echo "Status: " . $response->getStatusCode() . "\n";
