<?php

use Illuminate\Support\Facades\Route;
use Modules\ArticleWriter\Http\Controllers\ArticleWriterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('dashboard/article-writer')->middleware(['auth', 'tool.access:article-writer', 'check_credits'])->group(function () {
    Route::get('/', [ArticleWriterController::class, 'index'])->name('dashboard.article-writer.index');
    Route::post('/generate', [ArticleWriterController::class, 'store'])
        ->middleware('ai.security')
        ->name('dashboard.article-writer.generate');
    Route::get('/history', [ArticleWriterController::class, 'history'])->name('dashboard.article-writer.history');
    Route::get('/{id}', [ArticleWriterController::class, 'show'])->name('dashboard.article-writer.show');
    Route::delete('/{id}', [ArticleWriterController::class, 'destroy'])->name('dashboard.article-writer.destroy');
});
