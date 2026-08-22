<?php

/**
 * proc_open smoke test + artisan queue:work --once probe.
 * Run: php scripts/debug/test_proc.php
 */
require __DIR__.'/../bootstrap.php';

echo "<pre>\n";
echo "=== proc_open() TEST ===\n";
echo 'Time: '.now()."\n\n";

$php = PHP_BINARY;
$artisan = base_path('artisan');
$logFile = storage_path('logs/queue-worker.log');

echo "PHP Binary: {$php}\n";
echo "Artisan: {$artisan}\n";
echo "Log: {$logFile}\n\n";

echo "--- Test 1: proc_open basic ---\n";
$cmd = "echo 'proc_open works!' 2>&1";
$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$process = @proc_open($cmd, $descriptors, $pipes);
if (is_resource($process)) {
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    echo 'Output: '.trim((string) $output)."\n";
    echo "Exit code: {$exitCode}\n\n";
} else {
    echo "❌ proc_open failed\n\n";
}

echo "--- Test 2: artisan queue:work --once ---\n";
$cmd2 = "{$php} {$artisan} queue:work --once --timeout=30 --memory=512 2>&1";
$process2 = @proc_open($cmd2, $descriptors, $pipes2);
if (is_resource($process2)) {
    fclose($pipes2[0]);
    stream_set_blocking($pipes2[1], false);
    $startTime = time();
    $output2 = '';
    while (time() - $startTime < 10) {
        $chunk = fread($pipes2[1], 8192);
        if ($chunk) {
            $output2 .= $chunk;
        }
        $status = proc_get_status($process2);
        if (! $status['running']) {
            break;
        }
        usleep(200000);
    }
    fclose($pipes2[1]);
    fclose($pipes2[2]);
    $status = proc_get_status($process2);
    if ($status['running']) {
        proc_terminate($process2);
        echo "Worker still running after 10s (terminated)\n";
    } else {
        proc_close($process2);
    }
    echo 'Output: '.trim($output2)."\n\n";
} else {
    echo "❌ proc_open artisan failed\n\n";
}

echo "--- Queue / cache row counts ---\n";
try {
    echo 'Pending jobs: '.\Illuminate\Support\Facades\DB::table('jobs')->count()."\n";
    echo 'sync_lock rows (DB cache): '.\Illuminate\Support\Facades\DB::table('cache')->where('key', 'like', '%sync_lock_%')->count()."\n";
} catch (\Throwable $e) {
    echo $e->getMessage()."\n";
}

echo "\nDone.\n</pre>\n";
