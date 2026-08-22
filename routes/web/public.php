<?php

use App\Http\Controllers\Web\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ToolController::class, 'index'])->name('home');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->name('tools.show');
Route::get('/pricing', [ToolController::class, 'pricing'])->name('pricing');
Route::get('/help-center', fn () => view('pages.help-center'))->name('help-center');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');
Route::get('/privacy', fn () => view('pages.privacy'))->name('privacy');
Route::get('/refund', fn () => view('pages.refund'))->name('refund');
Route::get('/shipping', fn () => view('pages.shipping'))->name('shipping');

Route::get('/api-docs', fn () => view('pages.api-docs'))->name('api-docs');

Route::get('/media/image-proxy', [\App\Http\Controllers\Media\ImageProxyController::class, 'show'])
    ->middleware('auth')
    ->name('media.image-proxy');
