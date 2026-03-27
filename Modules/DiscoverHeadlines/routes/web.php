<?php

use Illuminate\Support\Facades\Route;
use Modules\DiscoverHeadlines\Http\Controllers\HeadlineController;

Route::group(['middleware' => ['auth', 'tool.access:discover-headlines'], 'prefix' => 'dashboard/headlines'], function () {
    Route::get('/', [HeadlineController::class, 'index'])->name('headlines.index');
    
    Route::group(['middleware' => ['check_credits']], function () {
        Route::post('/generate', [HeadlineController::class, 'generate'])->name('headlines.generate');
        Route::get('/progress/{id}', [HeadlineController::class, 'getProgress'])->name('headlines.progress');
    });
});
