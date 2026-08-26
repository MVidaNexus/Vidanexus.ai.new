<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('tools:renew')->daily();
Schedule::command('ledger:reconcile')->hourly();
Schedule::command('queue:work --stop-when-empty --tries=2 --max-time=50')->everyMinute()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
