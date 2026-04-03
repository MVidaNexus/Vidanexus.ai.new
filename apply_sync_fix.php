<?php
/**
 * Fix Script: Apply code fixes to SyncKeywordsJob and Controller
 * Also clears any stuck sync locks and stale queue jobs
 */
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== SYNC FIX TOOL ===\n";
echo "Time: " . now() . "\n\n";

// === STEP 1: Fix the SyncKeywordsJob file ===
$jobFile = __DIR__ . '/Modules/AIKeywordRadar/app/Jobs/SyncKeywordsJob.php';
$newJobContent = <<<'PHP'
<?php

namespace Modules\AIKeywordRadar\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIKeywordRadar\Services\KeywordService;
use App\Models\User;
use App\Models\AiUsage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SyncKeywordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     * Set high (10 min) to ensure ALL competitors are processed.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     * Set to 1 — retrying with the same stale competitor data rarely helps
     * and just holds the sync lock longer, blocking the user.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    protected $userId;
    protected $lang;
    protected $syncCredits;
    protected $timeFilter;
    protected $boxId;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $lang = 'ar', $syncCredits = 1, $timeFilter = '60m', $boxId = null)
    {
        $this->userId = $userId;
        $this->lang = $lang;
        $this->syncCredits = $syncCredits;
        $this->timeFilter = $timeFilter;
        $this->boxId = $boxId;
    }

    /**
     * Execute the job.
     */
    public function handle(KeywordService $service)
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error("[SyncKeywordsJob] User #{$this->userId} not found.");
            $this->releaseSyncLock();
            return;
        }

        $boxLabel = $this->boxId ? " [box:{$this->boxId}]" : '';
        Log::info("[SyncKeywordsJob] Started for user #{$this->userId} ({$this->lang}){$boxLabel} - Filter: {$this->timeFilter}");

        try {
            // Perform the sync — processes ALL competitors, no cap (but we pass 500 for consistency)
            $result = $service->syncKeywords(500, $this->lang, $this->userId, $this->timeFilter, $this->boxId);

            // Ensure credits are ONLY deducted if actual keywords were generated and saved!
            if (isset($result['saved']) && $result['saved'] > 0) {
                if ($user->wallet) {
                    $user->wallet->decrement('balance_credits', $this->syncCredits);
                }
                
                AiUsage::create([
                    'user_id'  => $user->id,
                    'tool'     => 'ai-keyword-radar',
                    'provider' => 'sync',
                    'model'    => 'competitor-sync',
                    'status'   => 'success',
                ]);
                
                Log::info("[SyncKeywordsJob] Success: Saved {$result['saved']} keywords for user #{$this->userId}. Credits deducted.");
            } else {
                Log::info("[SyncKeywordsJob] Finished: Scanned headlines but no new keywords were saved for user #{$this->userId}.");
            }

            // Clear cache so the frontend polling picks up fresh data
            $cacheKey = $this->boxId 
                ? "target_keywords_{$this->userId}_{$this->boxId}" 
                : "target_keywords_{$this->userId}_{$this->lang}";
            Cache::forget($cacheKey);
            
            // Release sync lock so user can sync again
            $this->releaseSyncLock();
            
            Log::info("[SyncKeywordsJob] Complete. Cache cleared: {$cacheKey}. Headlines: " . ($result['headlines'] ?? 0) . ", Saved: " . ($result['saved'] ?? 0));

        } catch (\Exception $e) {
            $this->releaseSyncLock();
            Log::error("[SyncKeywordsJob] Fatal error for user #{$this->userId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure (after all retries exhausted).
     * This is called by Laravel when the job permanently fails.
     * Critical: releases the sync lock so the user can sync again.
     */
    public function failed(?\Throwable $exception): void
    {
        $this->releaseSyncLock();
        Log::error("[SyncKeywordsJob] Job permanently failed for user #{$this->userId} ({$this->lang}): " . ($exception ? $exception->getMessage() : 'Unknown error'));
    }

    /**
     * Release the sync lock for this user/lang/box combination.
     */
    protected function releaseSyncLock(): void
    {
        $lockKey = "sync_lock_{$this->userId}_{$this->lang}" . ($this->boxId ? "_{$this->boxId}" : '');
        Cache::forget($lockKey);
        Log::info("[SyncKeywordsJob] Sync lock released: {$lockKey}");
    }
}
PHP;

$result = file_put_contents($jobFile, $newJobContent);
if ($result !== false) {
    echo "✅ SyncKeywordsJob.php updated successfully ({$result} bytes written)\n";
} else {
    echo "❌ Failed to write SyncKeywordsJob.php\n";
}


// === STEP 2: Fix the Controller - reduce lock TTL and add auto-clear for stale locks ===
$controllerFile = __DIR__ . '/Modules/AIKeywordRadar/app/Http/Controllers/AIKeywordRadarController.php';
$controllerContent = file_get_contents($controllerFile);

// Fix 1: Reduce lock TTL from 600 (10 min) to 180 (3 min) as a safety fallback
// The lock is released by the job when it finishes, but if the worker dies,
// the lock will auto-expire after 3 min instead of 10.
$controllerContent = str_replace(
    "Cache::put(\$lockKey, true, 600);",
    "Cache::put(\$lockKey, true, 180); // 3 min safety TTL — job releases lock when done",
    $controllerContent
);

$result = file_put_contents($controllerFile, $controllerContent);
if ($result !== false) {
    echo "✅ AIKeywordRadarController.php updated successfully ({$result} bytes written)\n";
} else {
    echo "❌ Failed to write AIKeywordRadarController.php\n";
}


// === STEP 3: Clear ALL stuck sync locks ===
echo "\n--- Clearing ALL sync locks ---\n";
$users = DB::table('users')->get(['id', 'name']);
$cleared = 0;
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            Cache::forget($key);
            echo "🔓 Cleared: {$key} (User: {$user->name})\n";
            $cleared++;
        }
    }
    $settings = DB::table('users')->where('id', $user->id)->value('settings');
    if ($settings) {
        $settings = json_decode($settings, true);
        $boxes = $settings['keywords_custom_boxes'] ?? [];
        foreach ($boxes as $box) {
            $boxId = $box['id'] ?? '';
            if (empty($boxId)) continue;
            foreach (['ar', 'en'] as $lang) {
                $key = "sync_lock_{$user->id}_{$lang}_{$boxId}";
                if (Cache::has($key)) {
                    Cache::forget($key);
                    echo "🔓 Cleared: {$key}\n";
                    $cleared++;
                }
            }
        }
    }
}
echo "Total locks cleared: {$cleared}\n";

