<?php

use Illuminate\Support\Facades\Route;
use Modules\DramaTrends\Http\Controllers\DramaTrendsController;

Route::prefix('dashboard/drama-trends')->middleware(['auth', 'tool.access:drama-trends'])->name('dashboard.drama-trends.')->group(function () {
    // Views
    Route::get('/', [DramaTrendsController::class, 'index'])->name('index');
    Route::get('/report', [DramaTrendsController::class, 'report'])->name('report');
    Route::get('/management', [DramaTrendsController::class, 'management'])->name('management');

    // API: Fetch combined trends data
    Route::post('/trends-data', [DramaTrendsController::class, 'dramaTrends'])->name('trends-data');
    Route::post('/series-details', [DramaTrendsController::class, 'seriesDetails'])->name('series-details');

    // API: Series CRUD
    Route::get('/api/series', [DramaTrendsController::class, 'getSeries'])->name('api.series.get');
    Route::post('/api/series', [DramaTrendsController::class, 'saveSeries'])->name('api.series.save');

    // API: WATCH IT Ranking (admin-managed)
    Route::get('/api/watchit-ranking', [DramaTrendsController::class, 'getWatchItRanking'])->name('api.watchit.get');
    Route::post('/api/watchit-ranking', [DramaTrendsController::class, 'saveWatchItRanking'])->name('api.watchit.save');

    // API: Google Trends CSV Upload
    Route::post('/api/upload-csv', [DramaTrendsController::class, 'uploadCSV'])->name('api.csv.upload');
    Route::post('/api/clear-csv', [DramaTrendsController::class, 'clearCSV'])->name('api.csv.clear');

    // API: NotebookLM Report Override
    Route::post('/api/save-notebook-override', [DramaTrendsController::class, 'saveNotebookOverride'])->name('api.notebook.save');
    Route::post('/api/clear-notebook-override', [DramaTrendsController::class, 'clearNotebookOverride'])->name('api.notebook.clear');
});
