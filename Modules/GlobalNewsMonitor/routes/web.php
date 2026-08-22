<?php

use Illuminate\Support\Facades\Route;
use Modules\GlobalNewsMonitor\Http\Controllers\GlobalNewsMonitorController;

Route::prefix('dashboard/global-news-monitor')->middleware(['auth', 'tool.access:global-news-monitor'])->group(function () {
    Route::get('/', [GlobalNewsMonitorController::class, 'index'])->name('dashboard.global-news-monitor.index');
    Route::post('/analyze', [GlobalNewsMonitorController::class, 'analyzeArticle'])->name('dashboard.global-news-monitor.analyze');
    Route::post('/extract-keywords', [GlobalNewsMonitorController::class, 'extractKeywords'])->name('dashboard.global-news-monitor.extract-keywords');
    Route::post('/generate-brief', [GlobalNewsMonitorController::class, 'generateBrief'])->name('dashboard.global-news-monitor.generate-brief');
});