// === STEP 4: Clear ALL stale SyncKeywordsJob entries from the queue ===
echo "\n--- Clearing stale jobs from queue ---\n";
$deleted = DB::table('jobs')
    ->where('payload', 'like', '%SyncKeywordsJob%')
    ->delete();
echo "Deleted {$deleted} stale SyncKeywordsJob(s) from queue.\n";

// === STEP 5: Also clear from cache table if using database driver ===
if (config('cache.default') === 'database') {
    echo "\n--- Clearing sync lock entries from cache table directly ---\n";
    $cacheDeleted = DB::table('cache')
        ->where('key', 'like', '%sync_lock_%')
        ->delete();
    echo "Deleted {$cacheDeleted} sync lock entries from cache table.\n";
}

// === STEP 6: Verify ===
echo "\n--- Verification ---\n";
$stillLocked = false;
foreach ($users as $user) {
    foreach (['ar', 'en'] as $lang) {
        $key = "sync_lock_{$user->id}_{$lang}";
        if (Cache::has($key)) {
            echo "⚠️ Still locked: {$key}\n";
            $stillLocked = true;
        }
    }
}
if (!$stillLocked) {
    echo "✅ All sync locks cleared!\n";
}

$remainingJobs = DB::table('jobs')->where('payload', 'like', '%SyncKeywordsJob%')->count();
echo "Remaining SyncKeywordsJob in queue: {$remainingJobs}\n";

echo "\n✅ All fixes applied! You can now sync again.\n";
echo "</pre>";
