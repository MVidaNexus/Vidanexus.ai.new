<?php

use Illuminate\Support\Facades\Route;
use Modules\SEOAuditor\Http\Controllers\SEOAuditorController;

Route::group(['middleware' => ['auth', 'tool.access:seo-auditor'], 'prefix' => 'dashboard/seo-auditor'], function () {
    Route::get('/', [SEOAuditorController::class, 'index'])->name('dashboard.seo-auditor.index');
    Route::post('/analyze', [SEOAuditorController::class, 'analyze'])->name('dashboard.seo-auditor.analyze');
});
