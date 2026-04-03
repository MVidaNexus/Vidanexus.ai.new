<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobExceptionOccurred;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ─── SYNC LOCK SAFETY NET ───
        // Release sync locks when SyncKeywordsJob fails permanently.
        Queue::failing(function (JobFailed $event) {
            $this->releaseSyncLockForJob($event->job, 'permanently failed');
        });

        // Also release on exception (before retry) to prevent lock hold during backoff
        Queue::exceptionOccurred(function (JobExceptionOccurred $event) {
            $this->releaseSyncLockForJob($event->job, 'exception occurred');
        });

        // ─── UI ENHANCEMENTS ───
        // Inject CSS to make the Keyword Radar and Discover Headlines loading overlays more opaque
        view()->composer('*', function ($view) {
            $viewName = $view->getName();
            
            if (str_contains($viewName, 'aikeywordradar')) {
                echo '<style>
                    [id^="sync-loading-"] { 
                        background: rgba(15, 23, 42, 0.98) !important; 
                        backdrop-filter: blur(15px) !important;
                        z-index: 9999 !important;
                    }
                    .radar-spinner { transform: scale(1.1); margin-bottom: 35px !important; }
                </style>';
            }
            
            if (str_contains($viewName, 'discoverheadlines') || str_contains($viewName, 'headlines_index')) {
                echo '<style>
                    #generation-progress-overlay { 
                        background: rgba(13, 14, 18, 0.98) !important;
                        backdrop-filter: blur(15px) !important;
                    }
                </style>';
            }
        });
    }

    /**
     * If the given job is a SyncKeywordsJob, release its sync lock.
     */
    protected function releaseSyncLockForJob($job, string $reason): void
    {
        $payload = $job->payload();
        $displayName = $payload['displayName'] ?? '';

        if (str_contains($displayName, 'SyncKeywordsJob')) {
            // Extract job data from the serialized command
            try {
                $command = @unserialize($payload['data']['command'] ?? '');
                if ($command) {
                    $reflect = new \ReflectionClass($command);
                    
                    $userId = $this->getProtectedProperty($reflect, $command, 'userId');
                    $lang = $this->getProtectedProperty($reflect, $command, 'lang') ?? 'ar';
                    $boxId = $this->getProtectedProperty($reflect, $command, 'boxId');

                    if ($userId) {
                        $lockKey = "sync_lock_{$userId}_{$lang}" . ($boxId ? "_{$boxId}" : '');
                        Cache::forget($lockKey);
                        Log::warning("[SyncLock SafetyNet] Released lock {$lockKey} — job {$reason}");
                    }
                }
            } catch (\Throwable $e) {
                // Fallback: clear ALL sync locks from the cache table
                Log::warning("[SyncLock SafetyNet] Could not parse job payload: " . $e->getMessage());
                \DB::table('cache')->where('key', 'like', '%sync_lock_%')->delete();
            }
        }
    }

    /**
     * Get a protected property value from an object via reflection.
     */
    protected function getProtectedProperty(\ReflectionClass $reflect, $object, string $prop)
    {
        if ($reflect->hasProperty($prop)) {
            $property = $reflect->getProperty($prop);
            $property->setAccessible(true);
            return $property->getValue($object);
        }
        return null;
    }
}
