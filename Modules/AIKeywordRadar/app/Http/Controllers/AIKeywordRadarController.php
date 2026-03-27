<?php

namespace Modules\AIKeywordRadar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIKeywordRadarController extends Controller
{
    protected $service;

    public function __construct(KeywordService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->get('lang', 'ar');
        $region = $request->get('region', 'EG');
        $isAjax = $request->ajax();

        $countryMap = config('keywords.countries', []);
        $topicsMap = config('keywords.news_topics', [
            'WORLD' => ['name' => 'World', 'icon' => 'fas fa-globe'],
            'NATION' => ['name' => 'Nation', 'icon' => 'fas fa-flag'],
            'BUSINESS' => ['name' => 'Business', 'icon' => 'fas fa-chart-line'],
            'TECHNOLOGY' => ['name' => 'Technology', 'icon' => 'fas fa-microchip'],
            'ENTERTAINMENT' => ['name' => 'Entertainment', 'icon' => 'fas fa-film'],
            'SPORTS' => ['name' => 'Sports', 'icon' => 'fas fa-running'],
            'SCIENCE' => ['name' => 'Science', 'icon' => 'fas fa-flask'],
            'HEALTH' => ['name' => 'Health', 'icon' => 'fas fa-heartbeat']
        ]);

        $currentCountry = $countryMap[strtoupper($region)] ?? ['name' => $region, 'flag' => '🌐'];
        $currentCountry['code'] = strtoupper($region);

        $settings = auth()->user()->settings ?? [];
        $enableEn = !empty($settings['enable_keywords_en']);

        // Fetch Target Keywords
        $userId = auth()->id();
        
        // Fetch Arabic Keywords
        $targetKeywordsAr = Cache::remember("target_keywords_{$userId}_ar", 300, function () use ($userId) {
            $retentionLimit = now()->subMinutes(1440);
            $dbKeywords = Keyword::where('user_id', $userId)
                ->where('category', 'Target')
                ->where('lang', 'ar')
                ->where(function($q) use ($retentionLimit) {
                    $q->where('published_at', '>=', $retentionLimit)
                      ->orWhere(function($q2) use ($retentionLimit) {
                          $q2->whereNull('published_at')
                             ->where('created_at', '>=', $retentionLimit);
                      });
                })
                ->latest()
                ->take(100)
                ->get();

            if ($dbKeywords->isNotEmpty()) {
                return $dbKeywords->map(function($kw) {
                    return [
                        'text' => $kw->keyword,
                        'source' => $kw->source,
                        'published_at' => $kw->published_at ? $kw->published_at->toDateTimeString() : null,
                        'created_at' => $kw->created_at->toDateTimeString(),
                    ];
                })->toArray();
            }
            return [];
        });

        // Fetch English Keywords if enabled
        $targetKeywordsEn = [];
        if ($enableEn) {
            $targetKeywordsEn = Cache::remember("target_keywords_{$userId}_en", 300, function () use ($userId) {
                $retentionLimit = now()->subMinutes(1440);
                $dbKeywords = Keyword::where('user_id', $userId)
                    ->where('category', 'Target')
                    ->where('lang', 'en')
                    ->where(function($q) use ($retentionLimit) {
                        $q->where('published_at', '>=', $retentionLimit)
                          ->orWhere(function($q2) use ($retentionLimit) {
                              $q2->whereNull('published_at')
                                 ->where('created_at', '>=', $retentionLimit);
                          });
                    })
                    ->latest()
                    ->take(100)
                    ->get();

                if ($dbKeywords->isNotEmpty()) {
                    return $dbKeywords->map(function($kw) {
                        return [
                            'text' => $kw->keyword,
                            'source' => $kw->source,
                            'published_at' => $kw->published_at ? $kw->published_at->toDateTimeString() : null,
                            'created_at' => $kw->created_at->toDateTimeString(),
                        ];
                    })->toArray();
                }
                return [];
            });
        }

        $stats = [
            'total' => Keyword::where('user_id', $userId)->count(),
        ];

        return view('aikeywordradar::index', compact('stats', 'targetKeywordsAr', 'targetKeywordsEn', 'enableEn'));
    }

    /**
     * Show the settings page.
     */
    public function settings()
    {
        $settings = auth()->user()->settings ?? [];
        return view('aikeywordradar::settings', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'keywords_competitors' => 'nullable|string',
            'keywords_competitors_en' => 'nullable|string',
            'enable_keywords_en' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];
        
        // Handle checkbox which might not be sent if unchecked
        $settings['enable_keywords_en'] = $request->has('enable_keywords_en');

        foreach ($validated as $key => $value) {
            if ($key !== 'enable_keywords_en') {
                $settings[$key] = $value;
            }
        }

        $user->update(['settings' => $settings]);
        
        return back()->with('success', 'Trend Radar settings updated successfully.');
    }

