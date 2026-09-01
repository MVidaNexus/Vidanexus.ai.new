<?php

@ini_set('memory_limit', '1024M');
@ini_set('max_execution_time', '300');
@set_time_limit(300);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies so Laravel detects HTTPS when behind a reverse proxy / load balancer.
        $middleware->append(\App\Http\Middleware\ServeMarkdownToAI::class);
        $middleware->web(append: [
            \App\Http\Middleware\PreLaunchMiddleware::class,
        ]);
        $middleware->alias([
            'check_credits' => \App\Http\Middleware\CheckCredits::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'tool.access' => \App\Http\Middleware\ToolAccessMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerifiedCustom::class,
            'ai.security' => \App\Http\Middleware\AISecurityMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            // Skip Slack reporting when the webhook isn't configured. Otherwise
            // SlackWebhookHandler throws inside the report path and masks the
            // real exception (e.g. silently breaks queue failure recording).
            if (!config('logging.channels.slack.url') && !env('LOG_SLACK_WEBHOOK_URL')) {
                return;
            }

            try {
                Log::channel('slack')->critical('Critical exception reported', [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
            } catch (\Throwable) {
                // Never let the reporter itself break exception handling.
            }
        });
    })->create();
