<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \App\Http\Middleware\ServeMarkdownToAI::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\PreLaunchMiddleware::class,
        ]);
        $middleware->alias([
            'check_credits' => \App\Http\Middleware\CheckCredits::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'tool.access' => \App\Http\Middleware\ToolAccessMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerifiedCustom::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
