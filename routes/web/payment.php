<?php

use App\Http\Controllers\Billing\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
Route::post('/payment/initiate', [PaymentController::class, 'initiate']);
Route::get('/payment/fawaterk/callback', [PaymentController::class, 'callback']);
Route::post('/payment/fawaterk/webhook', [PaymentController::class, 'webhook']);
