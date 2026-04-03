<?php
/**
 * Strategy: Since the files inside app/Jobs/ and app/Http/Controllers/ are owned by root,
 * we can't modify them directly. BUT the parent AIKeywordRadar directory IS writable.
 * 
 * Approach: Copy the files to a temp location, modify them, then replace them.
 * We can delete files (unlink works on writable parent directories), 
 * then create new ones with the correct owner.
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>\n";
echo "=== FILE REPLACEMENT STRATEGY ===\n\n";

// Check if we can unlink (delete) and recreate files
// unlink() might work if the directory is writable by the user, even if the file is owned by root
$jobFile = base_path('Modules/AIKeywordRadar/app/Jobs/SyncKeywordsJob.php');
$controllerFile = base_path('Modules/AIKeywordRadar/app/Http/Controllers/AIKeywordRadarController.php');

// Check parent dir writability
$jobDir = dirname($jobFile);
$controllerDir = dirname($controllerFile);
echo "Jobs dir writable: " . (is_writable($jobDir) ? 'YES' : 'NO') . "\n";
echo "Controllers dir writable: " . (is_writable($controllerDir) ? 'YES' : 'NO') . "\n\n";

if (!is_writable($jobDir) || !is_writable($controllerDir)) {
    echo "❌ Parent directories not writable. Cannot proceed.\n";
    echo "Please run: chown -R vidanexusai:vidanexusai /home/vidanexusai/public_html/Modules/AIKeywordRadar/\n";
    echo "</pre>";
    exit;
}

// Backup first
$jobBackup = $jobFile . '.bak.' . date('Ymd_His');
$controllerBackup = $controllerFile . '.bak.' . date('Ymd_His');

// Read current contents for backup
$jobContent = file_get_contents($jobFile);
$controllerContent = file_get_contents($controllerFile);

// --- STEP 1: Patch the Job file ---
echo "--- Patching SyncKeywordsJob.php ---\n";

$newJobContent = <<<'PHPCODE'
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
PHPCODE;

// Try to delete the old file and create a new one
$deleted = @unlink($jobFile);
if ($deleted) {
    $written = file_put_contents($jobFile, $newJobContent);
    if ($written !== false) {
        echo "✅ SyncKeywordsJob.php replaced successfully ({$written} bytes)\n";
        echo "   - Added failed() method for lock release on permanent failure\n";
        echo "   - Added releaseSyncLock() helper method\n";
        echo "   - Reduced tries from 3 to 1\n";
        echo "   - Reduced maxExceptions from 2 to 1\n";
        echo "   - Lock released on early return (user not found)\n";
    } else {
        echo "❌ File deleted but couldn't create new one! Restoring backup...\n";
        file_put_contents($jobFile, $jobContent);
    }
} else {
    echo "❌ Cannot delete Job file (unlink failed). Root owns the file.\n";
    echo "   Manual fix needed: chown vidanexusai:vidanexusai {$jobFile}\n";
}

// --- STEP 2: Patch the Controller file ---
echo "\n--- Patching AIKeywordRadarController.php ---\n";

$newControllerContent = str_replace(
    "Cache::put(\$lockKey, true, 600);",
    "Cache::put(\$lockKey, true, 180); // 3 min safety TTL — job releases lock when done",
    $controllerContent
);

if ($newControllerContent !== $controllerContent) {
    $deleted2 = @unlink($controllerFile);
    if ($deleted2) {
        $written2 = file_put_contents($controllerFile, $newControllerContent);
        if ($written2 !== false) {
            echo "✅ Controller patched: Lock TTL reduced 600s → 180s ({$written2} bytes)\n";
        } else {
            echo "❌ File deleted but couldn't create new one! Restoring...\n";
            file_put_contents($controllerFile, $controllerContent);
        }
    } else {
        echo "❌ Cannot delete Controller file (unlink failed). Root owns the file.\n";
    }
} else {
    echo "ℹ️ Controller lock TTL already patched or pattern changed.\n";
}

echo "\n=== DONE ===\n";
echo "</pre>";
