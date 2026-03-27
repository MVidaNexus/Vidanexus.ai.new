<?php

use Illuminate\Support\Facades\Route;
use Modules\ImgCompress\Http\Controllers\ImgCompressController;

Route::group(['middleware' => ['auth', 'tool.access:img-compress'], 'prefix' => 'dashboard/img-compress'], function () {
    Route::get('/', [ImgCompressController::class, 'index'])->name('dashboard.img-compress.index');
    Route::post('/process', [ImgCompressController::class, 'process'])->name('dashboard.img-compress.process');
});
