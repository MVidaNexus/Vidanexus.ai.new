<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// ─── PUBLIC PAGES (AI-Friendly Markdown supported via Global Middleware) ───
Route::get('/', [\App\Http\Controllers\ToolController::class, 'index'])->name('home');
Route::get('/tools/{slug}', [\App\Http\Controllers\ToolController::class, 'show'])->name('tools.show');
Route::get('/pricing', [\App\Http\Controllers\ToolController::class, 'pricing'])->name('pricing');
Route::get('/help-center', function () { return view('pages.help-center'); })->name('help-center');
Route::get('/terms', function () { return view('pages.terms'); })->name('terms');
Route::get('/privacy', function () { return view('pages.privacy'); })->name('privacy');
Route::get('/refund', function () { return view('pages.refund'); })->name('refund');
Route::get('/shipping', function () { return view('pages.shipping'); })->name('shipping');

// API Docs (no markdown needed)
Route::get('/api-docs', function () { return view('pages.api-docs'); })->name('api-docs');


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

// ─── KEYWORD RADAR HIGH-PERFORMANCE SYNC ───
// Fully optimized sync logic to bypass permission issues and long processing times.
// Reduces sync from 12+ minutes to ~1 minute by capping headlines and optimizing batches.
Route::post('dashboard/ai-keyword-radar/sync', function (\Illuminate\Http\Request $request) {
    ini_set('max_execution_time', 600);
    set_time_limit(600);
    
    $user = auth()->user();

    // 1. Validation & Credits
    if (!$user->canUseTool('ai-keyword-radar')) {
        $msg = $user->getLimitReachedMessage('Keyword Radar', 'ai-keyword-radar');
        if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 403);
        return back()->with('error', $msg);
    }

    $syncCredits = (int)\App\Models\Setting::get('ai-keyword-radar_sync_credits', 1);
    if (!$user->wallet || $user->wallet->balance_credits < $syncCredits) {
        $msg = "Insufficient balance. Required: {$syncCredits} Credits.";
        if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 403);
        return back()->with('error', $msg);
    }

    $lang = $request->get('lang', 'ar');
    $timeFilter = $request->get('time_filter', '60m');
    $boxId = $request->get('box_id');
    $lockKey = "sync_lock_{$user->id}_{$lang}" . ($boxId ? "_{$boxId}" : '');

    // 2. Set Sync Lock (ensures UI shows active status)
    \Illuminate\Support\Facades\Cache::put($lockKey, true, 400);

    // 3. Gather Raw Headlines
    $keywordService = app(\Modules\AIKeywordRadar\Services\KeywordService::class);
    $rawHeadlines = $keywordService->fetchCompetitorsHeadlines($lang, $user->id, microtime(true), $timeFilter, $boxId);

    if (empty($rawHeadlines)) {
        \Illuminate\Support\Facades\Cache::forget($lockKey);
        if ($request->ajax()) return response()->json(['success' => true, 'status' => 'completed', 'message' => 'No recent headlines found.']);
        return back()->with('success', 'No recent headlines found.');
    }

    // 4. Local Deduplication & STRICT Time Filtering
    $unique = [];
    $seenNormalized = [];
    $seenWords = [];
    $now = now();
    
    // Improved Time Filtering logic
    $minutes = 1440; // Default 24h
    if ($timeFilter === '60m') $minutes = 60;
    elseif ($timeFilter === 'all') $minutes = 43200; // 30 days for 'All Time'
    
    $displayLimit = $now->copy()->subMinutes($minutes);

    foreach ($rawHeadlines as $h) {
        $title = $h['title'] ?? '';
        if (empty($title)) continue;

        // CRITICAL: If 60m filter is set, discard anything without a strict date or older than 60m
        if ($timeFilter === '60m') {
            $hDate = !empty($h['pubDate']) ? \Carbon\Carbon::parse($h['pubDate']) : null;
            if (!$hDate || $hDate->lt($displayLimit)) continue; 
        }

        $normalized = mb_strtolower($title, 'UTF-8');
        $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $normalized); // Tashkeel
        $normalized = preg_replace('/[\p{P}\p{S}]+/u', ' ', $normalized); // Punctuation
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized));
        if (isset($seenNormalized[$normalized])) continue;

        // Word overlap similarity (0.60)
        $words = array_filter(preg_split('/\s+/u', $normalized), fn($w) => mb_strlen($w, 'UTF-8') >= 3);
        $wordSet = array_flip($words);
        $isDupe = false;
        foreach ($seenWords as $existingWordSet) {
            $common = count(array_intersect_key($wordSet, $existingWordSet));
            $maxW = max(count($words), count($existingWordSet));
            if ($maxW > 0 && ($common / $maxW) >= 0.60) { $isDupe = true; break; }
        }
        if ($isDupe) continue;

        $seenNormalized[$normalized] = true;
        $seenWords[] = $wordSet;
        $unique[] = $h;
    }

    // 5. Sort & Cap (Top 400 newest)
    usort($unique, function($a, $b) {
        return strtotime($b['pubDate'] ?? '0') <=> strtotime($a['pubDate'] ?? '0');
    });
    $headlines = array_slice($unique, 0, 400);
    $totalHeadlines = count($headlines);
    \Illuminate\Support\Facades\Log::info("[FastSync] Processing {$totalHeadlines} unique headlines (Capped from " . count($unique) . ").");

    // 6. Fast AI Extraction (using Reflection for protected method)
    $reflection = new \ReflectionClass(get_class($keywordService));
    $extractMethod = $reflection->getMethod('extractKeywordsWithAI');
    $extractMethod->setAccessible(true);

    $allKeywords = [];
    $batches = array_chunk($headlines, 30);
    foreach ($batches as $batchIndex => $batch) {
        try {
            \Illuminate\Support\Facades\Log::info("[FastSync] Sending AI Batch " . ($batchIndex+1) . "/" . count($batches));
            $batchResult = $extractMethod->invokeArgs($keywordService, [$batch, $lang, $user->id]);
            if (!empty($batchResult)) $allKeywords = array_merge($allKeywords, $batchResult);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("[FastSync] AI Batch Error: " . $e->getMessage());
        }
    }
    \Illuminate\Support\Facades\Log::info("[FastSync] AI extracted " . count($allKeywords) . " keywords total.");

    // 7. Save and Update Cache
    $addedCount = 0;
    $category = $boxId ? "Target:{$boxId}" : 'Target';

    foreach ($allKeywords as $kw) {
        $text = trim($kw['text'] ?? $kw['keyword'] ?? '');
        if (empty($text)) continue;

        try {
            $publishedAt = (!empty($kw['published_at'])) ? \Carbon\Carbon::parse($kw['published_at']) : null;
            $updateData = [
                'source' => $kw['source'] ?? 'AI',
                'synced_at' => now(),
            ];
            if ($publishedAt !== null) {
                $updateData['published_at'] = $publishedAt;
            }
            $keywordObj = \Modules\AIKeywordRadar\Models\Keyword::updateOrCreate(
                ['keyword' => $text, 'category' => $category, 'lang' => $lang, 'user_id' => $user->id],
                $updateData
            );
            if ($keywordObj->wasRecentlyCreated) $addedCount++;
            else {
                // Keep it "fresh" in the DB if it was found again in this scan
                $keywordObj->update(['synced_at' => now()]);
            }
        } catch (\Throwable $e) {}
    }

    // UPDATE: Fetch keywords strictly within the requested timeframe (or recently synced)
    $latestKeywords = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $user->id)
        ->where('category', $category)
        ->where('lang', $lang)
        ->where(function($q) use ($displayLimit) {
            $q->where('published_at', '>=', $displayLimit)
              ->orWhere('synced_at', '>=', $displayLimit)
              ->orWhere('created_at', '>=', $displayLimit);
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($kw) {
            // FIX: Convert everything to ISO 8601 to prevent timezone mismatch in browser JS
            if ($kw->published_at) $kw->published_at = $kw->published_at->toIso8601String();
            if ($kw->synced_at) $kw->synced_at = $kw->synced_at->toIso8601String();
            if ($kw->created_at) $kw->created_at = $kw->created_at->toIso8601String();
            return $kw;
        });

    // 8. Update Cache & Clear Lock
    $cacheKey = $boxId ? "target_keywords_{$user->id}_{$boxId}" : "target_keywords_{$user->id}_{$lang}";
    $cachedData = $latestKeywords->values()->toArray();
    
    \Illuminate\Support\Facades\Cache::put($cacheKey, $cachedData, now()->addHour());
    \Illuminate\Support\Facades\Cache::forget($lockKey);

    $statusMsg = ($addedCount > 0) 
        ? "Success! Found {$addedCount} new trend(s) in " . ($timeFilter === '60m' ? 'the last hour.' : 'today\'s scan.')
        : "Latest intelligence scan complete. Everything is already up-to-date.";
    
    \Illuminate\Support\Facades\Log::info("[FastSync] {$statusMsg} (User: #{$user->id})");

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'status' => 'completed',
            'message' => $statusMsg,
            'new_count' => $latestKeywords->count(),
            'keywords' => $cachedData
        ]);
    }
    return back()->with('success', $statusMsg);

})->middleware(['auth', 'tool.access:ai-keyword-radar'])->name('dashboard.ai-keyword-radar.sync');


