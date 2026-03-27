<?php

use Illuminate\Support\Facades\Route;
use Modules\AIOOptimizer\Http\Controllers\AIOOptimizerController;

Route::group(['middleware' => ['auth', 'tool.access:aio-optimizer'], 'prefix' => 'dashboard/aio-optimizer'], function () {
    Route::get('/', [AIOOptimizerController::class, 'index'])->name('dashboard.aio-optimizer.index');
    Route::post('/analyze', [AIOOptimizerController::class, 'analyze'])->name('dashboard.aio-optimizer.analyze');
});
