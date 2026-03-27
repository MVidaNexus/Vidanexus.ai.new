<?php

use Illuminate\Support\Facades\Route;
use Modules\NLPEntitiesAnalysis\Http\Controllers\NLPEntitiesAnalysisController;

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

Route::group(['prefix' => 'dashboard', 'middleware' => ['web', 'auth', 'tool.access:nlp-entities']], function () {
    Route::get('nlp-entities', [NLPEntitiesAnalysisController::class, 'index'])->name('dashboard.nlp-entities.index');
    Route::post('nlp-entities/analyze', [NLPEntitiesAnalysisController::class, 'analyze'])->name('dashboard.nlp-entities.analyze');
});
