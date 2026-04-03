<?php
/**
 * Test proc_open queue worker spawn (the exact code from our route override)
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";
echo "=== TESTING proc_open() QUEUE WORKER ===\n";
echo "Time: " . now() . "\n\n";

$php = PHP_BINARY;
$artisan = base_path('artisan');
$logFile = storage_path('logs/queue-worker.log');

echo "PHP Binary: {$php}\n";
echo "Artisan: {$artisan}\n";
echo "Log: {$logFile}\n\n";

// Test 1: Can proc_open run at all?
echo "--- Test 1: proc_open basic ---\n";
$cmd = "echo 'proc_open works!' 2>&1";
$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$process = proc_open($cmd, $descriptors, $pipes);
if (is_resource($process)) {
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    echo "Output: " . trim($output) . "\n";
    echo "Exit code: {$exitCode}\n\n";
} else {
    echo "❌ proc_open failed!\n\n";
}

// Test 2: Can we run artisan?
echo "--- Test 2: artisan queue:work --once ---\n";
$cmd2 = "{$php} {$artisan} queue:work --once --timeout=30 --memory=512 2>&1";
$process2 = proc_open($cmd2, $descriptors, $pipes2);
if (is_resource($process2)) {
    fclose($pipes2[0]);
    // Set non-blocking and wait max 10 seconds
    stream_set_blocking($pipes2[1], false);
    $startTime = time();
    $output2 = '';
    while (time() - $startTime < 10) {
        $chunk = fread($pipes2[1], 8192);
        if ($chunk) $output2 .= $chunk;
        $status = proc_get_status($process2);
        if (!$status['running']) break;
        usleep(200000); // 200ms
    }
    fclose($pipes2[1]);
    fclose($pipes2[2]);
    $status = proc_get_status($process2);
    $running = $status['running'];
    if ($running) {
        // Still running, kill it
        proc_terminate($process2);
        echo "Worker started and is running (killed after 10s timeout for test)\n";
    } else {
        $exitCode2 = proc_close($process2);
        echo "Worker completed. Exit code: {$exitCode2}\n";
    }
    echo "Output: " . trim($output2) . "\n\n";
} else {
    echo "❌ proc_open for artisan failed!\n\n";
}

// Test 3: Check if there are pending jobs
echo "--- Current Queue State ---\n";
$pendingJobs = \DB::table('jobs')->count();
echo "Pending jobs: {$pendingJobs}\n";
$locks = \DB::table('cache')->where('key', 'like', '%sync_lock_%')->count();
echo "Sync locks: {$locks}\n";

echo "\n✅ proc_open test complete\n";
echo "</pre>";
