<?php

use Illuminate\Support\Facades\Route;
use Modules\SEOAnalyzer\Http\Controllers\SEOAnalyzerController;

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

Route::prefix('dashboard/seo-analyzer')->middleware(['auth', 'tool.access:seo-analyzer'])->name('dashboard.seo-analyzer.')->group(function () {
    Route::get('/', [SEOAnalyzerController::class, 'index'])->name('index');
    Route::post('/analyze-headline', [SEOAnalyzerController::class, 'analyzeHeadline'])->name('analyze-headline');
    Route::post('/analyze-content', [SEOAnalyzerController::class, 'analyzeContent'])->name('analyze-content');
});
