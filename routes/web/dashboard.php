<?php

use App\Http\Controllers\Dashboard\AiKeywordRadarSyncController;
use App\Http\Controllers\Dashboard\CouponRedemptionController;
use App\Http\Controllers\Dashboard\CreditsBalanceController;
use App\Http\Controllers\Dashboard\FeedbackController;
use App\Http\Controllers\Dashboard\DiscoverHeadlinesGenerateController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\GenericIntelligenceController;
use App\Http\Controllers\Dashboard\NLPController;
use App\Support\GenericToolRoutes;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::post('/dashboard/settings', [DashboardController::class, 'updateSettings'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.settings.update');
Route::post('/dashboard/upgrade', [DashboardController::class, 'upgrade'])
    ->middleware(['auth', 'verified']);

Route::group(['middleware' => ['auth', 'verified'], 'prefix' => 'dashboard'], function () {
    Route::group(['prefix' => 'marketing'], function () {
        foreach (GenericToolRoutes::marketingSlugs() as $tool) {
            Route::group(['middleware' => "tool.access:$tool"], function () use ($tool) {
                Route::get($tool, [GenericIntelligenceController::class, 'show'])
                    ->defaults('slug', $tool)
                    ->name("dashboard.marketing.$tool");
                Route::post("$tool/generate", [GenericIntelligenceController::class, 'generate'])
                    ->defaults('slug', $tool)
                    ->name("dashboard.marketing.$tool.generate");
            });
        }
    });

    Route::group(['prefix' => 'seo'], function () {
        foreach (GenericToolRoutes::seoSlugs() as $tool) {
            Route::group(['middleware' => "tool.access:$tool"], function () use ($tool) {
                Route::get($tool, [GenericIntelligenceController::class, 'show'])
                    ->defaults('slug', $tool)
                    ->name("dashboard.seo.$tool");
                Route::post("$tool/generate", [GenericIntelligenceController::class, 'generate'])
                    ->defaults('slug', $tool)
                    ->name("dashboard.seo.$tool.generate");
            });
        }
    });

    Route::get('nlp-entities', [NLPController::class, 'index'])->name('dashboard.nlp-entities.index');
    Route::post('nlp-entities/analyze', [NLPController::class, 'analyze'])->name('dashboard.nlp-entities.analyze');
});

Route::post('dashboard/ai-keyword-radar/sync', \App\Http\Controllers\Dashboard\AiKeywordRadarSyncController::class)
    ->middleware(['auth', 'verified', 'tool.access:ai-keyword-radar'])
    ->name('dashboard.ai-keyword-radar.sync');

Route::post('dashboard/headlines/generate', DiscoverHeadlinesGenerateController::class)
    ->middleware(['auth', 'verified', 'check_credits'])
    ->name('headlines.generate');

Route::post('dashboard/redeem-coupon', CouponRedemptionController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard.redeem-coupon');

Route::post('dashboard/feedback', [FeedbackController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.feedback.store');

Route::get('dashboard/credits/balance', CreditsBalanceController::class)
    ->middleware(['auth'])
    ->name('dashboard.credits.balance');
