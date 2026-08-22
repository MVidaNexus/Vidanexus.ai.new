<?php

namespace Modules\GlobalNewsMonitor\Http\Controllers;

use App\Support\CountryRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\GlobalNewsMonitor\Services\NewsMonitorService;

class GlobalNewsMonitorController extends Controller
{
    protected $service;

    public function __construct(NewsMonitorService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $isAjax = $request->ajax();
        
        $availableCountriesText = \App\Models\Setting::get('global-news-monitor_available_countries', '');
        $activeCountriesRaw = \App\Models\Setting::get('global-news-monitor_countries', '[]');
        $activeCountries = is_array($activeCountriesRaw) ? $activeCountriesRaw : json_decode($activeCountriesRaw, true);

        // effectiveMap intersects the tool list with the global visibility
        // registry — countries hidden globally never leak into this tool.
        $countryMap = CountryRegistry::effectiveMap(
            (string) $availableCountriesText,
            is_array($activeCountries) ? $activeCountries : null
        );

        $availableTopicsText = \App\Models\Setting::get('global-news-monitor_available_topics', "WORLD:World\nNATION:Nation\nBUSINESS:Business\nTECHNOLOGY:Technology\nENTERTAINMENT:Entertainment\nSPORTS:Sports\nSCIENCE:Science\nHEALTH:Health");
        $activeTopicsRaw = \App\Models\Setting::get('global-news-monitor_topics', '[]');
        $activeTopics = is_array($activeTopicsRaw) ? $activeTopicsRaw : json_decode($activeTopicsRaw, true);
        
        $iconMap = [
            'GENERAL' => 'fas fa-newspaper', 'WORLD' => 'fas fa-globe', 'NATION' => 'fas fa-flag',
            'BUSINESS' => 'fas fa-briefcase', 'TECHNOLOGY' => 'fas fa-microchip', 'ENTERTAINMENT' => 'fas fa-film',
            'SPORTS' => 'fas fa-futbol', 'SCIENCE' => 'fas fa-flask', 'HEALTH' => 'fas fa-heartbeat'
        ];

        $topicsMap = [];
        foreach(explode("\n", $availableTopicsText) as $line) {
            $parts = explode(':', trim($line));
            if(count($parts) >= 2) {
                $key = strtoupper(trim($parts[0]));
                if(empty($activeTopics) || in_array($key, $activeTopics)) {
                    $topicsMap[$key] = [
                        'name' => trim($parts[1]),
                        'icon' => $iconMap[$key] ?? 'fas fa-tag'
                    ];
                }
            }
        }
        
        // Fallback for safety
        if(empty($countryMap)) $countryMap = ['US' => ['name' => 'United States', 'flag' => '🇺🇸', 'lang' => 'en']];
        if(empty($topicsMap)) $topicsMap = ['WORLD' => ['name' => 'World', 'icon' => 'fas fa-globe']];

        $resolved = CountryRegistry::resolveRegion($request->get('region'), $countryMap, CountryRegistry::defaultRegion());
        $region = $resolved['region'];
        $currentCountry = $resolved['country'];
        $topic = $request->get('topic', 'WORLD');
        if (!array_key_exists($topic, $topicsMap)) {
            $topic = array_key_first($topicsMap) ?: 'WORLD';
        }

        $forceRefresh = $request->get('refresh') == '1';

        // USER REQUEST: Charge on initial load (Non-AJAX), Refresh (F5), or Update Button (refresh=1)
        // We charge regardless of cache status for these specific events.
        $shouldCharge = ($isAjax || $forceRefresh);

        if ($shouldCharge) {
            $user = auth()->user();

            if (!$user->canUseTool('global-news-monitor')) {
                $msg = $user->getLimitReachedMessage('Global News Monitor', 'global-news-monitor');
                
                if ($isAjax) return response()->json(['error' => $msg], 403);
                return back()->with('error', $msg);
            }

            if (!$user->wallet || $user->wallet->balance_credits < 1) {
                if ($isAjax) return response()->json(['error' => 'Insufficient balance for tracking news.'], 402);
                return back()->with('error', 'Insufficient balance for tracking news.');
            }
        }

        $cacheKey = "google_news_radar_v5_{$region}_{$topic}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // Read admin-configured time window
        $timeWindow = \App\Models\Setting::get('global-news-monitor_time_window', '24h');

        $countryName = $currentCountry['name'] ?? '';
        $lang = CountryRegistry::langFor($region);

        $googleNews = [];
        if ($isAjax || $forceRefresh) {
            $cached = $forceRefresh ? null : Cache::get($cacheKey);
            if (is_array($cached) && count($cached) > 0) {
                $googleNews = $cached;
            } else {
                Cache::forget($cacheKey);
                $googleNews = $this->service->fetchGoogleNews($region, $topic, $lang, $timeWindow, $countryName);
                if (count($googleNews) > 0) {
                    Cache::put($cacheKey, $googleNews, 300);
                }
            }
        }

        if ($shouldCharge && !empty($googleNews)) {
            $chargeUser = auth()->user();
            if (! $chargeUser->deductToolCredits('global-news-monitor')) {
                \Illuminate\Support\Facades\Log::critical('[Global News Monitor] Credits could not be deducted after successful fetch', [
                    'user_id' => $chargeUser->id,
                ]);
            }
            \App\Models\AiUsage::create([
                'user_id' => $chargeUser->id,
                'tool' => 'global-news-monitor',
                'provider' => 'rss',
                'model' => 'google-news',
                'status' => 'success',
            ]);
        }

        $thresholdHigh = (int) \App\Models\Setting::get('global-news-monitor_threshold_high', 70);
        $thresholdModerate = (int) \App\Models\Setting::get('global-news-monitor_threshold_moderate', 45);

        // Handle AJAX requests
        if ($isAjax) {
            $html = view('globalnewsmonitor::partials.news_grid', compact('googleNews', 'region', 'lang', 'thresholdHigh', 'thresholdModerate', 'topic'))->render();

            // Echo the post-deduction wallet balance so the front-end can
            // animate the chip in place (credits-live.js / VidaCredits.apply).
            $balance = null;
            if ($shouldCharge && !empty($googleNews)) {
                $balanceUser = auth()->user();
                $balanceUser->load('wallet');
                $balance = (float) ($balanceUser->wallet->balance_credits ?? 0);
            }

            return response()->json([
                'html' => $html,
                'stats' => [
                    'total' => count($googleNews),
                    'high' => collect($googleNews)->where('seo_score', '>=', $thresholdHigh)->count(),
                    'moderate' => collect($googleNews)->where('seo_score', '>=', $thresholdModerate)->where('seo_score', '<', $thresholdHigh)->count()
                ],
                'balance' => $balance,
            ]);
        }

        $isInitial = empty($googleNews) && !$forceRefresh && !$isAjax;
        return view('globalnewsmonitor::index', compact('countryMap', 'topicsMap', 'currentCountry', 'googleNews', 'topic', 'lang', 'region', 'thresholdHigh', 'thresholdModerate', 'isInitial'));
    }

