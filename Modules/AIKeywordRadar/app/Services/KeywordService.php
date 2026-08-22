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
    public function syncKeywords(int $limit = 500, string $lang = 'ar', $userId = null, string $timeFilter = '60m', ?string $boxId = null)
    {
        ini_set('max_execution_time', 600);
        set_time_limit(600);
        $syncStart = microtime(true);
        $user = \App\Models\User::find($userId);
        
        $results = $this->getTargetKeywordsFromCompetitors($lang, $userId, $syncStart, $timeFilter, $boxId);
        $newKeywords = $results['keywords'] ?? [];
        $headlinesCount = $results['headlines_count'] ?? 0;
        
        $cacheKey = $boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}";
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
        
        $retentionLimit = match($timeFilter) {
            '60m' => now()->subMinutes(60),
            '24h' => now()->subHours(24),
            default => now()->subHours(24),
        };

        $keywordCategory = $boxId ? "Target:{$boxId}" : 'Target';

        // Purge any stored DB keywords for this category/lang that fall outside retention limit
        Keyword::where('user_id', $userId)
            ->where('category', $keywordCategory)
            ->where('lang', $lang)
            ->where(function($q) use ($retentionLimit) {
                $q->where('published_at', '<', $retentionLimit)
                  ->orWhere(function($q2) use ($retentionLimit) {
                      $q2->whereNull('published_at')
                         ->where('created_at', '<', $retentionLimit);
                  });
            })->delete();

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

            // Parse date more reliably
            $publishedAt = null;
            if (!empty($kw['published_at'])) {
                try {
                    $publishedAt = \Carbon\Carbon::parse($kw['published_at'])->setTimezone('UTC');
                } catch (\Exception $e) {
                    Log::warning("[Keyword Sync] Date parse failed for: " . $kw['published_at']);
                }
            }

            // Discard keywords published before retention limit
            if ($publishedAt !== null && $publishedAt->lt($retentionLimit)) {
                Log::info("[Keyword Sync] Discarded keyword older than retention limit: {$text} ({$publishedAt->toDateTimeString()})");
                continue;
            }

            $textLower = mb_strtolower($text, 'UTF-8');
            $isDuplicate = false;
            
            // Explicit exact match check first for speed
            if (isset($existingTexts[$text])) {
                $isDuplicate = true;
            } else {
                // Similarity check (prevents saving almost identical keywords)
                foreach ($existingTexts as $existing => $val) {
                    similar_text($textLower, mb_strtolower($existing, 'UTF-8'), $percent);
                    if ($percent >= 96) {
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
                        $publishedAt = \Carbon\Carbon::parse($kw['published_at'])->setTimezone('UTC');
                    } catch (\Exception $e) {
                        Log::warning("[Keyword Sync] Date parse failed for: " . $kw['published_at']);
                    }
                }

                $keywordCategory = $boxId ? "Target:{$boxId}" : 'Target';
                $updateData = [
                    'source' => $kw['source'] ?? 'AI', 
                    'synced_at' => now(), 
                ];
                if ($publishedAt !== null) {
                    $updateData['published_at'] = $publishedAt;
                }

                $keywordObj = Keyword::updateOrCreate(
                    ['keyword' => $text, 'category' => $keywordCategory, 'lang' => $lang, 'user_id' => $userId],
                    $updateData
                );

                $effectivePubAt = $keywordObj->published_at ?? $publishedAt;

                $existingKeywords[] = [
                    'text' => $text,
                    'source' => $kw['source'] ?? 'AI',
                    'published_at' => $effectivePubAt ? $effectivePubAt->toIso8601String() : null,
                    'created_at' => $keywordObj->created_at->toIso8601String()
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
            'duplicates' => count($newKeywords) - $addedCount,
            'added_to_cache' => $addedCount,
            'total_in_cache' => count($existingKeywords),
            'headlines' => $headlinesCount
        ];
    }

    /**
     * Fetch competitor headlines and extract keywords using AI
     */
    public function getTargetKeywordsFromCompetitors($lang = 'ar', $userId = null, $syncStart = null, string $timeFilter = '60m', ?string $boxId = null)
    {
        $syncStart = $syncStart ?? microtime(true);
        $rawHeadlines = $this->fetchCompetitorsHeadlines($lang, $userId, $syncStart, $timeFilter, $boxId);
        
        if (empty($rawHeadlines)) {
            return ['keywords' => [], 'headlines_count' => 0];
        }

        // === STEP 1: Deduplicate headlines BEFORE sending to AI ===
        // Multiple competitors often cover the same story.
        // Sending duplicates wastes AI tokens and produces redundant keywords.
        $headlines = $this->deduplicateHeadlines($rawHeadlines);
        $removedDupes = count($rawHeadlines) - count($headlines);
        
        Log::info("[Keyword Radar] Dedup: {$removedDupes} duplicate headlines removed. {" . count($rawHeadlines) . " raw → " . count($headlines) . " unique}");

        if (empty($headlines)) {
            return ['keywords' => [], 'headlines_count' => 0];
        }

        // === STEP 2: AI Keyword Extraction in batches ===
        // Batch size of 30 — balanced between speed (fewer API calls) and reliability
        $keywordBatches = array_chunk($headlines, 30);
        $allKeywords = [];
        
        // AI extraction time budget — background job has 600s timeout,
        // headlines may take up to 120s, so we give AI extraction 400s.
        $aiExtractionStart = microtime(true);
        
        Log::info("[Keyword Radar] Sending " . count($headlines) . " unique headlines to AI in " . count($keywordBatches) . " batches.");
        
        foreach ($keywordBatches as $batchIndex => $batch) {
            $aiElapsed = microtime(true) - $aiExtractionStart;
            if ($aiElapsed > 400) {
                Log::warning("[Keyword Radar] AI extraction time budget reached (" . round($aiElapsed) . "s elapsed, " . count($allKeywords) . " keywords so far). Returning partial batch results.");
                break;
            }

            $batchKeywords = $this->extractKeywordsWithAI($batch, $lang, $userId);
            if (!empty($batchKeywords)) {
                $allKeywords = array_merge($allKeywords, $batchKeywords);
            }
        }

        return [
            'keywords' => $allKeywords,
            'headlines_count' => count($headlines),
            'raw_headlines' => count($rawHeadlines),
            'duplicates_removed' => $removedDupes
        ];
    }

    /**
     * Remove duplicate and near-duplicate headlines before AI processing.
     * Uses normalized text matching + Arabic/English word-overlap similarity.
     * This prevents wasting AI tokens on similar headlines from multiple sources.
     */
    protected function deduplicateHeadlines(array $headlines): array
    {
        $unique = [];
        $seenNormalized = [];  // Exact match after normalization
        $seenWords = [];       // For word-overlap similarity check

        foreach ($headlines as $h) {
            $title = $h['title'] ?? '';
            if (empty($title)) continue;

            // Step 1: Normalize — lowercase, remove punctuation/diacritics, collapse whitespace
            $normalized = mb_strtolower($title, 'UTF-8');
            // Remove Arabic diacritics (tashkeel)
            $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $normalized);
            // Remove common punctuation
            $normalized = preg_replace('/[\p{P}\p{S}]+/u', ' ', $normalized);
            // Collapse whitespace
            $normalized = preg_replace('/\s+/u', ' ', trim($normalized));

            // Step 2: Exact normalized match
            if (isset($seenNormalized[$normalized])) {
                continue;
            }

            // Step 3: Word-overlap similarity
            // Split into meaningful words (3+ chars to skip prepositions)
            $words = array_filter(
                preg_split('/\s+/u', $normalized),
                fn($w) => mb_strlen($w, 'UTF-8') >= 3
            );
            $wordSet = array_flip($words);
            $wordCount = count($words);

            if ($wordCount >= 3) {
                $isDuplicate = false;
                foreach ($seenWords as $idx => $existingWordSet) {
                    // Calculate word overlap
                    $commonWords = count(array_intersect_key($wordSet, $existingWordSet));
                    $maxWords = max($wordCount, count($existingWordSet));
                    $overlapRatio = $maxWords > 0 ? ($commonWords / $maxWords) : 0;

                    if ($overlapRatio >= 0.60) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if ($isDuplicate) continue;
            }

            // This headline is unique — keep it
            $seenNormalized[$normalized] = true;
            $seenWords[] = $wordSet;
            $unique[] = $h;
        }

        return $unique;
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
                            'pubDate' => null,
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

    public function fetchCompetitorsHeadlines($lang = 'ar', $userId = null, $syncStart = null, string $timeFilter = '60m', ?string $boxId = null)
    {
        $syncStart = $syncStart ?? microtime(true);
        $competitorUrls = $this->getMergedCompetitorUrls($userId, $lang, $boxId);
        
        if (empty($competitorUrls)) {
            Log::warning("[Keyword Radar] No competitors found for user #{$userId} in lang {$lang}.");
            return [];
        }

        // Process ALL competitors — no cap, no rotation.
        Log::info("[Keyword Radar] Processing ALL " . count($competitorUrls) . " competitors.");

        $allHeadlines = [];
        $userAgent = $this->getRandomUserAgent();
        $hl = ($lang === 'en') ? 'en' : 'ar';
        $ceid = ($lang === 'en') ? 'US:en' : 'EG:ar';

        // === PHASE 1: MASSIVE PARALLEL FETCH ===
        // Fire ALL request variants at once — sitemap, Google News RSS, RSS feeds, and homepage HTML.
        // Index tracking ensures responses are mapped correctly to string keys.
        $keys = [];
        $responses = Http::pool(function ($pool) use ($competitorUrls, $userAgent, $hl, $ceid, $timeFilter, &$keys) {
            $reqs = [];
            foreach ($competitorUrls as $url) {
                $url = trim($url);
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }
                $url = rtrim($url, '/');
                $domain = parse_url($url, PHP_URL_HOST) ?: $url;
                $cleanDomain = preg_replace('/^www\./i', '', $domain);
                
                // Sitemap News
                $keys[] = "{$domain}_sitemap";
                $reqs[] = $pool->withHeaders(['User-Agent' => $userAgent])
                    ->timeout(6)->get($url . '/sitemap-news.xml');

                // Google News RSS (High reliability for real article publication dates)
                $whenTag = ($timeFilter === '24h') ? '+when:1d' : '+when:1h';
                $keys[] = "{$domain}_gnews";
                $reqs[] = $pool->withHeaders(['User-Agent' => $userAgent])
                    ->timeout(6)->get("https://news.google.com/rss/search?q=site:{$cleanDomain}{$whenTag}&hl={$hl}&gl=EG&ceid={$ceid}");
                
                // RSS variants — try multiple paths in parallel
                $keys[] = "{$domain}_rss";
                $reqs[] = $pool->withHeaders(['User-Agent' => $userAgent])
                    ->timeout(6)->get($url . '/rss');
                $keys[] = "{$domain}_feed";
                $reqs[] = $pool->withHeaders(['User-Agent' => $userAgent])
                    ->timeout(6)->get($url . '/feed');
                
                // Homepage HTML — for scraping fallback
                $keys[] = "{$domain}_html";
                $reqs[] = $pool->withHeaders(['User-Agent' => $userAgent])
                    ->timeout(6)->get($url);
            }
            return $reqs;
        });

        // Keyed response mapping
        $keyedResponses = [];
        foreach ($responses as $i => $resp) {
            if (isset($keys[$i])) {
                $keyedResponses[$keys[$i]] = $resp;
            }
        }

        Log::info("[Keyword Radar] Parallel pool completed for " . count($competitorUrls) . " competitors. Processing responses...");

        // Track which competitors need sequential fallback
        $needsFallback = [];

        foreach ($competitorUrls as $url) {
            // Time safety check
            if ((microtime(true) - $syncStart) > 300) {
                Log::warning("[Keyword Radar] Headline fetch budget reached (" . round(microtime(true) - $syncStart) . "s). " . count($allHeadlines) . " headlines collected.");
                break;
            }

            $url = trim($url);
            if (!preg_match('/^https?:\/\//i', $url)) {
                $url = 'https://' . $url;
            }
            $url = rtrim($url, '/');
            $domain = parse_url($url, PHP_URL_HOST) ?: $url;
            $siteHeadlines = [];

            // 1. Try Sitemap first (best quality — has dates)
            $sitemapResp = $keyedResponses["{$domain}_sitemap"] ?? null;
            if ($sitemapResp instanceof \Illuminate\Http\Client\Response && $sitemapResp->successful()) {
                $siteHeadlines = $this->parseSimpleSitemap($sitemapResp->body());
            }

            // 2. Try Google News RSS (High reliability — has real publication dates)
            if (empty($siteHeadlines)) {
                $gnewsResp = $keyedResponses["{$domain}_gnews"] ?? null;
                if ($gnewsResp instanceof \Illuminate\Http\Client\Response && $gnewsResp->successful()) {
                    $siteHeadlines = $this->parseGoogleNewsRss($gnewsResp->body());
                }
            }

            // 3. Try RSS variants
            if (empty($siteHeadlines)) {
                foreach (["{$domain}_rss", "{$domain}_feed"] as $key) {
                    $rssResp = $keyedResponses[$key] ?? null;
                    if ($rssResp instanceof \Illuminate\Http\Client\Response && $rssResp->successful()) {
                        $body = $rssResp->body();
                        if (str_contains($body, '<rss') || str_contains($body, '<feed') || str_contains($body, '<channel')) {
                            $siteHeadlines = $this->parseSimpleRss($body);
                            if (!empty($siteHeadlines)) break;
                        }
                    }
                }
            }

            // 4. Try HTML scraping from homepage (already fetched in parallel)
            if (empty($siteHeadlines)) {
                $htmlResp = $keyedResponses["{$domain}_html"] ?? null;
                if ($htmlResp instanceof \Illuminate\Http\Client\Response && $htmlResp->successful()) {
                    $siteHeadlines = $this->extractHeadlinesFromHtml($htmlResp->body(), $domain);
                }
            }

            // If ALL parallel attempts failed, queue for sequential fallback
            if (empty($siteHeadlines)) {
                $needsFallback[] = $url;
                continue;
            }

            // === STRICT TIME FILTER ===
            $freshnessLimit = match($timeFilter) {
                '60m' => now()->subMinutes(60),
                '24h' => now()->subHours(24),
                default => now()->subHours(24),
            };
            
            $seenLocalTitles = [];
            $totalFoundOnSite = count($siteHeadlines);
            $freshOnSite = 0;
            $skippedNoDate = 0;
            $skippedTooOld = 0;

            foreach ($siteHeadlines as $item) {
                $title = $item['title'] ?? '';
                $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
                $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title);
                $title = trim($title);
                if (empty($title) || mb_strlen($title) < 5 || isset($seenLocalTitles[$title])) continue;

                $rawDate = $item['pubDate'] ?? null;

                // IMMEDIATELY DISCARD any headline containing a past year (2010-2025)
                if (preg_match('/\b(201[0-9]|202[0-5])\b/', $title . ' ' . ($rawDate ?? ''))) {
                    $skippedTooOld++;
                    continue;
                }

                $pubDate = null;
                if (!empty($rawDate)) {
                    $parsedStr = $this->parseDateString($rawDate);
                    if ($parsedStr) {
                        try { $pubDate = \Carbon\Carbon::parse($parsedStr); } catch (\Exception $e) { $pubDate = null; }
                    }
                }

                // STRICT ENFORCEMENT: Discard if pubDate is older than freshnessLimit
                if ($pubDate && $pubDate->lt($freshnessLimit)) {
                    $skippedTooOld++;
                    continue;
                }

                // If feed/sitemap item has no parseable date when 60m filter is requested, discard to prevent old leakage
                if (!$pubDate && $timeFilter === '60m' && !empty($item['is_feed'])) {
                    $skippedNoDate++;
                    continue;
                }

                $allHeadlines[] = ['title' => $title, 'source' => $domain, 'pubDate' => $item['pubDate'] ?? null];
                $seenLocalTitles[$title] = true;
                $freshOnSite++;
            }
            
            Log::info("[Competitor Sync] {$domain}: {$totalFoundOnSite} total → {$freshOnSite} fresh (skipped: {$skippedTooOld} old, {$skippedNoDate} no-date) [Filter: {$timeFilter}]");
        }

        // === PHASE 2: FAST SEQUENTIAL FALLBACK ===
        // Only for competitors that returned NOTHING from parallel fetch.
        // Try Google News/Search as fallback (these can't be parallelized easily).
        if (!empty($needsFallback)) {
            Log::info("[Keyword Radar] Phase 2: Sequential fallback for " . count($needsFallback) . " competitors.");
            foreach ($needsFallback as $url) {
                if ((microtime(true) - $syncStart) > 350) {
                    Log::warning("[Keyword Radar] Fallback time budget reached. Skipping remaining " . count($needsFallback) . " competitors.");
                    break;
                }

                $url = trim($url);
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }
                $url = rtrim($url, '/');
                $domain = parse_url($url, PHP_URL_HOST) ?: $url;
                $testResult = $this->testUrl($url, $lang, $userId);
                $siteHeadlines = $testResult['headlines'] ?? [];

                if (empty($siteHeadlines)) continue;

                $freshnessLimit = null;
                if ($timeFilter === '60m') { $freshnessLimit = now()->subMinutes(60); }
                elseif ($timeFilter === '24h') { $freshnessLimit = now()->subHours(24); }

                $freshOnSite = 0;
                $totalFoundOnSite = count($siteHeadlines);
                foreach ($siteHeadlines as $item) {
                    $title = $item['title'] ?? '';
                    $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
                    $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title);
                    $title = trim($title);
                    if (empty($title) || mb_strlen($title) < 5) continue;

                    $pubDate = null;
                    if (!empty($item['pubDate'])) {
                        try { $pubDate = \Carbon\Carbon::parse($item['pubDate']); } catch (\Exception $e) { $pubDate = null; }
                    }
                    if ($freshnessLimit && $pubDate) {
                        if ($pubDate->lt($freshnessLimit)) continue;
                    }

                    $allHeadlines[] = ['title' => $title, 'source' => $domain, 'pubDate' => $item['pubDate'] ?? null];
                    $freshOnSite++;
                }
                Log::info("[Fallback Sync] {$domain}: {$totalFoundOnSite} total → {$freshOnSite} fresh [Filter: {$timeFilter}]");
            }
        }

        Log::info("[Keyword Radar] Total headlines collected: " . count($allHeadlines) . " in " . round(microtime(true) - $syncStart) . "s");
        shuffle($allHeadlines);
        return $allHeadlines;
    }

    /**
     * Helpers for fast parallel parsing
     */
    protected function parseSimpleSitemap($xmlBody): array {
        try {
            $xml = @simplexml_load_string($xmlBody, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml) return [];
            $items = [];
            
            // Register namespaces to handle news sitemaps properly
            $namespaces = $xml->getNamespaces(true);
            $newsNs = $namespaces['news'] ?? $namespaces['n'] ?? 'http://www.google.com/schemas/sitemap-news/0.9';
            
            foreach ($xml->url ?? [] as $url) {
                $newsNode = $url->children($newsNs);
                if (isset($newsNode->news)) {
                    $news = $newsNode->news;
                    $title = (string)($news->title ?? '');
                    $pubDate = (string)($news->publication_date ?? '');
                    if (!empty($title)) {
                        $items[] = [
                            'title' => $title,
                            'pubDate' => $pubDate,
                        ];
                    }
                }
            }
            
            Log::debug("[parseSimpleSitemap] Parsed " . count($items) . " items from news sitemap.");
            return $items;
        } catch (\Exception $e) { 
            Log::debug("[parseSimpleSitemap] Parse error: " . $e->getMessage());
            return []; 
        }
    }

    protected function parseSimpleRss($xmlBody): array {
        try {
            $xml = @simplexml_load_string($xmlBody, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml) return [];
            $items = [];
            $channel = $xml->channel ?? $xml;
            foreach ($channel->item ?? $channel->entry ?? [] as $item) {
                $items[] = [
                    'title' => (string)($item->title ?? ''),
                    'pubDate' => (string)($item->pubDate ?? $item->published ?? $item->updated ?? '')
                ];
            }
            return $items;
        } catch (\Exception $e) { return []; }
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
            $googleNewsUrl = "https://news.google.com/rss/search?q={$encodedQ}+when:1h&hl={$hl}&gl=EG&ceid=EG:{$hl}";

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
        $xml = @simplexml_load_string($xmlBody, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml || !isset($xml->channel->item)) return [];

        $items = [];
        foreach ($xml->channel->item as $item) {
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
                foreach ($items as &$item) {
                    $item['pubDate'] = $this->parseDateString($item['pubDate'] ?? null);
                }
            }
            return $items;
        } catch (\Exception $e) {
            Log::warning("[Strategy:HTML] Failed for {$url}: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'Direct HTML Scraper', $userId, ['url' => $url]);
            return [];
        }
    }

    /**
     * Extract headlines from already-fetched HTML body (no HTTP request needed).
     * Used by the parallel pool phase for fast HTML fallback.
     */
    protected function extractHeadlinesFromHtml(string $html, string $domain): array
    {
        $depth = (int)\App\Models\Setting::get('ai-keyword-radar_scraping_depth', 20);
        $items = [];
        $seen = [];

        // Extract from <h1>, <h2>, <h3> tags
        if (preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/si', $html, $matches)) {
            foreach ($matches[1] as $rawTitle) {
                $title = trim(strip_tags($rawTitle));
                $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                $title = preg_replace('/\s+/', ' ', $title);
                if (!empty($title) && mb_strlen($title) >= 15 && mb_strlen($title) <= 200 && !isset($seen[$title])) {
                    // Try to extract an explicit <time> or date tag if present nearby
                    $pubDate = null;
                    if (preg_match('/<time[^>]*datetime=["\']([^"\']+)["\']/i', $rawTitle, $tm)) {
                        $pubDate = $this->parseDateString($tm[1]);
                    }
                    $items[] = ['title' => $title, 'pubDate' => $pubDate];
                    $seen[$title] = true;
                    if (count($items) >= $depth) break;
                }
            }
        }

        // Fallback: <a> tags with long text
        if (count($items) < 5) {
            if (preg_match_all('/<a[^>]+>([^<]{20,150})<\/a>/u', $html, $linkMatches)) {
                foreach ($linkMatches[1] as $linkText) {
                    $title = trim(html_entity_decode($linkText, ENT_QUOTES, 'UTF-8'));
                    if (!empty($title) && mb_strlen($title) >= 20 && !isset($seen[$title])
                        && !str_contains($title, 'http') && !str_contains($title, '@')) {
                        $items[] = ['title' => $title, 'pubDate' => null];
                        $seen[$title] = true;
                        if (count($items) >= $depth) break;
                    }
                }
            }
        }

        if (!empty($items)) {
            Log::info("[Parallel HTML] Scraped " . count($items) . " headlines from: {$domain}");
        }
        return $items;
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
            // Sanitize title — force valid UTF-8 to prevent json_encode failures
            $title = $h['title'] ?? '';
            $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
            $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title); // Remove control chars
            $title = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\p{S}]/u', '', $title); // Keep only valid unicode
            $title = trim($title);
            if (empty($title)) continue;
            $titlesText .= ($idx + 1) . ". [{$sourceName}] " . $title . "\n";
        }

        $count = count($headlines);
        $langInstruction = ($lang === 'en') ? "English" : "Arabic";
        
        $dbPrompt = \App\Models\Setting::get('ai-keyword-radar_prompt');
        if ($dbPrompt) {
            $prompt = str_replace(['[Headlines]', '[headlines]', '[lang]'], [$titlesText, $titlesText, $langInstruction], $dbPrompt);
            if (!str_contains($prompt, 'Return ONLY a JSON array')) {
                $prompt .= "\n\nCRITICAL: You must process ALL {$count} headlines provided and return exactly {$count} keywords. Each keyword MUST be a complete, understandable search phrase (2 to 7 words). Return ONLY a valid JSON array: [{\"index\": 1, \"keyword\": \"...\"}]";
            }
        } else {
            $prompt = "You are an expert SEO specialist. Your task is to transform EVERY SINGLE competitor headline provided below into a highly searched, high-intent, complete Target Search Query (Keyword). You MUST output exactly ONE search query for EACH headline, meaning you must return exactly {$count} keywords.
            
Headlines:
{$titlesText}

Rules:
1. COMPLETE & UNDERSTANDABLE: Each keyword MUST be a complete, clear search phrase (2 to 7 words). Do NOT output single generic words (e.g. 'مصر' or 'الذهب') or vague incomplete fragments.
2. HIGH SEARCH INTENT: Focus on key entities, events, prices, steps, or news topics that real users search for on Google (e.g. 'سعر جرام الذهب عيار 21 اليوم', 'موعد مباراة الأهلي والزمالك القادمة والقنوات الناقلة', 'قرارات البنك المركزي بشأن الفائدة').
3. NO DATES/YEARS: Omit specific dates or years (like 2025 or 2026) unless strictly part of the official entity name.
4. Output language MUST be strictly: {$langInstruction}.
5. You MUST process ALL {$count} headlines provided.
6. Return ONLY a valid JSON array of objects, with each object containing the exact 'index' of the headline and the transformed 'keyword': [{\"index\": 1, \"keyword\": \"...\"}]";
        }

        try {
            // === TOOL AI SETTINGS RESOLUTION ===
            // Priority: User settings → Tool settings (DB) → Hardcoded defaults
            $userSettings = \App\Models\User::find($userId)?->settings ?? [];
            
            // 1. Resolve Provider (tool-specific → user-specific → default)
            $provider = \App\Models\Setting::get('ai-keyword-radar_provider')
                        ?? $userSettings['keywords_ai_provider'] 
                        ?? 'openrouter';
            // Normalize: 'gemini' → 'google'
            if ($provider === 'gemini') $provider = 'google';
            
            // 2. Resolve Model (tool-specific → user-specific → default per provider)
            $model = \App\Models\Setting::get('ai-keyword-radar_model')
                     ?? $userSettings['keywords_ai_model'] 
                     ?? null;
            // If no model specified, use a sensible default for the chosen provider
            if (empty($model)) {
                $model = match ($provider) {
                    'openrouter' => 'openai/gpt-4o-mini',
                    'google'     => 'gemini-2.0-flash',
                    'openai'     => 'gpt-4o-mini',
                    'anthropic'  => 'claude-3-haiku-20240307',
                    default      => 'openai/gpt-4o-mini',
                };
            }
            
            // 3. Resolve API Key: Tool-specific key → Global key for same provider
            $toolApiKey = \App\Models\Setting::get('ai-keyword-radar_api_key');
            if (empty($toolApiKey)) {
                // Check the ai_chain for a stored key (legacy support)
                $chainData = \App\Models\Setting::get('ai-keyword-radar_ai_chain');
                $chain = is_array($chainData) ? $chainData : ($chainData ? json_decode($chainData, true) : null);
                if (!empty($chain[0]['api_key'])) {
                    $toolApiKey = $chain[0]['api_key'];
                }
            }
            // If still no key, resolve global key for the provider
            if (empty($toolApiKey)) {
                $globalKeyName = match ($provider) {
                    'openrouter' => 'openrouter_api_key',
                    'google'     => 'gemini_api_key',
                    'openai'     => 'openai_api_key',
                    'anthropic'  => 'anthropic_api_key',
                    default      => null,
                };
                if ($globalKeyName) {
                    $toolApiKey = trim(\App\Models\Setting::get($globalKeyName) ?? '');
                    // Final fallback: check .env
                    if (empty($toolApiKey)) {
                        $toolApiKey = trim(env(strtoupper($globalKeyName)) ?? '');
                        if (empty($toolApiKey) && $provider === 'google') {
                            $toolApiKey = trim(env('GOOGLE_API_KEY') ?? '');
                        }
                    }
                }
            }

            Log::info("[Keyword Radar AI] Provider: {$provider}, Model: {$model}, Key: " . (empty($toolApiKey) ? 'MISSING' : '...' . substr($toolApiKey, -4)));

            $aiResult = $this->aiManager->generate('ai-keyword-radar', $prompt, [
                'provider'    => $provider,
                'model'       => $model,
                'api_key'     => $toolApiKey,
                'temperature' => 0.1,
                'json_mode'   => false,
                'max_tokens'  => 4000,
                'tool_name'   => 'Keyword Radar',
            ]);
            
            Log::info("AI Keyword Radar [{$lang}] Prompt Snippet: " . substr($prompt, 0, 400));
            $response = $aiResult['text'] ?? '';
            $parsedKeywords = $this->parseKeywordsResponse($response, $headlines, $userId);
            
            if (empty($parsedKeywords)) {
                $rawSnippet = substr($response, 0, 800);
                Log::emergency("[Keyword Radar] AI Raw Response (Empty Arrays): {$rawSnippet}");
                
                \App\Models\ToolError::log('ai-keyword-radar', "AI returned 0 valid keywords from " . count($headlines) . " headlines. Check raw response for potential API errors or formatting issues.", 'AI Keyword Extraction', $userId, [
                    'provider' => $provider,
                    'model' => $model,
                    'raw_response' => $response ?: ($aiResult['raw_response'] ?? 'EMPTY_RESPONSE')
                ]);
                return [];
            }

            // Apply global Quality Filters
            $keywords = $this->filterSimilarKeywords($parsedKeywords, 0.6, $userId);
            
            Log::info("[Keyword Radar] AI generated " . count($parsedKeywords) . " keywords. Filter kept " . count($keywords));

            return $keywords;

        } catch (\Exception $e) {
            Log::error("[Competitor Keywords] AI Failed: " . $e->getMessage());
            ToolError::log('ai-keyword-radar', $e, 'AI Keyword Extraction', $userId, [
                'headline_count' => count($headlines),
                'provider' => $provider ?? 'unknown',
                'model' => $model ?? 'unknown'
            ]);
            return [];
        }
    }

    public function parseKeywordsResponse($response, $headlines, $userId = null)
    {
        $uniqueResults = [];
        
        // 1. Clean response: Remove markdown code blocks and 'Thinking' signatures
        $cleanResponse = preg_replace('/<think>.*?<\/think>/s', '', $response);

        if (preg_match('/```(?:json)?\s*(.*?)```/s', $cleanResponse, $matches)) {
            $cleanResponse = $matches[1];
        }

        // 2. Try to find any JSON array [...]
        $decoded = null;
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $cleanResponse, $matches)) {
            $decoded = json_decode($matches[0], true);
        } else {
            $decoded = json_decode(trim($cleanResponse), true);
        }

        // 2.2 Radical Fallback: If JSON is completely broken or truncated due to token limits, 
        // aggressively extract any valid complete JSON objects from the string.
        if (!$decoded && preg_match_all('/\{[^{}]*"keyword"\s*:[^{}]*\}/s', $cleanResponse, $objMatches)) {
            $decoded = [];
            foreach ($objMatches[0] as $strObj) {
                // Ensure it ends cleanly
                if (substr(trim($strObj), -1) !== '}') $strObj .= '}';
                $parsedObj = json_decode($strObj, true);
                if ($parsedObj) $decoded[] = $parsedObj;
            }
        }

        // 2.5 Handle cases where the model wraps the array in an object (e.g. { "keywords": [...] })
        if (is_array($decoded) && !isset($decoded[0]) && count($decoded) > 0) {
            foreach (['keywords', 'results', 'data', 'suggestions', 'list'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    $decoded = $decoded[$key];
                    break;
                }
            }
        }

        if (is_array($decoded)) {
            foreach ($decoded as $itemIdx => $item) {
                $text = '';
                $source = 'AI';
                $pubDate = null;
                $idx = -1;

                if (is_array($item)) {
                    $text = $item['keyword'] ?? $item['text'] ?? '';
                    if (isset($item['index'])) {
                        $idx = (int)$item['index'] - 1;
                    }
                } else {
                    $text = (string)$item;
                }

                $text = trim($text);
                if (empty($text)) continue;

                // 3. Fallback 1: Try fuzzy match and word overlap against headlines
                if ($idx < 0 || !isset($headlines[$idx])) {
                    $bestMatchIdx = -1;
                    $bestScore = 0.0;
                    $cleanKeyword = $this->normalizeForComparison($text);
                    $kwWords = $this->extractSignificantWords($cleanKeyword, []);

                    foreach ($headlines as $hIdx => $h) {
                        $cleanHeadline = $this->normalizeForComparison($h['title']);
                        if (str_contains($cleanHeadline, $cleanKeyword) || str_contains($cleanKeyword, $cleanHeadline)) {
                            $bestMatchIdx = $hIdx;
                            break;
                        }
                        $hWords = $this->extractSignificantWords($cleanHeadline, []);
                        $score = $this->calculateWordOverlap($kwWords, $hWords);
                        if ($score > $bestScore && $score >= 0.3) {
                            $bestScore = $score;
                            $bestMatchIdx = $hIdx;
                        }
                    }
                    if ($bestMatchIdx >= 0) {
                        $idx = $bestMatchIdx;
                    }
                }

                // Fallback 2: Direct batch index alignment
                if ($idx < 0 || !isset($headlines[$idx])) {
                    if (isset($headlines[$itemIdx])) {
                        $idx = $itemIdx;
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
                    'created_at' => now()->toIso8601String()
                ];
            }
        }
        // We return the raw parsed unique results here. 
        // Filtering is done separately so we can accurately diagnose AI output.
        return array_values($uniqueResults);
    }

    protected function filterSimilarKeywords(array $keywords, float $threshold = 0.6, $userId = null): array
    {
        if (count($keywords) <= 1) return $keywords;
        
        $minChars = (int)\App\Models\Setting::get('ai-keyword-radar_min_chars', 8);
        $minWords = (int)\App\Models\Setting::get('ai-keyword-radar_min_words', 2);
        $maxWords = (int)\App\Models\Setting::get('ai-keyword-radar_max_words', 12);
        
        $similarity = \App\Models\Setting::get('ai-keyword-radar_similarity_threshold');
        if ($similarity !== null && is_numeric($similarity)) {
            $threshold = (float)$similarity / 100.0;
        }
        
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
            
            // Dynamic length filter
            if (empty($text) || mb_strlen($text) < $minChars) continue;
            
            $words = array_filter(explode(' ', $text));
            $wordCount = count($words);
            
            // Dynamic word count filter
            if ($wordCount < $minWords || $wordCount > $maxWords) continue;

            $normalizedText = $this->normalizeForComparison($text);
            $currentWords = $this->extractSignificantWords($normalizedText, $fillerWords);
            
            // If the keyword is mostly filler words, still keep it if it meets the minWords criteria
            if (count($currentWords) < 2) {
                if (!in_array($normalizedText, $usedTexts) && $wordCount >= $minWords) {
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
    public function getSuggestedCompetitors(string $lang = 'ar', ?string $topic = null): array
    {
        $langName = ($lang === 'en') ? 'English' : 'Arabic';
        $region = ($lang === 'en') ? 'Global/US/UK' : 'Middle East/Egypt/Gulf';
        
        $topicPhrase = $topic ? "specifically focused on '{$topic}' (or closely related fields)" : "high-authority, high-traffic news websites, trending blogs, or viral content platforms";

        $prompt = "As an SEO and Digital Marketing expert, suggest a list of 15 {$topicPhrase} in {$langName} language (targeting {$region}) that would be excellent sources for extracting trending search keywords and breaking news headlines.

Rules:
1. Return ONLY a JSON array of complete URLs (starting with https://).
2. The sources must be reliable and frequently updated.
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
    public function getMergedCompetitorUrls($userId, $lang = 'ar', ?string $boxId = null)
    {
        $normalizeUrls = function ($text) {
            $lines = preg_split('/\r\n|\r|\n/', (string)$text);
            $clean = [];
            foreach ($lines as $line) {
                $url = trim($line);
                if (empty($url)) continue;
                if (!preg_match('/^https?:\/\//i', $url)) {
                    $url = 'https://' . $url;
                }
                $url = rtrim($url, '/');
                $clean[] = $url;
            }
            return array_values(array_unique($clean));
        };

        $user = \App\Models\User::find($userId);
        if (!$user) {
            // If user is null (system sync), use global only
            $globalCompetitorsText = \App\Models\Setting::get('ai-keyword-radar_competitors', '');
            return $normalizeUrls($globalCompetitorsText);
        }
        
        $settings = $user->settings ?? [];

        // Custom Box: return only that box's competitors
        if ($boxId) {
            $customBoxes = $settings['keywords_custom_boxes'] ?? [];
            foreach ($customBoxes as $box) {
                if (($box['id'] ?? '') === $boxId) {
                    return $normalizeUrls($box['competitors'] ?? '');
                }
            }
            Log::warning("[Keyword Radar] Custom box '{$boxId}' not found for user #{$userId}");
            return [];
        }
        
        // 1. User Competitors
        $userCompetitorsText = ($lang === 'en') ? ($settings['keywords_competitors_en'] ?? '') : ($settings['keywords_competitors'] ?? '');
        $userUrls = $normalizeUrls($userCompetitorsText);
        
        // 2. Global Admin Competitors
        $globalCompetitorsText = \App\Models\Setting::get('ai-keyword-radar_competitors', '');
        $globalUrls = $normalizeUrls($globalCompetitorsText);
        
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
                        $pubDateStr = $this->parseDateString($res['date'] ?? $res['snippet'] ?? null);
                        $items[] = [
                            'title' => $title,
                            'pubDate' => $pubDateStr,
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

    /**
     * Helper: Robustly parse relative and ISO date strings into standard UTC datetime string
     */
    public function parseDateString(?string $str): ?string
    {
        if (empty($str)) return null;
        $str = trim(strip_tags($str));
        if (empty($str)) return null;

        // Convert Eastern Arabic numerals (٠١٢٣٤٥٦٧٨٩) to Western (0123456789)
        $normalized = strtr($str, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        
        // Arabic relative time patterns
        if (preg_match('/قبل\s+(\d+)\s+ساع/u', $normalized, $m)) return now()->subHours((int)$m[1])->toDateTimeString();
        if (preg_match('/قبل\s+ساعتين/u', $normalized)) return now()->subHours(2)->toDateTimeString();
        if (preg_match('/قبل\s+ساعة/u', $normalized)) return now()->subHours(1)->toDateTimeString();
        if (preg_match('/قبل\s+(\d+)\s+دقيق/u', $normalized, $m)) return now()->subMinutes((int)$m[1])->toDateTimeString();
        if (preg_match('/قبل\s+دقيقتين/u', $normalized)) return now()->subMinutes(2)->toDateTimeString();
        if (preg_match('/قبل\s+دقيقة/u', $normalized)) return now()->subMinutes(1)->toDateTimeString();
        if (preg_match('/قبل\s+(\d+)\s+يوم/u', $normalized, $m)) return now()->subDays((int)$m[1])->toDateTimeString();
        if (preg_match('/قبل\s+يومين/u', $normalized)) return now()->subDays(2)->toDateTimeString();
        if (preg_match('/قبل\s+يوم/u', $normalized)) return now()->subDays(1)->toDateTimeString();

        // English relative time patterns
        if (preg_match('/(\d+)\s*h(?:ours?)?\s*ago/i', $normalized, $m)) return now()->subHours((int)$m[1])->toDateTimeString();
        if (preg_match('/(\d+)\s*m(?:ins?|inutes?)?\s*ago/i', $normalized, $m)) return now()->subMinutes((int)$m[1])->toDateTimeString();
        if (preg_match('/(\d+)\s*d(?:ays?)?\s*ago/i', $normalized, $m)) return now()->subDays((int)$m[1])->toDateTimeString();

        try {
            $dt = \Carbon\Carbon::parse($normalized);
            if ($dt->year >= 2020) {
                // If parsed date is in the future relative to UTC now (likely local time parsed without timezone offset),
                // adjust for local UTC+3 offset
                if ($dt->gt(now())) {
                    $diffMinutes = $dt->diffInMinutes(now());
                    if ($diffMinutes <= 240) { // Up to 4 hours offset skew
                        $dt->subHours(3);
                    }
                    if ($dt->gt(now())) {
                        $dt = now();
                    }
                }
                return $dt->setTimezone('UTC')->toDateTimeString();
            }
        } catch (\Exception $e) {}

        return null;
    }
}
