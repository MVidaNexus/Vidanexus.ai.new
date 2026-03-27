<?php

namespace Modules\TrendingSearchMonitor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class TrendingSearchController extends Controller
{
    /**
     * Display the trending searches page
     */
    public function index(Request $request)
    {
        $countryMap = config('keywords.countries', []);
        
        // Load configurations from Horizon Admin
        $settings = [];
        $settingsRaw = Setting::where('group', 'tool_settings')
            ->where('key', 'like', 'trending-search-monitor_%')
            ->get();
        foreach ($settingsRaw as $s) {
            $key = str_replace('trending-search-monitor_', '', $s->key);
            $settings[$key] = $s->value;
        }

        // Apply Country Whitelist if Admin configured it
        if (!empty($settings['available_countries'])) {
            $countryMap = [];
            foreach(explode("\n", trim($settings['available_countries'])) as $line) {
                $parts = explode(':', trim($line));
                if(count($parts) === 2) {
                    $code = strtoupper(trim($parts[0]));
                    $nameStr = trim($parts[1]);
                    
                    // Try to extract flag from the end of the text (emojis)
                    $flag = '';
                    if (preg_match('/(.*?)\s*([\x{1F1E6}-\x{1F1FF}]{2}|[\p{So}\p{Sk}]+)$/u', $nameStr, $matches)) {
                        $nameStr = trim($matches[1]);
                        $flag = trim($matches[2]);
                    }
                    
                    // Base data from config if exists, otherwise fallback
                    $baseConfig = config("keywords.countries.{$code}", []);
                    
                    $countryMap[$code] = array_merge([
                        'name' => $nameStr,
                        'code' => $code,
                        'flag' => $flag,
                        'lang' => 'ar' // Fallback
                    ], $baseConfig); // Overwrite missing keys with base config
                    
                    // Force the name to exactly what the admin typed if it's different
                    $countryMap[$code]['name'] = $nameStr ?: ($baseConfig['name'] ?? $code);
                    $countryMap[$code]['flag'] = $flag ?: ($baseConfig['flag'] ?? '');
                }
            }
        }
        
        $activeCountries = json_decode($settings['countries'] ?? 'null', true);
        if (is_array($activeCountries) && count($activeCountries) > 0) {
            $filteredMap = [];
            foreach($activeCountries as $c) {
                $c = strtoupper($c);
                if (isset($countryMap[$c])) {
                    $filteredMap[$c] = $countryMap[$c];
                }
            }
            if (count($filteredMap) > 0) {
                $countryMap = $filteredMap;
            }
        }

        // Use first available country as default if 'EG' is not allowed
        $defaultRegion = isset($countryMap['EG']) ? 'EG' : (array_key_first($countryMap) ?: 'EG');
        $region = strtoupper($request->get('country', $defaultRegion));

        // Validate region exists in our country map
        if (!isset($countryMap[$region])) {
            $region = $defaultRegion;
        }

        $currentCountry = $countryMap[$region] ?? ['name' => $region, 'code' => $region, 'lang' => 'ar'];
        $currentCountry['code'] = $region;

        // Check limits before fetching
        $user = auth()->user();
        if (!$user->canUseTool('trending-search-monitor')) {
            $msg = $user->getLimitReachedMessage('Trending Search Monitor', 'trending-search-monitor');
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            $msg = 'Insufficient balance to track trends.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $msg], 402);
            }
            return back()->with('error', $msg);
        }

        $forceRefresh = $request->has('refresh') || $request->has('force');

        // Extract Feed Type and Category from Admin settings
        $feedType = $settings['feed_type'] ?? 'daily';
        $category = $settings['category'] ?? 'all';

        // Fetch trending search suggestions for the selected country
        $trends = $this->fetchTrendingSuggestions($region, $currentCountry['lang'] ?? 'ar', $forceRefresh, $feedType, $category);

        $isAjax = $request->ajax() || $request->wantsJson();
        $shouldCharge = !$isAjax || $forceRefresh;

        if ($shouldCharge && !empty($trends)) {
            $user->wallet->decrement('balance_credits', 1);
            \App\Models\AiUsage::create([
                'user_id' => $user->id,
                'tool' => 'trending-search-monitor',
                'provider' => 'rss',
                'model' => 'google-trends',
                'status' => 'success',
            ]);
        }

