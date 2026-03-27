<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditX\Http\Controllers\AuditXController;

Route::group(['middleware' => ['auth', 'tool.access:audit-x'], 'prefix' => 'dashboard/audit-x'], function () {
    Route::get('/', [AuditXController::class, 'index'])->name('dashboard.audit-x.index');
    Route::post('/analyze', [AuditXController::class, 'analyze'])->name('dashboard.audit-x.analyze');
});
