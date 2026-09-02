<?php

namespace Modules\AIKeywordRadar\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use Modules\AIKeywordRadar\Support\KeywordPayload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIKeywordRadarController extends Controller
{
    /**
     * Max "Inject Market Source" URLs (Arabic + English combined) for non-admin users.
     */
    private const MAX_MARKET_SOURCES_NON_ADMIN = 20;

    /**
     * Time filter tokens exposed by the radar UI. Anything outside this
     * allow-list is coerced to the default "Last 60 Minutes" so we never
     * propagate junk filter strings (or skip filtering entirely by accident).
     *
     * @var array<int, string>
     */
    private const ALLOWED_TIME_FILTERS = [
        '60m', '24h', 'all',
    ];

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
        $settings = auth()->user()->settings ?? [];
        $enableEn = !empty($settings['enable_keywords_en']);

        // Fetch Target Keywords
        $userId = auth()->id();
        $cacheTtl = $request->boolean('refresh') ? 0 : 60;

        $targetKeywordsAr = $this->loadKeywordsForBox($userId, 'ar', 'Target', null, $cacheTtl);

        // Fetch English Keywords if enabled
        $targetKeywordsEn = [];
        if ($enableEn) {
            $targetKeywordsEn = $this->loadKeywordsForBox($userId, 'en', 'Target', null, $cacheTtl);
        }

        // Fetch Custom Box Keywords
        $customBoxes = $settings['keywords_custom_boxes'] ?? [];
        $customBoxKeywords = [];
        foreach ($customBoxes as $box) {
            $boxId = $box['id'] ?? '';
            if (empty($boxId)) continue;
            
            $boxLang = $box['lang'] ?? 'ar';
            $category = "Target:{$box['id']}";
            $customBoxKeywords[$boxId] = $this->loadKeywordsForBox($userId, $boxLang, $category, $boxId, $cacheTtl);
        }

        // Fetch Direct Seed Keywords (Dedicated Box)
        $directSeedKeywordsAr = $this->loadKeywordsForBox($userId, 'ar', 'Direct:Seed', 'direct_seed', $cacheTtl);
        $directSeedKeywordsEn = $enableEn ? $this->loadKeywordsForBox($userId, 'en', 'Direct:Seed', 'direct_seed', $cacheTtl) : [];

        $hasSeedTopics = !empty(trim((string)($settings['keywords_seed_topics'] ?? ''))) 
            || !empty(trim((string)($settings['keywords_seed_topics_en'] ?? '')))
            || !empty($directSeedKeywordsAr)
            || !empty($directSeedKeywordsEn);

        $stats = [
            'total' => Keyword::where('user_id', $userId)->count(),
            'retention_hours' => KeywordPayload::retentionHours(),
        ];

