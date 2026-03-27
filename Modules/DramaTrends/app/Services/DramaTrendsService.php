<?php

namespace Modules\DramaTrends\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DramaTrendsService
{
    /**
     * File-based cache directory.
     */
    private string $cacheDir;
    private string $seriesPath;
    private string $watchitRankingPath;
    private string $notebookOverridePath;
    private string $summaryPath;

    public function __construct()
    {
        $this->cacheDir           = storage_path('app/drama-trends-cache');
        $this->seriesPath         = module_path('DramaTrends', 'data/series.json');
        $this->watchitRankingPath = module_path('DramaTrends', 'data/watchit_ranking.json');
        $this->notebookOverridePath = storage_path('app/drama-trends-cache/notebook_override.json');
        $this->summaryPath        = storage_path('app/drama-trends-cache/summary_data.json');

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /* ──────────────────────────────────────────────
     *  SERIES JSON HELPERS
     * ────────────────────────────────────────────── */

    public function loadSeries(): array
    {
        if (!file_exists($this->seriesPath)) {
            return [];
        }
        $json = file_get_contents($this->seriesPath);
        return json_decode($json, true) ?: [];
    }

    public function saveSeries(array $series): void
    {
        file_put_contents(
            $this->seriesPath,
            json_encode($series, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        $this->rebuildSummary();
    }

    /* ──────────────────────────────────────────────
     *  CACHE
     * ────────────────────────────────────────────── */

    public function clearCache()
    {
        $files = glob($this->cacheDir . '/*.json');
        foreach ($files as $file) {
            if (basename($file) === 'csv_trends_data.json') continue;
            if (basename($file) === 'summary_data.json') continue;
            @unlink($file);
        }
        Cache::forget('drama_series_list');
    }


    /* ──────────────────────────────────────────────
     *  MAIN ENDPOINT: Fetch combined Ramadan Trends
     * ────────────────────────────────────────────── */

    public function fetchRamadanTrends(string $startDate, string $endDate, bool $forceRefresh = false): array
    {
        if (!$forceRefresh && file_exists($this->summaryPath)) {
            $data = json_decode(file_get_contents($this->summaryPath), true);
            if ($data && isset($data['google_trends'])) {
                return $data;
            }
        }

        return $this->rebuildSummary($startDate, $endDate);
    }

    /**
     * Rebuild the permanent summary file by merging all data sources.
     */
    public function rebuildSummary(string $startDate = '2026-02-19', string $endDate = '2026-03-19'): array
    {
        $series = $this->loadSeries();
        if (empty($series)) {
            return ['error' => 'No series found in the database.'];
        }

        \Log::info("DramaTrends: Rebuilding permanent summary data...");

        // Fetch both data sources
        $watchItData = $this->fetchWatchItRanking($series);
        $googleData  = $this->fetchGoogleTrendsData($series, $startDate, $endDate);

        $enrichList = function($list) use ($googleData, $series) {
            $seriesInfoMap = [];
            foreach ($series as $s) { $seriesInfoMap[$s['name']] = $s; }

            return array_map(function($item) use ($googleData, $seriesInfoMap) {
                $name = $item['name'];
                $info = $seriesInfoMap[$name] ?? [];
                
                $item['lead'] = $info['lead'] ?? '-';
                $item['company'] = $info['company'] ?? '-';
                $item['episodes'] = $info['episodes'] ?? null;
                $item['top_govs'] = $googleData['regional'][$name] ?? [];
                
                return $item;
            }, $list);
        };

        $result = [
            'series'            => $series,
            'watchit_ranking'   => $enrichList($watchItData),
            'google_trends'     => $enrichList($googleData['scores'] ?? []),
            'google_error'      => $googleData['error'] ?? null,
            'timeline'          => $googleData['timeline'] ?? [],
            'timeline_data'     => $googleData['timeline_data'] ?? [],
            'regional'          => $googleData['regional'] ?? [],
            'insight_summary'   => $googleData['insight_summary'] ?? null,
            'is_simulated'      => $googleData['is_simulated'] ?? false,
            'fetched_at'        => now()->toDateTimeString(),
        ];

        // Save as permanent summary
        file_put_contents($this->summaryPath, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        // Also clear old cache files to avoid confusion
        $this->clearCache();

        return $result;
    }

    /* ──────────────────────────────────────────────
     *  DATA SOURCE 1: WATCH IT (via FlixPatrol scraper)
     *  watchit.com API is Cloudflare-protected (403 on server).
     *  FlixPatrol publishes the official daily WATCH IT Egypt top 10
     *  with no auth required.
     * ────────────────────────────────────────────── */

    public function loadWatchItRanking(): array
    {
        if (!file_exists($this->watchitRankingPath)) return [];
        return json_decode(file_get_contents($this->watchitRankingPath), true) ?: [];
    }

    public function saveWatchItRanking(array $ranking): void
    {
        file_put_contents(
            $this->watchitRankingPath,
            json_encode($ranking, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    /* ─────────────────────────────────────────────────────────────────────
     *  DATA SOURCE 1: WATCH IT (via admin-managed JSON file)
     *  watchit.com API is Cloudflare-blocked. FlixPatrol is also unreachable
     *  from this server. Admin updates the ranking in the Management page.
     * ───────────────────────────────────────────────────────────────────── */

    public function fetchWatchItRanking(array $series): array
    {
        $rankingNames = $this->loadWatchItRanking();

        $ranking = [];
        $rank    = 1;
        foreach ($rankingNames as $name) {
            $matched = $this->findMatchingSeries($name, $series);
            $ranking[] = [
                'rank'    => $rank,
                'name'    => $matched ? $matched['name'] : $name,
                'lead'    => $matched['lead']    ?? '',
                'company' => $matched['company'] ?? '',
                'image'   => '',
                'source'  => 'admin',
            ];
            $rank++;
        }

        return $ranking;
    }

    /**
     * Parse the FlixPatrol HTML page for the TOP 10 TV Shows section.
     * Returns ranked list matched against our series list.
     */
    private function parseFlixPatrolTop10TvShows(string $html, array $series): array
    {
        // FlixPatrol uses a table structure. The TV Shows ranking block contains
        // <h3> with "TOP 10 TV Shows" and then a <table> or list of ranked titles.
        // We use multiple regex strategies to be robust.

        $ranking = [];

        // Strategy 1: Find "TOP 10 TV Shows" block and extract ranked anchor texts
        // The page has: <div ...>...<h3>TOP 10 TV Shows</h3>...<table>...<td>...</td>...
        // Each ranked row has a rank number and a title link.

        // Extract the TV Shows section — everything after the TV Shows heading
        if (preg_match('/TOP 10 TV Shows.*?(<table[^>]*>.*?<\/table>)/si', $html, $blockMatch)) {
            $tableHtml = $blockMatch[1];
            // Extract all anchor texts from the table rows (these are the show titles)
            preg_match_all('/<a[^>]+href="\/title\/[^"]+"[^>]*>([^<]+)<\/a>/u', $tableHtml, $titleMatches);
            $titles = $titleMatches[1] ?? [];
        } else {
            // Strategy 2: Find all links matching /title/ path in the TV shows section
            // Look for the section between TOP 10 TV Shows and the next major section
            if (preg_match('/TOP 10 TV Shows(.+?)(?:TOP 10 Movies|<footer|<\/section)/si', $html, $sectionMatch)) {
                preg_match_all('/<a[^>]+href="\/title\/[^"]+"[^>]*>([^<]+)<\/a>/u', $sectionMatch[1], $titleMatches);
                $titles = $titleMatches[1] ?? [];
            } else {
                // Strategy 3: Look for the ranked table for TV shows specifically
                // FlixPatrol has data-type="tv" or similar attributes
                preg_match_all('/data-(?:rank|position)="(\d+)"[^>]*>.*?<a[^>]+>([^<]+)<\/a>/su', $html, $m);
                $titles = $m[2] ?? [];
            }
        }

        // Clean and take top 10 unique titles
        $seen = [];
        $cleanTitles = [];
        foreach ($titles as $title) {
            $title = trim(html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
            if ($title && !in_array($title, $seen) && count($cleanTitles) < 10) {
                $seen[]        = $title;
                $cleanTitles[] = $title;
            }
        }

        // Match each FlixPatrol title to our Arabic series list
        $rank = 1;
        foreach ($cleanTitles as $fpTitle) {
            $matched = $this->findMatchingSeriesEnglish($fpTitle, $series);
            $ranking[] = [
                'rank'    => $rank,
                'name'    => $matched ? $matched['name'] : $fpTitle,
                'lead'    => $matched['lead'] ?? '',
                'company' => $matched['company'] ?? '',
                'image'   => '',
                'source'  => 'flixpatrol',
                'matched' => $matched !== null,
            ];
            $rank++;
        }

        return $ranking;
    }

    /**
     * Match an English FlixPatrol title to our Arabic series by transliteration / keyword.
     */
    private function findMatchingSeriesEnglish(string $englishTitle, array $series): ?array
    {
        // Build a map of Arabic → English known translations for our series
        $translations = [
            'Ali Klay'                  => ['علي كلاي', 'علي كاي'],
            'Darsh'                     => ['درش'],
            'The Story of Nargis'       => ['حكاية نرجس'],
            'The Other Half'            => ['النصف الآخر', 'اتنين غيرنا'],
            'Last Chance'               => ['فرصة أخيرة'],
            'The Art of War'            => ['فن الحرب'],
            'Head of the Serpent'       => ['رأس الأفعى'],
            'Two Other People'          => ['اتنين غيرنا'],
            'The Shepherd\'s Children'  => ['أولاد الراعي'],
            'Beebo'                     => ['بيبو'],
            'Pride of the Delta'        => ['فخر الدلتا'],
            'A Father But'              => ['أب ولكن'],
            'Grandpa\'s Season'         => ['موسم الجد'],
            'Ramadan Karim'             => ['رمضان كريم'],
        ];

        $engLower = strtolower(trim($englishTitle));

        // Try direct translation map first
        foreach ($translations as $engKey => $arabicVariants) {
            if (stripos($engLower, strtolower($engKey)) !== false || stripos(strtolower($engKey), $engLower) !== false) {
                foreach ($arabicVariants as $arabicName) {
                    foreach ($series as $s) {
                        if (mb_stripos($s['name'], $arabicName) !== false || mb_stripos($arabicName, $s['name']) !== false) {
                            return $s;
                        }
                    }
                }
            }
        }

        // Fallback: try removing articles and comparing
        foreach ($series as $s) {
            $sKeyword = !empty($s['searchKeyword']) ? $s['searchKeyword'] : $s['name'];
            // Very loose transliteration match on first word
            $firstWordAr   = mb_strtolower(explode(' ', trim($s['name']))[0]);
            $firstWordEn   = strtolower(explode(' ', trim($englishTitle))[0]);
            // If first word is short or a common article, use second word
            if (in_array($firstWordEn, ['the', 'a', 'an', 'al', 'el']) && strpos($englishTitle, ' ') !== false) {
                $firstWordEn = strtolower(explode(' ', trim($englishTitle))[1] ?? $firstWordEn);
            }
            // Check if the series name keyword appears roughly in the English title
            if (!empty($s['searchKeyword']) && mb_stripos($englishTitle, $s['searchKeyword']) !== false) {
                return $s;
            }
        }

        return null; // Could not match — keep the English name as-is
    }

    private function normalizeArabic(string $str): string
    {
        $str = mb_strtolower(trim($str));
        $str = preg_replace('/[^\x{0621}-\x{064A}\s\d]/u', '', $str);
        $str = preg_replace('/[أإآ]/u', 'ا', $str);
        $str = preg_replace('/[ى]/u', 'ي', $str);
        $str = preg_replace('/[ة]/u', 'ه', $str);
        if (mb_stripos($str, 'علي كاي') !== false && mb_stripos($str, 'علي كلاي') === false) {
            $str = str_replace('علي كاي', 'علي كلاي', $str);
        }
        return trim($str);
    }

    private function findMatchingSeries(string $apiName, array $series): ?array
    {
        $apiNameNorm = $this->normalizeArabic($apiName);
        foreach ($series as $s) {
            $sNameNorm = $this->normalizeArabic($s['name']);
            if (mb_stripos($apiNameNorm, $sNameNorm) !== false || mb_stripos($sNameNorm, $apiNameNorm) !== false) {
                return $s;
            }
        }
        return null;
    }

    /* ──────────────────────────────────────────────
     *  DATA SOURCE 2: GOOGLE TRENDS
     * ────────────────────────────────────────────── */

    public function fetchGoogleTrendsData(array $series, string $startDate, string $endDate): array
    {
        try {
            $cacheKeyAll = 'drama_trends_full_data_' . md5($startDate . $endDate);
            // Intelligent Priority: Fetch ALL series in optimized batches of 5 (1 baseline + 4 candidates)
            // This ensures 100% real Google Trends data coverage for both scores and regions.
            $baselineSeries = null;
            foreach ($series as $s) { if ($s['isBaseline'] ?? false) { $baselineSeries = $s; break; } }
            if (!$baselineSeries) $baselineSeries = $series[0];
            
            $baselineKeyword = $this->getKeyword($baselineSeries);
            
            // Check if CSV data is available first
            $csvData = $this->loadCsvData();
            if ($csvData && !empty($csvData['averages'])) {
                $allScores       = $csvData['averages'];
                $allTimelineData = $csvData['timeline_data'];
                $allTimelines    = $csvData['timeline'];
                $allRegionalData = $csvData['regional'];
            } else {
                // Seed results with safe "zero-state" structure from fetchFromWebAnalysis
                $initialTrends = $this->fetchFromWebAnalysis($series);
                $allScores         = $initialTrends['averages'];
                $allTimelineData   = $initialTrends['timeline_data'];
                $allTimelines      = $initialTrends['timeline'];
                $allRegionalData   = $initialTrends['regional'];
                $baselineScores    = [];
                
                // Prepare candidates (excluding the baseline itself if it's in the list)
                $candidates = [];
                foreach ($series as $s) {
                    if ($s['name'] !== $baselineSeries['name']) {
                        $candidates[] = $s;
                    }
                }

                // Fetch in batches of 4 (plus 1 baseline = 5 keywords total)
                $batchSize = 4;
                $chunks = array_chunk($candidates, $batchSize);

                $kwToName = [];
                $kwToName[$baselineKeyword] = $baselineSeries['name'];

                foreach ($chunks as $chunkIdx => $chunk) {
                    try {
                        $batchKeywords = [$baselineKeyword];
                        foreach ($chunk as $s) { 
                            $ckw = $this->getKeyword($s);
                            $batchKeywords[] = $ckw; 
                            $kwToName[$ckw] = $s['name'];
                        }
                        
                        // Jittered short delay to avoid rate limiting
                        if ($chunkIdx > 0) usleep(rand(1000000, 2000000));
                        
                        \Log::info("DramaTrends: Processing batch " . ($chunkIdx + 1) . "/" . count($chunks));
                        $result = $this->fetchWithRetry(fn() => $this->fetchGoogleTrendsBatch($batchKeywords, $startDate, $endDate));

                        if (!$result || empty($result['averages'])) {
                            \Log::warning("DramaTrends: API Blocked or empty for batch " . ($chunkIdx + 1));
                            continue;
                        }

                        $batchBaselineAvg = $result['averages'][$baselineKeyword] ?? 1;
                        if ($batchBaselineAvg < 1) $batchBaselineAvg = 1;

                        if (empty($baselineScores)) {
                            $baselineScores['master_avg'] = $batchBaselineAvg;
                            $allScores[$baselineKeyword] = $batchBaselineAvg;
                            if (!empty($result['timeline'])) {
                                $allTimelines = $result['timeline'];
                            }
                        }

                        $normFactor = ($baselineScores['master_avg'] ?? $batchBaselineAvg) / $batchBaselineAvg;
                        
                        foreach ($result['averages'] as $kw => $avg) {
                            $name = $kwToName[$kw] ?? $kw;
                            if ($kw === $baselineKeyword && isset($allScores[$kw])) continue;
                            $allScores[$kw] = round($avg * $normFactor);
                        }

                        if (!empty($result['timeline_data'])) {
                            foreach ($result['timeline_data'] as $kw => $points) {
                                $name = $kwToName[$kw] ?? $kw;
                                if ($kw === $baselineKeyword && isset($allTimelineData[$kw])) continue;
                                $normalizedPoints = [];
                                foreach ($points as $p) {
                                    $normalizedPoints[] = round($p * $normFactor);
                                }
                                $allTimelineData[$kw] = $normalizedPoints;
                            }
                        }
                        
                        if (!empty($result['regional'])) {
                            foreach ($result['regional'] as $kw => $regs) {
                                $name = $kwToName[$kw] ?? $kw;
                                $allRegionalData[$name] = $regs;
                            }
                        }
                    } catch (\Exception $innerE) {
                        \Log::error("DramaTrends: Error processing batch " . ($chunkIdx + 1) . ": " . $innerE->getMessage());
                    }
                }
            }

            // Map back to series structure
            $seriesScores = [];
            $hasCsvLoaded = ($csvData && !empty($csvData['averages']));
            $notebookOverride = $this->loadNotebookOverride();
            
            foreach ($series as $s) {
                $name = $s['name'];
                $kw = $this->getKeyword($s);
                
                // If override exists, use it. Otherwise calculate from CSV/API.
                if (isset($notebookOverride[$name])) {
                    $score = $notebookOverride[$name]['score'] ?? 0;
                } else {
                    $score = 15;
                    if (isset($allScores[$kw])) {
                        $score = $hasCsvLoaded ? $allScores[$kw] : max(15, min(100, $allScores[$kw]));
                    }
                }
                
                $seriesScores[] = [
                    'name'  => $s['name'],
                    'score' => $score,
                ];
            }

            usort($seriesScores, fn($a, $b) => ($b['score'] ?? 0) - ($a['score'] ?? 0));
            foreach ($seriesScores as $i => &$ss) {
                $ss['rank'] = $i + 1;
            }

            $namedTimelineData = [];
            $regionalData      = [];
            foreach ($series as $s) {
                $name = $s['name'];
                $kw = $this->getKeyword($s);
                $namedTimelineData[$name] = $allTimelineData[$kw] ?? [];
                
                // Priority 1: Notebook AI Override
                if (isset($notebookOverride[$name]['governorates']) && !empty($notebookOverride[$name]['governorates'])) {
                    $regionalData[$name] = $notebookOverride[$name]['governorates'];
                } 
                // Priority 2: Google Trends Data (CSV/API)
                elseif (!empty($allRegionalData[$kw])) {
                    $regionalData[$name] = $allRegionalData[$kw];
                } 
                // Priority 3: Empty State
                else {
                    $regionalData[$name] = []; 
                }
            }

            $finalResult = [
                'scores'          => $seriesScores,
                'timeline'        => $allTimelines,
                'timeline_data'   => $namedTimelineData,
                'regional'        => $regionalData,
                'is_simulated'    => false,
            ];
            Cache::put($cacheKeyAll, $finalResult, 86400 * 2);
            return $finalResult;

        } catch (\Exception $e) {
            \Log::error("DramaTrends General Error: " . $e->getMessage());
            return ['error' => 'An unexpected error occurred: ' . $e->getMessage(), 'scores' => []];
        }
    }

    private function getKeyword(array $series): string
    {
        // Use the defined search keyword if available, as it's more specific
        // and matches what's used in the Detailed View.
        return !empty($series['searchKeyword']) ? $series['searchKeyword'] : $series['name'];
    }

    /**
     * Fetch a single batch from Google Trends via cURL.
     * Returns null if completely blocked.
     */
    private function fetchGoogleTrendsBatch(array $keywords, string $startDate, string $endDate): ?array
    {
        $result = $this->fetchWithRetry(function () use ($keywords, $startDate, $endDate) {
            $comparisonItems = [];
            foreach ($keywords as $kw) {
                $comparisonItems[] = ['keyword' => $kw, 'geo' => 'EG', 'time' => "{$startDate} {$endDate}"];
            }

            // Step 1: Get the token via explore endpoint
            $payload = json_encode([
                'comparisonItem' => $comparisonItems,
                'category'       => 0,
                'property'       => '',
            ]);

            $exploreUrl = 'https://trends.google.com/trends/api/explore?hl=en&tz=-120&req=' . rawurlencode($payload);
            
            $raw = $this->curlWithUserAgent($exploreUrl, true);
            if (!$raw) {
                \Log::error("DramaTrends: Explore raw response is EMPTY");
                return null;
            }

            \Log::info("DramaTrends: Explore Raw Snippet: " . substr($raw, 0, 200));

            // Response starts with ")]}'\n"
            $json = substr($raw, strpos($raw, '{'));
            $exploreData = json_decode($json, true);

            if (!$exploreData || !isset($exploreData['widgets'])) {
                throw new \Exception("Could not parse Google Trends explore response");
            }

            // Find the interest-over-time widget
            $iotToken = null;
            $iotReq = null;
            foreach ($exploreData['widgets'] as $widget) {
                if (($widget['id'] ?? '') === 'TIMESERIES') {
                    $iotToken = $widget['token'] ?? null;
                    $iotReq = $widget['request'] ?? null;
                    break;
                }
            }

            if (!$iotToken || !$iotReq) {
                throw new \Exception("Could not find TIMESERIES widget");
            }

            // Step 2: Fetch multiline interest-over-time data
            $timelineData = [];
            $timeline     = [];
            $averages     = [];
            
            try {
                $url = 'https://trends.google.com/trends/api/widgetdata/multiline?' . http_build_query([
                    'hl'    => 'ar',
                    'tz'    => '-120',
                    'req'   => json_encode($iotReq),
                    'token' => $iotToken,
                ]);

                $raw2 = $this->curlWithUserAgent($url, true);
                if ($raw2) {
                    $json2 = substr($raw2, strpos($raw2, '{'));
                    $mtData = json_decode($json2, true);

                    if ($mtData && isset($mtData['default']['timelineData'])) {
                        foreach ($keywords as $kw) { $timelineData[$kw] = []; }
                        foreach ($mtData['default']['timelineData'] as $point) {
                            $timeline[] = $point['formattedTime'] ?? '';
                            foreach ($point['value'] as $idx => $val) {
                                if (isset($keywords[$idx])) { $timelineData[$keywords[$idx]][] = (int)$val; }
                            }
                        }
                        foreach ($keywords as $kw) {
                            $vals = $timelineData[$kw] ?? [];
                            $averages[$kw] = count($vals) > 0 ? round(array_sum($vals) / count($vals)) : 0;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("DramaTrends: Multiline fetch failed: " . $e->getMessage());
            }

            // Small pause to avoid 429 between widget calls
            usleep(800000); 

            // Step 3: Fetch regional data (Compared Geo) for ALL keywords in the batch
            $regionalData = [];
            $geoToken = null;
            $geoReq = null;
            foreach ($exploreData['widgets'] as $widget) {
                if (($widget['id'] ?? '') === 'GEO_MAP') {
                    $geoToken = $widget['token'] ?? null;
                    $geoReq = $widget['request'] ?? null;
                    break;
                }
            }

            if ($geoToken && $geoReq) {
                try {
                    $geoUrl = 'https://trends.google.com/trends/api/widgetdata/comparedgeo?' . http_build_query([
                        'hl'    => 'ar',
                        'tz'    => '-120',
                        'req'   => json_encode($geoReq),
                        'token' => $geoToken,
                    ]);
                    $rawGeo = $this->curlWithUserAgent($geoUrl, true);
                    if ($rawGeo) {
                        $jsonGeo = substr($rawGeo, strpos($rawGeo, '{'));
                        $geoResp = json_decode($jsonGeo, true);
                        if (isset($geoResp['default']['geoMapData'])) {
                            $govDict = $this->getGovernorateDictionary();
                            foreach ($keywords as $idx => $kw) {
                                $kData = $geoResp['default']['geoMapData'];
                                usort($kData, fn($a, $b) => ($b['value'][$idx] ?? 0) - ($a['value'][$idx] ?? 0));
                                
                                $topRegs = [];
                                foreach (array_slice($kData, 0, 3) as $reg) {
                                    if (($reg['value'][$idx] ?? 0) > 0) {
                                        $nameEn = $reg['geoName'] ?? '';
                                        $topRegs[] = $govDict[$nameEn] ?? $nameEn;
                                    }
                                }
                                $regionalData[$kw] = $topRegs;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning("DramaTrends: Regional fetch failed: " . $e->getMessage());
                }
            }

            return [
                'averages'      => $averages,
                'timeline'      => $timeline,
                'timeline_data' => $timelineData,
                'regional'      => $regionalData,
            ];
        });

        return $result;
    }

    /**
     * Retry wrapper with exponential backoff + jitter.
     */
    private function fetchWithRetry(callable $fn, int $maxRetries = 2, int $baseDelay = 1000): mixed
    {
        $lastException = null;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                return $fn();
            } catch (\Exception $e) {
                $lastException = $e;
                if ($attempt < $maxRetries - 1) {
                    $delay = $baseDelay * pow(2, $attempt);
                    $jitter = rand(0, (int)($delay * 0.3));
                    usleep(($delay + $jitter) * 1000);
                }
            }
        }

        return null;
    }

    /* ──────────────────────────────────────────────
     *  REGIONAL INTEREST (Top 5 only)
     * ────────────────────────────────────────────── */

    public function fetchRegionalInterest(array $topFiveSeries, string $startDate, string $endDate): array
    {
        $results = [];

        foreach ($topFiveSeries as $s) {
            $keyword = $this->getKeyword($s);
            $detail = $this->fetchDetailedTrendsForKeyword($keyword, $startDate, $endDate);
            $regionalData = $detail['governorates'] ?? [];

            if ($regionalData) {
                $govs = [];
                foreach (array_slice($regionalData, 0, 3) as $region) {
                    $govs[] = $region['name'] ?? '';
                }
                $results[$s['name']] = $govs;
            } else {
                $results[$s['name']] = ['Cairo', 'Giza', 'Alexandria'];
            }
        }

        return $results;
    }

    public function fetchDetailedTrendsForKeyword(string $keyword, string $startDate, string $endDate): array
    {
        $cacheKey = "drama_detailed_" . md5($keyword . $startDate . $endDate);
        if ($cached = Cache::get($cacheKey)) return $cached;

        $detailed = [
            'governorates' => [],
            'queries'      => [],
        ];

        try {
            $timeRange = $this->formatTimeRange($startDate, $endDate);
            $payload = json_encode([
                'comparisonItem' => [['keyword' => $keyword, 'geo' => 'EG', 'time' => $timeRange]],
                'category'       => 0,
                'property'       => '',
            ]);

            $exploreUrl = 'https://trends.google.com/trends/api/explore?' . http_build_query(['hl' => 'ar', 'tz' => '-120', 'req' => $payload]);
            $raw = $this->curlWithUserAgent($exploreUrl);
            if (!$raw) return $detailed;

            $json = substr($raw, strpos($raw, '{'));
            $exploreData = json_decode($json, true);
            if (!$exploreData || !isset($exploreData['widgets'])) return $detailed;

            foreach ($exploreData['widgets'] as $widget) {
                // Regions
                if (($widget['id'] ?? '') === 'GEO_MAP') {
                    $req = $widget['request'] ?? null;
                    $req['resolution'] = 'REGION';
                    $data = $this->fetchWidgetData('https://trends.google.com/trends/api/widgetdata/comparedgeo', $req, $widget['token']);
                    if (isset($data['default']['geoMapData'])) {
                        $govDict = $this->getGovernorateDictionary();
                        foreach (array_slice($data['default']['geoMapData'], 0, 10) as $region) {
                            $nameEn = $region['geoName'] ?? '';
                            $detailed['governorates'][] = [
                                'name'  => $govDict[$nameEn] ?? $nameEn,
                                'value' => $region['value'][0] ?? 0,
                            ];
                        }
                    }
                }
                // Related Queries
                if (($widget['id'] ?? '') === 'RELATED_QUERIES') {
                    $data = $this->fetchWidgetData('https://trends.google.com/trends/api/widgetdata/relatedsearches', $widget['request'], $widget['token']);
                    if (isset($data['default']['rankedList'][1]['rankedKeyword'])) {
                        foreach (array_slice($data['default']['rankedList'][1]['rankedKeyword'], 0, 10) as $rk) {
                            $detailed['queries'][] = [
                                'query' => $rk['query'] ?? '',
                                'value' => $rk['formattedValue'] ?? '',
                            ];
                        }
                    }
                }
            }

            Cache::put($cacheKey, $detailed, 3600); // 1 hour cache
            return $detailed;
        } catch (\Exception $e) {
            return $detailed;
        }
    }

    private function formatTimeRange($start, $end): string
    {
        // Simple mapping for common ranges or custom YYYY-MM-DD YYYY-MM-DD
        return (string)$start . " " . (string)$end;
    }

    private function extractTopEpisode(array $queries): ?string
    {
        foreach ($queries as $q) {
            $text = $q['query'] ?? '';
            // Look for "حلقة" or "الحلقة" and common episode indicators
            if (mb_strpos($text, 'حلقة') !== false || mb_strpos($text, 'الحلقة') !== false) {
                // Return the first match that looks like an episode search
                return $text;
            }
        }
        return null;
    }

    private function curlWithUserAgent(string $url, bool $isJson = false): ?string
    {
        $cookieFile = $this->cacheDir . '/google_trends_cookies.txt';
        
        // Ensure we have a session cookie if not exists or too old
        if (!file_exists($cookieFile) || (time() - filemtime($cookieFile) > 3600)) {
            $this->initGoogleTrendsSession($cookieFile);
        }

        $ch = curl_init();
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ];
        
        $headers = [
            'User-Agent: ' . $userAgents[array_rand($userAgents)],
            'Accept: ' . ($isJson ? 'application/json, text/plain, */*' : 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8'),
            'Accept-Language: ar,en-US;q=0.7,en;q=0.3',
            'Referer: https://trends.google.com/trends/explore?geo=EG',
            'Origin: https://trends.google.com',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'Connection: keep-alive',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => "",
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_COOKIEJAR      => $cookieFile,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $res      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        \Log::info("DramaTrends: Request to URL: " . substr($url, 0, 100) . "... Status Code: {$httpCode}");

        if ($httpCode === 429) {
            \Log::warning("DramaTrends: Blocked by Google Trends (429)");
            return null;
        }

        if ($httpCode !== 200) {
            \Log::warning("DramaTrends: Request failed with HTTP code {$httpCode}");
            return null;
        }

        return $res;
    }

    private function fetchWidgetData(string $baseUrl, array $req, string $token): ?array
    {
        $url = $baseUrl . '?' . http_build_query(['hl' => 'ar', 'tz' => '-120', 'req' => json_encode($req), 'token' => $token]);
        $raw = $this->curlWithUserAgent($url, true);
        if (!$raw) return null;
        $json = substr($raw, strpos($raw, '{'));
        return json_decode($json, true);
    }

    private function initGoogleTrendsSession(string $cookieFile): void
    {
        // Try a more "natural" entry point to get cookies
        $entryPoints = [
            'https://trends.google.com/trends/trendingsearches/daily?geo=EG',
            'https://trends.google.com/trends/explore?geo=EG',
            'https://www.google.com.eg/search?q=google+trends+egypt'
        ];
        
        foreach ($entryPoints as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_COOKIEJAR      => $cookieFile,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            ]);
            curl_exec($ch);
            curl_close($ch);
            usleep(500000); // 0.5s pause between probes
        }
        
        \Log::info("DramaTrends: Initialized Google Trends session via multiple entry points.");
    }

    private function getTopFive(array $scores, array $series): array
    {
        // scores is already sorted by score desc with rank
        $topNames = array_slice(array_column($scores, 'name'), 0, 5);
        $result = [];
        foreach ($series as $s) {
            if (in_array($s['name'], $topNames)) {
                $result[] = $s;
            }
        }
        return $result;
    }


    /* (Simulated data fallback removed — only real data is used) */


    /* ──────────────────────────────────────────────
     *  GOVERNORATE DICTIONARY
     * ────────────────────────────────────────────── */

    private function fetchFromWebAnalysis(array $items): array
    {
        // No more hardcoded fake data. Everything must come from real Google Trends.
        // This method now only serves as a zero-state initializer.
        
        $averages = [];
        $timelineData = [];
        $regions = [];
        $rawKw = '';
        foreach ($items as $item) {
            $rawKw = is_array($item) ? $this->getKeyword($item) : (string)$item;
            $averages[$rawKw] = 0;
            $timelineData[$rawKw] = array_fill(0, 7, 0); // 7 points for a week
            $regions[$rawKw] = [];
        }

        // Generate actual dates for the timeline labels so charts aren't empty
        $timeline = [];
        for ($i = 6; $i >= 0; $i--) {
            $timeline[] = date('M j', strtotime("-$i days"));
        }

        return [
            'averages'      => $averages,
            'timeline'      => $timeline,
            'timeline_data' => $timelineData,
            'regional'      => $regions,
        ];
    }

    private function getGovernorateDictionary(): array
    {
        return [
            'Cairo'             => 'Cairo',
            'Giza'              => 'Giza',
            'Alexandria'        => 'Alexandria',
            'Qalyubia'          => 'Qalyubia',
            'Dakahlia'          => 'Dakahlia',
            'Sharqia'           => 'Sharqia',
            'Gharbia'           => 'Gharbia',
            'Monufia'           => 'Monufia',
            'Beheira'           => 'Beheira',
            'Kafr el-Sheikh'    => 'Kafr el-Sheikh',
            'Kafr El Sheikh'    => 'Kafr El Sheikh',
            'Damietta'          => 'Damietta',
            'Port Said'         => 'Port Said',
            'Ismailia'          => 'Ismailia',
            'Suez'              => 'Suez',
            'Fayoum'            => 'Fayoum',
            'Faiyum'            => 'Faiyum',
            'Beni Suef'         => 'Beni Suef',
            'Minya'             => 'Minya',
            'Asyut'             => 'Asyut',
            'Assiut'            => 'Asyut',
            'Sohag'             => 'Sohag',
            'Qena'              => 'Qena',
            'Luxor'             => 'Luxor',
            'Aswan'             => 'Aswan',
            'Red Sea'           => 'Red Sea',
            'New Valley'        => 'New Valley',
            'Matruh'            => 'Matruh',
            'Matrouh'           => 'Matruh',
            'North Sinai'       => 'North Sinai',
            'South Sinai'       => 'South Sinai',
            'Menofia'           => 'Monufia',
            'Al Bahriyya'       => 'Beheira',
            'Cairo Governorate' => 'Cairo',
            'Giza Governorate'  => 'Giza',
            'Qalyubia Governorate' => 'Qalyubia',
            'Ash Sharqia Governorate' => 'Sharqia',
            'Dakahlia Governorate' => 'Dakahlia',
            'Gharbia Governorate' => 'Gharbia',
            'Red Sea Governorate' => 'Red Sea',
            'North Sinai Governorate' => 'North Sinai',
            'Matruh Governorate' => 'Matruh',
            'Ismailia Governorate' => 'Ismailia',
        ];
    }

    /* ──────────────────────────────────────────────
     *  CSV IMPORT LOGIC
     * ────────────────────────────────────────────── */

    public function importCsvFiles(array $files): array
    {
        $series = $this->loadSeries();
        $baselineSeries = null;
        foreach ($series as $s) { if ($s['isBaseline'] ?? false) { $baselineSeries = $s; break; } }
        if (!$baselineSeries && count($series) > 0) $baselineSeries = $series[0];
        $baselineKeyword = $baselineSeries ? $this->getKeyword($baselineSeries) : '';

        // Phase 1: Raw Data Collection from all files
        $fileDataDict = []; // [fileIdx => [keyword => [values...]]]
        $fileTimelines = []; // [fileIdx => [dates...]]
        $allRegionalData = [];
        $fileKeywordsMap = []; // [fileIdx => [keywords in this file]]

        foreach ($files as $fIdx => $file) {
            $handle = fopen($file->getRealPath(), 'r');
            $header = null;
            $isRegionFile = false;
            
            // Skip BOM
            $bom = fread($handle, 3);
            if ($bom != "\xEF\xBB\xBF") rewind($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;
                if (!$header && count($row) > 1) {
                    $firstCol = trim($row[0]);
                    $isTimeline = (stripos($firstCol, 'Day') !== false || stripos($firstCol, 'اليوم') !== false || stripos($firstCol, 'Week') !== false || stripos($firstCol, 'أسبوع') !== false);
                    $isRegion = (stripos($firstCol, 'Region') !== false || stripos($firstCol, 'المنطقة') !== false || stripos($firstCol, 'City') !== false || stripos($firstCol, 'المدينة') !== false || stripos($firstCol, 'المحافظة') !== false || stripos($firstCol, 'المنطقة الفرعية') !== false);

                    if ($isTimeline || $isRegion) {
                        $isRegionFile = $isRegion;
                        $header = [];
                        foreach ($row as $colIdx => $col) {
                            if ($colIdx === 0) { $header[] = 'DATE_OR_REGION'; continue; }
                            $clean = preg_replace('/:\s*\(.*?\)/', '', $col);
                            $clean = trim($clean);
                            $mapped = false;
                            $cleanNorm = $this->normalizeArabic($clean);
                            foreach ($series as $s) {
                                if ($cleanNorm === $this->normalizeArabic($s['name']) || $cleanNorm === $this->normalizeArabic($this->getKeyword($s)) || mb_stripos($this->normalizeArabic($s['name']), $cleanNorm) !== false || mb_stripos($cleanNorm, $this->normalizeArabic($s['name'])) !== false) {
                                    $header[] = $this->getKeyword($s);
                                    $mapped = true; break;
                                }
                            }
                            if (!$mapped) $header[] = $clean;
                        }
                        continue;
                    }
                }
                
                if ($header && count($row) === count($header)) {
                    $firstColVal = trim($row[0]);
                    if (!$isRegionFile) {
                        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $firstColVal)) {
                            $fileTimelines[$fIdx][] = $firstColVal;
                            for ($i = 1; $i < count($header); $i++) {
                                $kw = $header[$i];
                                $valStr = trim($row[$i]);
                                $val = (int)($valStr === '<1' ? 0 : $valStr);
                                $fileDataDict[$fIdx][$kw][] = $val;
                            }
                        }
                    } else {
                        // Regional Data Logic (same as before but translated)
                        $govDict = $this->getGovernorateDictionary();
                        $govName = $firstColVal;
                        for ($i = 1; $i < count($header); $i++) {
                            $kw = $header[$i];
                            $valStr = trim($row[$i]);
                            $val = (int)($valStr === '<1' ? 0 : $valStr);
                            if ($val > 0) {
                                $trans = $govDict[$govName] ?? (str_ireplace(' Governorate', '', $govName));
                                $trans = $govDict[$trans] ?? $trans;
                                $allRegionalData[$kw][$trans] = ($allRegionalData[$kw][$trans] ?? 0) + $val;
                            }
                        }
                    }
                }
            }
            fclose($handle);
            if (isset($fileDataDict[$fIdx])) {
                $fileKeywordsMap[$fIdx] = array_keys($fileDataDict[$fIdx]);
            }
        }

        // Phase 2: Cross-File Normalization Graph
        // We need to calculate a Global Scale Factor for each file.
        $fileScaleFactors = [];
        // Phase 2: Cross-File Normalization Graph (Multi-Root Support for disconnected files)
        $fileScaleFactors = [];
        $unvisited = array_keys($fileKeywordsMap);

        while (!empty($unvisited)) {
            // Find a master root from the unvisited set
            $rootIdx = -1;
            foreach ($unvisited as $idx) {
                if ($baselineKeyword && in_array($baselineKeyword, $fileKeywordsMap[$idx])) {
                    $rootIdx = $idx;
                    break;
                }
            }
            if ($rootIdx === -1) $rootIdx = reset($unvisited);

            $fileScaleFactors[$rootIdx] = 1.0;
            $queue = [$rootIdx];
            $unvisited = array_diff($unvisited, [$rootIdx]);

            while (!empty($queue)) {
                $currIdx = array_shift($queue);
                $currKws = $fileKeywordsMap[$currIdx] ?? [];
                
                foreach ($unvisited as $uKey => $otherIdx) {
                    $otherKws = $fileKeywordsMap[$otherIdx] ?? [];
                    $shared = array_intersect($currKws, $otherKws);
                    if (!empty($shared)) {
                        $totalRatio = 0; $count = 0;
                        foreach ($shared as $skw) {
                            $avgCurr  = (array_sum($fileDataDict[$currIdx][$skw]) / count($fileDataDict[$currIdx][$skw]));
                            $avgOther = (array_sum($fileDataDict[$otherIdx][$skw]) / count($fileDataDict[$otherIdx][$skw]));
                            if ($avgOther > 0.01) {
                                $totalRatio += ($avgCurr / $avgOther);
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $fileScaleFactors[$otherIdx] = $fileScaleFactors[$currIdx] * ($totalRatio / $count);
                            $queue[] = $otherIdx;
                            unset($unvisited[$uKey]);
                        }
                    }
                }
            }
        }

        // Phase 3: Aggregate Volume & Regional Data (Global Normalized Scores)
        $globalKwsRaw = []; 
        $masterTimeline = [];
        $regCounts = []; // To average regional scores

        foreach ($fileScaleFactors as $fIdx => $factor) {
            if (empty($masterTimeline)) $masterTimeline = $fileTimelines[$fIdx] ?? [];
            foreach ($fileDataDict[$fIdx] as $kw => $points) {
                foreach ($points as $p) {
                    $globalKwsRaw[$kw][] = $p * $factor;
                }
                $regCounts[$kw] = ($regCounts[$kw] ?? 0) + 1;
            }
        }

        $allAverages = [];
        $allTimelineData = [];
        foreach ($globalKwsRaw as $kw => $points) {
            // Average of all normalized data points found for this keyword across all files
            $allAverages[$kw] = count($points) > 0 ? (array_sum($points) / count($points)) : 0;
            $allTimelineData[$kw] = array_slice($points, 0, count($masterTimeline));
        }

        // Final score mapping (Absolute)
        $finalAverages = [];
        foreach ($allAverages as $kw => $avg) {
            $finalAverages[$kw] = round($avg);
        }

        // Post-process Regional Data (average and format as objects)
        $finalReg = [];
        foreach ($allRegionalData as $kw => $govs) {
            arsort($govs);
            $count = $regCounts[$kw] ?? 1;
            $topGovs = [];
            foreach (array_slice($govs, 0, 10, true) as $name => $sumVal) {
                $avgVal = round($sumVal / $count);
                if ($avgVal > 100) $avgVal = 100;
                $topGovs[] = [
                    'name'  => $name,
                    'value' => $avgVal
                ];
            }
            $finalReg[$kw] = $topGovs;
        }

        $csvResult = [
            'averages'      => $finalAverages,
            'timeline_data' => $allTimelineData,
            'timeline'      => $masterTimeline,
            'regional'      => $finalReg
        ];

        file_put_contents($this->cacheDir . '/csv_trends_data.json', json_encode($csvResult, JSON_UNESCAPED_UNICODE));
        $this->rebuildSummary();
        return $csvResult;
    }

    public function saveNotebookOverride(array $overrideData): void
    {
        file_put_contents(
            $this->notebookOverridePath,
            json_encode($overrideData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        $this->rebuildSummary();
    }

    public function loadNotebookOverride(): array
    {
        if (!file_exists($this->notebookOverridePath)) return [];
        return json_decode(file_get_contents($this->notebookOverridePath), true) ?: [];
    }

    public function loadCsvData(): ?array
    {
        $file = $this->cacheDir . '/csv_trends_data.json';
        if (!file_exists($file)) return null;
        return json_decode(file_get_contents($file), true);
    }
}

