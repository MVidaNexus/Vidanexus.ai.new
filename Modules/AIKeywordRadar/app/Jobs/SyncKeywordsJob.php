<?php

namespace Modules\AIKeywordRadar\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIKeywordRadar\Services\KeywordService;
use Modules\AIKeywordRadar\Support\KeywordPayload;
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
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 10;

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
            return;
        }

        $boxLabel = $this->boxId ? " [box:{$this->boxId}]" : '';
        Log::info("[SyncKeywordsJob] Started for user #{$this->userId} ({$this->lang}){$boxLabel} - Filter: {$this->timeFilter}");

        try {
            // Perform the sync — processes ALL competitors, no cap (but we pass 500 for consistency)
            $result = $service->syncKeywords(500, $this->lang, $this->userId, $this->timeFilter, $this->boxId);

            // Ensure credits are ONLY deducted if actual keywords were generated and saved!
            if (isset($result['saved']) && $result['saved'] > 0) {
                // Route through the canonical credit consumption service so
                // wallet → bonus ordering, ledger, transactions and audit log
                // all apply, and the admin "Action Cost"
                // (tool_credit_cost_ai-keyword-radar) is the source of truth.
                if (! $user->deductToolCredits('ai-keyword-radar')) {
                    Log::critical('[SyncKeywordsJob] Credits could not be deducted after successful sync', [
                        'user_id' => $user->id,
                    ]);
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
            
            KeywordPayload::releaseSyncLock($this->userId, $this->lang, $this->boxId);
            
            Log::info("[SyncKeywordsJob] Complete. Cache cleared: {$cacheKey}. Headlines: " . ($result['headlines'] ?? 0) . ", Saved: " . ($result['saved'] ?? 0));

        } catch (\Exception $e) {
            KeywordPayload::releaseSyncLock($this->userId, $this->lang, $this->boxId);
            Log::error("[SyncKeywordsJob] Fatal error for user #{$this->userId}: " . $e->getMessage());
            throw $e;
        }
    }
}
