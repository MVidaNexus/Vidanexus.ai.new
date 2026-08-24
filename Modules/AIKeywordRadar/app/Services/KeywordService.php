<?php

namespace Modules\AIKeywordRadar\Services;

use Modules\AIKeywordRadar\Models\Keyword;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Core\AI\AIManager;
use App\Models\ToolError;
use App\Support\CountryRegistry;
use Modules\AIKeywordRadar\Support\KeywordPayload;

class KeywordService
{
    protected $aiManager;

    /** Last raw AI text from keyword extraction (for error diagnostics). */
    protected ?string $lastKeywordExtractionRawResponse = null;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Fetch Google Trends from RSS
     */
    public function fetchGoogleTrends($region = 'EG')
    {
        $region = CountryRegistry::normalizeCode($region) ?: CountryRegistry::defaultRegion();
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
    public function fetchNewsFromGoogle($country = 'EG', $topic = 'WORLD', $lang = null)
    {
        $country = CountryRegistry::normalizeCode($country) ?: CountryRegistry::defaultRegion();
        $lang = $lang ?: CountryRegistry::langFor($country);
        $topic = strtoupper($topic);

        if ($topic === 'GENERAL' || $topic === 'TOP_STORIES') {
            $url = \App\Support\GoogleNewsRss::feedUrl($country, $lang);
        } else {
            $url = \App\Support\GoogleNewsRss::sectionUrl($topic, $country, $lang);
        }
        
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->failed()) return [];

            $xml = new \SimpleXMLElement($response->body());
            $news = [];
            foreach ($xml->channel->item as $item) {
                $mapped = \App\Support\GoogleNewsRss::mapRssItem($item);
                if ($mapped === null) {
                    continue;
                }
                $news[] = [
                    'title' => $mapped['title'],
                    'link' => $mapped['link'],
                    'pubDate' => $mapped['pubDate'],
                    'source' => $mapped['source'],
                    'description' => $mapped['description'],
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
        ini_set('max_execution_time', 300);
        set_time_limit(300);
        $syncStart = microtime(true);
        $user = \App\Models\User::find($userId);
        
        $results = $this->getTargetKeywordsFromCompetitors($lang, $userId, $syncStart, $timeFilter, $boxId);
        $newKeywords = $results['keywords'] ?? [];
        $headlinesCount = $results['headlines_count'] ?? 0;

        $keywordCategory = $boxId ? "Target:{$boxId}" : 'Target';
        $cacheKey = $boxId ? "target_keywords_{$userId}_{$boxId}" : "target_keywords_{$userId}_{$lang}";

        $existingTexts = Keyword::where('user_id', $userId)
            ->where('category', $keywordCategory)
            ->where('lang', $lang)
            ->pluck('keyword')
            ->mapWithKeys(fn ($k) => [mb_strtolower(trim($k), 'UTF-8') => trim($k)])
            ->all();

        // Track which EXISTING keywords are re-detected during this run so we
        // can refresh their synced_at — and ONLY theirs. Previously we bulk-
        // bumped synced_at on every row in the category, which made stale
        // content look freshly synced and broke the "Last 60m" filter.
        $rediscoveredExisting = [];

        $addedCount = 0;
        $touchedCount = 0;

        foreach ($newKeywords as $kw) {
            $text = trim(is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : (string) $kw);
            if ($text === '') {
                continue;
            }

            $textLower = mb_strtolower($text, 'UTF-8');
            $matchedExistingOriginal = null;
            if (isset($existingTexts[$textLower])) {
                $matchedExistingOriginal = $existingTexts[$textLower];
            } else {
                foreach ($existingTexts as $existingLower => $existingOriginal) {
                    similar_text($textLower, $existingLower, $percent);
                    if ($percent >= 96) {
                        $matchedExistingOriginal = $existingOriginal;
                        break;
                    }
                }
            }

            if ($matchedExistingOriginal !== null) {
                $rediscoveredExisting[$matchedExistingOriginal] = true;
                continue;
            }

            $publishedAt = null;
            if (! empty($kw['published_at'])) {
                try {
                    $publishedAt = \Carbon\Carbon::parse($kw['published_at']);
                } catch (\Exception $e) {
                    Log::warning('[Keyword Sync] Date parse failed for: '.$kw['published_at']);
                }
            }

            // Sanity-clamp pubDate so the UI can never display the
            // temporally-impossible "synced 2h ago / published 21m ago"
            // combination. Some Arabic news RSS feeds (BTOLAT, etc.) ship
            // future-dated pubDate values for SEO ranking; we treat any
            // pubDate that's later than now as "published just now", and
            // discard pubDates older than a year as obviously stale.
            if ($publishedAt instanceof \Carbon\Carbon) {
                $nowUtc = now();
                if ($publishedAt->gt($nowUtc)) {
                    Log::info('[Keyword Sync] Clamped future pubDate to now', [
                        'source' => $kw['source'] ?? '(unknown)',
                        'declared' => $publishedAt->toIso8601String(),
                    ]);
                    $publishedAt = $nowUtc;
                } elseif ($publishedAt->lt($nowUtc->copy()->subYear())) {
                    // Stale dates (e.g. site default Unix epoch) are
                    // worse than no date at all — drop them.
                    $publishedAt = null;
                }
            }

            $keywordObj = Keyword::updateOrCreate(
                ['keyword' => $text, 'category' => $keywordCategory, 'lang' => $lang, 'user_id' => $userId],
                [
                    'source' => $kw['source'] ?? 'AI',
                    'headline_title' => ! empty($kw['headline_title']) ? mb_substr(trim($kw['headline_title']), 0, 500) : null,
                    'synced_at' => now(),
                    'published_at' => $publishedAt,
                ]
            );

            $existingTexts[$textLower] = $text;
            $touchedCount++;
            if ($keywordObj->wasRecentlyCreated) {
                $addedCount++;
            }
        }

        // Bump synced_at ONLY on existing keywords that the AI rediscovered
        // this run. Keywords that didn't come back in this pull keep their
        // previous synced_at and will naturally age out of the time filter
        // window — which is the whole point of the dropdown.
        if (!empty($rediscoveredExisting)) {
            Keyword::where('user_id', $userId)
                ->where('category', $keywordCategory)
                ->where('lang', $lang)
                ->whereIn('keyword', array_keys($rediscoveredExisting))
                ->update(['synced_at' => now()]);
        }

        Cache::forget($cacheKey);

        Log::info("[Competitor Keywords] [{$lang}] Saved new: {$addedCount}, rediscovered: ".count($rediscoveredExisting).", touched: {$touchedCount}, AI candidates: ".count($newKeywords));

        return [
            'saved' => $addedCount,
            'found' => count($newKeywords),
            'duplicates' => count($newKeywords) - $addedCount,
            'touched' => $touchedCount,
            'rediscovered' => count($rediscoveredExisting),
            'headlines' => $headlinesCount,
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

        $beforeLang = count($headlines);
        $headlines = $this->filterHeadlinesByLanguage($headlines, $lang);
        $removedLang = $beforeLang - count($headlines);
        
        Log::info("[Keyword Radar] Dedup: {$removedDupes} duplicate headlines removed. {" . count($rawHeadlines) . " raw → " . count($headlines) . " unique}".($removedLang > 0 ? " Lang filter ({$lang}): {$removedLang} removed." : ''));

        if (empty($headlines)) {
            return ['keywords' => [], 'headlines_count' => 0];
        }

        $headlines = $this->prioritizeHeadlinesForAi($headlines, KeywordPayload::maxHeadlinesForAi());

        // === STEP 2: AI Keyword Extraction — BATCHED for speed ===
        // Instead of calling AI once per headline (which causes 120+ API calls
        // and minutes of waiting), we batch 10 headlines per call. This reduces
        // a 120-headline sync from ~120 API calls → ~12, cutting sync time
        // from 6–10 minutes to under 2 minutes.
        $allKeywords = [];
        $aiExtractionStart = microtime(true);
        $batchSize = (int) \App\Models\Setting::get('ai-keyword-radar_batch_size', 10);
        $batchSize = max(1, min(25, $batchSize));
        $timeBudgetSeconds = (int) \App\Models\Setting::get('ai-keyword-radar_ai_time_budget', 180);

        $batches = array_chunk($headlines, $batchSize);
        Log::info('[Keyword Radar] AI extraction: ' . count($headlines) . ' headlines in ' . count($batches) . " batches of {$batchSize}");

        foreach ($batches as $batchIndex => $batch) {
            $aiElapsed = microtime(true) - $aiExtractionStart;
            if ($aiElapsed > $timeBudgetSeconds) {
                Log::warning('[Keyword Radar] AI extraction time budget reached (' . round($aiElapsed) . "s elapsed, batch {$batchIndex}/" . count($batches) . ', ' . count($allKeywords) . ' keywords). Returning partial results.');
                break;
            }

            $batchKeywords = $this->extractKeywordsWithAI($batch, $lang, $userId);
            if (! empty($batchKeywords)) {
                $allKeywords = array_merge($allKeywords, $batchKeywords);
            }
        }

        if (empty($allKeywords)) {
            // The headline-as-keyword fallback is the source of the
            // "card shows the full title instead of an extracted query"
            // problem. Disable it by default — better to surface an empty
            // result + a logged warning than to pollute the database with
            // raw titles that look indistinguishable from real keywords.
            // Admins who explicitly want a soft-fail safety net can opt in.
            $useFallback = filter_var(
                \App\Models\Setting::get('ai-keyword-radar_use_headline_fallback', true),
                FILTER_VALIDATE_BOOLEAN
            );

            if ($useFallback) {
                Log::warning('[Keyword Radar] AI returned no keywords; using headline fallback for '.count($headlines).' headlines.');
                $allKeywords = $this->headlinesToFallbackKeywords($headlines, $lang);
            } else {
                Log::warning('[Keyword Radar] AI returned no keywords for '.count($headlines).' headlines. Fallback is disabled, returning empty result.');
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
     * Keep the newest headlines only — prevents sync timeouts on large competitor lists.
     */
    protected function prioritizeHeadlinesForAi(array $headlines, int $limit): array
    {
        usort($headlines, function ($a, $b) {
            $ta = ! empty($a['pubDate']) ? strtotime($a['pubDate']) : 0;
            $tb = ! empty($b['pubDate']) ? strtotime($b['pubDate']) : 0;

            return $tb <=> $ta;
        });

        if (count($headlines) > $limit) {
            Log::info('[Keyword Radar] Capped headlines for AI: '.count($headlines)." → {$limit}");
            $headlines = array_slice($headlines, 0, $limit);
        }

        return $headlines;
    }

    /**
     * Soft-fail safety net used only when the AI chain is unavailable AND
     * the admin has opted in via Setting('ai-keyword-radar_use_headline_fallback').
     *
     * We aggressively shorten the title here so the resulting "keyword"
     * is at least a short phrase rather than a sentence — and we tag the
     * source as "Fallback" so these rows are trivially identifiable in the
     * database (and can be deleted without touching real AI-extracted rows).
     *
     * @return list<array{text: string, source: string, published_at: mixed, created_at: string}>
     */
    protected function headlinesToFallbackKeywords(array $headlines, string $lang): array
    {
        $keywords = [];
        $seen = [];

        foreach ($headlines as $h) {
            $title = trim($h['title'] ?? '');
            if ($title === '' || mb_strlen($title) < 8) {
                continue;
            }

            // Strip source attribution suffix and trailing parentheticals
            // (e.g. "... - BTOLAT.COM", "... (video)").
            $text = preg_replace('/\s+/u', ' ', $title);
            $text = preg_replace('/\s*[|–—\-]+\s*[^|–—]{1,40}$/u', '', $text);
            $text = preg_replace('/\s*\([^)]{1,40}\)\s*$/u', '', $text);
            $text = preg_replace('/\d{4}[-\/]\d{1,2}[-\/]\d{1,2}/u', '', $text);
            $text = trim($text, " \t\n\r\0\x0B-:|.…");

            // Keep at most the first 6 meaningful words so the fallback can
            // never store a full sentence as a "keyword".
            $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($words) && count($words) > 6) {
                $text = implode(' ', array_slice($words, 0, 6));
            }

            if (mb_strlen($text) < 8) {
                continue;
            }

            $key = mb_strtolower($text, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $keywords[] = [
                'text' => $text,
                'headline_title' => $title,
                // Distinct source label so admins can wipe just the
                // fallback-sourced rows without nuking AI-extracted ones.
                'source' => 'Fallback',
                'published_at' => $h['pubDate'] ?? null,
                'created_at' => now()->toDateTimeString(),
            ];
        }

        return $this->filterSimilarKeywords($keywords, 0.6);
    }

    /**
     * Strategy 1: Fetch headlines via News Sitemap
     */
    protected function fetchViaSitemap(string $url, string $userAgent, $userId = null, string $lang = 'ar'): array
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

                // Standard sitemap URL-slug fallback — English-only. Arabic
                // radar boxes were picking up slug titles like "premier league
                // asia trophy" from /sitemap.xml when no news sitemap exists.
                if (empty($items) && $lang === 'en') {
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
     * Strategy 3: Direct Google Search HTML Scraping, narrowed by time filter.
     *
     * tbs=qdr: tokens map to: n5 (5 min), n15 (15 min), n30 (30 min), h (hour),
     * h2..h24 (N hours), d (day). We pick the closest available bucket for
     * the active time filter so Google itself returns fresher SERP rows.
     */
    protected function fetchViaDirectGoogleSearch(string $domain, string $lang, string $userAgent, $userId = null, ?string $timeFilter = '60m'): array
    {
        $hl = ($lang === 'en') ? 'en' : 'ar';
        $qdr = $this->timeFilterToGoogleQdr($timeFilter);
        $googleSearchUrl = "https://www.google.com/search?q=site:{$domain}&tbs=qdr:{$qdr}&hl={$hl}&gl=EG&gbv=1";
        
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
                        
                        // Google's HTML SERP doesn't expose precise publish timestamps
                        // — mark the date as unknown so the strict time filter can
                        // exclude these when a narrow window is requested.
                        $items[] = [
                            'title' => $cleanTitle,
                            'pubDate' => null,
                            'pubDate_known' => false,
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
                    $items = $this->fetchViaSerpApi($domain, $lang, $serpApiKey, $timeFilter);
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
    public function testUrl(string $url, string $lang = 'ar', $userId = null, ?string $timeFilter = '60m'): array
    {
        $url = rtrim(trim($url), '/');
        $domain = parse_url($url, PHP_URL_HOST) ?: $url;
        $userAgent = $this->getRandomUserAgent();

        Log::info("[Competitor Test] Testing: {$url} ({$lang}) [Filter: " . $this->describeTimeFilter($timeFilter) . "]");

        $strategies = \App\Models\Setting::get('ai-keyword-radar_strategies', 'sitemap,google_html,google_news,rss,html_scrape');
        $strategyList = explode(',', $strategies);

        $fetched = [];
        $finalStrategy = 'None';

        foreach ($strategyList as $s) {
            $s = trim($s);
            if ($s === 'sitemap') {
                $fetched = $this->fetchViaSitemap($url, $userAgent, $userId, $lang);
                if (!empty($fetched)) { $finalStrategy = 'Sitemap Index'; break; }
            } elseif ($s === 'google_html') {
                $fetched = $this->fetchViaDirectGoogleSearch($domain, $lang, $userAgent, $userId, $timeFilter);
                if (!empty($fetched)) { $finalStrategy = 'Google Search HTML'; break; }
            } elseif ($s === 'google_news') {
                $fetched = $this->fetchViaGoogleNews($domain, $lang, $userAgent, $userId, $timeFilter);
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

        $freshnessLimit = $this->resolveFreshnessLimit($timeFilter);
        $filterLabel = $this->describeTimeFilter($timeFilter);

        Log::info("[Keyword Radar] Processing ALL " . count($competitorUrls) . " competitors. [Filter: {$filterLabel}]");

        @ini_set('memory_limit', '-1');
        @ini_set('max_execution_time', 300);
        $allHeadlines = [];
        $userAgent = $this->getRandomUserAgent();
        $needsFallback = [];

        $googleWhen = ($timeFilter === '24h' || $timeFilter === '1d') ? '24h' : (($timeFilter === 'all' || $timeFilter === 'unlimited') ? '7d' : '1h');
        $googleCountry = ($lang === 'en') ? 'US' : 'EG';
        $googleHl = ($lang === 'en') ? 'en' : 'ar';
        $googleHeaders = [
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => ($googleHl === 'en') ? 'en-US,en;q=0.9' : 'ar,en-US;q=0.9,en;q=0.8',
            'Referer' => 'https://www.google.com/',
        ];

        // === PHASE 1: LIGHTWEIGHT PARALLEL GOOGLE NEWS FETCH (IPv4 forced, 5s timeout) ===
        $chunks = array_chunk($competitorUrls, 10);
        foreach ($chunks as $chunkIndex => $chunkUrls) {
            if ((microtime(true) - $syncStart) > 40) {
                Log::warning("[Keyword Radar] Headline fetch budget reached (" . round(microtime(true) - $syncStart) . "s). " . count($allHeadlines) . " headlines collected.");
                break;
            }

            $responses = Http::pool(function ($pool) use ($chunkUrls, $googleHeaders, $googleWhen, $googleCountry, $googleHl) {
                $reqs = [];
                foreach ($chunkUrls as $url) {
                    $url = rtrim(trim($url), '/');
                    $host = parse_url($url, PHP_URL_HOST) ?: $url;
                    $domain = preg_replace('/^www\./i', '', $host);
                    $googleNewsUrl = \App\Support\GoogleNewsRss::searchUrl("site:{$domain} when:{$googleWhen}", $googleCountry, $googleHl);

                    $reqs[] = $pool->as($domain)
                        ->withHeaders($googleHeaders)
                        ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, CURLOPT_TIMEOUT => 5]])
                        ->timeout(5)->get($googleNewsUrl);
                }
                return $reqs;
            });

            foreach ($chunkUrls as $url) {
                $url = rtrim(trim($url), '/');
                $host = parse_url($url, PHP_URL_HOST) ?: $url;
                $domain = preg_replace('/^www\./i', '', $host);
                $siteHeadlines = [];

                $gnewsResp = $responses[$domain] ?? null;
                if ($gnewsResp instanceof \Illuminate\Http\Client\Response && $gnewsResp->successful()) {
                    $siteHeadlines = $this->parseGoogleNewsRss($gnewsResp->body());
                }

                if (empty($siteHeadlines)) {
                    $needsFallback[] = $url;
                    continue;
                }

                $applied = $this->applyTimeFilter($siteHeadlines, $freshnessLimit, $domain);
                $allHeadlines = array_merge($allHeadlines, $applied['kept']);

                Log::info("[Competitor Sync] {$domain}: {$applied['total']} total → {$applied['fresh']} fresh "
                    . "(skipped: {$applied['too_old']} old, {$applied['no_date']} no-date) [Filter: {$filterLabel}]");
            }

            unset($responses);
        }

        // === PHASE 2: FAST DIRECT RSS FALLBACK (ONLY if Phase 1 collected ZERO headlines) ===
        if (empty($allHeadlines) && !empty($needsFallback) && (microtime(true) - $syncStart) < 30) {
            Log::info("[Keyword Radar] Phase 2: Fast fallback for " . count($needsFallback) . " competitors.");
            foreach ($needsFallback as $url) {
                if ((microtime(true) - $syncStart) > 40) break;

                $url = rtrim(trim($url), '/');
                $host = parse_url($url, PHP_URL_HOST) ?: $url;
                $domain = preg_replace('/^www\./i', '', $host);
                $siteHeadlines = [];

                try {
                    $rssResp = Http::withHeaders(['User-Agent' => $userAgent])
                        ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, CURLOPT_TIMEOUT => 4]])
                        ->timeout(4)->get($url . '/feed');
                    if ($rssResp->successful() && (str_contains($rssResp->body(), '<rss') || str_contains($rssResp->body(), '<feed'))) {
                        $siteHeadlines = $this->parseSimpleRss($rssResp->body());
                    }
                } catch (\Throwable $e) {}

                if (empty($siteHeadlines)) {
                    try {
                        $rssResp = Http::withHeaders(['User-Agent' => $userAgent])
                            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, CURLOPT_TIMEOUT => 4]])
                            ->timeout(4)->get($url . '/rss');
                        if ($rssResp->successful() && (str_contains($rssResp->body(), '<rss') || str_contains($rssResp->body(), '<feed'))) {
                            $siteHeadlines = $this->parseSimpleRss($rssResp->body());
                        }
                    } catch (\Throwable $e) {}
                }

                if (!empty($siteHeadlines)) {
                    $applied = $this->applyTimeFilter($siteHeadlines, $freshnessLimit, $domain);
                    $allHeadlines = array_merge($allHeadlines, $applied['kept']);
                    Log::info("[Fallback Sync] {$domain}: {$applied['total']} total → {$applied['fresh']} fresh [Filter: {$filterLabel}]");
                }
            }
        }

        Log::info("[Keyword Radar] Total headlines collected: " . count($allHeadlines) . " in " . round(microtime(true) - $syncStart) . "s [Filter: {$filterLabel}]");

        $beforeLang = count($allHeadlines);
        $allHeadlines = $this->filterHeadlinesByLanguage($allHeadlines, $lang);
        if ($beforeLang !== count($allHeadlines)) {
            Log::info('[Keyword Radar] Language filter ('.$lang.'): '.($beforeLang - count($allHeadlines)).' headlines removed before AI.');
        }

        shuffle($allHeadlines);
        return $allHeadlines;
    }

    /**
     * Whether a scraped headline title belongs in the given radar language box.
     */
    public static function headlineMatchesLanguage(string $title, string $lang): bool
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) < 5) {
            return false;
        }