    /**
     * Sync Competitor Keywords manually.
     */
    public function sync(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canUseTool('ai-keyword-radar')) {
            $msg = $user->getLimitReachedMessage('Keyword Radar', 'ai-keyword-radar');
            if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 403);
            return back()->with('error', $msg);
        }

        $syncCredits = (int)\App\Models\Setting::get('ai-keyword-radar_sync_credits', 1);

        if (!$user->wallet || $user->wallet->balance_credits < $syncCredits) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => "Insufficient balance to update trends. Required: {$syncCredits} Credits."], 403);
            }
            return back()->with('error', "Insufficient balance to update trends. Required: {$syncCredits} Credits.");
        }

        try {
            $lang = $request->get('lang', 'ar');

            // Refresh user to get latest settings from DB (not session cache)
            $user->refresh();
            
            // Check if user has added any competitors OR if global ones exist
            $settings = $user->settings ?? [];
            $userCompetitors = ($lang === 'en')
                ? ($settings['keywords_competitors_en'] ?? '')
                : ($settings['keywords_competitors'] ?? '');
            
            $globalCompetitors = \App\Models\Setting::get('ai-keyword-radar_competitors', '');

            Log::info('[Sync] User #' . $user->id . ' competitors check', [
                'lang' => $lang,
                'user_has_competitors' => !empty(trim($userCompetitors)),
                'global_has_competitors' => !empty(trim($globalCompetitors)),
            ]);

            if (empty(trim($userCompetitors)) && empty(trim($globalCompetitors))) {
                $msg = 'No competitors found. Please add competitor website links in "Radar Settings" (or contact admin to set global ones) before syncing.';
                if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 422);
                return back()->with('error', $msg);
            }

            $result = $this->service->syncKeywords(100, $lang, $user->id);
            
            // Ensure credits are ONLY deducted if actual keywords were generated and saved!
            if ($result['saved'] > 0) {
                $user->wallet->decrement('balance_credits', $syncCredits);
                \App\Models\AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'ai-keyword-radar',
                    'provider' => 'sync',
                    'model' => 'competitor-sync',
                    'status' => 'success',
                ]);
            }

            if ($result['saved'] > 0) {
                $successMsg = "Competitor analysis (" . ($lang === 'en' ? 'EN' : 'AR') . ") updated successfully. Added " . $result['saved'] . " new keywords.";
            } else {
                if ($result['headlines'] > 0) {
                    $successMsg = "Scanned " . $result['headlines'] . " headlines from competitors, but no new search keywords were found.";
                } else {
                    $successMsg = "No new news found at competitors in the last few hours. Try again later or check your competitor URLs in settings.";
                }
            }
            
            if ($request->ajax()) {
                // Return json only; the frontend will append ?synced= and trigger the SweetAlert toast.
                return response()->json(['success' => true, 'message' => $successMsg]);
            }

            return back()->with('success', $successMsg);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete all competitor keywords for the current user.
     */
    public function deleteAll(Request $request)
    {
        $userId = auth()->id();
        $lang = $request->get('lang', 'ar');
        
        Log::info("[Keyword Radar] Delete All requested. User: {$userId}, Lang: {$lang}");

        $count = Keyword::where('user_id', $userId)
            ->where('category', 'Target')
            ->where('lang', $lang)
            ->count();
            
        Keyword::where('user_id', $userId)
            ->where('category', 'Target')
            ->where('lang', $lang)
            ->delete();

        Log::info("[Keyword Radar] Deleted {$count} keywords from DB.");

        $cacheKey = "target_keywords_{$userId}_{$lang}";
        Cache::forget($cacheKey);
        
        Log::info("[Keyword Radar] Cache forgotten: {$cacheKey}");

        return back()->with('success', 'All competitor keywords deleted successfully.');
    }

    /**
     * Test connection to a competitor URL.
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'lang' => 'nullable|string|in:ar,en'
        ]);

        try {
            $result = $this->service->testUrl($request->url, $request->get('lang', 'ar'));
            if ($result['success']) {
                $result['headlines'] = array_slice($result['headlines'], 0, 5); // Slice for preview
            }
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suggest competitors using AI.
     */
    public function suggestCompetitors(Request $request)
    {
        $request->validate([
            'lang' => 'required|string|in:ar,en'
        ]);

        try {
            $urls = $this->service->getSuggestedCompetitors($request->lang);
            return response()->json([
                'success' => true,
                'urls' => $urls
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