        return view('aikeywordradar::index', compact(
            'stats',
            'targetKeywordsAr',
            'targetKeywordsEn',
            'enableEn',
            'customBoxes',
            'customBoxKeywords',
            'directSeedKeywordsAr',
            'directSeedKeywordsEn',
            'hasSeedTopics'
        ));
    }

    private function loadKeywordsForBox(int $userId, string $lang, string $category, ?string $boxId, int $cacheTtl): array
    {
        $cacheKey = $boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}";

        if ($cacheTtl <= 0) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $cacheTtl > 0 ? $cacheTtl : 1, function () use ($userId, $lang, $category) {
            return $this->fetchKeywordsFromDb($userId, $lang, $category);
        });
    }

    /**
     * Helper: fetch keywords from DB for a given user/lang/category
     */
    private function fetchKeywordsFromDb($userId, $lang, $category)
    {
        $dbKeywords = Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang);
        KeywordPayload::applyRetentionScope($dbKeywords);
        $dbKeywords = $dbKeywords
            ->orderByRaw('COALESCE(synced_at, published_at, created_at) DESC')
            ->take(500)
            ->get();

        if ($dbKeywords->isNotEmpty()) {
            return KeywordPayload::fromCollection($dbKeywords);
        }

        return [];
    }

    /**
     * Show the settings page.
     */
    public function settings()
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];

        $limit = $user->isAdmin() ? null : (int) \App\Models\Setting::get('ai-keyword-radar_max_competitors', self::MAX_MARKET_SOURCES_NON_ADMIN);

        return view('aikeywordradar::settings', [
            'settings' => $settings,
            'keywordRadarMarketSourceLimit' => $limit,
        ]);
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
            'keywords_seed_topics' => 'nullable|string',
            'keywords_seed_topics_en' => 'nullable|string',
        ]);

        $user = auth()->user();
        $settings = $user->settings ?? [];

        $arClean = $this->parseDedupedCompetitorLines($request->input('keywords_competitors'));
        $enClean = $this->parseDedupedCompetitorLines($request->input('keywords_competitors_en'));
        $limit = $user->isAdmin() ? null : (int) \App\Models\Setting::get('ai-keyword-radar_max_competitors', self::MAX_MARKET_SOURCES_NON_ADMIN);

        if ($limit !== null && count($arClean) + count($enClean) > $limit) {
            return back()
                ->withErrors([
                    'market_sources' => 'Standard accounts may add up to '.$limit.' market sources combined (Arabic + English). Remove some sources or use an administrator account.',
                ])
                ->withInput();
        }
        
        // Handle checkbox which might not be sent if unchecked
        $settings['enable_keywords_en'] = $request->has('enable_keywords_en');

        foreach ($validated as $key => $value) {
            if ($key === 'enable_keywords_en') continue;
            
            if ($key === 'keywords_custom_boxes') {
                $boxes = json_decode($value, true) ?? [];
                foreach ($boxes as &$box) {
                    if (isset($box['competitors'])) {
                        $boxRaw = explode("\n", $box['competitors']);
                        $boxNorms = [];
                        $boxClean = [];
                        foreach ($boxRaw as $u) {
                            $u = trim($u);
                            if (empty($u)) continue;
                            
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

            if ($key === 'keywords_competitors') {
                $settings[$key] = implode("\n", $arClean);
                continue;
            }

            if ($key === 'keywords_competitors_en') {
                $settings[$key] = implode("\n", $enClean);
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
        $slug = 'ai-keyword-radar';

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $msg = $hasOwnership
                ? "Insufficient balance to update trends. Required: {$cost} Credits."
                : $user->getLimitReachedMessage('Keyword Radar', $slug);
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], $hasOwnership ? 402 : 403);
            }
            return back()->with('error', $msg);
        }

        $syncCredits = $user->getToolCreditCost($slug);

        try {
            $lang = $request->get('lang', 'ar');
            $timeFilter = $this->normalizeTimeFilter($request->get('time_filter', '60m'));
            $boxId = $request->get('box_id');

            $user->refresh();
            $settings = $user->settings ?? [];
            
            if ($boxId) {
                $customBoxes = $settings['keywords_custom_boxes'] ?? [];
                $boxFound = false;
                foreach ($customBoxes as $box) {
                    if (($box['id'] ?? '') === $boxId) {
                        $boxFound = true;
                        $lang = $box['lang'] ?? 'ar';
                        if (empty(trim($box['competitors'] ?? ''))) {
                            $msg = ($lang === 'ar')
                                ? 'لم يتم العثور على روابط منافسين لهذا الصندوق المخصص. يُرجى إضافة روابط مواقع المنافسين من "إعدادات الرادار" أولاً.'
                                : 'No competitors found in this custom box. Please add competitor URLs in Radar Settings first.';
                            if ($request->ajax()) return response()->json([
                                'success' => false,
                                'error_code' => 'NO_COMPETITORS',
                                'message' => $msg,
                                'settings_url' => route('dashboard.ai-keyword-radar.settings')
                            ], 422);
                            return back()->with('error', $msg);
                        }
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
                    $msg = ($lang === 'ar')
                        ? 'لم يتم العثور على روابط منافسين. يُرجى إضافة روابط مواقع المنافسين أولاً من "إعدادات الرادار" لسحب وتحليل الكلمات والعناوين الرائجة.'
                        : 'No competitors found. Please add competitor website links in "Radar Settings" before syncing trends.';
                    if ($request->ajax()) return response()->json([
                        'success' => false,
                        'error_code' => 'NO_COMPETITORS',
                        'message' => $msg,
                        'settings_url' => route('dashboard.ai-keyword-radar.settings')
                    ], 422);
                    return back()->with('error', $msg);
                }
            }

            // Count current keywords so frontend can detect new ones.
            // Use applyRetentionScope to match exactly what getKeywordsJSON returns.
            $category = $boxId ? "Target:{$boxId}" : 'Target';
            $currentCountQuery = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', $user->id)
                ->where('category', $category)
                ->where('lang', $lang);
            KeywordPayload::applyRetentionScope($currentCountQuery);
            $currentCount = $currentCountQuery->count();

            // Prevent overlapping syncs — if one is already running, don't start another
            if (KeywordPayload::isSyncLocked($user->id, $lang, $boxId)) {
                $msg = 'A sync is already running. Please wait for it to finish.';
                if ($request->ajax()) return response()->json(['success' => false, 'message' => $msg], 429);
                return back()->with('error', $msg);
            }
            KeywordPayload::acquireSyncLock($user->id, $lang, $boxId);

            // Clear ANY stale/old sync jobs from the queue before dispatching
            // This prevents old serialized jobs (with outdated code) from executing
            \DB::table('jobs')->whereIn('queue', ['default', 'keyword-radar'])
                ->where('payload', 'like', '%SyncKeywordsJob%')
                ->delete();

            $mode = $request->get('mode', 'smart');

            // Dispatch fresh job to a dedicated queue so --once always picks it up
            \Modules\AIKeywordRadar\Jobs\SyncKeywordsJob::dispatch($user->id, $lang, $syncCredits, $timeFilter, $boxId, $mode)
                ->onQueue('keyword-radar');

            Log::info("[Keyword Radar Sync] Job dispatched for user #{$user->id} ({$lang}) Filter: {$timeFilter} Mode: {$mode}");

            // Spawn queue worker targeting only the keyword-radar queue with explicit memory limit
            $php = $this->getCliPhpBinary();
            $artisan = base_path('artisan');
            $logFile = storage_path('logs/queue-worker.log');
            $cmd = "nohup {$php} -d memory_limit=512M -d max_execution_time=300 {$artisan} queue:work --queue=keyword-radar --once --timeout=300 >> {$logFile} 2>&1 &";
            exec($cmd);
            
            Log::info("[Keyword Radar Sync] Queue worker spawned in background with {$php}.");

            $boxLabel = $boxId ? " (Custom Box)" : " (" . ($lang === 'en' ? 'EN' : 'AR') . ")";
            $filterLabel = $this->service->describeTimeFilter($timeFilter);
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
            if (isset($lang)) {
                KeywordPayload::releaseSyncLock($user->id, $lang, $boxId ?? null);
            }
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
        $category = ($boxId === 'direct_seed') ? 'Direct:Seed' : ($boxId ? "Target:{$boxId}" : 'Target');
        
        $cacheKey = ($boxId === 'direct_seed') ? "target_keywords_{$userId}_direct_seed" : ($boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}");

        $keywords = $this->fetchKeywordsFromDb($userId, $lang, $category);

        Cache::put($cacheKey, $keywords, 3600);

        return response()->json([
            'success' => true,
            'keywords' => $keywords,
            'sync_running' => KeywordPayload::isSyncLocked($userId, $lang, $boxId),
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
        
        $category = ($boxId === 'direct_seed') ? 'Direct:Seed' : ($boxId ? "Target:{$boxId}" : 'Target');
        
        Log::info("[Keyword Radar] Delete All requested. User: {$userId}, Lang: {$lang}, Category: {$category}");

        $count = Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang)
            ->count();
            
        Keyword::where('user_id', $userId)
            ->where('category', $category)
            ->where('lang', $lang)
            ->delete();

        $cacheKey = ($boxId === 'direct_seed') ? "target_keywords_{$userId}_direct_seed" : ($boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}");
        Cache::forget($cacheKey);

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

    /**
     * Coerce arbitrary user-supplied time filter input into one of the
     * tokens the radar actually supports. Accepts a few aliases (e.g. "1h",
     * "1d") and falls back to "60m" for anything unknown.
     */
    private function normalizeTimeFilter(mixed $value): string
    {
        if (! is_string($value) && ! is_int($value)) {
            return '60m';
        }
        $token = strtolower(trim((string) $value));
        if ($token === '') {
            return '60m';
        }

        $aliases = [
            '1h'  => '60m',
            '2h'  => '120m',
            '3h'  => '180m',
            '6h'  => '360m',
            '1d'  => '24h',
            'any' => 'all',
        ];
        $token = $aliases[$token] ?? $token;

        return in_array($token, self::ALLOWED_TIME_FILTERS, true) ? $token : '60m';
    }

    /**
     * @return list<string>
     */
    private function parseDedupedCompetitorLines(?string $value): array
    {
        $raw = explode("\n", (string) $value);
        $norms = [];
        $clean = [];
        foreach ($raw as $u) {
            $u = trim($u);
            if ($u === '') {
                continue;
            }
            $norm = preg_replace('/^https?:\/\//', '', strtolower($u));
            $norm = preg_replace('/^www\./', '', (string) $norm);
            $norm = rtrim($norm, '/');
            if (in_array($norm, $norms, true)) {
                continue;
            }
            $norms[] = $norm;
            $clean[] = $u;
        }

        return $clean;
    }

    /**
     * Locate the real CLI PHP executable path across various server environments.
     */
    protected function getCliPhpBinary(): string
    {
        $candidates = [
            '/usr/local/bin/php',
            '/opt/alt/php83/usr/bin/php',
            '/opt/alt/php82/usr/bin/php',
            '/opt/cpanel/ea-php83/root/usr/bin/php',
            '/opt/cpanel/ea-php82/root/usr/bin/php',
            '/usr/bin/php',
        ];

        foreach ($candidates as $candidate) {
            if (@file_exists($candidate) && @is_executable($candidate)) {
                return $candidate;
            }
        }

        $binary = PHP_BINARY;
        if (!empty($binary)) {
            $lower = strtolower($binary);
            if (str_contains($lower, 'lsphp') || str_contains($lower, 'fpm') || str_contains($lower, 'cgi')) {
                return 'php';
            }
            return $binary;
        }

        return 'php';
    }
}
