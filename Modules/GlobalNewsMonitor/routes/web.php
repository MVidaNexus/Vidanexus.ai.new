<?php

use Illuminate\Support\Facades\Route;
use Modules\GlobalNewsMonitor\Http\Controllers\GlobalNewsMonitorController;

Route::prefix('dashboard/global-news-monitor')->middleware(['auth', 'tool.access:global-news-monitor'])->group(function () {
    Route::get('/', [GlobalNewsMonitorController::class, 'index'])->name('dashboard.global-news-monitor.index');
});
