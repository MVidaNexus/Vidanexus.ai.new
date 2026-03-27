<?php

use Illuminate\Support\Facades\Route;
use Modules\CompetitorXRay\Http\Controllers\CompetitorXRayController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('dashboard/competitor-xray')->middleware(['auth', 'tool.access:competitor-xray'])->group(function () {
    Route::get('/', [CompetitorXRayController::class, 'index'])->name('dashboard.competitor-xray.index');
    Route::post('/analyze', [CompetitorXRayController::class, 'analyze'])->name('dashboard.competitor-xray.analyze');
    Route::post('/settings', [CompetitorXRayController::class, 'saveSettings'])->name('dashboard.competitor-xray.settings');
    Route::post('/settings/delete', [CompetitorXRayController::class, 'deleteSettings'])->name('dashboard.competitor-xray.settings.delete');
    Route::post('/paa/fetch', [CompetitorXRayController::class, 'fetchPaa'])->name('dashboard.competitor-xray.paa.fetch');
});
