<?php

namespace Modules\DiscoverHeadlines\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DiscoverHeadlines\Services\HeadlineService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateHeadlinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    /**
     * One attempt is enough — `AIManager::generate` already walks the entire
     * provider failover chain internally. A queue-level retry would only
     * burn another round of credits without improving the odds, and it
     * causes the progress cache to flip back to `searching` / `ai_processing`
     * after the frontend already showed the user an error alert. That mid-
     * flow flip-flop is what produced the duplicate "success/error" toasts
     * users were reporting.
     */
    public $tries = 1;

    protected $userId;
    protected $params;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, array $params)
    {
        $this->userId = $userId;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(HeadlineService $service)
    {
        $pid = $this->params['progress_id'] ?? 'unknown';
        Log::info("[GenerateHeadlinesJob] Started for User #{$this->userId} - PID: {$pid}");

        try {
            $service->generate($this->userId, $this->params);
            Log::info("[GenerateHeadlinesJob] Successfully completed for PID: {$pid}");
        } catch (\Exception $e) {
            Log::error("[GenerateHeadlinesJob] Failed for PID: {$pid}. Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Persist failure for the headlines progress poller when the queue gives up.
     */
    public function failed(?\Throwable $e): void
    {
        $progressId = $this->params['progress_id'] ?? null;
        if (! $progressId) {
            return;
        }

        $message = $e ? $e->getMessage() : 'Background job failed after retries.';

        Cache::put("gen_progress_{$progressId}", [
            'stage' => 'error',
            'message' => $message,
        ], 600);
    }
}
