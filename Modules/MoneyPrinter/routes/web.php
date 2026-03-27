<?php

use Illuminate\Support\Facades\Route;
use Modules\MoneyPrinter\Http\Controllers\MoneyPrinterController;

Route::group(['middleware' => ['auth', 'tool.access:money-printer'], 'prefix' => 'dashboard/money-printer'], function () {
    Route::get('/', [MoneyPrinterController::class, 'index'])->name('dashboard.money-printer.index');
    Route::post('/run', [MoneyPrinterController::class, 'run'])->name('dashboard.money-printer.run');
});
