<?php

namespace Modules\TrendingSearchMonitor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
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
        $settings = [];
        $settingsRaw = Setting::where('group', 'tool_settings')
            ->where('key', 'like', 'trending-search-monitor_%')
            ->get();
        foreach ($settingsRaw as $s) {
            $key = str_replace('trending-search-monitor_', '', $s->key);
            $settings[$key] = $s->value;
        }

        $availableText = (string) ($settings['available_countries'] ?? '');
        $activeCountries = json_decode($settings['countries'] ?? 'null', true);

        // effectiveMap intersects the tool list with the global visibility
        // registry → admin-hidden countries never appear in the Viral Tool.
        $countryMap = CountryRegistry::effectiveMap(
            $availableText,
            is_array($activeCountries) ? $activeCountries : null
        );

        $defaultRegion = isset($countryMap['EG']) ? 'EG' : (array_key_first($countryMap) ?: CountryRegistry::defaultRegion());
        $resolved = CountryRegistry::resolveRegion($request->get('country'), $countryMap, $defaultRegion);
        $region = $resolved['region'];
        $currentCountry = $resolved['country'];

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
        $deducted = false;
        if ($shouldFetch) {
            // Extract Feed Type and Category from Admin settings
            $feedType = $settings['feed_type'] ?? 'daily';
            $category = $settings['category'] ?? 'all';

            // Fetch trends based on platform. The cap is honoured per-source
            // and a final post-filter pass dedupes + ranks the merged list.
            $maxTrends = max(10, min(100, (int) ($settings['max_trends'] ?? 50)));

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

            // Final cleanup that runs for every platform: dedupe by normalized
            // title, drop empty / noise entries, validate the top-3 headlines.
            $trends = $this->normalizeTrendList(
                $trends,
                $platform,
                $maxTrends,
                $region,
                $currentCountry['lang'] ?? 'ar'
            );
            $trends = $this->applyProxiedImagesToTrends($trends);

            // Charge credits only on explicit fetch/refresh — canonical service.
            if ($shouldFetch && !empty($trends) && !($isAjax && !$forceRefresh)) {
                if (! $user->deductToolCredits('trending-search-monitor')) {
                    \Illuminate\Support\Facades\Log::critical('[Trending Search Monitor] Credits could not be deducted after successful fetch', [
                        'user_id' => $user->id,
                    ]);
                } else {
                    $deducted = true;
                }
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
            // Echo the post-deduction wallet balance so the front-end can
            // animate the credit chip in place via VidaCredits.apply().
            $balance = null;
            if ($deducted) {
                $user->load('wallet');
                $balance = (float) ($user->wallet->balance_credits ?? 0);
            }

            return response()->json([
                'success' => true,
                'trends' => $trends,
                'country' => $currentCountry,
                'count' => count($trends),
                'platform' => $platform,
                'cached_at' => now()->format('h:i A'),
                'balance' => $balance,
            ]);
        }

        return view('trendingsearchmonitor::index', compact('trends', 'currentCountry', 'countryMap', 'region', 'platform', 'enabledPlatforms'));
    }

    /**
     * Fetch trending search data from Google Trends (cached).
     *
     * The previous version only used one source; on most countries Google's
     * Daily RSS returns 10–20 items and the Realtime API returns ~25, so
     * the combined feed yields a richer, more stable list. We dedupe by
     * normalized title and prefer the entry with the most metadata
     * (traffic / image / news items).
     */
    protected function fetchTrendingSuggestions($region, $lang = 'ar', $forceRefresh = false, $feedType = 'daily', $category = 'all', $maxTrends = 50)
    {
        $cacheKey = "trending_suggestions_v2_{$region}_{$lang}_{$feedType}_{$category}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $trends = Cache::remember($cacheKey, 600, function () use ($region, $lang, $feedType, $category) {
            $primary = $feedType === 'realtime'
                ? $this->fetchRealtimeFresh($region, $lang, $category)
                : $this->fetchDailyFresh($region, $lang);

            // Always enrich with the alternate source so a single sparse feed
            // never strands the user at <10 trends.
            $secondary = $feedType === 'realtime'
                ? $this->fetchDailyFresh($region, $lang)
                : $this->fetchRealtimeFresh($region, $lang, $category);

            return $this->mergeTrendSources([$primary, $secondary]);
        });

        // Slice to the admin-configured cap (post-merge).
        return array_slice($trends, 0, $maxTrends);
    }

    /**
     * Merge two trend lists from different sources, preferring the entry
     * with the richest metadata (traffic + image + news items) when the
     * normalized titles collide. Output is deduped and sorted by the
     * order of the primary source first.
     */
    protected function mergeTrendSources(array $sources): array
    {
        $byKey = [];
        $orderKeys = [];

        foreach ($sources as $list) {
            if (! is_array($list)) continue;

            foreach ($list as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                if ($title === '') continue;

                $key = $this->normalizeTitleKey($title);
                if ($key === '') continue;

                if (! isset($byKey[$key])) {
                    $byKey[$key] = $item;
                    $orderKeys[] = $key;
                    continue;
                }

                // Already seen: keep the richer record but merge missing fields.
                $existing = $byKey[$key];
                $merged = $existing;
                if (empty($merged['image']) && ! empty($item['image'])) {
                    $merged['image'] = $item['image'];
                }
                if (empty($merged['traffic']) && ! empty($item['traffic'])) {
                    $merged['traffic'] = $item['traffic'];
                }
                if (empty($merged['news']) && ! empty($item['news'])) {
                    $merged['news'] = $item['news'];
                }
                $byKey[$key] = $merged;
            }
        }

        $out = [];
        foreach ($orderKeys as $k) {
            $out[] = $byKey[$k];
        }
        return $out;
    }

    /**
     * Strip emoji / punctuation / whitespace to a stable lowercase key for
     * deduplication of trend titles ("صلاح" vs "محمد صلاح" intentionally
     * stay distinct; "Apple" vs "apple " collapse).
     */
    protected function normalizeTitleKey(string $title): string
    {
        $clean = mb_strtolower($title);
        $clean = preg_replace('/[\p{So}\p{Sk}\p{C}]+/u', '', $clean);          // emoji + control chars
        $clean = preg_replace('/[\p{P}]+/u', ' ', (string) $clean);             // punctuation → space
        $clean = preg_replace('/\s+/u', ' ', (string) $clean);                  // collapse whitespace
        return trim((string) $clean);
    }

    /**
     * Final post-fetch normalization that runs for every platform.
     *
     *  - drops empty titles and tiny one-character noise
     *  - dedupes by normalized title
     *  - validates / prunes news headlines (top 3, deduped, with URLs)
     *  - sorts authoritative news to the top of each trend's headline list
     *  - clamps the result to the admin-configured maxTrends
     */
    protected function normalizeTrendList(array $trends, string $platform, int $maxTrends, string $region = 'EG', string $lang = 'ar'): array
    {
        $authoritative = $this->authoritativeSources();

        $seen = [];
        $out = [];

        foreach ($trends as $trend) {
            if (! is_array($trend)) continue;

            $title = trim((string) ($trend['title'] ?? ''));
            if ($title === '' || mb_strlen($title) < 2) continue;

            $key = $this->normalizeTitleKey($title);
            if ($key === '' || isset($seen[$key])) continue;
            $seen[$key] = true;

            // ─── Headline validation ───
            $news = is_array($trend['news'] ?? null) ? $trend['news'] : [];
            $news = $this->validateHeadlines($news, $authoritative);

            // Enrich deferred — batch fetch after initial pass for speed.
            $trend['title'] = $title;
            $trend['news'] = $news;

            // Featured image: trend thumbnail, else first article image.
            if (empty($trend['image'])) {
                foreach ($news as $article) {
                    if (! empty($article['image'])) {
                        $trend['image'] = $article['image'];
                        break;
                    }
                }
            }

            if (! isset($trend['platform'])) {
                $trend['platform'] = $platform === 'twitter' ? 'x' : $platform;
            }

            $out[] = $trend;

            if (count($out) >= $maxTrends) break;
        }

        return $this->enrichTrendsNewsInParallel($out, $region, $lang, $authoritative);
    }

    /**
     * Parallel Google News enrichment for trends with fewer than 3 articles.
     */
    protected function enrichTrendsNewsInParallel(array $trends, string $region, string $lang, array $authoritative): array
    {
        $region = CountryRegistry::normalizeCode($region) ?: CountryRegistry::defaultRegion();
        $lang = CountryRegistry::langFor($region);

        foreach ($trends as $index => $trend) {
            $news = is_array($trend['news'] ?? null) ? $trend['news'] : [];
            if (count($news) < 3) {
                $pending[$index] = 3 - count($news);
            }
        }

        if (! empty($pending)) {
            try {
                $responses = Http::pool(function ($pool) use ($pending, $trends, $region, $lang) {
                    foreach ($pending as $index => $limit) {
                        $title = $trends[$index]['title'] ?? '';
                        $url = GoogleNewsRss::searchUrl($title, $region, $lang);
                        $pool->as("trend_{$index}")
                            ->withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (compatible; VidaNexus/1.0)',
                                'Accept' => 'application/rss+xml, application/xml, text/xml',
                            ])
                            ->timeout(10)
                            ->get($url);
                    }
                });

                foreach ($pending as $index => $limit) {
                    $response = $responses["trend_{$index}"] ?? null;
                    if (! $response || $response->failed()) {
                        continue;
                    }
                    $parsed = $this->parseGoogleNewsRssItems($response->body(), $limit);
                    $news = $this->validateHeadlines(
                        $this->mergeNewsItems($trends[$index]['news'] ?? [], $parsed),
                        $authoritative
                    );
                    $trends[$index]['news'] = $news;
                    if (empty($trends[$index]['image'])) {
                        foreach ($news as $article) {
                            if (! empty($article['image'])) {
                                $trends[$index]['image'] = $article['image'];
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[TrendingSearch] Parallel news enrichment failed: '.$e->getMessage());
            }
        }

        foreach ($trends as $index => $trend) {
            if (empty($trend['image'])) {
                foreach ($trend['news'] ?? [] as $article) {
                    if (! empty($article['image'])) {
                        $trends[$index]['image'] = $article['image'];
                        break;
                    }
                }
            }
        }

        return $trends;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function parseGoogleNewsRssItems(string $body, int $limit): array
    {
        $xml = @simplexml_load_string($body);
        if (! $xml || ! isset($xml->channel->item)) {
            return [];
        }

        $items = [];
        foreach ($xml->channel->item as $item) {
            $mapped = GoogleNewsRss::mapRssItem($item);
            if ($mapped === null) {
                continue;
            }

            $items[] = [
                'title' => $mapped['title'],
                'url' => $mapped['link'],
                'source' => $mapped['source'],
                'snippet' => $mapped['snippet'],
                'image' => $mapped['image'],
                'date' => $mapped['pubDate'] ?: null,
            ];

            if (count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }

    protected function applyProxiedImagesToTrends(array $trends): array
    {
        foreach ($trends as $i => $trend) {
            if (! empty($trend['image'])) {
                $trends[$i]['image'] = $this->proxiedImageUrl($trend['image']);
            }
            if (! empty($trend['news']) && is_array($trend['news'])) {
                foreach ($trend['news'] as $j => $article) {
                    if (! empty($article['image'])) {
                        $trends[$i]['news'][$j]['image'] = $this->proxiedImageUrl($article['image']);
                    }
                }
            }
        }

        return $trends;
    }

    protected function proxiedImageUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        return route('media.image-proxy', ['url' => $url]);
    }

    /**
     * Validate a list of related-news items for a single trend:
     *   - require a non-empty title and a working URL
     *   - dedupe by URL host + path
     *   - prefer authoritative outlets at the top
     *   - cap to top 3
     */
    protected function validateHeadlines(array $news, array $authoritative): array
    {
        $valid = [];
        $seenUrls = [];

        foreach ($news as $n) {
            if (! is_array($n)) {
                continue;
            }
            $title = trim((string) ($n['title'] ?? ''));
            $url = trim((string) ($n['url'] ?? ''));
            if ($title === '' || $url === '' || ! GoogleNewsRss::isValidOutboundUrl($url)) {
                continue;
            }

            $hostPath = preg_replace('/^https?:\/\/(www\.)?/i', '', $url);
            $hostPath = strtolower(strtok($hostPath, '?#'));
            if (isset($seenUrls[$hostPath])) {
                continue;
            }
            $seenUrls[$hostPath] = true;

            $valid[] = [
                'title' => $title,
                'url' => $url,
                'source' => trim((string) ($n['source'] ?? '')),
                'snippet' => trim((string) ($n['snippet'] ?? '')),
                'image' => trim((string) ($n['image'] ?? '')) ?: null,
                'date' => trim((string) ($n['date'] ?? '')) ?: null,
            ];
        }

        usort($valid, function ($a, $b) use ($authoritative) {
            $ai = $this->isAuthoritativeSource($a['source'], $a['url'], $authoritative) ? 0 : 1;
            $bi = $this->isAuthoritativeSource($b['source'], $b['url'], $authoritative) ? 0 : 1;

            return $ai <=> $bi;
        });

        return array_slice($valid, 0, 3);
    }

    protected function mergeNewsItems(array $primary, array $secondary): array
    {
        $merged = $primary;
        $seenUrls = [];

        foreach ($primary as $item) {
            $url = trim((string) ($item['url'] ?? ''));
            if ($url !== '') {
                $seenUrls[$this->normalizeNewsUrlKey($url)] = true;
            }
        }

        foreach ($secondary as $item) {
            if (! is_array($item)) {
                continue;
            }
            $url = trim((string) ($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $key = $this->normalizeNewsUrlKey($url);
            if (isset($seenUrls[$key])) {
                continue;
            }
            $seenUrls[$key] = true;
            $merged[] = $item;
        }

        return $merged;
    }

    protected function normalizeNewsUrlKey(string $url): string
    {
        $hostPath = preg_replace('/^https?:\/\/(www\.)?/i', '', $url);

        return strtolower(strtok((string) $hostPath, '?#'));
    }

    protected function fetchNewsForTrend(string $trendTitle, string $region, string $lang, int $limit = 3): array
    {
        if ($limit <= 0 || $trendTitle === '') {
            return [];
        }

        $region = CountryRegistry::normalizeCode($region) ?: CountryRegistry::defaultRegion();
        $lang = CountryRegistry::langFor($region);
        $cacheKey = 'trend_news_'.md5(mb_strtolower($trendTitle)."_{$region}_{$lang}_{$limit}");

        return Cache::remember($cacheKey, 600, function () use ($trendTitle, $region, $lang, $limit) {
            $url = GoogleNewsRss::searchUrl($trendTitle, $region, $lang);

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/rss+xml, application/xml, text/xml',
                ])->timeout(10)->get($url);

                if ($response->failed()) {
                    return [];
                }

                return $this->parseGoogleNewsRssItems($response->body(), $limit);
            } catch (\Exception $e) {
                Log::warning("Trend news enrichment failed for {$trendTitle}: ".$e->getMessage());

                return [];
            }
        });
    }

    /**
     * Pull the admin-curated authority lists from the Global News Monitor
     * settings so the Viral Tool surfaces the same trusted outlets at the
     * top of each trend's headlines.
     */
    protected function authoritativeSources(): array
    {
        $major = (string) Setting::get('global-news-monitor_major_authority_sources', "bbc\ncnn\nreuters\napnews\nassociated press\nbloomberg\nguardian\nالجزيرة\nالعربية\nرويترز\nسكاي نيوز\nbbc.com\nbbc.co.uk\ncnn.com\nreuters.com\nbloomberg.com\nft.com");
        $mid = (string) Setting::get('global-news-monitor_mid_authority_sources', "اليوم السابع\nالشروق\nالمصري اليوم\nforbes\ntechcrunch\nthe verge\nverge\nwired");

        return array_filter(array_map(
            fn ($s) => mb_strtolower(trim($s)),
            preg_split('/\r?\n/', $major . "\n" . $mid)
        ));
    }

    protected function isAuthoritativeSource(string $source, string $url, array $authoritative): bool
    {
        $haystack = mb_strtolower($source . ' ' . $url);
        foreach ($authoritative as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
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
     * Falls back to Google Trends RSS if Trends24 doesn't support the country
     */
    protected function fetchXTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_x_v2_{$region}_{$maxTrends}";
        if ($forceRefresh) Cache::forget($cacheKey);

        $trends = Cache::remember($cacheKey, 600, function () use ($region, $countryName, $maxTrends) {
            // Step 1: Try Trends24
            $items = $this->tryTrends24($region, $countryName, $maxTrends);
            if (!empty($items)) return $items;

            // Step 2: Fallback — use Google Trends RSS for this country
            Log::info("X Trends: Trends24 unavailable for {$region}, falling back to Google Trends RSS");
            $lang = CountryRegistry::langFor($region);
            $googleItems = $this->fetchDailyFresh($region, $lang);
            
            // Re-format Google trends as X-style entries
            $items = [];
            foreach ($googleItems as $gt) {
                $items[] = [
                    'title' => $gt['title'],
                    'traffic' => $gt['traffic'] ?? 'Trending',
                    'image' => $gt['image'] ?? null,
                    'news' => $gt['news'] ?? [],
                    'subtitle' => "🔥 Trending in {$countryName}",
                    'platform' => 'x'
                ];
                if (count($items) >= $maxTrends) break;
            }
            
            if (!empty($items)) {
                Log::info("X Trends: Google fallback SUCCESS. {$region} → " . count($items) . " trends");
            }
            return $items;
        });

        return $trends;
    }

    /**
     * Try fetching X trends from Trends24.in.
     *
     * The Trends24 page renders multiple cards (Now / 1h ago / 3h ago / …);
     * each card holds up to ~50 trends. The previous regex only captured one
     * pass, so users got ~10 trends. We now walk every <ol class="trend-card__list"> block
     * and dedupe across them so the country page always reaches `$maxTrends`
     * when data is available, and we strip well-known noise (single-character
     * tokens, hex colors, trends24 chrome links).
     */
    protected function tryTrends24($region, $countryName, $maxTrends)
    {
        $slug = str_replace(' ', '-', strtolower($countryName));
        $map = [
            'EG' => 'egypt', 'SA' => 'saudi-arabia', 'AE' => 'united-arab-emirates',
            'KW' => 'kuwait', 'QA' => 'qatar', 'BH' => 'bahrain', 'OM' => 'oman',
            'JO' => 'jordan', 'IQ' => 'iraq', 'LB' => 'lebanon',
            'MA' => 'morocco', 'DZ' => 'algeria', 'TN' => 'tunisia',
            'YE' => 'yemen', 'LY' => 'libya', 'SY' => 'syria',
            'PS' => 'palestine', 'SD' => 'sudan',
            'US' => 'united-states', 'GB' => 'united-kingdom',
            'PL' => 'poland', 'TR' => 'turkey', 'DE' => 'germany',
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
            ])->timeout(12)->get($url);

            if ($response->failed() || $response->status() === 404) {
                Log::warning("X Trends: Trends24 returned {$response->status()} for {$region}");
                return [];
            }

            $html = $response->body();
            $items = [];
            $seen = [];

            // Walk every trend-card block (Now, 1h ago, 3h ago, …).
            $blocks = [];
            if (preg_match_all('/<ol[^>]*class=["\'][^"\']*trend-card__list[^"\']*["\'][^>]*>(.*?)<\/ol>/sui', $html, $blockMatches)) {
                $blocks = $blockMatches[1];
            } elseif (preg_match_all('/<ul[^>]*class=["\'][^"\']*trend-card__list[^"\']*["\'][^>]*>(.*?)<\/ul>/sui', $html, $blockMatches)) {
                $blocks = $blockMatches[1];
            }

            if (empty($blocks)) {
                // Older Trends24 layout still in some markets.
                $blocks = [$html];
            }

            $rank = 0;
            foreach ($blocks as $block) {
                if (! preg_match_all('/<a[^>]*class=["\'][^"\']*trend-link[^"\']*["\'][^>]*>([^<]+)<\/a>/iu', (string) $block, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $titleRaw) {
                    $title = html_entity_decode(trim($titleRaw), ENT_QUOTES, 'UTF-8');
                    if (! $this->isValidTwitterTrend($title)) continue;

                    $key = $this->normalizeTitleKey($title);
                    if ($key === '' || isset($seen[$key])) continue;
                    $seen[$key] = true;

                    $rank++;
                    $items[] = [
                        'title' => $title,
                        'traffic' => 'Trending',
                        'image' => null,
                        'news' => [],
                        'subtitle' => "🔥 Viral on X in {$countryName}",
                        'platform' => 'x',
                        '_x_rank' => $rank,
                    ];
                    if (count($items) >= $maxTrends) break 2;
                }
            }

            Log::info("X Trends [Trends24]: Fetched " . count($items) . " unique trends across " . count($blocks) . " time windows for {$region}");
            return $items;
        } catch (\Exception $e) {
            Log::error("X Trends Error for {$region}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Filter out the noise that Trends24 sometimes returns: hex colors,
     * trends24 internal links, short single-token strings.
     */
    protected function isValidTwitterTrend(string $title): bool
    {
        if ($title === '' || mb_strlen($title) < 2) return false;
        if (preg_match('/^#?[0-9a-f]{3,8}$/i', $title)) return false; // hex color
        if (preg_match('/^https?:\/\//i', $title)) return false;
        $blocked = ['trends24', 'trending now', 'home', 'login', 'signup', 'twitter'];
        $lower = mb_strtolower($title);
        foreach ($blocked as $b) {
            if ($lower === $b) return false;
        }
        return true;
    }
    /**
     * Fetch YouTube Trending Videos for a specific country
     * Strategy: YouTube Direct Scraping → Google Fallback
     */
    protected function fetchYouTubeTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_youtube_v2_{$region}_{$maxTrends}";
        if ($forceRefresh) Cache::forget($cacheKey);

        $trends = Cache::remember($cacheKey, 3600, function () use ($region, $countryName, $maxTrends) {
            
            // Strategy 1: Direct YouTube Trending Page Scraping
            $items = $this->tryYouTubeDirectScrape($region, $countryName, $maxTrends);
            if (!empty($items)) {
                Log::info("YouTube Trends [Direct]: SUCCESS. Fetched " . count($items) . " for {$region}");
                return $items;
            }

            // Strategy 2: Google video search fallback
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
     * YouTube Trending via Search Results
     * YouTube's trending page requires JS rendering, but search results include
     * ytInitialData with full video metadata embedded in the HTML
     */
    protected function tryYouTubeDirectScrape($region, $countryName, $maxTrends)
    {
        try {
            $lang = CountryRegistry::langFor($region);
            $searchTerms = $lang === 'ar' 
                ? urlencode("ترند اليوم {$countryName}")
                : urlencode("trending {$countryName} today");
            
            // sp=CAMSAhAB means sort by view count, filter by today
            $url = "https://www.youtube.com/results?search_query={$searchTerms}&gl=" . strtoupper($region) . "&sp=CAMSAhAB";
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => "{$lang},en;q=0.9",
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(15)->get($url);

            if ($response->failed()) {
                Log::warning("YouTube Search: HTTP failed for {$region}");
                return [];
            }

            $html = $response->body();

            // Extract ytInitialData JSON
            if (!preg_match('/var\s+ytInitialData\s*=\s*({.+?});\s*<\/script>/s', $html, $jsonMatch)) {
                Log::warning("YouTube Search: No ytInitialData found for {$region}");
                return [];
            }

            $data = json_decode($jsonMatch[1], true);
            if (!$data) {
                Log::warning("YouTube Search: JSON parse failed for {$region}");
                return [];
            }

            return $this->extractVideosFromSearchResults($data, $countryName, $maxTrends);
        } catch (\Exception $e) {
            Log::warning("YouTube Search failed for {$region}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract videos from YouTube search results ytInitialData.
     * Also parses the "viewCountText" string into an integer so we can sort
     * by popularity and surface the country's actually-trending videos at
     * the top.
     */
    protected function extractVideosFromSearchResults($data, $countryName, $maxTrends)
    {
        $items = [];
        $seenIds = [];
        $seenTitles = [];

        $sections = $data['contents']['twoColumnSearchResultsRenderer']['primaryContents']['sectionListRenderer']['contents'] ?? [];

        foreach ($sections as $section) {
            $sectionContents = $section['itemSectionRenderer']['contents'] ?? [];

            foreach ($sectionContents as $item) {
                $video = $item['videoRenderer'] ?? [];
                if (empty($video)) continue;

                $videoId = $video['videoId'] ?? '';
                $title = $video['title']['runs'][0]['text'] ?? ($video['title']['simpleText'] ?? '');
                $channel = $video['ownerText']['runs'][0]['text'] ?? '';
                $viewsText = $video['viewCountText']['simpleText']
                    ?? ($video['shortViewCountText']['simpleText'] ?? '');
                $publishedText = $video['publishedTimeText']['simpleText'] ?? '';

                if (empty($videoId) || empty($title)) continue;
                if (mb_strlen($title) < 5) continue;
                if (isset($seenIds[$videoId])) continue;

                $titleKey = $this->normalizeTitleKey($title);
                if ($titleKey === '' || isset($seenTitles[$titleKey])) continue;

                $seenIds[$videoId] = true;
                $seenTitles[$titleKey] = true;

                $thumbnail = "https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg";
                if (!empty($video['thumbnail']['thumbnails'])) {
                    $lastThumb = end($video['thumbnail']['thumbnails']);
                    $thumbnail = $lastThumb['url'] ?? $thumbnail;
                }

                $viewsInt = $this->parseViewCount($viewsText);

                $items[] = [
                    'title' => $title,
                    'traffic' => $viewsText ?: 'Trending',
                    'image' => $thumbnail,
                    'news' => [[
                        'title' => $channel ?: 'Watch on YouTube',
                        'url' => "https://www.youtube.com/watch?v={$videoId}",
                        'source' => $channel ? "YouTube · {$channel}" : 'YouTube',
                    ]],
                    'subtitle' => $publishedText
                        ? "🔥 Trending on YouTube in {$countryName} · {$publishedText}"
                        : "🔥 Trending on YouTube in {$countryName}",
                    'platform' => 'youtube',
                    '_views_int' => $viewsInt,
                ];

                if (count($items) >= $maxTrends * 2) break 2; // collect more, sort, then trim
            }
        }

        // Re-rank by view count so the most viewed video for the country is rank #1.
        usort($items, fn ($a, $b) => ($b['_views_int'] ?? 0) <=> ($a['_views_int'] ?? 0));

        return array_slice($items, 0, $maxTrends);
    }

    /**
     * Parse YouTube's "1.2M views" / "100K views" / "1,234 views" /
     * "23 ألف مشاهدة" / "30 مليون" strings into an integer.
     */
    protected function parseViewCount(string $text): int
    {
        if ($text === '') return 0;
        $clean = mb_strtolower($text);

        if (preg_match('/([\d.,]+)\s*([a-zا-ي]*)/u', $clean, $m)) {
            $num = (float) str_replace(',', '', $m[1]);
            $unit = $m[2] ?? '';

            $multipliers = [
                'k' => 1_000, 'thousand' => 1_000, 'ألف' => 1_000, 'الف' => 1_000,
                'm' => 1_000_000, 'million' => 1_000_000, 'مليون' => 1_000_000,
                'b' => 1_000_000_000, 'billion' => 1_000_000_000, 'مليار' => 1_000_000_000,
            ];
            foreach ($multipliers as $needle => $mult) {
                if ($needle !== '' && str_contains($unit, $needle)) {
                    return (int) round($num * $mult);
                }
            }
            return (int) round($num);
        }

        return 0;
    }

    /**
     * Fallback: Google search for YouTube trending videos
     */
    protected function tryYouTubeGoogleFallback($region, $countryName, $maxTrends)
    {
        try {
            $lang = CountryRegistry::langFor($region);
            $searchTerms = $lang === 'ar' 
                ? "اشهر فيديوهات يوتيوب {$countryName} اليوم"
                : "most popular youtube videos {$countryName} today";
            $query = urlencode($searchTerms . " site:youtube.com/watch");
            $url = "https://www.google.com/search?q={$query}&gl=" . strtolower($region) . "&num=50";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->timeout(10)->get($url);

            if ($response->failed()) return [];

            $html = $response->body();
            $items = [];
            $seen = [];

            // Extract YouTube video IDs from Google results
            preg_match_all('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/', $html, $idMatches);
            
            // Try to extract titles from Google result snippets
            preg_match_all('/<h3[^>]*>([^<]+)<\/h3>/', $html, $titleMatches);
            $titles = $titleMatches[1] ?? [];
            
            $ids = array_unique($idMatches[1] ?? []);
            $titleIdx = 0;

            foreach ($ids as $videoId) {
                if (isset($seen[$videoId])) continue;
                $seen[$videoId] = true;

                $title = isset($titles[$titleIdx]) 
                    ? html_entity_decode(trim($titles[$titleIdx]), ENT_QUOTES, 'UTF-8')
                    : "Trending Video";
                // Clean Google's "... - YouTube" suffix
                $title = preg_replace('/\s*-\s*YouTube\s*$/i', '', $title);
                $titleIdx++;

                $items[] = [
                    'title' => $title,
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
     * Strategy Chain: RapidAPI → Creative Center → Google Fallback → Google Trends RSS → Empty
     */
    protected function fetchTikTokTrends($region, $countryName, $forceRefresh = false, $maxTrends = 50)
    {
        $cacheKey = "trending_tiktok_v2_{$region}_{$maxTrends}";
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

            // Strategy 3: Use Google Trends RSS and format as TikTok hashtags
            Log::info("TikTok Trends: Primary strategies failed for {$region}, using Google Trends RSS fallback");
            $lang = CountryRegistry::langFor($region);
            $googleItems = $this->fetchDailyFresh($region, $lang);
            
            $items = [];
            foreach ($googleItems as $gt) {
                $title = $gt['title'] ?? '';
                if (empty($title)) continue;
                
                // Format as hashtag if it's not already one
                $hashtag = str_starts_with($title, '#') ? $title : '#' . str_replace(' ', '_', $title);
                
                $items[] = [
                    'title' => $hashtag,
                    'traffic' => $gt['traffic'] ?? 'Trending',
                    'image' => $gt['image'] ?? null,
                    'news' => $gt['news'] ?? [],
                    'subtitle' => "🔥 Trending in {$countryName}",
                    'platform' => 'tiktok'
                ];
                if (count($items) >= $maxTrends) break;
            }
            
            if (!empty($items)) {
                Log::info("TikTok Trends: Google Trends RSS SUCCESS. {$region} → " . count($items) . " trends");
                return $items;
            }

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
            
            // Common query params that work with most RapidAPI TikTok providers.
            // Most providers cap at 100; we honour whatever admin asked for up
            // to that ceiling.
            $cap = min($maxTrends, 100);
            $params = [
                'country' => strtoupper($region),
                'country_code' => strtoupper($region),
                'period' => 7,
                'count' => $cap,
                'limit' => $cap,
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
     * Try fetching from TikTok Creative Center internal API.
     *
     * The endpoint paginates with a hard 50-per-page cap, so to support
     * `max_trends > 50` we walk additional pages until we have enough or
     * the API returns an empty list. Hashtags are deduped across pages,
     * sorted by the API's "popular" weight, and cleaned up
     * (no empty `#`, no hex colors, etc.).
     */
    protected function tryTikTokCreativeCenter($region, $maxTrends)
    {
        try {
            $url = "https://ads.tiktok.com/creative_radar_api/v1/popular_trend/hashtag/list";
            $items = [];
            $seen = [];
            $page = 1;
            $remaining = $maxTrends;

            while ($remaining > 0 && $page <= 4) { // 4 pages × 50 = 200 max
                $params = [
                    'period' => 7,
                    'page' => $page,
                    'limit' => min($remaining, 50),
                    'country_code' => strtoupper($region),
                    'sort_by' => 'popular',
                ];

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Referer' => 'https://ads.tiktok.com/business/creativecenter/inspiration/popular/hashtag/pc/en',
                    'Origin' => 'https://ads.tiktok.com',
                ])->timeout(10)->get($url, $params);

                if ($response->failed()) break;
                $data = $response->json();
                if (($data['code'] ?? 0) !== 0 || empty($data['data']['list'])) break;

                $pageItems = 0;
                foreach ($data['data']['list'] as $hashtag) {
                    $name = trim((string) ($hashtag['hashtag_name'] ?? $hashtag['name'] ?? ''));
                    if ($name === '') continue;

                    $title = '#' . ltrim($name, '#');
                    $key = $this->normalizeTitleKey($title);
                    if ($key === '' || isset($seen[$key])) continue;
                    $seen[$key] = true;

                    $views = $hashtag['publish_cnt'] ?? $hashtag['video_views'] ?? null;
                    $viewsInt = is_numeric($views) ? (int) $views : 0;
                    $viewsFormatted = $viewsInt > 0 ? number_format($viewsInt) . ' posts' : 'Trending';

                    $items[] = [
                        'title' => $title,
                        'traffic' => $viewsFormatted,
                        'image' => null,
                        'news' => [],
                        'subtitle' => "🚀 Trending on TikTok",
                        'platform' => 'tiktok',
                        '_views_int' => $viewsInt,
                    ];
                    $pageItems++;
                    if (count($items) >= $maxTrends) break;
                }

                if ($pageItems === 0 || count($items) >= $maxTrends) break;
                $remaining = $maxTrends - count($items);
                $page++;
            }

            // Sort by view count desc — the API already does this within a
            // single page but the multi-page merge benefits from a final pass.
            usort($items, fn ($a, $b) => ($b['_views_int'] ?? 0) <=> ($a['_views_int'] ?? 0));

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
            'headlines' => 'nullable|array',
            'articles' => 'nullable|array',
            'articles.*.title' => 'required_with:articles|string',
            'articles.*.summary' => 'nullable|string',
            'articles.*.source' => 'nullable|string',
            'articles.*.date' => 'nullable|string',
        ]);

        $user = auth()->user();
        
        $articles = $request->get('articles', []);
        if (empty($articles) && $request->filled('headlines')) {
            foreach ($request->get('headlines', []) as $headline) {
                $headline = trim((string) $headline);
                if ($headline !== '') {
                    $articles[] = ['title' => $headline];
                }
            }
        }

        \Illuminate\Support\Facades\Log::info("[TrendingSearch AI] Incoming Request For: {$request->trend}", [
            'country' => $request->country,
            'articles_count' => count($articles),
        ]);
        
        // ─── 1. Access & Credit Control ───
        // canUseTool covers ownership + balance against the canonical cost
        // (tool_credit_cost_trending-search-monitor). Wallet → bonus order
        // is handled by deductToolCredits below.
        if (!$user->canUseTool('trending-search-monitor')) {
            $cost = $user->getToolCreditCost('trending-search-monitor');
            $hasOwnership = $user->ownsTool('trending-search-monitor');
            $msg = $hasOwnership
                ? "Insufficient credits for AI Deep Intel. ({$cost} CRS required)"
                : 'You do not have access to this intelligence tool.';
            return response()->json(['error' => $msg], $hasOwnership ? 402 : 403);
        }

        try {
            $service = app(\Modules\TrendingSearchMonitor\Services\TrendIntelligenceService::class);
            $result = $service->analyzeTrendWithAI(
                $request->trend,
                $request->country,
                $request->lang,
                $request->get('platform', 'google'),
                $articles
            );

            if ($result['success']) {
                // Canonical deduction → ledger / transaction / audit log.
                if (! $user->deductToolCredits('trending-search-monitor')) {
                    Log::critical('[Trending Search Monitor] Credits could not be deducted after successful trend analysis', [
                        'user_id' => $user->id,
                    ]);
                }

                \App\Models\AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'trending-search-monitor',
                    'provider' => 'ai_intel',
                    'model' => 'gpt-4-trend-analyst',
                    'status' => 'success',
                ]);

                // Echo the new wallet balance for live chip updates.
                $user->load('wallet');
                $result['balance'] = (float) ($user->wallet->balance_credits ?? 0);

                return response()->json($result);
            }

            return response()->json(['error' => $result['message'] ?? 'AI failure.'], 500);

        } catch (\Exception $e) {
            Log::error('[TrendingSearch AI] Controller Error: ' . $e->getMessage());
            return response()->json(['error' => 'Intelligence system error.'], 500);
        }
    }
}
