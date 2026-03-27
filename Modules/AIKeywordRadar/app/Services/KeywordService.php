<?php

namespace Modules\AIKeywordRadar\Services;

use Modules\AIKeywordRadar\Models\Keyword;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Core\AI\AIManager;
use App\Models\ToolError;

class KeywordService
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Fetch Google Trends from RSS
     */
    public function fetchGoogleTrends($region = 'EG')
    {
        $region = strtoupper($region);
        return Cache::remember('google_trends_radar_' . $region, 1800, function () use ($region) {
            $url = "https://trends.google.com/trending/rss?geo={$region}&sort=recency";
            try {
                $userAgent = $this->getRandomUserAgent();
                $response = Http::withHeaders([
                    'User-Agent' => $userAgent,
                ])->timeout(10)->get($url);

                if ($response->failed()) return [];

                $xml = new \SimpleXMLElement($response->body());
                $items = [];
                $namespaces = $xml->getNamespaces(true);

                foreach ($xml->channel->item as $item) {
                    $htData = $item->children($namespaces['ht'] ?? null);
                    $items[] = [
                        'title' => (string) $item->title,
                        'pubDate' => (string) $item->pubDate,
                        'traffic' => (string) ($htData->approx_traffic ?? ''),
                        'picture' => (string) ($htData->picture ?? ''),
                        'description' => (string) $item->description,
                        'link' => (string) $item->link,
                    ];
                }
                return $items;
            } catch (\Exception $e) {
                Log::error("Keyword Radar Trends Fetch Error: " . $e->getMessage());
                ToolError::log('ai-keyword-radar', $e, 'Trends RSS Fetch', null, ['region' => $region]);
                return [];
            }
        });
    }

    public function getExtendedTrends($region = 'EG', $lang = 'ar', $limit = 50)
    {
        // User requested ONLY real Google Trends (no Top Stories supplement)
        $trends = $this->fetchGoogleTrends($region);
        
        return array_slice($trends, 0, $limit);
    }

    /**
     * Fetch News from Google News
     */
    public function fetchNewsFromGoogle($country = 'EG', $topic = 'WORLD', $lang = 'ar')
    {
        $country = strtoupper($country);
        $topic = strtoupper($topic);
        
        if ($topic === 'GENERAL' || $topic === 'TOP_STORIES') {
            $url = "https://news.google.com/rss?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        } else {
            $url = "https://news.google.com/rss/headlines/section/topic/{$topic}?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        }
        
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->failed()) return [];

            $xml = new \SimpleXMLElement($response->body());
            $news = [];
            foreach ($xml->channel->item as $item) {
                $news[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'pubDate' => (string) $item->pubDate,
                    'source' => (string) ($item->source ?? ''),
                    'description' => (string) $item->description,
                ];
            }
            return $news;
        } catch (\Exception $e) {
            Log::error("Keyword Radar News Fetch Error: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'Google News Fetch', null, ['country' => $country, 'topic' => $topic]);
            return [];
        }
    }

    /**
     * Sync and Save Keywords (Manual Trigger)
     */
    public function syncKeywords(int $limit = 500, string $lang = 'ar', $userId = null)
    {
        ini_set('max_execution_time', 120);
        $user = \App\Models\User::find($userId);
        
        $results = $this->getTargetKeywordsFromCompetitors($lang, $userId);
        $newKeywords = $results['keywords'] ?? [];
        $headlinesCount = $results['headlines_count'] ?? 0;
        
        $cacheKey = "target_keywords_{$userId}_{$lang}";
        $existingKeywords = Cache::get($cacheKey, []);
        
        if (!empty($existingKeywords)) {
            $existingKeywords = array_values(array_filter($existingKeywords, function($k) {
                $createdAt = is_array($k) ? ($k['created_at'] ?? null) : null;
                if ($createdAt) {
                    try {
                        return \Carbon\Carbon::parse($createdAt)->gt(now()->subMinutes(1440));
                    } catch (\Exception $e) { return false; }
                }
                return true;
            }));
        }
        
        $existingTexts = [];
        foreach ($existingKeywords as $kw) {
            $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
            if (!empty($text)) {
                $existingTexts[trim($text)] = true;
            }
        }
        
        $addedCount = 0;
        $nowStr = now()->toDateTimeString();
        foreach ($newKeywords as &$kw) {
            $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
            $text = trim($text);
            
            if (empty($text)) continue;

            $textLower = mb_strtolower($text, 'UTF-8');
            $isDuplicate = false;
            
            // Explicit exact match check first for speed
            if (isset($existingTexts[$text])) {
                $isDuplicate = true;
            } else {
                // Similarity check (prevents saving almost identical keywords)
                foreach ($existingTexts as $existing => $val) {
                    similar_text($textLower, mb_strtolower($existing, 'UTF-8'), $percent);
                    if ($percent >= 92) { // Increased from 85 to 92 to allow more variations
                        $isDuplicate = true;
                        break;
                    }
                }
            }

            if (!$isDuplicate) {
                $existingTexts[$text] = true;
                $addedCount++;
                
                // Parse date more reliably
                $publishedAt = null;
                if (!empty($kw['published_at'])) {
                    try {
                        $publishedAt = \Carbon\Carbon::parse($kw['published_at']);
                    } catch (\Exception $e) {
                        Log::warning("[Keyword Sync] Date parse failed for: " . $kw['published_at']);
                    }
                }

                $keywordObj = Keyword::updateOrCreate(
                    ['keyword' => $text, 'category' => 'Target', 'lang' => $lang, 'user_id' => $userId],
                    [
                        'source' => $kw['source'] ?? 'AI', 
                        'synced_at' => now(), 
                        'published_at' => $publishedAt
                    ]
                );

                $existingKeywords[] = [
                    'text' => $text,
                    'source' => $kw['source'] ?? 'AI',
                    'published_at' => $publishedAt ? $publishedAt->toDateTimeString() : null,
                    'created_at' => $keywordObj->created_at->toDateTimeString()
                ];
                $existingTexts[$text] = true;
            } else {
                // Log duplication for debug
                // Log::debug("[Keyword Sync] Skipping duplicate: " . $text);
            }
        }

        usort($existingKeywords, function($a, $b) {
            $dateA = is_array($a) ? ($a['created_at'] ?? 0) : 0;
            $dateB = is_array($b) ? ($b['created_at'] ?? 0) : 0;
            return strcmp((string)$dateB, (string)$dateA);
        });
        
        $now = now();
        $midnight = $now->copy()->addDay()->startOfDay();
        $secondsUntilMidnight = $now->diffInSeconds($midnight);
        
        if (!empty($existingKeywords)) {
            Cache::put($cacheKey, $existingKeywords, $secondsUntilMidnight);
        }

        Log::info("[Competitor Keywords] [{$lang}] Total in cache: " . count($existingKeywords) . ", New added: $addedCount");
        
        return [
            'saved' => $addedCount,
            'found' => count($newKeywords),
            'added_to_cache' => $addedCount,
            'total_in_cache' => count($existingKeywords),
            'headlines' => $headlinesCount
        ];
    }

    /**
     * Fetch competitor headlines and extract keywords using AI
     */
    public function getTargetKeywordsFromCompetitors($lang = 'ar', $userId = null)
    {
        $headlines = $this->fetchCompetitorsHeadlines($lang, $userId);
        
        if (empty($headlines)) {
            return ['keywords' => [], 'headlines_count' => 0];
        }

        // Chunk headlines to avoid exceeding AI output token limits (max 40 headlines per batch)
        $keywordBatches = array_chunk($headlines, 40);
        $allKeywords = [];
        
        foreach ($keywordBatches as $batch) {
            $batchKeywords = $this->extractKeywordsWithAI($batch, $lang, $userId);
            if (!empty($batchKeywords)) {
                $allKeywords = array_merge($allKeywords, $batchKeywords);
            }
        }

        return [
            'keywords' => $allKeywords,
            'headlines_count' => count($headlines)
        ];
    }

    /**
     * Strategy 1: Fetch headlines via News Sitemap
     */
    protected function fetchViaSitemap(string $url, string $userAgent, $userId = null): array
    {
        $url = rtrim($url, '/');
        $sitemapUrls = [
            $url . '/sitemap-news.xml',
            $url . '/news-sitemap.xml',
            $url . '/sitemap.xml',
            $url . '/sitemap_index.xml',
        ];

        foreach ($sitemapUrls as $sitemapUrl) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept' => 'application/xml, text/xml, */*'
                ])->timeout(12)->get($sitemapUrl);
                
                if (!$response->successful()) continue;

                $xml = @simplexml_load_string($response->body());
                if (!$xml) continue;

                $items = [];
                $xml->registerXPathNamespace('n', 'http://www.google.com/schemas/sitemap-news/0.9');
                $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
                
                // Try News Sitemap first
                $nodes = $xml->xpath('//s:url[n:news]');
                if (!empty($nodes)) {
                    foreach ($nodes as $node) {
                        $news = $node->children('http://www.google.com/schemas/sitemap-news/0.9')->news;
                        $items[] = [
                            'title' => (string) $news->title,
                            'pubDate' => (string) $news->publication_date,
                        ];
                    }
                }

                // Fallback to standard sitemap with lastmod filtering
                if (empty($items)) {
                    $urls = $xml->xpath('//s:url');
                    foreach ($urls as $node) {
                        // if (count($items) >= 200) break;
                        $lastmod = (string) $node->lastmod;
                        if (empty($lastmod)) continue;
                        
                        // Only consider if lastmod is within some reasonable window (e.g. today)
                        try {
                            if (\Carbon\Carbon::parse($lastmod)->lt(now()->subHours(24))) continue;
                        } catch (\Exception $e) { continue; }

                        $loc = (string) $node->loc;
                        $title = str_replace(['-', '_', '.html', '.php'], ' ', basename(parse_url($loc, PHP_URL_PATH) ?? ''));
                        if (mb_strlen($title) > 25) {
                            $items[] = [
                                'title' => trim($title),
                                'pubDate' => $lastmod,
                            ];
                        }
                    }
                }

                if (!empty($items)) {
                    // Sort by date descending
                    usort($items, function($a, $b) {
                        return strtotime($b['pubDate'] ?? '0') <=> strtotime($a['pubDate'] ?? '0');
                    });
                    Log::info("[Strategy:Sitemap] Successfully fetched " . count($items) . " from: {$sitemapUrl}");
                    return $items;
                }
            } catch (\Exception $e) {
                Log::warning("[Strategy:Sitemap] Error for {$sitemapUrl}: " . $e->getMessage());
                ToolError::log('ai-keyword-radar', $e, 'Sitemap Parser', $userId, ['url' => $sitemapUrl]);
            }
        }
        return [];
    }

    /**
     * Strategy 2: Fetch headlines via RSS feed
     */
    protected function fetchViaRss(string $url, string $userAgent, $userId = null): array
    {
        $rssUrl = $this->guessRssUrl($url);
        if (!$rssUrl) return [];

        try {
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                'Accept-Language' => 'ar,en-US;q=0.9,en;q=0.8',
                'Referer' => 'https://www.google.com/',
                'Cache-Control' => 'no-cache',
            ])->timeout(12)->get($rssUrl);
            
            if (!$response->successful()) return [];
            
            $xml = @simplexml_load_string($response->body());
            $items = [];

            if ($xml && isset($xml->channel->item)) {
                foreach ($xml->channel->item as $item) {
                    if (count($items) >= 30) break;
                    $title = trim((string) $item->title);
                    if (!empty($title)) {
                        $items[] = [
                            'title' => $title,
                            'pubDate' => (string) ($item->pubDate ?? $item->children('dc', true)->date ?? '')
                        ];
                    }
                }
            } elseif ($xml && isset($xml->entry)) {
                foreach ($xml->entry as $entry) {
                    if (count($items) >= 30) break;
                    $title = trim((string) $entry->title);
                    if (!empty($title)) {
                        $items[] = [
                            'title' => $title,
                            'pubDate' => (string) ($entry->published ?? $entry->updated ?? '')
                        ];
                    }
                }
            }

            if (!empty($items)) {
                Log::info("[Strategy:RSS] Fetched " . count($items) . " from: {$rssUrl}");
            }
            return $items;
        } catch (\Exception $e) {
            Log::warning("[Strategy:RSS] Failed for {$rssUrl}: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'RSS Crawler', $userId, ['url' => $rssUrl]);
            return [];
        }
    }

    /**
     * Strategy 3: Direct Google Search HTML Scraping (Last Hour)
     */
    protected function fetchViaDirectGoogleSearch(string $domain, string $lang, string $userAgent, $userId = null): array
    {
        $hl = ($lang === 'en') ? 'en' : 'ar';
        $googleSearchUrl = "https://www.google.com/search?q=site:{$domain}&tbs=qdr:h&hl={$hl}&gl=EG&gbv=1";
        
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => ($lang === 'en') ? 'en-US,en;q=0.9' : 'ar,en-US;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => 'https://www.google.com/',
            'Upgrade-Insecure-Requests' => '1',
        ];

        try {
            $response = Http::withHeaders($headers)->timeout(15)->get($googleSearchUrl);
            if (!$response->successful()) return [];

            $html = $response->body();
            $items = [];

            // Pattern 1: Mobile search result titles
            preg_match_all('/<div class="[^"]*BNeawe[^"]*">([^<]+)<\/div>/i', $html, $matches);
            
            // Pattern 2: Standard H3 Titles
            if (empty($matches[1])) {
                preg_match_all('/<h3[^>]*>(.*?)<\/h3>/i', $html, $matches);
            }
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $title) {
                    $cleanTitle = trim(strip_tags($title));
                    $cleanTitle = html_entity_decode($cleanTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleanTitle = preg_replace('/\s*[·…|].*$/u', '', $cleanTitle);
                    
                    if (mb_strlen($cleanTitle) > 15 && 
                        !str_contains($cleanTitle, 'Google') && 
                        !str_contains($cleanTitle, 'Search settings')) {
                        
                        $items[] = [
                            'title' => $cleanTitle,
                            'pubDate' => now()->toDateTimeString(),
                        ];
                    }
                }
            }

            $items = array_values(array_unique($items, SORT_REGULAR));

            // --- SERPAPI FALLBACK ---
            if (empty($items)) {
                $serpApiKey = \App\Models\Setting::get('ai-keyword-radar_serpapi_key');
                if ($serpApiKey) {
                    Log::info("[Strategy:GoogleHTML] Falling back to SerpApi for {$domain}");
                    $items = $this->fetchViaSerpApi($domain, $lang, $serpApiKey);
                }
            }

            return $items;
        } catch (\Exception $e) {
            Log::error("[Strategy:GoogleHTML] Error for {$domain}: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'Google HTML Scraper', $userId, ['domain' => $domain]);
            return [];
        }
    }

    /**
     * Test a single competitor URL and return headlines
     */
    public function testUrl(string $url, string $lang = 'ar', $userId = null): array
    {
        $url = rtrim(trim($url), '/');
        $domain = parse_url($url, PHP_URL_HOST) ?: $url;
        $userAgent = $this->getRandomUserAgent();
        
        Log::info("[Competitor Test] Testing: {$url} ({$lang})");

        $strategies = \App\Models\Setting::get('ai-keyword-radar_strategies', 'sitemap,google_html,google_news,rss,html_scrape');
        $strategyList = explode(',', $strategies);
        
        $fetched = [];
        $finalStrategy = 'None';

        foreach ($strategyList as $s) {
            $s = trim($s);
            if ($s === 'sitemap') {
                $fetched = $this->fetchViaSitemap($url, $userAgent, $userId);
                if (!empty($fetched)) { $finalStrategy = 'Sitemap Index'; break; }
            } elseif ($s === 'google_html') {
                $fetched = $this->fetchViaDirectGoogleSearch($domain, $lang, $userAgent, $userId);
                if (!empty($fetched)) { $finalStrategy = 'Google Search HTML'; break; }
            } elseif ($s === 'google_news') {
                $fetched = $this->fetchViaGoogleNews($domain, $lang, $userAgent, $userId);
                if (!empty($fetched)) { $finalStrategy = 'Google News RSS'; break; }
            } elseif ($s === 'rss') {
                $fetched = $this->fetchViaRss($url, $userAgent, $userId);
                if (!empty($fetched)) { $finalStrategy = 'Direct RSS'; break; }
            } elseif ($s === 'html_scrape') {
                $fetched = $this->fetchViaHtmlScraping($url, $userAgent, $userId);
                if (!empty($fetched)) { $finalStrategy = 'Direct HTML Scraping'; break; }
            }
        }

        if (empty($fetched)) {
            Log::warning("[Competitor Test] All strategies failed for: {$url}");
            ToolError::log('ai-keyword-radar', "No headlines found using any strategy.", 'Multi-Strategy Fetch', $userId, ['url' => $url, 'domain' => $domain, 'strategies_attempted' => $strategyList]);
        }

        return [
            'success' => !empty($fetched),
            'count' => count($fetched),
            'strategy' => $finalStrategy,
            'headlines' => $fetched,
            'domain' => $domain
        ];
    }

    protected function fetchCompetitorsHeadlines($lang = 'ar', $userId = null)
    {
        $competitorUrls = $this->getMergedCompetitorUrls($userId, $lang);
        
        if (empty($competitorUrls)) {
            Log::warning("[Keyword Radar] No competitors found for user #{$userId} in lang {$lang}.");
            return [];
        }

        $headlines = [];
        
        // Multi-layered Time Filters (Tiered approach)
        $windows = [
            ['hours' => 1, 'label' => 'Very Fresh'],
            ['hours' => 2, 'label' => 'Fresh'],
            ['hours' => 4, 'label' => 'Recent']
        ];

        foreach ($competitorUrls as $url) {
            $result = $this->testUrl($url, $lang, $userId);
            $currentFetchedCount = 0;
            $seenLocalTitles = [];

            foreach ($windows as $window) {
                $freshnessLimit = now()->subHours($window['hours']);
                
                foreach ($result['headlines'] as $item) {
                    $title = $item['title'];
                    if (isset($seenLocalTitles[$title])) continue;

                    $pubDate = !empty($item['pubDate']) ? \Carbon\Carbon::parse($item['pubDate']) : null;
                    
                    if ($pubDate && $pubDate->lt($freshnessLimit)) continue;

                    $headlines[] = [
                        'title' => $title,
                        'source' => $result['domain'],
                        'pubDate' => $item['pubDate'] ?? null,
                    ];
                    $seenLocalTitles[$title] = true;
                    $currentFetchedCount++;
                }
                
                // If we found enough headlines in the most recent window, skip wider windows
                // No limit - get everything
            }
            
            Log::info("[Competitor Fetch] {$result['domain']}: got {$currentFetchedCount} headlines via {$result['strategy']}");
        }

        shuffle($headlines);
        return $headlines;
    }



    /**
     * Strategy 2: Fetch headlines via Google News RSS with site:domain filter
     */
    protected function fetchViaGoogleNews(string $domain, string $lang, string $userAgent, $userId = null): array
    {
        $hl = ($lang === 'en') ? 'en' : 'ar';
        
        // Query Rotation: Try specific site search first, then domain keyword search
        $queries = [
            "site:{$domain}",
            $domain
        ];

        $headers = [
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => ($lang === 'en') ? 'en-US,en;q=0.9' : 'ar,en-US;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => 'https://www.google.com/',
            'Upgrade-Insecure-Requests' => '1',
        ];

        foreach ($queries as $q) {
            $encodedQ = urlencode($q);
            $googleNewsUrl = "https://news.google.com/rss/search?q={$encodedQ}+when:12h&hl={$hl}&gl=EG&ceid=EG:{$hl}";

            try {
                $response = Http::withHeaders($headers)->timeout(12)->get($googleNewsUrl);
                
                if ($response->successful()) {
                    $items = $this->parseGoogleNewsRss($response->body());
                    if (!empty($items)) {
                        // Sort items by pubDate descending to ensure newest articles are first
                        usort($items, function($a, $b) {
                            $t1 = strtotime($a['pubDate'] ?? '0');
                            $t2 = strtotime($b['pubDate'] ?? '0');
                            return $t2 <=> $t1;
                        });

                        Log::info("[Strategy:GoogleNews] Found " . count($items) . " items for query: {$q}");
                        return $items;
                    }
                }
                
                Log::warning("[Strategy:GoogleNews] No results for query: {$q} (Status: " . $response->status() . ")");
            } catch (\Exception $e) {
                Log::error("[Strategy:GoogleNews] Error for query {$q}: " . $e->getMessage());
                ToolError::log('ai-keyword-radar', $e, 'Google News Strategy', $userId, ['query' => $q]);
            }
        }

        return [];
    }

    protected function parseGoogleNewsRss($xmlBody): array
    {
        if (empty($xmlBody)) return [];
        $xml = @simplexml_load_string($xmlBody);
        if (!$xml || !isset($xml->channel->item)) return [];
        if (!$xml || !isset($xml->channel->item)) return [];

        $items = [];
        foreach ($xml->channel->item as $item) {
            // if (count($items) >= 200) break;
            $title = trim((string) $item->title);
            // Only remove source if it's at the end and looks like a typical short source name (< 25 chars)
            if (preg_match('/\s*-\s*([^-]{2,25})$/u', $title, $matches)) {
                $title = preg_replace('/\s*-\s*[^-]+$/u', '', $title);
            }
            if (!empty($title) && mb_strlen($title) > 10) {
                $items[] = [
                    'title' => $title,
                    'pubDate' => (string) ($item->pubDate ?? ''),
                ];
            }
        }
        return $items;
    }

    /**
     * Strategy 3: Fetch headlines by scraping HTML page for article titles
     */
    protected function fetchViaHtmlScraping(string $url, string $userAgent, $userId = null): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => $userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'ar,en-US;q=0.9,en;q=0.8',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Referer' => 'https://www.google.com/',
            ])->timeout(10)->get($url);
            
            $depth = (int)\App\Models\Setting::get('ai-keyword-radar_scraping_depth', 20);
            
            if (!$response->successful()) return [];

            $html = $response->body();
            $items = [];
            $seen = [];

            // Extract from <h1>, <h2>, <h3> tags that are typically article headlines
            if (preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/si', $html, $matches)) {
                foreach ($matches[1] as $rawTitle) {
                    $title = trim(strip_tags($rawTitle));
                    $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                    $title = preg_replace('/\s+/', ' ', $title);
                    
                    if (
                        !empty($title) && 
                        mb_strlen($title) >= 15 && 
                        mb_strlen($title) <= 200 &&
                        !isset($seen[$title])
                    ) {
                        $items[] = ['title' => $title];
                        $seen[$title] = true;
                        if (count($items) >= $depth) break;
                    }
                }
            }

            // If not enough from headings, also try <a> tags with long text (likely article links)
            if (count($items) < 5) {
                if (preg_match_all('/<a[^>]+>([^<]{20,150})<\/a>/u', $html, $linkMatches)) {
                    foreach ($linkMatches[1] as $linkText) {
                        $title = trim(html_entity_decode($linkText, ENT_QUOTES, 'UTF-8'));
                        if (
                            !empty($title) && 
                            mb_strlen($title) >= 20 &&
                            !isset($seen[$title]) &&
                            !str_contains($title, 'http') &&
                            !str_contains($title, '@')
                        ) {
                            $items[] = ['title' => $title];
                            $seen[$title] = true;
                            if (count($items) >= $depth) break;
                        }
                    }
                }
            }

            if (!empty($items)) {
                Log::info("[Strategy:HTML] Scraped " . count($items) . " headlines from: {$url}");
                // For HTML scraping, we don't have a specific pubDate per item easily, 
                // so we use the current time (but at least we return it as an array structure)
                foreach ($items as &$item) {
                    $item['pubDate'] = now()->toDateTimeString();
                }
            }
            return $items;
        } catch (\Exception $e) {
            Log::warning("[Strategy:HTML] Failed for {$url}: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'Direct HTML Scraper', $userId, ['url' => $url]);
            return [];
        }
    }

    protected function guessRssUrl($url)
    {
        $url = rtrim($url, '/');

        // If the URL itself looks like an RSS feed, use it directly
        if (str_contains($url, '.xml') || str_contains($url, '/rss') || str_contains($url, '/feed')) {
            return $url;
        }

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        // Common RSS paths for news sites (including Arabic sites)
        $tries = ['/rss', '/feed', '/rss.xml', '/rss.aspx', '/index.xml', '/feeds/rss', '/atom.xml',
                  '/rss/all', '/feeds/all.rss.xml', '/rss/breaking', '/feed/rss2',
                  '/syndication/rss', '/?feed=rss2', '/rss/rss.xml'];
        
        foreach ($tries as $t) {
            try {
                $rss = $url . $t;
                $resp = Http::withHeaders($headers)->timeout(8)->get($rss);
                if ($resp->successful()) {
                    $body = $resp->body();
                    // Verify it's actually XML/RSS content
                    if (str_contains($body, '<rss') || str_contains($body, '<feed') || str_contains($body, '<channel')) {
                        Log::info("[Competitor RSS] Found valid feed: {$rss}");
                        return $rss;
                    }
                }
            } catch (\Exception $e) {}
        }

        // Fallback: Try to discover RSS from the site's HTML <link> tags
        try {
            $resp = Http::withHeaders($headers)->timeout(8)->get($url);
            if ($resp->successful()) {
                $html = $resp->body();
                // Look for <link rel="alternate" type="application/rss+xml" href="...">
                if (preg_match_all('/<link[^>]+type=["\']application\/(rss|atom)\+xml["\'][^>]+href=["\']([^"\']+)["\']/i', $html, $matches)) {
                    foreach ($matches[2] as $rssHref) {
                        // Resolve relative URLs
                        if (!str_starts_with($rssHref, 'http')) {
                            $rssHref = rtrim($url, '/') . '/' . ltrim($rssHref, '/');
                        }
                        Log::info("[Competitor RSS] Discovered from HTML: {$rssHref}");
                        return $rssHref;
                    }
                }
            }
        } catch (\Exception $e) {}

        Log::warning("[Competitor RSS] No RSS feed found for: {$url}");
        return null;
    }

    protected function extractKeywordsWithAI(array $headlines, string $lang = 'ar', $userId = null)
    {
        $settings = \App\Models\Setting::getAllSettings();
        
        if (count($headlines) > 100) {
            $headlines = array_slice($headlines, 0, 100);
        }

        $titlesText = "";
        foreach ($headlines as $idx => $h) {
            $sourceName = $h['source'] ?? 'Site';
            $titlesText .= ($idx + 1) . ". [{$sourceName}] " . $h['title'] . "\n";
        }

        $langInstruction = ($lang === 'en') ? "English" : "Arabic";
        
        $dbPrompt = \App\Models\Setting::get('ai-keyword-radar_prompt');
        if ($dbPrompt) {
            $prompt = str_replace(['[Headlines]', '[headlines]', '[lang]'], [$titlesText, $titlesText, $langInstruction], $dbPrompt);
        } else {
            $prompt = "أنت خبير SEO ومحلل بيانات محترف. مهمتك هي تحويل عناوين أخبار ومقالات المنافسين إلى 'كلمات بحث مستهدفة' (Target Search Queries) ذكية وعالية الجودة.

العناوين:
{$titlesText}

الشروط والقواعد الصارمة (STRICT RULES):
1- **يمنع تماماً إضافة أي \"تاريخ\" أو \"عام\" (مثل 2024 أو 2025) للكلمات ما لم يكن مذكوراً صراحةً في العنوان الأصلي**.
2- **التحويل (TRANSFORM)**: لا تقم بنسخ الكلمات كما هي، بل حول العنوان إلى استعلام بحثي دقيق يبحث عنه الناس (مثلاً: 'تراجع الذهب' يصبح 'أسباب انخفاض أسعار الذهب اليوم').
3- **التفصيل (SPECIFICITY)**: يجب أن تحتوي الكلمة على الكيانات (Entities) المذكورة بدقة (أسماء أشخاص، جهات، بطولات) دون أي اختصار.
4- **يمنع استخدام الرموز أو الهاشتاجات**.
5- **اللغة**: يجب أن تكون المخرجات باللغة {$langInstruction}.
6- **الكمية**: استخرج 3 كلمات بحثية مختلفة لكل عنوان لضمان تغطية كل نوايا البحث.

التنسيق المطلوب (Format):
أخرج فقط مصفوفة JSON صالحة دون أي نصوص إضافية:
[{\"index\": 1, \"keyword\": \"...\"}]";
        }

        try {
            // Check settings and default to VidaNexus AI settings if module specific aren't set
            $userSettings = \App\Models\User::find($userId)?->settings ?? [];
            $provider = $userSettings['keywords_ai_provider'] ?? \App\Models\Setting::get('ai-keyword-radar_provider', 'openrouter');
            $model = $userSettings['keywords_ai_model'] ?? \App\Models\Setting::get('ai-keyword-radar_model', 'google/gemini-2.0-flash-001');
            
            $aiResult = $this->aiManager->generate('ai-keyword-radar', $prompt, [
                'provider' => ($provider === 'gemini') ? 'google' : $provider,
                'model' => $model,
                'temperature' => 0.1,
                'json_mode' => true,
            ]);
            
            $response = $aiResult['text'] ?? '';
            $keywords = $this->parseKeywordsResponse($response, $headlines);
            
            if (empty($keywords)) {
                \App\Models\ToolError::log('ai-keyword-radar', new \Exception("AI returned 0 valid keywords from " . count($headlines) . " headlines. Raw response: " . substr($response, 0, 100)), 'AI Keyword Extraction', $userId, ['headline_count' => count($headlines)]);
            }

            return $keywords;

        } catch (\Exception $e) {
            Log::error("[Competitor Keywords] AI Failed: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'AI Keyword Extraction', $userId, ['headline_count' => count($headlines)]);
            return [];
        }
    }

    protected function parseKeywordsResponse($response, $headlines)
    {
        $uniqueResults = [];
        
        // 1. Clean response: Remove markdown code blocks if present
        $cleanResponse = $response;
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $response, $matches)) {
            $cleanResponse = $matches[1];
        }

        // 2. Try to find any JSON array
        if (preg_match('/\[.*\]/s', $cleanResponse, $matches)) {
            $decoded = json_decode($matches[0], true);
        } else {
            $decoded = json_decode(trim($cleanResponse), true);
        }

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $text = '';
                $source = 'AI';
                $pubDate = null;
                $idx = -1;

                if (is_array($item)) {
                    $text = $item['keyword'] ?? $item['text'] ?? '';
                    $idx = (isset($item['index']) ? (int)$item['index'] : 0) - 1;
                } else {
                    $text = (string)$item;
                }

                $text = trim($text);
                if (empty($text)) continue;

                // 3. Fallback: If index is missing or wrong, try to find matching headline
                if ($idx < 0 || !isset($headlines[$idx])) {
                    foreach ($headlines as $hIdx => $h) {
                        // Very simple fuzzy match: check if significant words of keyword are in headline
                        $cleanKeyword = $this->normalizeForComparison($text);
                        $cleanHeadline = $this->normalizeForComparison($h['title']);
                        if (str_contains($cleanHeadline, $cleanKeyword) || str_contains($cleanKeyword, $cleanHeadline)) {
                            $idx = $hIdx;
                            break;
                        }
                    }
                }

                if ($idx >= 0 && isset($headlines[$idx])) {
                    $source = $headlines[$idx]['source'] ?? 'AI';
                    $pubDate = $headlines[$idx]['pubDate'] ?? null;
                }

                $uniqueResults[$text] = [
                    'text' => $text,
                    'source' => $source,
                    'published_at' => $pubDate,
                    'created_at' => now()->toDateTimeString()
                ];
            }
        }
        
        return $this->filterSimilarKeywords(array_values($uniqueResults));
    }

    protected function filterSimilarKeywords(array $keywords, float $threshold = 0.6): array
    {
        if (count($keywords) <= 1) return $keywords;
        
        $filtered = [];
        $usedTexts = [];
        
        // Comprehensive list of Arabic filler words and generic news terms to exclude
        $fillerWords = [
            'اليوم', 'غدا', 'أمس', 'الآن', 'في', 'على', 'من', 'إلى', 'عن', 'مع',
            'و', 'أو', 'ال', 'لـ', 'بـ', 'السبت', 'الأحد', 'الاثنين', 'الثلاثاء',
            'الأربعاء', 'الخميس', 'الجمعة', 'شهر', 'يناير', 'فبراير', 'مارس',
            'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر',
            'نوفمبر', 'ديسمبر', '2025', '2026', '2027', '2028',
            'أخبار', 'سعر', 'حالة', 'مصر', 'القاهرة', 'بث', 'مباشر', 'عاجل',
            'تفاصيل', 'موعد', 'خطوات', 'رابط', 'رسميا', 'تعرف', 'شاهد',
            'بسبب', 'طريقة', 'نتيجة', 'تطبيق', 'برنامج', 'تحديث', 'جديد',
            'أهم', 'آخر', 'توقعات', 'تقرير', 'دراسة', 'كشف', 'بيان'
        ];
        
        foreach ($keywords as $kw) {
            $text = is_array($kw) ? ($kw['text'] ?? '') : $kw;
            $text = trim($text);
            
            // Stricter quality filters
            if (empty($text) || mb_strlen($text) < 12) continue;
            
            $words = array_filter(explode(' ', $text));
            $wordCount = count($words);
            
            // Skip keywords shorter than 3 words or longer than 10 words
            if ($wordCount < 3 || $wordCount > 10) continue;
            
            // Skip if it contains too many filler words or generic terms at the start/end
            $firstWord = $words[0] ?? '';
            if (in_array($firstWord, ['أخبار', 'عاجل', 'شاهد', 'بث', 'تعرف', 'عاجل:', 'فيديو'])) continue;

            $normalizedText = $this->normalizeForComparison($text);
            $currentWords = $this->extractSignificantWords($normalizedText, $fillerWords);
            
            // At least 2 significant words for meaningful context
            if (count($currentWords) < 2) {
                if (!in_array($normalizedText, $usedTexts) && $wordCount >= 2) {
                    $filtered[] = $kw;
                    $usedTexts[] = $normalizedText;
                }
                continue;
            }
            
            $isSimilar = false;
            foreach ($filtered as $existingKw) {
                $existingText = is_array($existingKw) ? ($existingKw['text'] ?? '') : $existingKw;
                $existingNormalized = $this->normalizeForComparison($existingText);
                $existingWords = $this->extractSignificantWords($existingNormalized, $fillerWords);
                
                $similarity = $this->calculateWordOverlap($currentWords, $existingWords);
                
                if ($similarity >= $threshold) {
                    $isSimilar = true;
                    break;
                }
            }
            
            if (!$isSimilar) {
                $filtered[] = $kw;
                $usedTexts[] = $normalizedText;
            }
        }
        
        return $filtered;
    }

    protected function normalizeForComparison(string $text): string
    {
        // Preserve years (4 digits) by temporarily replacing them
        $text = preg_replace_callback('/\b\d{4}\b/', function($m) {
            return "YEAR" . $m[0] . "VNX";
        }, $text);

        $text = preg_replace('/\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}/', '', $text);
        $text = preg_replace('/\b\d+\b/', ' ', $text); // Remove other numbers
        $text = preg_replace('/[[:punct:]]/u', ' ', $text);
        
        // Restore years
        $text = preg_replace_callback('/YEAR(\d{4})VNX/', function($m) {
            return $m[1];
        }, $text);

        $text = preg_replace('/\s+/', ' ', $text);
        return trim(mb_strtolower($text, 'UTF-8'));
    }

    protected function extractSignificantWords(string $text, array $fillerWords): array
    {
        $words = explode(' ', $text);
        $significant = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) >= 2 && !in_array($word, $fillerWords)) {
                $significant[] = $word;
            }
        }
        return $significant;
    }

    protected function calculateWordOverlap(array $words1, array $words2): float
    {
        if (empty($words1) || empty($words2)) return 0.0;
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        if (count($union) === 0) return 0.0;
        return count($intersection) / count($union);
    }

    protected function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:123.0) Gecko/20100101 Firefox/123.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2.1 Safari/605.1.15',
        ];

        return $userAgents[array_rand($userAgents)];
    }

    /**
     * Use AI to suggest top competitors for a given language
     */
    public function getSuggestedCompetitors(string $lang = 'ar'): array
    {
        $langName = ($lang === 'en') ? 'English' : 'Arabic';
        $region = ($lang === 'en') ? 'Global/US/UK' : 'Middle East/Egypt/Gulf';

        $prompt = "As an SEO and Digital Marketing expert, suggest a list of 15 high-authority, high-traffic news websites, trending blogs, or viral content platforms in {$langName} language (targeting {$region}) that would be excellent sources for extracting trending search keywords and breaking news headlines.

Rules:
1. Return ONLY a JSON array of complete URLs (starting with https://).
2. The sources must be reliable and frequently updated (news portals, major sports sites, tech blogs, etc.).
3. No social media platforms (Twitter, Facebook, etc.).
4. Do not include any text, explanations, or markdown formatting outside the JSON array.

Example Output:
[\"https://site1.com\", \"https://site2.org\"]";

        try {
            $aiResult = $this->aiManager->generate('ai-keyword-radar', $prompt, [
                'provider' => 'google',
                'model' => 'google/gemini-2.0-flash-001',
                'temperature' => 0.2,
            ]);

            $response = $aiResult['text'] ?? '';
            
            // Extract JSON array
            if (preg_match('/\[.*\]/s', $response, $matches)) {
                $urls = json_decode($matches[0], true);
                if (is_array($urls)) {
                    return array_values(array_filter($urls, function($u) {
                        return filter_var($u, FILTER_VALIDATE_URL);
                    }));
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::error("[Keyword Radar] Suggestion Failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get merged list of competitors (User specific + Global Admin)
     */
    public function getMergedCompetitorUrls($userId, $lang = 'ar')
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            // If user is null (system sync), use global only
            $globalCompetitorsText = \App\Models\Setting::get('ai-keyword-radar_competitors', '');
            return array_values(array_filter(array_map('trim', explode("\n", $globalCompetitorsText))));
        }
        
        $settings = $user->settings ?? [];
        
        // 1. User Competitors
        $userCompetitorsText = ($lang === 'en') ? ($settings['keywords_competitors_en'] ?? '') : ($settings['keywords_competitors'] ?? '');
        $userUrls = array_filter(array_map('trim', explode("\n", $userCompetitorsText)));
        
        // 2. Global Admin Competitors
        $globalCompetitorsText = \App\Models\Setting::get('ai-keyword-radar_competitors', '');
        $globalUrls = array_filter(array_map('trim', explode("\n", $globalCompetitorsText)));
        
        // Merge and unique
        $allUrls = array_unique(array_merge($userUrls, $globalUrls));
        
        return array_values($allUrls);
    }

    /**
     * Get merged list of RSS feeds (User specific + Global Admin)
     */
    public function getMergedRssFeeds($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user) {
             $globalRssText = \App\Models\Setting::get('ai-keyword-radar_rss_feeds', '');
             return array_values(array_filter(array_map('trim', explode("\n", $globalRssText))));
        }
        
        $settings = $user->settings ?? [];
        
        // 1. User RSS
        $userRssText = $settings['keywords_rss_feeds'] ?? '';
        $userUrls = array_filter(array_map('trim', explode("\n", $userRssText)));
        
        // 2. Global Admin RSS
        $globalRssText = \App\Models\Setting::get('ai-keyword-radar_rss_feeds', '');
        $globalUrls = array_filter(array_map('trim', explode("\n", $globalRssText)));
        
        return array_values(array_unique(array_merge($userUrls, $globalUrls)));
    }

    /**
     * Strategy 2.5: Fetch headlines via SerpApi (High Reliability)
     */
    protected function fetchViaSerpApi(string $domain, string $lang, string $apiKey): array
    {
        $hl = ($lang === 'en') ? 'en' : 'ar';
        $query = "site:{$domain} when:1h";
        
        try {
            $response = Http::timeout(15)->get('https://serpapi.com/search', [
                'engine' => 'google',
                'q' => $query,
                'hl' => $hl,
                'gl' => 'eg',
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['organic_results'] ?? [];
                $items = [];
                
                foreach ($results as $res) {
                    $title = $res['title'] ?? '';
                    if (!empty($title) && mb_strlen($title) > 15) {
                        $items[] = [
                            'title' => $title,
                            'pubDate' => now()->toDateTimeString(),
                        ];
                    }
                }
                
                Log::info("[Strategy:SerpApi] Found " . count($items) . " headlines for {$domain}");
                return $items;
            }
            
            Log::warning("[Strategy:SerpApi] Failed for {$domain}: " . ($response->json()['error'] ?? 'Unknown Error'));
        } catch (\Exception $e) {
            Log::error("[Strategy:SerpApi] Connection error: " . $e->getMessage());
        }
        
        return [];
    }
}