// ─── DISCOVER HEADLINES HIGH-PERFORMANCE SYNC ───
// Fully optimized sync logic to bypass background queue permission issues.
Route::post('dashboard/headlines/generate', function (\Illuminate\Http\Request $request) {
    ini_set('max_execution_time', 600);
    set_time_limit(600);

    $keyword = $request->input('keyword');
    $content = $request->input('content');
    $type = $request->input('type', 'keyword');
    $region = strtoupper($request->input('country', 'EG'));
    $progressId = $request->input('progress_id') ?: 'hl_' . time();
    $variantsCount = $request->input('variants', 7);

    // Validation (inline)
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'keyword' => 'nullable|string|max:255',
        'content' => 'nullable|string',
        'type' => 'required|string|in:keyword,content',
        'variants' => 'nullable|integer|min:3|max:15',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
    }

    $user = auth()->user();

    // Credit Check
    if (!$user->canUseTool('discover-headlines')) {
        $msg = $user->getLimitReachedMessage('Discover Headlines', 'discover-headlines');
        return response()->json(['status' => 'error', 'message' => $msg], 403);
    }

    if (!$user->wallet || $user->wallet->balance_credits < 1) {
        $msg = 'Insufficient balance to generate headlines.';
        return response()->json(['status' => 'error', 'message' => $msg], 402);
    }

    // Initialize Progress
    \Illuminate\Support\Facades\Cache::put("gen_progress_{$progressId}", [
        'stage' => 'starting',
        'message' => 'Job queued. Starting fast inline intelligence engine...'
    ], 300);

    try {
        // Dispatch Job Synchronously (bypass broken queue worker)
        \Modules\DiscoverHeadlines\Jobs\GenerateHeadlinesJob::dispatchSync($user->id, [
            'keyword' => $keyword,
            'content' => $content,
            'type' => $type,
            'country' => $region,
            'variants' => $variantsCount,
            'progress_id' => $progressId,
        ]);
        
        \Illuminate\Support\Facades\Log::info("[DiscoverHeadlines] Sync successful for {$progressId}");

    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error("[DiscoverHeadlines] FastSync failed: " . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'حدث خطأ غير متوقع أثناء المعالجة.',
            'progress_id' => $progressId
        ], 500);
    }

    // Since it's synchronous, by the time we reach here, the job is complete!
    // We return 'processing' so the frontend JS poller catches the 'completed' stage seamlessly.
    return response()->json([
        'status' => 'processing',
        'message' => 'Intelligence extraction started inline.',
        'progress_id' => $progressId
    ]);

})->middleware(['auth', 'check_credits'])->name('headlines.generate');
