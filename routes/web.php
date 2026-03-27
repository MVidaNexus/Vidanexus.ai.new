<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/', [\App\Http\Controllers\ToolController::class, 'index'])->name('home');

// Tool & Marketing Routes
Route::get('/tools/{slug}', [\App\Http\Controllers\ToolController::class, 'show'])->name('tools.show');
Route::get('/pricing', [\App\Http\Controllers\ToolController::class, 'pricing'])->name('pricing');

// Public Resource Pages
Route::get('/api-docs', function () { return view('pages.api-docs'); })->name('api-docs');
Route::get('/help-center', function () { return view('pages.help-center'); })->name('help-center');
Route::get('/terms', function () { return view('pages.terms'); })->name('terms');
Route::get('/privacy', function () { return view('pages.privacy'); })->name('privacy');
Route::get('/refund', function () { return view('pages.refund'); })->name('refund');
Route::get('/shipping', function () { return view('pages.shipping'); })->name('shipping');


// Auth routes
Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);
Route::get('/forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\AuthController::class, 'sendResetLink'])->name('password.email');
Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

// ─── Email Verification Routes ───
Route::get('/email/verify', function () {
    return view('verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/dashboard')->with('success', 'Email verified successfully! Welcome to VidaNexus AI.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('resent', true);
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// User Dashboard (requires verified email)
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/dashboard/settings', [\App\Http\Controllers\DashboardController::class, 'updateSettings'])->middleware(['auth', 'verified']);
Route::post('/dashboard/upgrade', [\App\Http\Controllers\DashboardController::class, 'upgrade'])->middleware(['auth', 'verified']);

// Generic AI Tools Routes
Route::group(['middleware' => ['auth'], 'prefix' => 'dashboard'], function () {
    // Marketing Tools
    Route::group(['prefix' => 'marketing'], function () {
        $marketingTools = ['ad-copy', 'social-posts', 'market-research', 'plan', 'creative-ideas', 'buyer-persona', 'video-script', 'swot'];
        foreach ($marketingTools as $tool) {
            Route::group(['middleware' => "tool.access:$tool"], function() use ($tool) {
                Route::get($tool, [\App\Http\Controllers\GenericIntelligenceController::class, 'show'])->defaults('slug', $tool)->name("dashboard.marketing.$tool");
                Route::post("$tool/generate", [\App\Http\Controllers\GenericIntelligenceController::class, 'generate'])->defaults('slug', $tool)->name("dashboard.marketing.$tool.generate");
            });
        }
    });

    // SEO Tools
    Route::group(['prefix' => 'seo'], function () {
        $seoTools = ['meta-generator', 'faq-generator', 'keyword-coverage', 'word-counter'];
        foreach ($seoTools as $tool) {
            Route::group(['middleware' => "tool.access:$tool"], function() use ($tool) {
                Route::get($tool, [\App\Http\Controllers\GenericIntelligenceController::class, 'show'])->defaults('slug', $tool)->name("dashboard.seo.$tool");
                Route::post("$tool/generate", [\App\Http\Controllers\GenericIntelligenceController::class, 'generate'])->defaults('slug', $tool)->name("dashboard.seo.$tool.generate");
            });
        }
    });

    // NLP Engine (Refactored)
    Route::get('nlp-entities', [\App\Http\Controllers\NLPController::class, 'index'])->name('dashboard.nlp-entities.index');
    Route::post('nlp-entities/analyze', [\App\Http\Controllers\NLPController::class, 'analyze'])->name('dashboard.nlp-entities.analyze');
});

// Payment & Billing (auth handled inside controller — guests allowed for new account registration)
Route::get('/payment', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payment');
Route::post('/payment/initiate', [\App\Http\Controllers\PaymentController::class, 'initiate']);
Route::get('/payment/fawaterk/callback', [\App\Http\Controllers\PaymentController::class, 'callback']);

// --- SUPER ADMIN (HORIZON) ---
Route::middleware(['auth', 'admin'])->prefix('horizon-admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\HorizonController::class, 'index'])->name('admin.horizon.index');
    Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('admin.horizon.settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('admin.horizon.settings.update');
    
    Route::get('/api-keys', [App\Http\Controllers\Admin\SystemSettingsController::class, 'apiKeys'])->name('admin.horizon.api-keys.index');
    Route::post('/api-keys', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateApiKeys'])->name('admin.horizon.api-keys.update');

    Route::get('/api-reference', [App\Http\Controllers\Admin\SystemSettingsController::class, 'apiReference'])->name('admin.horizon.api-reference.index');

    Route::get('/tool/{slug}', [App\Http\Controllers\Admin\HorizonController::class, 'show'])->name('admin.horizon.show');
    Route::post('/tool/{slug}', [App\Http\Controllers\Admin\HorizonController::class, 'update'])->name('admin.horizon.update');
    Route::get('/roadmap', [App\Http\Controllers\Admin\HorizonController::class, 'roadmap'])->name('admin.horizon.roadmap');
    Route::post('/roadmap', [App\Http\Controllers\Admin\HorizonController::class, 'roadmapStore'])->name('admin.horizon.roadmap.store');
    Route::put('/roadmap/{id}', [App\Http\Controllers\Admin\HorizonController::class, 'roadmapUpdate'])->name('admin.horizon.roadmap.update');
    Route::delete('/roadmap/{id}', [App\Http\Controllers\Admin\HorizonController::class, 'roadmapDestroy'])->name('admin.horizon.roadmap.destroy');

    // User Management (Migrated from legacy admin)
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/update-balance', [App\Http\Controllers\Admin\UserController::class, 'updateBalance'])->name('admin.users.update-balance');
    Route::post('/users/{user}/update-tier', [App\Http\Controllers\Admin\UserController::class, 'updateTier'])->name('admin.users.update-tier');
    Route::post('/users/{user}/update-tools', [App\Http\Controllers\Admin\UserController::class, 'updateTools'])->name('admin.users.update-tools');
    Route::post('/users/{user}/update-password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::post('/users/{user}/update-email', [App\Http\Controllers\Admin\UserController::class, 'updateEmail'])->name('admin.users.update-email');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'delete'])->name('admin.users.delete');
    Route::post('/users/toggle-verification', [App\Http\Controllers\Admin\UserController::class, 'toggleVerification'])->name('admin.users.toggle-verification');
});

// Impersonation Control (Shared)
Route::get('/horizon-admin/stop-impersonating', [App\Http\Controllers\Admin\UserController::class, 'stopImpersonating'])
    ->middleware('auth')
    ->name('admin.stop-impersonating');

// Legacy Admin Redirects (Ensuring zero impact from decommissioning)
Route::redirect('/admin', '/horizon-admin/dashboard', 301);
Route::redirect('/admin/users', '/horizon-admin/users', 301);

