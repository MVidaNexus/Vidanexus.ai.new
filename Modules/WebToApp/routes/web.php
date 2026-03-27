<?php

use Illuminate\Support\Facades\Route;
use Modules\WebToApp\Http\Controllers\WebToAppController;

Route::group(['middleware' => ['auth', 'tool.access:web-to-app'], 'prefix' => 'dashboard/web-to-app'], function () {
    Route::get('/', [WebToAppController::class, 'index'])->name('dashboard.web-to-app.index');
    Route::post('/generate', [WebToAppController::class, 'generate'])->name('dashboard.web-to-app.generate');
});