        // If AJAX request, return JSON
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'trends' => $trends,
                'country' => $currentCountry,
                'count' => count($trends),
                'cached_at' => now()->format('h:i A'),
            ]);
        }

        return view('trendingsearchmonitor::index', compact('trends', 'currentCountry', 'countryMap', 'region'));
    }

    /**
     * Fetch trending search data from Google Trends (cached)
     */
    protected function fetchTrendingSuggestions($region, $lang = 'ar', $forceRefresh = false, $feedType = 'daily', $category = 'all')
    {
        $cacheKey = "trending_suggestions_{$region}_{$lang}_{$feedType}_{$category}";
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, function () use ($region, $lang, $feedType, $category) {
            if ($feedType === 'realtime') {
                return $this->fetchRealtimeFresh($region, $lang, $category);
            }
            return $this->fetchDailyFresh($region, $lang);
        });
    }

    /**
     * Fetch fresh Daily trending searches from Google Trends RSS feed
     */
    protected function fetchDailyFresh($region, $lang)
    {
        $region = strtoupper($region);
        Log::info("TrendingSearch: Fetching fresh trends from RSS for {$region}");
        
        $url = "https://trends.google.com/trending/rss?geo={$region}";

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/rss+xml, application/xml, text/xml',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                Log::warning("TrendingSearch: RSS failed for {$region}, status: " . $response->status());
                return [];
            }

            $body = $response->body();
            
            // Parse RSS XML
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body);
            
            if ($xml === false) {
                Log::warning("TrendingSearch: Could not parse RSS XML for {$region}");
                return [];
            }

            $items = [];
            $channel = $xml->channel ?? null;
            if (!$channel || !isset($channel->item)) {
                return [];
            }

            foreach ($channel->item as $item) {
                $title = trim((string) $item->title);
                if (empty($title)) continue;

                // Get data from ht: namespace
                $ht = $item->children('ht', true);
                
                $traffic = isset($ht->approx_traffic) ? (string) $ht->approx_traffic : null;
                $picture = isset($ht->picture) ? (string) $ht->picture : null;
                
                // Extract all news items for a richer dashboard (like the Google app)
                $newsItems = [];
                if (isset($ht->news_item)) {
                    foreach ($ht->news_item as $ni) {
                        $newsItems[] = [
                            'title' => html_entity_decode(trim((string) $ni->news_item_title), ENT_QUOTES, 'UTF-8'),
                            'url' => (string) $ni->news_item_url,
                            'source' => (string) $ni->news_item_source,
                            'snippet' => (string) $ni->news_item_snippet,
                        ];
                        if (count($newsItems) >= 3) break;
                    }
                }

                $entry = [
                    'title' => $title,
                    'traffic' => $traffic,
                    'image' => $picture,
                    'news' => $newsItems,
                    'subtitle' => $newsItems[0]['title'] ?? ($traffic ? "🔥 {$traffic} searches" : null),
                ];

                $items[] = $entry;
            }

            Log::info("TrendingSearch [Daily]: SUCCESS. Fetched " . count($items) . " items for {$region}");
            return $items;

        } catch (\Exception $e) {
            Log::error("TrendingSearch [Daily] error for {$region}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch fresh Realtime trending searches from Google Trends API
     */
    protected function fetchRealtimeFresh($region, $lang, $category = 'all')
    {
        $region = strtoupper($region);
        Log::info("TrendingSearch [Realtime]: Fetching fresh top trends for {$region} (Cat: {$category})");
        
        $hl = $lang === 'ar' ? 'ar' : 'en-US';
        // Note: Realtime API does not natively support many Middle Eastern countries, but this is managed by Admin
        $url = "https://trends.google.com/trends/api/realtimetrends?hl={$hl}&tz=-120&cat={$category}&fi=0&fs=0&geo={$region}&ri=300&rs=20&sort=0";

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                Log::warning("TrendingSearch [Realtime]: API failed for {$region}, status: " . $response->status());
                return [];
            }

            $body = $response->body();
            // Google prefixes API responses with )]}',
            if (strpos($body, ")]}',\n") !== false) {
                $body = str_replace(")]}',\n", "", $body);
            }

            $data = json_decode($body, true);
            if (!isset($data['storySummaries']['trendingStories'])) {
                Log::warning("TrendingSearch [Realtime]: No trending stories found for {$region}. The region might not be supported for Realtime Trends.");
                return [];
            }

            $items = [];
            foreach ($data['storySummaries']['trendingStories'] as $story) {
                $title = $story['entityNames'][0] ?? 'New Trend';
                if (isset($story['title']) && !empty($story['title'])) {
                    // Sometimes Google offers a better title string
                    $title = $story['title'];
                }

                $picture = $story['image']['newsUrl'] ?? ($story['image']['imageUrl'] ?? null);
                
                // Extract news items
                $newsItems = [];
                if (isset($story['articles'])) {
                    foreach ($story['articles'] as $article) {
                        $newsItems[] = [
                            'title' => html_entity_decode(strip_tags($article['articleTitle']), ENT_QUOTES, 'UTF-8'),
                            'url' => $article['url'],
                            'source' => $article['source'],
                            'snippet' => $article['snippet'] ?? '',
                        ];
                        if (count($newsItems) >= 3) break;
                    }
                }

                $entry = [
                    'title' => $title,
                    'traffic' => null, // Realtime API doesn't provide exact traffic
                    'image' => $picture,
                    'news' => $newsItems,
                    'subtitle' => $newsItems[0]['title'] ?? 'Trending Now 🔥',
                ];

                $items[] = $entry;
            }

            Log::info("TrendingSearch [Realtime]: SUCCESS. Fetched " . count($items) . " items for {$region}");
            return $items;

        } catch (\Exception $e) {
            Log::error("TrendingSearch [Realtime] error for {$region}: " . $e->getMessage());
            return [];
        }
    }
}
