<?php

use Illuminate\Support\Facades\Route;
use Modules\FolioOCR\Http\Controllers\FolioOCRController;

Route::group(['middleware' => ['auth', 'tool.access:folio-ocr'], 'prefix' => 'dashboard/folio-ocr'], function () {
    Route::get('/', [FolioOCRController::class, 'index'])->name('dashboard.folio-ocr.index');
    Route::post('/process', [FolioOCRController::class, 'process'])->name('dashboard.folio-ocr.process');
});
