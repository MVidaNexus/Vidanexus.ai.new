<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache key prefix for generic background task status payloads.
    |--------------------------------------------------------------------------
    |
    | Used by App\Services\BackgroundTaskService. Poll from the frontend via
    | GET route name "background-tasks.show" until status is completed|failed.
    |
    */

    'cache_key_prefix' => env('BACKGROUND_TASK_CACHE_PREFIX', 'bg_task'),

    /*
    |--------------------------------------------------------------------------
    | Time-to-live (seconds) for task status in cache.
    |--------------------------------------------------------------------------
    */

    'ttl_seconds' => (int) env('BACKGROUND_TASK_TTL', 3600),

];