    /**
     * On-demand AI Deep Analysis for a single article
     */
    public function analyzeArticle(Request $request)
    {
        $user = auth()->user();
        $slug = 'global-news-monitor';

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $msg = $hasOwnership
                ? "Insufficient balance. Required: {$cost} CRS."
                : 'Tool access denied.';
            return response()->json(['success' => false, 'message' => $msg], $hasOwnership ? 402 : 403);
        }

        $title = $request->input('title', '');
        $description = $request->input('description', '');
        $country = $request->input('country', 'EG');
        $lang = $request->input('lang', 'ar');
        $topic = $request->input('topic', 'WORLD');

        if (empty($title)) {
            return response()->json(['success' => false, 'message' => 'Title is required.'], 422);
        }

        $result = $this->service->analyzeArticleWithAI($title, $description, $country, $lang, $topic);

        if ($result['success']) {
            if (! $user->deductToolCredits($slug)) {
                \Illuminate\Support\Facades\Log::critical('[Global News Monitor] Credits could not be deducted after successful analysis', [
                    'user_id' => $user->id,
                ]);
            }
            \App\Models\AiUsage::create([
                'user_id'  => $user->id,
                'tool'     => $slug,
                'provider' => 'ai',
                'model'    => 'ai-analysis',
                'status'   => 'success',
            ]);
            $user->load('wallet');
            $result['balance'] = (float) ($user->wallet->balance_credits ?? 0);
        }

        return response()->json($result);
    }

    /**
     * Extract keywords from selected titles (client-side aggregation helper).
     */
    public function extractKeywords(Request $request)
    {
        $titles = $request->input('titles', []);
        if (! is_array($titles) || empty($titles)) {
            return response()->json(['success' => false, 'message' => 'No titles provided.'], 422);
        }

        $titles = array_values(array_filter(array_map('trim', $titles)));
        $keywords = $this->service->extractKeywordsFromTitles($titles);

        return response()->json([
            'success' => true,
            'keywords' => $keywords,
            'count' => count($keywords),
        ]);
    }

    /**
     * Generate a unified content brief from multiple selected titles.
     */
    public function generateBrief(Request $request)
    {
        $user = auth()->user();
        $slug = 'global-news-monitor';

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $msg = $hasOwnership
                ? "Insufficient balance. Required: {$cost} CRS."
                : 'Tool access denied.';

            return response()->json(['success' => false, 'message' => $msg], $hasOwnership ? 402 : 403);
        }

        $titles = $request->input('titles', []);
        if (! is_array($titles) || count($titles) < 1) {
            return response()->json(['success' => false, 'message' => 'Select at least one title.'], 422);
        }

        $titles = array_values(array_filter(array_map('trim', $titles)));
        $keywords = $request->input('keywords', []);
        if (! is_array($keywords) || empty($keywords)) {
            $keywords = $this->service->extractKeywordsFromTitles($titles);
        }

        $country = $request->input('country', 'EG');
        $lang = $request->input('lang', 'ar');
        $topic = $request->input('topic', 'WORLD');

        $result = $this->service->generateMultiTitleBrief($titles, $keywords, $country, $lang, $topic);

        if ($result['success']) {
            if (! $user->deductToolCredits($slug)) {
                \Illuminate\Support\Facades\Log::critical('[Global News Monitor] Credits could not be deducted after successful brief generation', [
                    'user_id' => $user->id,
                ]);
            }
            \App\Models\AiUsage::create([
                'user_id'  => $user->id,
                'tool'     => $slug,
                'provider' => 'ai',
                'model'    => 'multi-title-brief',
                'status'   => 'success',
            ]);
            $user->load('wallet');
            $result['balance'] = (float) ($user->wallet->balance_credits ?? 0);
            $result['keywords'] = $keywords;
        }

        return response()->json($result);
    }
}
