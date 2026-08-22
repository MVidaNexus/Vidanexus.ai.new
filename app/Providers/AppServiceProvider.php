<?php

namespace App\Providers;

use App\Models\Coupon;
use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Policies\CouponPolicy;
use App\Policies\FinancialLedgerEntryPolicy;
use App\Policies\InvoicePolicy;
use App\Services\Logging\EmailLogService;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Modules\AIKeywordRadar\Support\KeywordPayload;

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
        Gate::policy(FinancialLedgerEntry::class, FinancialLedgerEntryPolicy::class);
        Gate::policy(Coupon::class, CouponPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);

        // Implicitly grant all permissions to admins / super_admins
        Gate::before(function ($user, $ability) {
            return $user->isAdmin() ? true : null;
        });

        // ─── HTTPS ENFORCEMENT ───
        // Force HTTPS scheme for all generated URLs when behind a reverse proxy in production.
        // trustProxies (bootstrap/app.php) handles X-Forwarded-Proto; this is the safety net
        // for any edge case where the header is missing or APP_URL is still http://.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Event::listen(MessageSent::class, function (MessageSent $event): void {
            $to = array_keys($event->message->getTo() ?? []);
            if ($to === []) {
                return;
            }

            app(EmailLogService::class)->logSuccess(
                null,
                (string) $to[0],
                $event->message->getSubject()
            );
        });

        // ─── QUEUE OBSERVABILITY (dedicated log channel) ───
        Queue::before(function (JobProcessing $event) {
            Log::channel('queue')->info('Job processing', [
                'connection' => $event->connectionName,
                'name' => $event->job->resolveName(),
                'id' => $event->job->getJobId(),
            ]);
        });

        Queue::after(function (JobProcessed $event) {
            Log::channel('queue')->info('Job processed', [
                'connection' => $event->connectionName,
                'name' => $event->job->resolveName(),
                'id' => $event->job->getJobId(),
            ]);
        });

        // ─── SYNC LOCK SAFETY NET ───
        // Release sync locks when SyncKeywordsJob fails permanently.
        Queue::failing(function (JobFailed $event) {
            $tags = $this->inferJobTags($event->job->resolveName());
            Log::channel('queue')->error('Job failed permanently', [
                'connection' => $event->connectionName,
                'name' => $event->job->resolveName(),
                'id' => $event->job->getJobId(),
                'exception' => $event->exception->getMessage(),
                'tags' => $tags,
            ]);
            $this->releaseSyncLockForJob($event->job, 'permanently failed');
            $this->notifyOpsForFailedJob($event->job->resolveName(), $event->exception->getMessage(), $tags);
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
                        KeywordPayload::releaseSyncLock((int) $userId, (string) ($lang ?? 'ar'), $boxId);
                        Log::warning("[SyncLock SafetyNet] Released lock for user {$userId} — job {$reason}");
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

    protected function inferJobTags(string $jobName): array
    {
        $name = strtolower($jobName);
        $tags = [];
        if (str_contains($name, 'payment')) {
            $tags[] = 'payment';
        }
        if (str_contains($name, 'mail') || str_contains($name, 'notification')) {
            $tags[] = 'email';
        }
        if (str_contains($name, 'credit') || str_contains($name, 'wallet')) {
            $tags[] = 'credits';
        }

        return $tags ?: ['general'];
    }

    protected function notifyOpsForFailedJob(string $jobName, string $exception, array $tags): void
    {
        Log::channel('slack')->critical('Queue job failed after retries', [
            'job' => $jobName,
            'exception' => $exception,
            'tags' => $tags,
        ]);

        try {
            Mail::raw(
                "Queue job failed permanently.\nJob: {$jobName}\nTags: ".implode(',', $tags)."\nError: {$exception}",
                function ($message): void {
                    $message->to(config('mail.from.address'))
                        ->subject('Queue Failure Alert');
                }
            );
        } catch (\Throwable) {
            // Slack alert is the primary fallback path.
        }
    }
}
