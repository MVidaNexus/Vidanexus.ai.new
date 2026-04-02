<?php

namespace Modules\DiscoverHeadlines\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DiscoverHeadlines\Services\HeadlineService;
use Illuminate\Support\Facades\Log;

class GenerateHeadlinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;

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
}