        $arabicLetters = (int) preg_match_all('/\p{Arabic}/u', $title);
        $latinLetters = (int) preg_match_all('/\p{Latin}/u', $title);
        $totalLetters = $arabicLetters + $latinLetters;

        if ($totalLetters === 0) {
            return false;
        }

        if ($arabicLetters === 0 && preg_match('/^[a-z0-9][a-z0-9\s\-\'&\.]+$/u', $title)) {
            return $lang === 'en';
        }

        if ($lang === 'ar') {
            if ($arabicLetters < 3) {
                return false;
            }

            return $arabicLetters >= ($latinLetters * 0.5);
        }

        if ($latinLetters < 3) {
            return false;
        }

        return $latinLetters >= $arabicLetters;
    }

    /**
     * Drop headlines whose script does not match the active radar language.
     *
     * @param  list<array<string, mixed>>  $headlines
     * @return list<array<string, mixed>>
     */
    protected function filterHeadlinesByLanguage(array $headlines, string $lang): array
    {
        return array_values(array_filter($headlines, function ($h) use ($lang) {
            return self::headlineMatchesLanguage($h['title'] ?? '', $lang);
        }));
    }

    /**
     * Apply the active freshness window to a set of raw fetched headlines.
     *
     * STRICT semantics:
     *  - When a time filter is active, items WITHOUT a verifiable real
     *    publish date (e.g. HTML-scraped homepage links flagged as
     *    pubDate_known=false, or items whose date cannot be parsed)
     *    are excluded — they would otherwise leak older content into
     *    the "Last 60 minutes" / "Last 24 hours" windows.
     *  - Comparison happens in UTC after normalizing the publisher's
     *    timezone, so naive timestamps don't drift the filter.
     *  - Future-dated items (publisher TZ skew) are clamped to "now"
     *    rather than being rejected.
     *
     * @return array{kept: array<int, array{title: string, source: string, pubDate: ?string}>, total: int, fresh: int, no_date: int, too_old: int}
     */
    protected function applyTimeFilter(array $siteHeadlines, ?\Carbon\Carbon $freshnessLimit, string $domain): array
    {
        $seenLocalTitles = [];
        $kept = [];
        $total = count($siteHeadlines);
        $fresh = 0;
        $noDate = 0;
        $tooOld = 0;

        foreach ($siteHeadlines as $item) {
            $title = $item['title'] ?? '';
            $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
            $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title);
            $title = trim($title);
            if ($title === '' || mb_strlen($title) < 5 || isset($seenLocalTitles[$title])) continue;

            if ($freshnessLimit !== null) {
                $hasKnownDate = ! (isset($item['pubDate_known']) && $item['pubDate_known'] === false);
                $pubDate = $this->parsePublishDate($item['pubDate'] ?? null);

                if (! $hasKnownDate || $pubDate === null) {
                    $noDate++;
                    continue;
                }

                $nowUtc = now('UTC');
                if ($pubDate->gt($nowUtc)) {
                    // Publisher provided a future timestamp (TZ skew); clamp to now.
                    $pubDate = $nowUtc;
                }

                if ($pubDate->lt($freshnessLimit)) {
                    $tooOld++;
                    continue;
                }
            }

            $kept[] = [
                'title'   => $title,
                'source'  => $domain,
                'pubDate' => $item['pubDate'] ?? null,
            ];
            $seenLocalTitles[$title] = true;
            $fresh++;
        }

        return [
            'kept'    => $kept,
            'total'   => $total,
            'fresh'   => $fresh,
            'no_date' => $noDate,
            'too_old' => $tooOld,
        ];
    }

    /**
     * Map a time filter token ("15m", "60m", "6h", "24h", "all", "1d", etc.)
     * to a number of minutes. Returns null when no constraint should apply.
     */
    protected function timeFilterToMinutes(?string $timeFilter): ?int
    {
        if ($timeFilter === null) {
            return 60;
        }

        $token = strtolower(trim($timeFilter));
        if ($token === '' || $token === 'all' || $token === 'any' || $token === 'unlimited') {
            return null;
        }

        if (preg_match('/^(\d+)\s*(m|min|mins|minute|minutes)$/', $token, $m)) {
            return max(1, (int) $m[1]);
        }
        if (preg_match('/^(\d+)\s*(h|hr|hrs|hour|hours)$/', $token, $m)) {
            return max(1, (int) $m[1]) * 60;
        }
        if (preg_match('/^(\d+)\s*(d|day|days)$/', $token, $m)) {
            return max(1, (int) $m[1]) * 60 * 24;
        }
        if (ctype_digit($token)) {
            return max(1, (int) $token);
        }

        // Unknown token — fail safe to the strictest documented default.
        Log::warning("[Keyword Radar] Unknown time filter token: '{$timeFilter}' — defaulting to 60 minutes.");
        return 60;
    }

    /**
     * Resolve the freshness cutoff timestamp in UTC for a given time filter.
     * Returns null when the filter is "all" (no restriction).
     */
    protected function resolveFreshnessLimit(?string $timeFilter): ?\Carbon\Carbon
    {
        $minutes = $this->timeFilterToMinutes($timeFilter);
        if ($minutes === null) {
            return null;
        }
        return now('UTC')->subMinutes($minutes);
    }

    /**
     * Map a time filter token to the Google News "when:" operator value.
     */
    protected function timeFilterToGoogleWhen(?string $timeFilter): string
    {
        $minutes = $this->timeFilterToMinutes($timeFilter);
        if ($minutes === null) {
            return '7d';
        }
        if ($minutes >= 60 * 24) {
            $days = max(1, (int) round($minutes / (60 * 24)));
            return $days . 'd';
        }
        if ($minutes >= 60) {
            $hours = max(1, (int) round($minutes / 60));
            return $hours . 'h';
        }
        // Google News doesn't support a minutes operator — clamp to 1h
        // and rely on our own post-fetch filter to enforce the real window.
        return '1h';
    }

    /**
     * Robust parser for any publish-date string. Always normalizes to UTC
     * so comparisons against `now('UTC')` are consistent regardless of
     * publisher timezone or the app's APP_TIMEZONE.
     */
    protected function parsePublishDate($raw): ?\Carbon\Carbon
    {
        if ($raw === null || $raw === '' || is_array($raw)) {
            return null;
        }
        $raw = trim((string) $raw);
        if ($raw === '' || $raw === '0' || $raw === '0000-00-00' || $raw === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($raw)->setTimezone('UTC');
        } catch (\Throwable $e) {
            foreach ([
                \DateTime::RSS,
                \DateTime::ATOM,
                \DateTime::ISO8601,
                \DateTime::W3C,
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:sP',
                'D, d M Y H:i:s O',
            ] as $fmt) {
                $dt = \DateTime::createFromFormat($fmt, $raw);
                if ($dt instanceof \DateTime) {
                    return \Carbon\Carbon::instance($dt)->setTimezone('UTC');
                }
            }
            return null;
        }
    }

    /**
     * Human-readable label for a time filter token (used for logs / messages).
     */
    public function describeTimeFilter(?string $timeFilter): string
    {
        $minutes = $this->timeFilterToMinutes($timeFilter);
        if ($minutes === null) {
            return 'All Time';
        }
        if ($minutes < 60) {
            return "Last {$minutes} Minutes";
        }
        if ($minutes < 60 * 24) {
            $hours = (int) round($minutes / 60);
            return "Last {$hours} Hour" . ($hours === 1 ? '' : 's');
        }
        $days = (int) round($minutes / (60 * 24));
        return "Last {$days} Day" . ($days === 1 ? '' : 's');
    }

    /**
     * Helpers for fast parallel parsing
     */
    protected function parseSimpleSitemap($xmlBody): array {
        try {
            $xml = @simplexml_load_string($xmlBody);
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
            $xml = @simplexml_load_string($xmlBody);
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
    protected function fetchViaGoogleNews(string $domain, string $lang, string $userAgent, $userId = null, ?string $timeFilter = '60m', ?string $country = null): array
    {
        $country = CountryRegistry::normalizeCode($country) ?: ($lang === 'en' ? 'US' : CountryRegistry::defaultRegion());
        $hl = CountryRegistry::langFor($country);
        $when = $this->timeFilterToGoogleWhen($timeFilter);

        // Query Rotation: Try specific site search first, then domain keyword search
        $queries = [
            "site:{$domain}",
            $domain
        ];

        $headers = [
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => ($hl === 'en') ? 'en-US,en;q=0.9' : (($hl === 'pl') ? 'pl,en;q=0.9' : 'ar,en-US;q=0.9,en;q=0.8'),
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => 'https://www.google.com/',
            'Upgrade-Insecure-Requests' => '1',
        ];

        foreach ($queries as $q) {
            $googleNewsUrl = \App\Support\GoogleNewsRss::searchUrl($q." when:{$when}", $country, $hl);

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
                // HTML scraping has no per-article publish date — explicitly
                // flag pubDate as unknown so the strict freshness filter does
                // not treat these as "fresh just because we just fetched them".
                foreach ($items as &$item) {
                    $item['pubDate'] = null;
                    $item['pubDate_known'] = false;
                }
                unset($item);
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

        // Extract from <h1>, <h2>, <h3> tags. We have no real publish date
        // per item from a homepage HTML scrape — flag pubDate as unknown so
        // the strict freshness filter excludes these from narrow windows.
        if (preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/si', $html, $matches)) {
            foreach ($matches[1] as $rawTitle) {
                $title = trim(strip_tags($rawTitle));
                $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                $title = preg_replace('/\s+/', ' ', $title);
                if (!empty($title) && mb_strlen($title) >= 15 && mb_strlen($title) <= 200 && !isset($seen[$title])) {
                    $items[] = ['title' => $title, 'pubDate' => null, 'pubDate_known' => false];
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
                        $items[] = ['title' => $title, 'pubDate' => null, 'pubDate_known' => false];
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

    protected function sanitizeHeadlineTitle(string $title): string
    {
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        $title = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $title);
        $title = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}\p{S}]/u', '', $title);

        return trim($title);
    }

    protected function keywordExtractionJsonContract(int $headlineCount = 1): string
    {
        $example = $headlineCount === 1
            ? '{"keywords":[{"index":1,"keyword":"first query"},{"index":1,"keyword":"second query"}]}'
            : '{"keywords":[{"index":1,"keyword":"first query"},{"index":1,"keyword":"second query"},{"index":2,"keyword":"third query"}]}';

        return "\n\nOUTPUT FORMAT (CANONICAL — DO NOT VARY):"
            .' Return ONLY valid JSON. No markdown, no commentary, no code fences.'
            .' Must be a JSON object with a "keywords" array.'
            .' Each item: {"index":<headline number starting at 1>,"keyword":<short search query>}.'
            ." Example: {$example}";
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function requestKeywordExtraction(string $prompt, array $aiOptions, array $headlines, $userId, string $provider, ?string $model): array
    {
        $aiResult = $this->aiManager->generate('ai-keyword-radar', $prompt, $aiOptions);
        $response = trim($aiResult['text'] ?? '');
        $this->lastKeywordExtractionRawResponse = $response !== '' ? $response : ($aiResult['raw_response'] ?? null);

        if ($response === '') {
            Log::warning('[Keyword Radar] AI returned empty text.', [
                'provider' => $provider,
                'model' => $model,
                'provider_used' => $aiResult['provider_used'] ?? null,
            ]);
            \App\Models\ToolError::log('ai-keyword-radar', 'AI returned empty response body.', 'AI Keyword Extraction', $userId, [
                'provider' => $provider,
                'model' => $model,
                'raw_response' => $aiResult['raw_response'] ?? 'EMPTY_RESPONSE',
            ]);

            return [];
        }

        $parsedKeywords = $this->parseKeywordsResponse($response, $headlines, $userId);

        if (empty($parsedKeywords)) {
            $rawSnippet = mb_substr($response, 0, 1500);
            Log::warning('[Keyword Radar] AI response could not be parsed. Snippet: '.$rawSnippet);
            \App\Models\ToolError::log('ai-keyword-radar', 'AI response could not be parsed into keywords.', 'AI Keyword Extraction', $userId, [
                'provider' => $provider,
                'model' => $model,
                'headline_count' => count($headlines),
                'raw_response' => $response,
            ]);
        }

        return $parsedKeywords;
    }

    protected function extractKeywordsWithAI(array $headlines, string $lang = 'ar', $userId = null)
    {
        if (count($headlines) > 100) {
            $headlines = array_slice($headlines, 0, 100);
        }

        $sanitizedHeadlines = [];
        foreach ($headlines as $h) {
            $title = $this->sanitizeHeadlineTitle($h['title'] ?? '');
            if ($title === '') {
                continue;
            }
            $sanitizedHeadlines[] = array_merge($h, ['title' => $title]);
        }

        if (empty($sanitizedHeadlines)) {
            Log::warning('[Keyword Radar] No valid headlines left after sanitization for AI extraction.');

            return [];
        }

        $headlines = $sanitizedHeadlines;
        $titlesText = '';
        foreach ($headlines as $idx => $h) {
            $sourceName = $h['source'] ?? 'Site';
            $titlesText .= ($idx + 1).'. ['.$sourceName.'] '.$h['title']."\n";
        }

        $count = count($headlines);
        $langInstruction = ($lang === 'en') ? "English" : "Arabic";

        // Admin-configurable: how many distinct Target Search Queries the AI
        // should extract from EACH headline. Default to 3 — that's what the
        // shipped admin prompt asks for, and matches how a human would pivot
        // one news title into multiple high-intent search variants.
        $perHeadline = (int) \App\Models\Setting::get('ai-keyword-radar_keywords_per_headline', 3);
        if ($perHeadline < 1) {
            $perHeadline = 1;
        } elseif ($perHeadline > 5) {
            $perHeadline = 5;
        }
        $expectedKeywords = $count * $perHeadline;

        $jsonContract = $this->keywordExtractionJsonContract($count);

        $dbPrompt = \App\Models\Setting::get('ai-keyword-radar_prompt');
        if ($dbPrompt) {
            $prompt = str_replace(
                ['[Headlines]', '[headlines]', '[lang]', '[KeywordsPerHeadline]', '[keywords_per_headline]'],
                [$titlesText, $titlesText, $langInstruction, (string) $perHeadline, (string) $perHeadline],
                $dbPrompt
            );
        } elseif ($count === 1) {
            $headlineTitle = $headlines[0]['title'];
            $prompt = "You are an expert SEO specialist. Extract exactly {$perHeadline} DISTINCT short Google search queries (3–6 words each) from this {$langInstruction} news headline. Synthesize — do not copy the headline. Language: {$langInstruction}.

Headline:
{$headlineTitle}

Rules: different angle per query; no dates unless part of an entity name; no generic words like 'news' or 'today'.";
        } else {
            $prompt = "You are an expert SEO specialist. Your job is to mine the competitor headlines below for 'Target Search Queries' — the EXACT short, high-intent phrases real users would type into Google to learn about the SAME story.

Headlines:
{$titlesText}

STRICT RULES:
1. Extract {$perHeadline} DISTINCT Target Search Queries for EACH headline.
2. Each of the {$perHeadline} queries from the same headline MUST cover a DIFFERENT angle. Acceptable angles: the main entity + the event, the question this story raises (e.g. 'why ...', 'when ...'), the consequence ('after ...'), the supporting fact (score, price, date). NOT acceptable: swapping one word in the headline for a synonym. If two of your queries differ by only one word, you are FAILING this rule.
3. Each query MUST be 3–6 words. Longer than 6 words = forbidden. The original headline is NEVER an acceptable output — you must SYNTHESIZE, not paraphrase.
4. NO dates or years (like 2025) unless they are part of the entity name itself (e.g. 'Euro 2024').
5. Preserve the main named entities from the headline (people, teams, places, products). Generic SEO words like 'today', 'latest', 'breaking', 'news' should NOT be added.
6. Output language MUST be: {$langInstruction}.
7. You MUST process ALL {$count} headlines provided ({$expectedKeywords} keywords total).
8. NO markdown, NO commentary, NO code fences — output ONLY valid JSON (see format below).

GOOD example (headline: 'موقف معتمد جمال من العروض الخارجية بعد رفض الانتقال'):
  → {\"index\":1,\"keyword\":\"عروض معتمد جمال الخارجية\"}      (entity + event)
  → {\"index\":1,\"keyword\":\"لماذا رفض معتمد جمال الانتقال\"}  (the question)
  → {\"index\":1,\"keyword\":\"مستقبل معتمد جمال\"}              (the consequence)

BAD example (NEVER do this):
  → {\"index\":1,\"keyword\":\"نتائج كأس آسيا للدوري الإنجليزي الممتاز\"}
  → {\"index\":1,\"keyword\":\"فرق كأس آسيا للدوري الإنجليزي الممتاز\"}
  ↑ These two only differ by 'نتائج' vs 'فرق'. That violates rule 2.";
        }

        // Canonical JSON contract appended LAST so it is the final
        // example the model sees, regardless of what the admin prompt or
        // built-in default looks like.
        $prompt .= $jsonContract;

        try {
            // === TOOL AI SETTINGS RESOLUTION ===
            // Priority: User settings → Tool settings (DB) → Hardcoded defaults
            $userSettings = \App\Models\User::find($userId)?->settings ?? [];

            // 1. Resolve Provider (tool-specific → user-specific → default).
            //    This is only a HINT to AIManager — it brings the preferred
            //    provider to the front of the global tail so the chain still
            //    falls back to the rest of the registered providers.
            $provider = \App\Models\Setting::get('ai-keyword-radar_provider')
                        ?? $userSettings['keywords_ai_provider']
                        ?? 'openrouter';
            if ($provider === 'gemini') $provider = 'google';

            // 2. Resolve Model. AIManager will normalize a cross-provider
            //    name (e.g. "openai/gpt-4o-mini" used with provider=openai)
            //    and OpenRouter rewrites retired aliases internally.
            $model = \App\Models\Setting::get('ai-keyword-radar_model')
                     ?? $userSettings['keywords_ai_model']
                     ?? null;

            // 3. API key is intentionally NOT passed here. AIManager owns the
            //    resolution order: per-tool chain entry → per-tool api_key
            //    setting → env. Forcing the per-tool key as a per-call
            //    override turned it into a GLOBAL key for every link in the
            //    chain — meaning a stale admin-supplied key would be retried
            //    against the OpenAI / Google / Anthropic providers too (and
            //    block the env-based fallback that actually works). The
            //    chain in `ai-keyword-radar_ai_chain` already carries its
            //    own per-entry key, so the admin preference is still
            //    respected on the FIRST attempt.

            Log::info("[Keyword Radar AI] Extracting keywords for headline using {$provider}/".($model ?: 'default').': '.mb_substr($headlines[0]['title'] ?? '', 0, 80));

            // Each output object is roughly 35–45 tokens (index + keyword + JSON
            // structure). With $perHeadline keywords per headline we need
            // headlines * perHeadline * ~55 tokens, plus 200 tokens of slack
            // for the JSON envelope. Cap at 8K so we never get billed for an
            // accidental runaway response on a verbose model.
            $estimatedOutTokens = ($count * $perHeadline * 55) + 200;
            $maxOut = min(8000, max(1500, $estimatedOutTokens));

            $aiOptions = [
                'provider'    => $provider,
                'temperature' => 0.2,
                'json_mode'   => true,
                'max_tokens'  => $maxOut,
            ];
            if (!empty($model)) {
                $aiOptions['model'] = $model;
            }

            $parsedKeywords = $this->requestKeywordExtraction($prompt, $aiOptions, $headlines, $userId, $provider, $model);

            if (empty($parsedKeywords) && $count === 1) {
                $retryPrompt = "Extract {$perHeadline} {$langInstruction} Google search keywords from this headline. "
                    . $this->keywordExtractionJsonContract(1)."\n\nHeadline:\n".$headlines[0]['title'];
                Log::info('[Keyword Radar] Retrying single-headline extraction with simplified prompt.');
                $parsedKeywords = $this->requestKeywordExtraction($retryPrompt, $aiOptions, $headlines, $userId, $provider, $model);
            }

            if (empty($parsedKeywords)) {
                \App\Models\ToolError::log('ai-keyword-radar', 'AI returned 0 valid keywords from '.count($headlines).' headlines. Check raw response.', 'AI Keyword Extraction', $userId, [
                    'provider' => $provider,
                    'model' => $model,
                    'headline_count' => count($headlines),
                    'headline_sample' => mb_substr($headlines[0]['title'] ?? '', 0, 120),
                    'raw_response' => $this->lastKeywordExtractionRawResponse,
                ]);

                return [];
            }

            $keywords = $this->filterSimilarKeywords($parsedKeywords, 0.6, $userId);

            if (empty($keywords)) {
                Log::warning('[Keyword Radar] Quality filter removed all '.count($parsedKeywords).' parsed keywords; keeping raw AI output.');
                $keywords = $parsedKeywords;
            }

            Log::info('[Keyword Radar] AI generated '.count($parsedKeywords).' keywords. Filter kept '.count($keywords));

            return $keywords;

        } catch (\Exception $e) {
            Log::error("[Competitor Keywords] AI Failed: " . $e->getMessage());
            \App\Models\ToolError::log('ai-keyword-radar', $e->getMessage(), 'AI Keyword Extraction', $userId, [
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

        $cleanResponse = preg_replace('/.*?<\/think>/s', '', (string) $response);
        $cleanResponse = preg_replace('/<think>.*?<\/redacted_thinking>/s', '', $cleanResponse);

        if (preg_match('/```(?:json)?\s*(.*?)```/s', $cleanResponse, $matches)) {
            $cleanResponse = $matches[1];
        }

        $decoded = $this->decodeKeywordJsonPayload($cleanResponse);

        if (! is_array($decoded)) {
            return [];
        }

        foreach ($decoded as $item) {
            $parsed = $this->parseKeywordJsonItem($item, $headlines);
            if ($parsed === null) {
                continue;
            }

            $uniqueResults[$parsed['text']] = $parsed;
        }

        return array_values($uniqueResults);
    }

    /**
     * @return list<mixed>|null
     */
    protected function decodeKeywordJsonPayload(string $cleanResponse): ?array
    {
        $cleanResponse = trim($cleanResponse);
        if ($cleanResponse === '') {
            return null;
        }

        $decoded = null;
        if (preg_match('/\[\s*(\{.*\}|\s*".*")\s*\]/s', $cleanResponse, $matches)) {
            $decoded = json_decode($matches[0], true);
        } elseif (preg_match('/\{.*\}/s', $cleanResponse, $matches)) {
            $decoded = json_decode($matches[0], true);
        } else {
            $decoded = json_decode($cleanResponse, true);
        }

        if (! $decoded && preg_match_all('/\{[^{}]*"(?:keyword|query|text|search_query)"\s*:[^{}]*\}/su', $cleanResponse, $objMatches)) {
            $decoded = [];
            foreach ($objMatches[0] as $strObj) {
                if (substr(trim($strObj), -1) !== '}') {
                    $strObj .= '}';
                }
                $parsedObj = json_decode($strObj, true);
                if ($parsedObj) {
                    $decoded[] = $parsedObj;
                }
            }
        }

        if (! is_array($decoded)) {
            return null;
        }

        if (! isset($decoded[0]) && count($decoded) > 0) {
            foreach (['keywords', 'results', 'data', 'suggestions', 'list', 'items'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    $decoded = $decoded[$key];
                    break;
                }
            }
        }

        if (! isset($decoded[0]) && is_array($decoded) && count($decoded) > 0) {
            $hasKeywordField = isset($decoded['keyword']) || isset($decoded['query'])
                || isset($decoded['search_query']) || isset($decoded['text']);
            if ($hasKeywordField) {
                $decoded = [$decoded];
            }
        }

        if (isset($decoded[0]) && is_string($decoded[0])) {
            $decoded = array_map(fn ($text) => ['keyword' => $text, 'index' => 1], $decoded);
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @return array{text: string, source: string, headline_title: ?string, published_at: mixed, created_at: string}|null
     */
    protected function parseKeywordJsonItem(mixed $item, array $headlines): ?array
    {
        $text = '';
        $idx = -1;

        if (is_array($item)) {
            $text = (string) ($item['keyword'] ?? $item['query'] ?? $item['search_query'] ?? $item['text'] ?? $item['term'] ?? $item['keyphrase'] ?? '');
            if (isset($item['index'])) {
                $idx = (int) $item['index'];
                if ($idx > 0) {
                    $idx -= 1;
                }
            }
        } else {
            $text = (string) $item;
        }

        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($idx < 0 || ! isset($headlines[$idx])) {
            foreach ($headlines as $hIdx => $h) {
                $cleanKeyword = $this->normalizeForComparison($text);
                $cleanHeadline = $this->normalizeForComparison($h['title'] ?? '');
                if ($cleanHeadline !== '' && (str_contains($cleanHeadline, $cleanKeyword) || str_contains($cleanKeyword, $cleanHeadline))) {
                    $idx = $hIdx;
                    break;
                }
            }
        }

        $source = 'AI';
        $pubDate = null;
        $headlineTitle = null;

        if ($idx >= 0 && isset($headlines[$idx])) {
            $source = $headlines[$idx]['source'] ?? 'AI';
            $pubDate = $headlines[$idx]['pubDate'] ?? null;
            $headlineTitle = trim($headlines[$idx]['title'] ?? '');
        } elseif (count($headlines) === 1) {
            $source = $headlines[0]['source'] ?? 'AI';
            $pubDate = $headlines[0]['pubDate'] ?? null;
            $headlineTitle = trim($headlines[0]['title'] ?? '');
        }

        return [
            'text' => $text,
            'source' => $source,
            'headline_title' => $headlineTitle ?: null,
            'published_at' => $pubDate,
            'created_at' => now()->toDateTimeString(),
        ];
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
        $user = \App\Models\User::find($userId);
        if (!$user) {
            // If user is null (system sync), use global only
            $globalCompetitorsText = \App\Models\Setting::get('ai-keyword-radar_competitors', '');
            return array_values(array_filter(array_map('trim', explode("\n", $globalCompetitorsText))));
        }
        
        $settings = $user->settings ?? [];

        // Custom Box: return only that box's competitors
        if ($boxId) {
            $customBoxes = $settings['keywords_custom_boxes'] ?? [];
            foreach ($customBoxes as $box) {
                if (($box['id'] ?? '') === $boxId) {
                    $text = $box['competitors'] ?? '';
                    return array_values(array_filter(array_map('trim', explode("\n", $text))));
                }
            }
            Log::warning("[Keyword Radar] Custom box '{$boxId}' not found for user #{$userId}");
            return [];
        }
        
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
     * Strategy 2.5: Fetch headlines via SerpApi (High Reliability).
     *
     * Narrows the SERP to the active time window using both the Google
     * `when:` query operator and tbs=qdr (whichever Google honours that day).
     * Items still carry pubDate_known=false because SerpApi does NOT return
     * a precise per-result publish timestamp — our own strict filter excludes
     * them from sub-hour windows.
     */
    protected function fetchViaSerpApi(string $domain, string $lang, string $apiKey, ?string $timeFilter = '60m'): array
    {
        $hl = ($lang === 'en') ? 'en' : 'ar';
        $when = $this->timeFilterToGoogleWhen($timeFilter);
        $qdr = $this->timeFilterToGoogleQdr($timeFilter);
        $query = "site:{$domain} when:{$when}";

        try {
            $response = Http::timeout(15)->get('https://serpapi.com/search', [
                'engine'  => 'google',
                'q'       => $query,
                'hl'      => $hl,
                'gl'      => 'eg',
                'tbs'     => "qdr:{$qdr}",
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['organic_results'] ?? [];
                $items = [];

                foreach ($results as $res) {
                    $title = $res['title'] ?? '';
                    if (!empty($title) && mb_strlen($title) > 15) {
                        // Attempt to use the snippet date if SerpApi returned one
                        // (e.g. "2 hours ago"); fall back to "unknown" otherwise.
                        $rawDate = $res['date'] ?? ($res['snippet_date'] ?? null);
                        $parsed = $this->parsePublishDate($rawDate);

                        $items[] = [
                            'title'         => $title,
                            'pubDate'       => $parsed ? $parsed->toIso8601String() : null,
                            'pubDate_known' => $parsed !== null,
                        ];
                    }
                }

                Log::info("[Strategy:SerpApi] Found " . count($items) . " headlines for {$domain} [when:{$when}]");
                return $items;
            }

            Log::warning("[Strategy:SerpApi] Failed for {$domain}: " . ($response->json()['error'] ?? 'Unknown Error'));
        } catch (\Exception $e) {
            Log::error("[Strategy:SerpApi] Connection error: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Map a time filter token to a Google `tbs=qdr:` value.
     * Valid Google buckets: n5,n15,n30 (N minutes), h (last hour),
     * h2..h24 (N hours), d (day), w (week).
     */
    protected function timeFilterToGoogleQdr(?string $timeFilter): string
    {
        $minutes = $this->timeFilterToMinutes($timeFilter);
        if ($minutes === null) {
            return 'w';
        }
        if ($minutes <= 5)  return 'n5';
        if ($minutes <= 15) return 'n15';
        if ($minutes <= 30) return 'n30';
        if ($minutes <= 60) return 'h';
        if ($minutes < 60 * 24) {
            $hours = max(2, (int) round($minutes / 60));
            return 'h' . $hours;
        }
        return 'd';
    }
}
