<?php

use Illuminate\Support\Facades\Route;
use Modules\TrendingSearchMonitor\Http\Controllers\TrendingSearchController;

Route::prefix('dashboard/trending-searches')->middleware(['auth', 'tool.access:trending-search-monitor'])->group(function () {
    Route::get('/', [TrendingSearchController::class, 'index'])->name('dashboard.trending-searches.index');
    Route::post('/analyze', [TrendingSearchController::class, 'analyzeTrend'])->name('dashboard.trending-searches.analyze');
});
