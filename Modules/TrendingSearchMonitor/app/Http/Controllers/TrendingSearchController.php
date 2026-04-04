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

        $forceRefresh = $request->has('refresh') || $request->has('force') || $request->has('fetch');
        $platform = $request->get('platform', 'google'); // google, x (twitter), tiktok, youtube

        // ─── V2.6: Source Availability Check ───
        $isGoogleEnabled = (bool)($settings['source_google_enabled'] ?? true);
        $isXEnabled = (bool)($settings['source_x_enabled'] ?? true);
        $isTikTokEnabled = (bool)($settings['source_tiktok_enabled'] ?? true);
        $isYouTubeEnabled = (bool)($settings['source_youtube_enabled'] ?? true);

        // Build enabled platforms map for frontend tab visibility
        $enabledPlatforms = [
            'google' => $isGoogleEnabled,
            'x' => $isXEnabled,
            'tiktok' => $isTikTokEnabled,
            'youtube' => $isYouTubeEnabled,
        ];

        if ($platform === 'google' && !$isGoogleEnabled) return response()->json(['error' => 'Google Trends source is currently disabled by administrator.'], 403);
        if (($platform === 'x' || $platform === 'twitter') && !$isXEnabled) return response()->json(['error' => 'X (Twitter) source is currently disabled by administrator.'], 403);
        if ($platform === 'tiktok' && !$isTikTokEnabled) return response()->json(['error' => 'TikTok source is currently disabled by administrator.'], 403);
        if ($platform === 'youtube' && !$isYouTubeEnabled) return response()->json(['error' => 'YouTube source is currently disabled by administrator.'], 403);

        // V2.0: Only fetch if explicitly requested (Ajax, Fetch param, or Refresh)
        $isAjax = $request->ajax() || $request->wantsJson();
        $shouldFetch = $isAjax || $request->has('fetch') || $request->has('refresh');

        $trends = [];
        if ($shouldFetch) {
            // Extract Feed Type and Category from Admin settings
            $feedType = $settings['feed_type'] ?? 'daily';
            $category = $settings['category'] ?? 'all';

            // Fetch trends based on platform
            $maxTrends = (int)($settings['max_trends'] ?? 50);
            
            if ($platform === 'x' || $platform === 'twitter') {
                $trends = $this->fetchXTrends($region, $currentCountry['name'], $forceRefresh, $maxTrends);
            } elseif ($platform === 'tiktok') {
                $trends = $this->fetchTikTokTrends($region, $currentCountry['name'], $forceRefresh, $maxTrends);
            } elseif ($platform === 'youtube') {
                $trends = $this->fetchYouTubeTrends($region, $currentCountry['name'], $forceRefresh, $maxTrends);
            } else {
                // Default: Google
                $trends = $this->fetchTrendingSuggestions($region, $currentCountry['lang'] ?? 'ar', $forceRefresh, $feedType, $category, $maxTrends);
            }

            // Charge credits only on explicit fetch/refresh
            if ($shouldFetch && !empty($trends) && !($isAjax && !$forceRefresh)) {
                $user->wallet->decrement('balance_credits', 1);
                \App\Models\AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'trending-search-monitor',
                    'provider' => 'rss_scraper',
                    'model' => $platform . '-trends',
                    'status' => 'success',
                ]);
            }
        }

        // If AJAX request, return JSON
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'trends' => $trends,
                'country' => $currentCountry,
                'count' => count($trends),
                'platform' => $platform,
                'cached_at' => now()->format('h:i A'),
            ]);
        }

        return view('trendingsearchmonitor::index', compact('trends', 'currentCountry', 'countryMap', 'region', 'platform', 'enabledPlatforms'));
    }

    /**
     * Fetch trending search data from Google Trends (cached)
     */
    protected function fetchTrendingSuggestions($region, $lang = 'ar', $forceRefresh = false, $feedType = 'daily', $category = 'all', $maxTrends = 20)
    {
        $cacheKey = "trending_suggestions_{$region}_{$lang}_{$feedType}_{$category}";
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $trends = Cache::remember($cacheKey, 300, function () use ($region, $lang, $feedType, $category) {
            if ($feedType === 'realtime') {
                return $this->fetchRealtimeFresh($region, $lang, $category);
            }
            return $this->fetchDailyFresh($region, $lang);
        });

        return array_slice($trends, 0, $maxTrends);
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
    /**
     * Fetch Trends from X (Twitter) via Trends24 Mirror
     */
    protected function fetchXTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_x_{$region}";
        if ($forceRefresh) Cache::forget($cacheKey);

        $trends = Cache::remember($cacheKey, 300, function () use ($region, $countryName, $maxTrends) {
            $slug = str_replace(' ', '-', strtolower($countryName));
            // Special mappings for country slugs
            $map = [
                'EG' => 'egypt', 'SA' => 'saudi-arabia', 'AE' => 'united-arab-emirates',
                'US' => 'united-states', 'GB' => 'united-kingdom', 'KW' => 'kuwait',
                'QA' => 'qatar', 'BH' => 'bahrain', 'OM' => 'oman', 'JO' => 'jordan',
                'IQ' => 'iraq', 'LB' => 'lebanon', 'MA' => 'morocco', 'DZ' => 'algeria',
                'TN' => 'tunisia', 'PL' => 'poland', 'TR' => 'turkey', 'DE' => 'germany',
                'FR' => 'france', 'IN' => 'india', 'JP' => 'japan', 'BR' => 'brazil',
            ];
            $slug = $map[strtoupper($region)] ?? $slug;

            $url = "https://trends24.in/{$slug}/";
            Log::info("X Trends: Fetching from {$url}");
            
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])->timeout(10)->get($url);

                if ($response->failed()) {
                    Log::warning("X Trends: HTTP failed for {$region}, status: " . $response->status());
                    return [];
                }

                $html = $response->body();
                $items = [];
                $seen = [];
                
                // Correct pattern: Trends24 uses <a class=trend-link href="...">TrendName</a>
                preg_match_all('/<a[^>]*class=["\']?trend-link["\']?[^>]*>([^<]+)<\/a>/iu', $html, $matches);

                if (!empty($matches[1])) {
                    foreach ($matches[1] as $title) {
                        $title = html_entity_decode(trim($title), ENT_QUOTES, 'UTF-8');
                        if (empty($title)) continue;
                        
                        // Deduplicate (Trends24 repeats trends across time blocks)
                        $lower = mb_strtolower($title);
                        if (isset($seen[$lower])) continue;
                        $seen[$lower] = true;

                        $items[] = [
                            'title' => $title,
                            'traffic' => 'Trending',
                            'image' => null,
                            'news' => [],
                            'subtitle' => "🔥 Viral on X in {$countryName}",
                            'platform' => 'x'
                        ];
                        if (count($items) >= $maxTrends) break;
                    }
                }

                Log::info("X Trends: SUCCESS. Fetched " . count($items) . " unique trends for {$region}");
                return $items;
            } catch (\Exception $e) {
                Log::error("X Trends Error for {$region}: " . $e->getMessage());
                return [];
            }
        });

        return $trends;
    }
    /**
     * Fetch YouTube Trending Videos for a specific country
     * Strategy: Invidious API → Piped API → Google Fallback
     */
    protected function fetchYouTubeTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_youtube_{$region}";
        if ($forceRefresh) Cache::forget($cacheKey);

        $trends = Cache::remember($cacheKey, 3600, function () use ($region, $countryName, $maxTrends) {
            
            // Strategy 1: Invidious API (public YouTube mirror with JSON endpoints)
            $invidiousInstances = [
                'https://vid.puffyan.us',
                'https://invidious.fdn.fr',
                'https://y.com.sb',
                'https://invidious.nerdvpn.de',
            ];

            foreach ($invidiousInstances as $instance) {
                $items = $this->tryInvidiousAPI($instance, $region, $countryName, $maxTrends);
                if (!empty($items)) {
                    Log::info("YouTube Trends [Invidious]: SUCCESS via {$instance}. Fetched " . count($items) . " for {$region}");
                    return $items;
                }
            }

            // Strategy 2: Piped API (another YouTube mirror)
            $pipedInstances = [
                'https://pipedapi.kavin.rocks',
                'https://api.piped.yt',
            ];

            foreach ($pipedInstances as $instance) {
                $items = $this->tryPipedAPI($instance, $region, $countryName, $maxTrends);
                if (!empty($items)) {
                    Log::info("YouTube Trends [Piped]: SUCCESS via {$instance}. Fetched " . count($items) . " for {$region}");
                    return $items;
                }
            }

            // Strategy 3: Google video search fallback
            $items = $this->tryYouTubeGoogleFallback($region, $countryName, $maxTrends);
            if (!empty($items)) {
                Log::info("YouTube Trends [Google]: SUCCESS. Fetched " . count($items) . " for {$region}");
                return $items;
            }

            Log::warning("YouTube Trends: All strategies failed for {$region}.");
            return [];
        });

        return array_slice($trends, 0, $maxTrends);
    }

    /**
     * Try Invidious API for YouTube trending videos
     */
    protected function tryInvidiousAPI($instance, $region, $countryName, $maxTrends)
    {
        try {
            $url = "{$instance}/api/v1/trending?region=" . strtoupper($region);
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $videos = $response->json();
            if (!is_array($videos) || empty($videos)) return [];

            $items = [];
            foreach ($videos as $video) {
                $title = $video['title'] ?? '';
                if (empty($title) || mb_strlen($title) < 5) continue;

                $videoId = $video['videoId'] ?? '';
                $channel = $video['author'] ?? '';
                $views = $video['viewCount'] ?? 0;
                $viewsFormatted = $views > 0 ? number_format($views) . ' views' : 'Trending';
                
                // Use best available thumbnail
                $thumbnail = null;
                if (!empty($video['videoThumbnails'])) {
                    foreach ($video['videoThumbnails'] as $thumb) {
                        if (($thumb['quality'] ?? '') === 'medium' || ($thumb['quality'] ?? '') === 'default') {
                            $thumbnail = $thumb['url'] ?? null;
                            break;
                        }
                    }
                    if (!$thumbnail) {
                        $thumbnail = $video['videoThumbnails'][0]['url'] ?? null;
                    }
                }
                if (!$thumbnail && $videoId) {
                    $thumbnail = "https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg";
                }

                $items[] = [
                    'title' => $title,
                    'traffic' => $viewsFormatted,
                    'image' => $thumbnail,
                    'news' => [[
                        'title' => $channel ?: 'Watch on YouTube',
                        'url' => "https://www.youtube.com/watch?v={$videoId}",
                        'source' => 'YouTube'
                    ]],
                    'subtitle' => "🔥 Trending on YouTube in {$countryName}",
                    'platform' => 'youtube'
                ];

                if (count($items) >= $maxTrends) break;
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning("Invidious API ({$instance}) failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Try Piped API for YouTube trending videos
     */
    protected function tryPipedAPI($instance, $region, $countryName, $maxTrends)
    {
        try {
            $url = "{$instance}/trending?region=" . strtoupper($region);
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $videos = $response->json();
            if (!is_array($videos) || empty($videos)) return [];

            $items = [];
            foreach ($videos as $video) {
                $title = $video['title'] ?? '';
                if (empty($title) || mb_strlen($title) < 5) continue;

                $videoUrl = $video['url'] ?? '';
                $videoId = '';
                if (preg_match('/\/watch\?v=([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
                    $videoId = $m[1];
                }

                $channel = $video['uploaderName'] ?? $video['uploader'] ?? '';
                $views = $video['views'] ?? 0;
                $viewsFormatted = $views > 0 ? number_format($views) . ' views' : 'Trending';
                $thumbnail = $video['thumbnail'] ?? ($videoId ? "https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg" : null);

                $items[] = [
                    'title' => $title,
                    'traffic' => $viewsFormatted,
                    'image' => $thumbnail,
                    'news' => [[
                        'title' => $channel ?: 'Watch on YouTube',
                        'url' => "https://www.youtube.com/watch?v={$videoId}",
                        'source' => 'YouTube'
                    ]],
                    'subtitle' => "🔥 Trending on YouTube in {$countryName}",
                    'platform' => 'youtube'
                ];

                if (count($items) >= $maxTrends) break;
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning("Piped API ({$instance}) failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fallback: Google search for YouTube trending videos
     */
    protected function tryYouTubeGoogleFallback($region, $countryName, $maxTrends)
    {
        try {
            $query = urlencode("most popular youtube videos {$countryName} today 2026");
            $url = "https://www.google.com/search?q={$query}&gl=" . strtolower($region) . "&num=30";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $html = $response->body();
            $items = [];
            $seen = [];

            // Extract YouTube video IDs and titles from Google results
            preg_match_all('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $html, $idMatches);
            
            $ids = array_unique($idMatches[1] ?? []);

            foreach ($ids as $videoId) {
                if (isset($seen[$videoId])) continue;
                $seen[$videoId] = true;

                $items[] = [
                    'title' => "YouTube Video #{$videoId}",
                    'traffic' => 'Trending',
                    'image' => "https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg",
                    'news' => [[
                        'title' => 'Watch on YouTube',
                        'url' => "https://www.youtube.com/watch?v={$videoId}",
                        'source' => 'YouTube'
                    ]],
                    'subtitle' => "🔥 Trending on YouTube in {$countryName}",
                    'platform' => 'youtube'
                ];

                if (count($items) >= $maxTrends) break;
            }

            return $items;
        } catch (\Exception $e) {
            Log::warning("YouTube Google fallback failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch Trending Hashtags from TikTok
     * Strategy Chain: RapidAPI → Creative Center → Google Fallback → Empty
     */
    protected function fetchTikTokTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_tiktok_{$region}";
        if ($forceRefresh) Cache::forget($cacheKey);

        // Load TikTok API settings from admin panel
        $tiktokApiKey = Setting::get('trending-search-monitor_tiktok_api_key', '');
        $tiktokApiHost = Setting::get('trending-search-monitor_tiktok_api_host', '');
        $tiktokApiEndpoint = Setting::get('trending-search-monitor_tiktok_api_endpoint', '');

        $trends = Cache::remember($cacheKey, 3600, function () use ($region, $countryName, $maxTrends, $tiktokApiKey, $tiktokApiHost, $tiktokApiEndpoint) {
            
            // Strategy 0: RapidAPI (Highest Priority — configured by admin)
            if (!empty($tiktokApiKey) && !empty($tiktokApiHost)) {
                $items = $this->tryTikTokRapidAPI($region, $countryName, $maxTrends, $tiktokApiKey, $tiktokApiHost, $tiktokApiEndpoint);
                if (!empty($items)) {
                    Log::info("TikTok Trends [RapidAPI]: SUCCESS. Fetched " . count($items) . " for {$region}");
                    return $items;
                }
            }

            // Strategy 1: Try TikTok Creative Center internal API with browser-like headers
            $items = $this->tryTikTokCreativeCenter($region, $maxTrends);
            if (!empty($items)) {
                Log::info("TikTok Trends [Creative Center]: SUCCESS. Fetched " . count($items) . " for {$region}");
                return $items;
            }

            // Strategy 2: Scrape Google for "trending on TikTok" in the specific country
            $items = $this->tryGoogleTikTokTrends($region, $countryName, $maxTrends);
            if (!empty($items)) {
                Log::info("TikTok Trends [Google Fallback]: SUCCESS. Fetched " . count($items) . " for {$region}");
                return $items;
            }

            // Strategy 3: Return empty with a clear message — no fake data
            Log::warning("TikTok Trends: All strategies failed for {$region}. No data available.");
            return [];
        });

        return array_slice($trends, 0, $maxTrends);
    }

    /**
     * Fetch TikTok trends via RapidAPI (admin-configured external API)
     */
    protected function tryTikTokRapidAPI($region, $countryName, $maxTrends, $apiKey, $apiHost, $apiEndpoint)
    {
        try {
            $baseUrl = "https://{$apiHost}" . ltrim($apiEndpoint, '/');
            
            // Common query params that work with most RapidAPI TikTok providers
            $params = [
                'country' => strtoupper($region),
                'country_code' => strtoupper($region),
                'period' => 7,
                'count' => min($maxTrends, 50),
                'limit' => min($maxTrends, 50),
            ];

            $response = Http::withHeaders([
                'x-rapidapi-key' => $apiKey,
                'x-rapidapi-host' => $apiHost,
                'Accept' => 'application/json',
            ])->timeout(15)->get($baseUrl, $params);

            if ($response->failed()) {
                Log::warning("TikTok RapidAPI: HTTP {$response->status()} for {$region}");
                return [];
            }

            $data = $response->json();
            if (empty($data)) return [];

            $items = [];

            // Auto-detect response structure (different providers use different formats)
            $list = $data['data']['list'] ?? $data['data'] ?? $data['hashtags'] ?? $data['results'] ?? $data['trending'] ?? $data;
            
            if (!is_array($list)) return [];

            foreach ($list as $item) {
                if (!is_array($item)) continue;
                
                // Extract title from various possible keys
                $title = $item['hashtag_name'] ?? $item['name'] ?? $item['hashtag'] ?? $item['title'] ?? $item['tag'] ?? null;
                if (empty($title)) continue;
                
                // Ensure # prefix
                if (!str_starts_with($title, '#')) $title = '#' . $title;

                // Extract view/post count
                $views = $item['publish_cnt'] ?? $item['video_views'] ?? $item['views'] ?? $item['count'] ?? $item['posts'] ?? null;
                $viewsFormatted = $views ? number_format((int)$views) . ' posts' : 'Trending';

                $items[] = [
                    'title' => $title,
                    'traffic' => $viewsFormatted,
                    'image' => null,
                    'news' => [],
                    'subtitle' => "🚀 Trending on TikTok in {$countryName}",
                    'platform' => 'tiktok'
                ];

                if (count($items) >= $maxTrends) break;
            }

            return $items;

        } catch (\Exception $e) {
            Log::error("TikTok RapidAPI Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Try fetching from TikTok Creative Center internal API
     */
    protected function tryTikTokCreativeCenter($region, $maxTrends)
    {
        try {
            $url = "https://ads.tiktok.com/creative_radar_api/v1/popular_trend/hashtag/list";
            $params = [
                'period' => 7,
                'page' => 1,
                'limit' => min($maxTrends, 50),
                'country_code' => strtoupper($region),
                'sort_by' => 'popular',
            ];

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => 'https://ads.tiktok.com/business/creativecenter/inspiration/popular/hashtag/pc/en',
                'Origin' => 'https://ads.tiktok.com',
            ])->timeout(10)->get($url, $params);

            if ($response->failed()) return [];

            $data = $response->json();
            if (($data['code'] ?? 0) !== 0 || !isset($data['data']['list'])) return [];

            $items = [];
            foreach ($data['data']['list'] as $hashtag) {
                $title = '#' . ($hashtag['hashtag_name'] ?? $hashtag['name'] ?? '');
                if (empty($title) || $title === '#') continue;

                $views = $hashtag['publish_cnt'] ?? $hashtag['video_views'] ?? null;
                $viewsFormatted = $views ? number_format($views) . ' posts' : 'Trending';

                $items[] = [
                    'title' => $title,
                    'traffic' => $viewsFormatted,
                    'image' => null,
                    'news' => [],
                    'subtitle' => "🚀 Trending on TikTok",
                    'platform' => 'tiktok'
                ];
            }
            return $items;

        } catch (\Exception $e) {
            Log::warning("TikTok Creative Center API failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fallback: Use Google Search to find trending TikTok content for a specific country
     */
    protected function tryGoogleTikTokTrends($region, $countryName, $maxTrends)
    {
        try {
            // Strategy A: Search for TikTok tag pages directly
            $query = urlencode("site:tiktok.com/tag trending {$countryName} 2026");
            $url = "https://www.google.com/search?q={$query}&gl=" . strtolower($region) . "&num=50";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $html = $response->body();
            $items = [];
            $seen = [];

            // Extract TikTok tag URLs: tiktok.com/tag/HASHTAG
            preg_match_all('/tiktok\.com\/tag\/([A-Za-z0-9\p{L}\p{Arabic}]{2,40})/ui', $html, $tagMatches);
            if (!empty($tagMatches[1])) {
                foreach ($tagMatches[1] as $tag) {
                    $tag = urldecode($tag);
                    // Skip single characters and numeric-only
                    if (mb_strlen($tag) < 2 || ctype_digit($tag)) continue;
                    
                    $hashtag = '#' . $tag;
                    $lower = mb_strtolower($hashtag);
                    if (isset($seen[$lower])) continue;
                    $seen[$lower] = true;

                    $items[] = [
                        'title' => $hashtag,
                        'traffic' => 'Trending',
                        'image' => null,
                        'news' => [],
                        'subtitle' => "🚀 Trending on TikTok in {$countryName}",
                        'platform' => 'tiktok'
                    ];

                    if (count($items) >= $maxTrends) break;
                }
            }

            // Strategy B: If we got fewer than 5, try a broader search
            if (count($items) < 5) {
                $query2 = urlencode("تيك توك ترند {$countryName} OR \"tiktok trending\" \"{$countryName}\" hashtag");
                $url2 = "https://www.google.com/search?q={$query2}&gl=" . strtolower($region) . "&num=30";
                
                $response2 = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9,ar;q=0.8',
                ])->timeout(10)->get($url2);

                if ($response2->ok()) {
                    $html2 = $response2->body();
                    
                    // Extract hashtags from result snippets — but filter out hex colors and CSS
                    preg_match_all('/#([A-Za-z\p{Arabic}\p{L}][A-Za-z0-9\p{Arabic}\p{L}_]{2,30})/u', $html2, $hashtagMatches);
                    
                    if (!empty($hashtagMatches[0])) {
                        foreach ($hashtagMatches[0] as $hashtag) {
                            $hashtag = trim($hashtag);
                            $tagText = substr($hashtag, 1); // Remove #
                            
                            // Skip hex color codes (e.g. #681da8, #ff0000)
                            if (preg_match('/^[0-9a-f]{3,8}$/i', $tagText)) continue;
                            
                            // Skip CSS-like patterns (e.g. #content, #header, #main, #wrapper)
                            $cssPatterns = ['content', 'header', 'main', 'wrapper', 'footer', 'sidebar', 
                                          'container', 'body', 'nav', 'menu', 'section', 'article',
                                          'page', 'root', 'app', 'modal', 'btn', 'icon', 'text',
                                          'login', 'form', 'input', 'link', 'list', 'item', 'card',
                                          'title', 'desc', 'img', 'video', 'search', 'close',
                                          'responsive', 'mobile', 'desktop', 'tablet'];
                            if (in_array(strtolower($tagText), $cssPatterns)) continue;
                            
                            // Must contain at least one letter
                            if (!preg_match('/[A-Za-z\p{Arabic}\p{L}]/u', $tagText)) continue;
                            
                            $lower = mb_strtolower($hashtag);
                            if (isset($seen[$lower])) continue;
                            $seen[$lower] = true;

                            $items[] = [
                                'title' => $hashtag,
                                'traffic' => 'Trending',
                                'image' => null,
                                'news' => [],
                                'subtitle' => "🚀 Trending on TikTok in {$countryName}",
                                'platform' => 'tiktok'
                            ];

                            if (count($items) >= $maxTrends) break;
                        }
                    }
                }
            }

            return $items;

        } catch (\Exception $e) {
            Log::warning("Google TikTok fallback failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * AI Deep Analysis for a specific trend
     * Handles AJAX requests to decode trends and calculate monetization potential
     */
    public function analyzeTrend(Request $request)
    {
        $request->validate([
            'trend' => 'required|string',
            'country' => 'required|string',
            'lang' => 'required|string',
            'platform' => 'nullable|string',
            'headlines' => 'nullable|array'
        ]);

        $user = auth()->user();
        
        \Illuminate\Support\Facades\Log::info("[TrendingSearch AI] Incoming Request For: {$request->trend}", [
            'country' => $request->country,
            'headlines_count' => count($request->get('headlines', [])),
            'headlines' => $request->get('headlines', [])
        ]);
        
        // ─── 1. Access & Credit Control ───
        if (!$user->canUseTool('trending-search-monitor')) {
            return response()->json(['error' => 'You do not have access to this intelligence tool.'], 403);
        }

        // Setting: trending-search-monitor_ai_analysis_credits (Default: 2)
        $creditCost = (int) \App\Models\Setting::get('trending-search-monitor_ai_analysis_credits', 2);
        
        if (!$user->wallet || $user->wallet->balance_credits < $creditCost) {
            return response()->json(['error' => "Insufficient credits for AI Deep Intel. ({$creditCost} CRS required)"], 402);
        }

        try {
            $service = app(\Modules\TrendingSearchMonitor\Services\TrendIntelligenceService::class);
            $result = $service->analyzeTrendWithAI(
                $request->trend, 
                $request->country, 
                $request->lang, 
                $request->get('platform', 'google'),
                $request->get('headlines', [])
            );
            
            if ($result['success']) {
                // Deduct credits only on success
                $user->wallet->decrement('balance_credits', $creditCost);
                
                \App\Models\AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'trending-search-monitor',
                    'provider' => 'ai_intel',
                    'model' => 'gpt-4-trend-analyst',
                    'status' => 'success',
                ]);

                return response()->json($result);
            }

            return response()->json(['error' => $result['message'] ?? 'AI failure.'], 500);

        } catch (\Exception $e) {
            Log::error('[TrendingSearch AI] Controller Error: ' . $e->getMessage());
            return response()->json(['error' => 'Intelligence system error.'], 500);
        }
    }
}
