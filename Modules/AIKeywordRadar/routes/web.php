<?php

use Illuminate\Support\Facades\Route;
use Modules\AIKeywordRadar\Http\Controllers\AIKeywordRadarController;

Route::prefix('dashboard/ai-keyword-radar')->middleware(['auth', 'tool.access:ai-keyword-radar'])->group(function () {
    Route::get('/', [AIKeywordRadarController::class, 'index'])->name('dashboard.ai-keyword-radar.index');
    Route::get('/settings', [AIKeywordRadarController::class, 'settings'])->name('dashboard.ai-keyword-radar.settings');
    Route::post('/settings', [AIKeywordRadarController::class, 'updateSettings'])->name('dashboard.ai-keyword-radar.settings.update');
    Route::post('/sync', [AIKeywordRadarController::class, 'sync'])->name('dashboard.ai-keyword-radar.sync');
    Route::post('/delete-all', [AIKeywordRadarController::class, 'deleteAll'])->name('dashboard.ai-keyword-radar.delete-all');
    Route::post('/test-connection', [AIKeywordRadarController::class, 'testConnection'])->name('dashboard.ai-keyword-radar.test-connection');
    Route::post('/suggest-competitors', [AIKeywordRadarController::class, 'suggestCompetitors'])->name('dashboard.ai-keyword-radar.suggest-competitors');
    Route::get('/get-keywords', [AIKeywordRadarController::class, 'getKeywordsJSON'])->name('dashboard.ai-keyword-radar.get-keywords');
});
