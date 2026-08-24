<?php

use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\FeedbackAdminController;
use App\Http\Controllers\Admin\FinancialLedgerController;
use App\Http\Controllers\Admin\HorizonController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('horizon-admin')->group(function () {
    Route::get('/dashboard', [HorizonController::class, 'index'])->name('admin.horizon.index');
    Route::get('/feedback', [FeedbackAdminController::class, 'index'])->name('admin.horizon.feedback.index');
    Route::delete('/feedback/{feedback}', [FeedbackAdminController::class, 'destroy'])->name('admin.horizon.feedback.destroy');
    
    // Email Campaigns / Mass Broadcaster
    Route::get('/email-campaigns', [EmailCampaignController::class, 'index'])->name('admin.horizon.email-campaigns.index');
    Route::post('/email-campaigns/estimate', [EmailCampaignController::class, 'estimateAudience'])->name('admin.horizon.email-campaigns.estimate');
    Route::post('/email-campaigns/test', [EmailCampaignController::class, 'sendTest'])->name('admin.horizon.email-campaigns.test');
    Route::post('/email-campaigns/send', [EmailCampaignController::class, 'sendCampaign'])->name('admin.horizon.email-campaigns.send');
    Route::get('/settings', [SystemSettingsController::class, 'index'])->name('admin.horizon.settings.index');
    Route::get('/settings/{tab}', [SystemSettingsController::class, 'index'])->name('admin.horizon.settings.tab');
    Route::post('/settings/{tab?}', [SystemSettingsController::class, 'update'])->name('admin.horizon.settings.update');

    Route::get('/ledger/financial', [FinancialLedgerController::class, 'index'])
        ->middleware('permission:view_ledger')
        ->name('admin.horizon.ledger.index');

    Route::get('/api-keys', [SystemSettingsController::class, 'apiKeys'])->name('admin.horizon.api-keys.index');
    Route::post('/api-keys', [SystemSettingsController::class, 'updateApiKeys'])->name('admin.horizon.api-keys.update');

    Route::get('/api-reference', [SystemSettingsController::class, 'apiReference'])->name('admin.horizon.api-reference.index');

    Route::get('/tool/{slug}', [HorizonController::class, 'show'])->name('admin.horizon.show');
    Route::post('/tool/{slug}', [HorizonController::class, 'update'])->name('admin.horizon.update');
    Route::get('/roadmap', [HorizonController::class, 'roadmap'])->name('admin.horizon.roadmap');
    Route::post('/roadmap', [HorizonController::class, 'roadmapStore'])->name('admin.horizon.roadmap.store');
    Route::put('/roadmap/{id}', [HorizonController::class, 'roadmapUpdate'])->name('admin.horizon.roadmap.update');
    Route::delete('/roadmap/{id}', [HorizonController::class, 'roadmapDestroy'])->name('admin.horizon.roadmap.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/update-balance', [UserController::class, 'updateBalance'])->name('admin.users.update-balance');
    Route::post('/users/{user}/update-tier', [UserController::class, 'updateTier'])->name('admin.users.update-tier');
    Route::post('/users/{user}/update-tools', [UserController::class, 'updateTools'])->name('admin.users.update-tools');
    Route::post('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::post('/users/{user}/update-email', [UserController::class, 'updateEmail'])->name('admin.users.update-email');
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::delete('/users/{user}', [UserController::class, 'delete'])->name('admin.users.delete');
    Route::post('/users/toggle-verification', [UserController::class, 'toggleVerification'])->name('admin.users.toggle-verification');

    // Coupons
    Route::post('/coupons', [CouponController::class, 'store'])->middleware('permission:manage_coupons')->name('admin.coupons.store');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->middleware('permission:manage_coupons')->name('admin.coupons.destroy');
    Route::patch('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->middleware('permission:manage_coupons')->name('admin.coupons.toggle');
});

Route::get('/horizon-admin/stop-impersonating', [UserController::class, 'stopImpersonating'])
    ->middleware('auth')
    ->name('admin.stop-impersonating');

Route::redirect('/admin', '/horizon-admin/dashboard', 301);
Route::redirect('/admin/users', '/horizon-admin/users', 301);
