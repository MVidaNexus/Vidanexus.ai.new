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

Route::group(['middleware' => ['auth', 'tool.access:article-writer', 'check_credits']], function () {
    Route::resource('articlewriter', ArticleWriterController::class)->names('articlewriter');
});
