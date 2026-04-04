<?php

namespace Modules\GlobalNewsMonitor\Http\Controllers;

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
        
        // Professional Dynamic Filters from Settings & Config
        $defaultFallbackMap = config('keywords.countries', []);
        $fallbackText = [];
        foreach($defaultFallbackMap as $code => $data) {
            $fallbackText[] = $code . ':' . $data['name'] . ' ' . $data['flag'];
        }
        $availableCountriesText = \App\Models\Setting::get('global-news-monitor_available_countries', implode("\n", $fallbackText));
        $activeCountriesRaw = \App\Models\Setting::get('global-news-monitor_countries', '[]');
        $activeCountries = is_array($activeCountriesRaw) ? $activeCountriesRaw : json_decode($activeCountriesRaw, true);
        
        $countryMap = [];
        foreach(explode("\n", $availableCountriesText) as $line) {
            $parts = explode(':', trim($line));
            if(count($parts) >= 2) {
                $code = strtoupper(trim($parts[0]));
                if(empty($activeCountries) || in_array($code, $activeCountries)) {
                    $nameStr = trim($parts[1]);
                    
                    // Try to extract flag from the end of the text (emojis)
                    $flag = '';
                    if (preg_match('/(.*?)\s*([\x{1F1E6}-\x{1F1FF}]{2}|[\p{So}\p{Sk}]+)$/u', $nameStr, $matches)) {
                        $nameStr = trim($matches[1]);
                        $flag = trim($matches[2]);
                    }
                    
                    $sysConfig = config("keywords.countries.{$code}");
                    
                    if ($sysConfig) {
                        $countryMap[$code] = [
                            'name' => $nameStr ?: $sysConfig['name'],
                            'flag' => $flag ?: $sysConfig['flag'],
                            'lang' => $sysConfig['lang']
                        ];
                    } else {
                        $countryMap[$code] = [
                            'name' => $nameStr,
                            'flag' => $flag ?: '🌐',
                            'lang' => in_array($code, ['US', 'GB', 'FR', 'CA', 'AU', 'PL']) ? 'en' : 'ar'
                        ];
                    }
                }
            }
        }

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

        $region = $request->get('region', 'EG');
        if (!array_key_exists($region, $countryMap)) {
            $region = 'EG';
        }
        $currentCountry = $countryMap[$region];
        $topic = $request->get('topic', 'WORLD');
        if (!array_key_exists($topic, $topicsMap)) {
            $topic = array_key_first($topicsMap) ?: 'WORLD';
        }

        $forceRefresh = $request->get('refresh') == '1';

        // USER REQUEST: Charge on initial load (Non-AJAX), Refresh (F5), or Update Button (refresh=1)
        // We charge regardless of cache status for these specific events.
        $shouldCharge = !$isAjax || $forceRefresh;

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

        $cacheKey = "google_news_radar_v3_{$region}_{$topic}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        // Read admin-configured time window
        $timeWindow = \App\Models\Setting::get('global-news-monitor_time_window', '12h');

        $countryName = $currentCountry['name'] ?? '';
        $lang = $currentCountry['lang'] ?? 'ar'; // Define $lang here

        $googleNews = Cache::remember($cacheKey, 300, function () use ($region, $topic, $timeWindow, $currentCountry) {
            return $this->service->fetchGoogleNews($region, $topic, $currentCountry['lang'] ?? 'ar', $timeWindow, $currentCountry['name'] ?? '');
        });

        if ($shouldCharge && !empty($googleNews)) {
            auth()->user()->wallet->decrement('balance_credits', 1);
            \App\Models\AiUsage::create([
                'user_id' => auth()->id(),
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
            return view('globalnewsmonitor::partials.news_grid', compact('googleNews', 'region', 'lang', 'thresholdHigh', 'thresholdModerate'))->render();
        }

        return view('globalnewsmonitor::index', compact('countryMap', 'topicsMap', 'currentCountry', 'googleNews', 'topic', 'lang', 'region', 'thresholdHigh', 'thresholdModerate'));
    }

    /**
     * On-demand AI Deep Analysis for a single article
     */
    public function analyzeArticle(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canUseTool('global-news-monitor')) {
            return response()->json(['success' => false, 'message' => 'Tool access denied.'], 403);
        }

        $syncCredits = 1;
        if (!$user->wallet || $user->wallet->balance_credits < $syncCredits) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance. Required: 1 Credit.'], 402);
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
            // Charge credits only on success
            $user->wallet->decrement('balance_credits', $syncCredits);
            \App\Models\AiUsage::create([
                'user_id'  => $user->id,
                'tool'     => 'global-news-monitor',
                'provider' => 'ai',
                'model'    => 'ai-analysis',
                'status'   => 'success',
            ]);
        }

        return response()->json($result);
    }
}
