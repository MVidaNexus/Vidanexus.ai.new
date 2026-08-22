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
        $retentionLimit = now()->subMinutes(1440);
        
        // Fetch Arabic Keywords
        $targetKeywordsAr = Cache::remember("target_keywords_{$userId}_ar", 300, function () use ($userId, $retentionLimit) {
            return $this->fetchKeywordsFromDb($userId, 'ar', 'Target', $retentionLimit);
        });

        // Fetch English Keywords if enabled
        $targetKeywordsEn = [];
        if ($enableEn) {
            $targetKeywordsEn = Cache::remember("target_keywords_{$userId}_en", 300, function () use ($userId, $retentionLimit) {
                return $this->fetchKeywordsFromDb($userId, 'en', 'Target', $retentionLimit);
            });
        }

        // Fetch Custom Box Keywords
        $customBoxes = $settings['keywords_custom_boxes'] ?? [];
        $customBoxKeywords = [];
        foreach ($customBoxes as $box) {
            $boxId = $box['id'] ?? '';
            if (empty($boxId)) continue;
            
            $customBoxKeywords[$boxId] = Cache::remember("target_keywords_{$userId}_{$boxId}", 300, function () use ($userId, $box, $retentionLimit) {
                $boxLang = $box['lang'] ?? 'ar';
                $category = "Target:{$box['id']}";
                return $this->fetchKeywordsFromDb($userId, $boxLang, $category, $retentionLimit);
            });
        }

        $stats = [
            'total' => Keyword::where('user_id', $userId)->count(),
        ];

        return view('aikeywordradar::index', compact('stats', 'targetKeywordsAr', 'targetKeywordsEn', 'enableEn', 'customBoxes', 'customBoxKeywords'));
    }

    /**
     * Helper: fetch keywords from DB for a given user/lang/category
     */
    private function fetchKeywordsFromDb($userId, $lang, $category, $retentionLimit)
    {
        $dbKeywords = Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang)
            ->where(function($q) use ($retentionLimit) {
                $q->where('published_at', '>=', $retentionLimit)
                  ->orWhere(function($q2) use ($retentionLimit) {
                      $q2->whereNull('published_at')
                         ->where('created_at', '>=', $retentionLimit);
                  });
            })
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take(500)
            ->get();

        if ($dbKeywords->isNotEmpty()) {
            return $dbKeywords->map(function($kw) {
                return [
                    'text' => $kw->keyword,
                    'source' => $kw->source,
                    'published_at' => $kw->published_at ? $kw->published_at->toIso8601String() : null,
                    'synced_at' => $kw->synced_at ? $kw->synced_at->toIso8601String() : null,
                    'created_at' => $kw->created_at->toIso8601String(),
                ];
            })->toArray();
        }
        return [];
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
            'keywords_custom_boxes' => 'nullable|string',
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];
        
        // Handle checkbox which might not be sent if unchecked
        $settings['enable_keywords_en'] = $request->has('enable_keywords_en');

        foreach ($validated as $key => $value) {
            if ($key === 'enable_keywords_en') continue;
            
            if ($key === 'keywords_custom_boxes') {
                $boxes = json_decode($value, true) ?? [];
                foreach ($boxes as &$box) {
                    if (isset($box['competitors'])) {
                        $boxRaw = preg_split('/\r\n|\r|\n/', (string)$box['competitors']);
                        $boxNorms = [];
                        $boxClean = [];
                        foreach ($boxRaw as $u) {
                            $u = trim($u);
                            if (empty($u)) continue;
                            if (!preg_match('/^https?:\/\//i', $u)) {
                                $u = 'https://' . $u;
                            }
                            $u = rtrim($u, '/');
                            
                            // Normalize for comparison
                            $norm = preg_replace('/^https?:\/\//', '', strtolower($u));
                            $norm = preg_replace('/^www\./', '', $norm);
                            $norm = rtrim($norm, '/');
                            
                            if (in_array($norm, $boxNorms)) continue;
                            $boxNorms[] = $norm;
                            $boxClean[] = $u;
                        }
                        $box['competitors'] = implode("\n", $boxClean);
                    }
                }
                $settings[$key] = $boxes;
                continue;
            }

            if ($key === 'keywords_competitors' || $key === 'keywords_competitors_en') {
                $raw = preg_split('/\r\n|\r|\n/', (string)$value);
                $norms = [];
                $clean = [];
                foreach ($raw as $u) {
                    $u = trim($u);
                    if (empty($u)) continue;
                    if (!preg_match('/^https?:\/\//i', $u)) {
                        $u = 'https://' . $u;
                    }
                    $u = rtrim($u, '/');
                    
                    // Normalize for comparison
                    $norm = preg_replace('/^https?:\/\//', '', strtolower($u));
                    $norm = preg_replace('/^www\./', '', $norm);
                    $norm = rtrim($norm, '/');

                    if (in_array($norm, $norms)) continue;
                    $norms[] = $norm;
                    $clean[] = $u;
                }
                $settings[$key] = implode("\n", $clean);
                continue;
            }

            $settings[$key] = $value;
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
            $timeFilter = $request->get('time_filter', '60m');
            $boxId = $request->get('box_id');

            $user->refresh();
            $settings = $user->settings ?? [];
            
            if ($boxId) {
                $customBoxes = $settings['keywords_custom_boxes'] ?? [];
                $boxFound = false;
                foreach ($customBoxes as $box) {
                    if (($box['id'] ?? '') === $boxId) {
                        $boxFound = true;
                        if (empty(trim($box['competitors'] ?? ''))) {
                            $msg = 'No competitors found in this box. Please add competitor URLs first.';
                            if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 422);
                            return back()->with('error', $msg);
                        }
                        $lang = $box['lang'] ?? 'ar';
                        break;
                    }
                }
                if (!$boxFound) {
                    $msg = 'Custom box not found.';
                    if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 422);
                    return back()->with('error', $msg);
                }
            } else {
                $userCompetitors = ($lang === 'en')
                    ? ($settings['keywords_competitors_en'] ?? '')
                    : ($settings['keywords_competitors'] ?? '');
                
                $globalCompetitors = \App\Models\Setting::get('ai-keyword-radar_competitors', '');

                if (empty(trim($userCompetitors)) && empty(trim($globalCompetitors))) {
                    $msg = 'No competitors found. Please add competitor website links in "Radar Settings" (or contact admin to set global ones) before syncing.';
                    if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 422);
                    return back()->with('error', $msg);
                }
            }

            // Count current keywords so frontend can detect new ones
            // MUST use the same retention filter as getKeywordsJSON to be comparable!
            $retentionVal = (int)\App\Models\Setting::get('ai-keyword-radar_retention_hours', 24);
            $retentionLimit = now()->subHours($retentionVal);

            $category = $boxId ? "Target:{$boxId}" : 'Target';
            $currentCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $user->id)
                ->where('category', $category)
                ->where('lang', $lang)
                ->where(function($q) use ($retentionLimit) {
                    $q->where('published_at', '>=', $retentionLimit)
                      ->orWhere(function($q2) use ($retentionLimit) {
                          $q2->whereNull('published_at')
                             ->where('created_at', '>=', $retentionLimit);
                      });
                })
                ->count();

            // Prevent overlapping syncs — if one is already running, don't start another
            $lockKey = "sync_lock_{$user->id}_{$lang}" . ($boxId ? "_{$boxId}" : '');
            if (Cache::has($lockKey)) {
                $msg = 'A sync is already running. Please wait for it to finish.';
                if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 429);
                return back()->with('error', $msg);
            }
            // Set lock for 10 minutes (max job duration)
            Cache::put($lockKey, true, 600);

            // Clear ANY stale/old sync jobs from the queue before dispatching
            // This prevents old serialized jobs (with outdated code) from executing
            \DB::table('jobs')->where('queue', 'default')
                ->where('payload', 'like', '%SyncKeywordsJob%')
                ->delete();
            
            // Dispatch fresh job
            \Modules\AIKeywordRadar\Jobs\SyncKeywordsJob::dispatch($user->id, $lang, $syncCredits, $timeFilter, $boxId);
            
            Log::info("[Keyword Radar Sync] Job dispatched for user #{$user->id} ({$lang}) Filter: {$timeFilter}");

            // Spawn queue worker in background
            $php = PHP_BINARY;
            $artisan = base_path('artisan');
            $logFile = storage_path('logs/queue-worker.log');
            $cmd = "nohup {$php} {$artisan} queue:work --once --timeout=600 --memory=512 >> {$logFile} 2>&1 &";
            exec($cmd);
            
            Log::info("[Keyword Radar Sync] Queue worker spawned in background.");

            $boxLabel = $boxId ? " (Custom Box)" : " (" . ($lang === 'en' ? 'EN' : 'AR') . ")";
            $filterLabel = ($timeFilter === 'all' ? 'All Time' : ($timeFilter === '24h' ? 'Last 24 Hours' : 'Last 60 Minutes'));
            $successMsg = "Sync{$boxLabel} started in background (Filter: {$filterLabel}). Keywords will appear automatically when ready.";
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'status' => 'queued',
                    'message' => $successMsg,
                    'current_count' => $currentCount,
                    'lang' => $lang,
                    'box_id' => $boxId,
                ]);
            }

            return back()->with('success', $successMsg);
        } catch (\Exception $e) {
            Log::error("[Keyword Radar Sync] Exception: " . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function getKeywordsJSON(Request $request)
    {
        $userId = auth()->id();
        $lang = $request->get('lang', 'ar');
        $boxId = $request->get('box_id');
        $category = $boxId ? "Target:{$boxId}" : 'Target';
        
        $retentionVal = (int)\App\Models\Setting::get('ai-keyword-radar_retention_hours', 24);
        $retentionLimit = now()->subHours($retentionVal);
        
        $cacheKey = $boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}";
        
        // Always refresh from DB when called via AJAX for refresh
        $keywords = $this->fetchKeywordsFromDb($userId, $lang, $category, $retentionLimit);
        
        // Update cache for web views
        Cache::put($cacheKey, $keywords, 3600);

        return response()->json([
            'success' => true,
            'keywords' => $keywords,
            'sync_running' => Cache::has("sync_lock_{$userId}_{$lang}")
        ]);
    }

    /**
     * Delete all competitor keywords for the current user.
     */
    public function deleteAll(Request $request)
    {
        $userId = auth()->id();
        $lang = $request->get('lang', 'ar');
        $boxId = $request->get('box_id');
        
        $category = $boxId ? "Target:{$boxId}" : 'Target';
        
        Log::info("[Keyword Radar] Delete All requested. User: {$userId}, Lang: {$lang}, Category: {$category}");

        $count = Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang)
            ->count();
            
        Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang)
            ->delete();

        Log::info("[Keyword Radar] Deleted {$count} keywords from DB.");

        $cacheKey = $boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}";
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
            'lang' => 'required|string|in:ar,en',
            'topic' => 'nullable|string'
        ]);

        try {
            $urls = $this->service->getSuggestedCompetitors($request->lang, $request->topic);
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
