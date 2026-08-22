<?php

use App\Http\Controllers\Web\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::post('/waitlist', [WaitlistController::class, 'store']);
