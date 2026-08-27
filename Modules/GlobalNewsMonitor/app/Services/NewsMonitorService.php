<?php

namespace Modules\GlobalNewsMonitor\Services;

use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NewsMonitorService
{
    /**
     * Fetch News from Google News RSS — Using Google's Official Encoded Topic IDs
     * This uses the EXACT SAME topic IDs that Google News uses internally.
     * URL pattern: https://news.google.com/rss/topics/{ENCODED_TOPIC_ID}?hl={lang}&gl={country}&ceid={country}:{lang}
     *
     * Strict relevance is enforced AFTER fetching: the source must belong to the
     * selected country (TLD / known publisher / international wire mentioning the
     * country), and the article must contain at least one keyword belonging to
     * the selected topic. This applies to the primary fetch and every fallback
     * path so filters are never bypassed.
     */
    /**
     * Fetch News from Google News RSS — High-Volume Parallel Multi-Stream Engine
     * Combines official section feeds with targeted real-time search streams and
     * strict country/category isolation.
     */
    public function fetchGoogleNews($country = 'EG', $topic = 'WORLD', $lang = 'ar', $timeWindow = '24h', $countryName = '')
    {
        $country = CountryRegistry::normalizeCode($country) ?: 'EG';
        $topic = strtoupper($topic);
        $lang = $lang ?: CountryRegistry::langFor($country);

        // ─── 1. Build Multi-Stream URL Batch ───
        $urlsToFetch = [];

        $topicIds = [
            'ar' => [
                'NATION'        => 'CAAqIQgKIhtDQkFTRGdvSUwyMHZNREpyTlRRU0FtRnlLQUFQAQ',
                'WORLD'         => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtRnlHZ0pGUnlnQVAB',
                'BUSINESS'      => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx6TVdZU0FtRnlHZ0pGUnlnQVAB',
                'TECHNOLOGY'    => 'CAAqKAgKIiJDQkFTRXdvSkwyMHZNR1ptZHpWbUVnSmhjaG9DUlVjb0FBUAE',
                'ENTERTAINMENT' => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNREpxYW5RU0FtRnlHZ0pGUnlnQVAB',
                'SPORTS'        => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRFp1ZEdvU0FtRnlHZ0pGUnlnQVAB',
                'SCIENCE'       => '',
                'HEALTH'        => 'CAAqIQgKIhtDQkFTRGdvSUwyMHZNR3QwTlRFU0FtRnlLQUFQAQ',
            ],
            'en' => [
                'NATION'        => 'CAAqIggKIhxDQkFTRHdvSkwyMHZNRGxqTjNjd0VnSmxiaWdBUAE',
                'WORLD'         => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtVnVHZ0pWVXlnQVAB',
                'BUSINESS'      => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx6TVdZU0FtVnVHZ0pWVXlnQVAB',
                'TECHNOLOGY'    => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRGRqTVhZU0FtVnVHZ0pWVXlnQVAB',
                'ENTERTAINMENT' => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNREpxYW5RU0FtVnVHZ0pWVXlnQVAB',
                'SPORTS'        => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRFp1ZEdvU0FtVnVHZ0pWVXlnQVAB',
                'SCIENCE'       => 'CAAqJggKIiBDQkFTRWdvSUwyMHZNRFp0Y1RjU0FtVnVHZ0pWVXlnQVAB',
                'HEALTH'        => 'CAAqIQgKIhtDQkFTRGdvSUwyMHZNR3QwTlRFU0FtVnVLQUFQAQ',
            ],
        ];

        $langKey = $lang === 'ar' ? 'ar' : 'en';
        $encodedTopicId = $topicIds[$langKey][$topic] ?? null;

        // Stream 1: Official Section / Topic URL
        $generalTopics = ['GENERAL', 'TOP_STORIES'];
        if (in_array($topic, $generalTopics, true)) {
            $urlsToFetch[] = GoogleNewsRss::feedUrl($country, $lang);
        } elseif (!empty($encodedTopicId)) {
            $urlsToFetch[] = GoogleNewsRss::topicUrl($encodedTopicId, $country, $lang);
            $urlsToFetch[] = GoogleNewsRss::sectionUrl($topic, $country, $lang);
        } else {
            $urlsToFetch[] = GoogleNewsRss::sectionUrl($topic, $country, $lang);
        }

        // Stream 2: Targeted Category Search Queries with Freshness Operator
        $targetedQueries = $this->buildTargetedCategoryQueries($topic, $country, $lang, $countryName, $timeWindow);
        foreach ($targetedQueries as $q) {
            $urlsToFetch[] = GoogleNewsRss::searchUrl($q, $country, $lang);
        }

        // ─── 2. Fetch All Streams Concurrently ───
        $rawNews = $this->fetchFromUrlsParallel($urlsToFetch);
        Log::info("NewsMonitor [{$topic}]: multi-stream fetch returned " . count($rawNews) . " raw articles for {$country} [{$lang}]");

        // ─── 3. Strict Relevance & Country Filter ───
        $relevant = $this->applyRelevanceFilter($rawNews, $country, $topic, $lang);

        // ─── 4. Freshness Filtering ───
        $maxAge = min($this->parseTimeWindow($timeWindow), 172800);
        $scrapedAt = now()->toIso8601String();
        $freshNews = $this->collectFreshArticles($relevant, $maxAge, $scrapedAt);

        // If very tight window returned few items, relax to 48h
        if (count($freshNews) < 15 && $maxAge < 172800) {
            $relaxed = $this->collectFreshArticles($relevant, 172800, $scrapedAt);
            foreach ($relaxed as $item) {
                $titleKey = mb_strtolower(preg_replace('/\s+/u', '', $item['title']));
                $exists = false;
                foreach ($freshNews as $existing) {
                    if (mb_strtolower(preg_replace('/\s+/u', '', $existing['title'])) === $titleKey) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    $freshNews[] = $item;
                }
            }
        }

        // ─── 5. Sort by Newest Publication Date ───
        usort($freshNews, function ($a, $b) {
            return strtotime($b['pubDate']) <=> strtotime($a['pubDate']);
        });

        // ─── 6. Deduplication by Normalized Title ───
        $seenTitles = [];
        $finalNews = [];
        foreach ($freshNews as $item) {
            $titleKey = mb_strtolower(preg_replace('/\s+/u', '', $item['title']));
            if (! in_array($titleKey, $seenTitles, true)) {
                $seenTitles[] = $titleKey;
                $finalNews[] = $item;
            }
            if (count($finalNews) >= 80) {
                break;
            }
        }

        Log::info("NewsMonitor [{$topic}]: final count = " . count($finalNews) . " articles for {$country} (raw fetched: " . count($rawNews) . ")");

        return $finalNews;
    }

    /**
     * Build targeted category search queries for parallel multi-stream fetching
     */
    protected function buildTargetedCategoryQueries(string $topic, string $country, string $lang, string $countryName, string $timeWindow): array
    {
        $timeParam = ($timeWindow === '6h' || $timeWindow === '1h') ? '1h' : (($timeWindow === '12h') ? '12h' : '24h');
        $isAr = ($lang === 'ar');
        $queries = [];

        if ($topic === 'TECHNOLOGY') {
            if ($isAr) {
                $queries[] = "(تكنولوجيا OR تقنية OR هواتف OR ذكاء اصطناعي OR تطبيقات OR آبل OR سامسونج) when:{$timeParam}";
                $queries[] = "(برمجة OR روبوتات OR أمن سيبراني OR حاسوب OR إلكترونيات OR شركات تقنية) when:{$timeParam}";
                $queries[] = "(هواتف ذكية OR آيفون OR شات جي بي تي OR أجهزة OR رقائق إلكترونية) when:{$timeParam}";
            } else {
                $queries[] = "(technology OR tech OR AI OR smartphone OR Apple OR Google OR software) when:{$timeParam}";
                $queries[] = "(cybersecurity OR robotics OR gadgets OR semiconductor OR hardware) when:{$timeParam}";
                $queries[] = "(startups OR gadgets OR ChatGPT OR Nvidia OR tech industry) when:{$timeParam}";
            }
        } elseif ($topic === 'BUSINESS') {
            if ($isAr) {
                $queries[] = "(اقتصاد OR أسهم OR بورصة OR أسعار الذهب OR التضخم OR البنك المركزي) when:{$timeParam}";
                $queries[] = "(استثمار OR عقارات OR نفط OR شركات OR صفقات OR تمويل OR تجارة) when:{$timeParam}";
                $queries[] = "(سعر الذهب OR سعر الدولار OR البورصة OR العملات OR أرباح الشركات) when:{$timeParam}";
            } else {
                $queries[] = "(economy OR business OR stocks OR inflation OR central bank OR investment) when:{$timeParam}";
                $queries[] = "(markets OR real estate OR crude oil OR revenue OR trading OR mergers) when:{$timeParam}";
                $queries[] = "(stock market OR interest rates OR oil prices OR gold price OR corporate earnings) when:{$timeParam}";
            }
        } elseif ($topic === 'SPORTS') {
            if ($isAr) {
                $queries[] = "(رياضة OR كرة قدم OR دوري OR مباراة OR أهداف OR منتخب) when:{$timeParam}";
                $queries[] = "(الدوري الإنجليزي OR دوري أبطال أوروبا OR ريال مدريد OR برشلونة OR محمد صلاح) when:{$timeParam}";
                $queries[] = "(مباريات اليوم OR جدول الترتيب OR ملخص أهداف OR انتقالات اللاعبين) when:{$timeParam}";
                if ($country === 'EG') {
                    $queries[] = "(الأهلي OR الزمالك OR الدوري المصري) when:{$timeParam}";
                } elseif ($country === 'SA') {
                    $queries[] = "(الهلال OR النصر OR الاتحاد OR الدوري السعودي OR دوري روشن) when:{$timeParam}";
                }
            } else {
                $queries[] = "(sports OR football OR soccer OR champions league OR premier league OR goal) when:{$timeParam}";
                $queries[] = "(basketball OR NBA OR tennis OR Formula 1 OR athletes OR match) when:{$timeParam}";
                $queries[] = "(match results OR league standings OR transfer news OR sports highlights) when:{$timeParam}";
            }
        } elseif ($topic === 'HEALTH') {
            if ($isAr) {
                $queries[] = "(صحة OR طب OR دراسة طبية OR علاج OR دواء OR مستشفى) when:{$timeParam}";
                $queries[] = "(أمراض OR فيروس OR وقاية OR تغذية OR جراحة OR منظمة الصحة) when:{$timeParam}";
                $queries[] = "(أبحاث طبية OR فيروسات OR لقاحات OR صحة عامة OR نصائح طبية) when:{$timeParam}";
            } else {
                $queries[] = "(health OR medicine OR medical study OR treatment OR hospital OR doctor) when:{$timeParam}";
                $queries[] = "(disease OR virus OR wellness OR nutrition OR surgery OR pharma) when:{$timeParam}";
                $queries[] = "(medical breakthroughs OR public health OR nutrition advice OR clinical trial) when:{$timeParam}";
            }
        } elseif ($topic === 'ENTERTAINMENT') {
            if ($isAr) {
                $queries[] = "(سينما OR فيلم OR فنان OR مسلسلات OR دراما OR مهرجان) when:{$timeParam}";
                $queries[] = "(نجوم OR أوسكار OR نتفليكس OR موسيقى OR حفل OR مسرح) when:{$timeParam}";
                $queries[] = "(شباك التذاكر OR مسلسلات جديدة OR مهرجان سينمائي OR أغاني جديدة) when:{$timeParam}";
            } else {
                $queries[] = "(entertainment OR movie OR film OR celebrity OR series OR cinema) when:{$timeParam}";
                $queries[] = "(box office OR Netflix OR Oscar OR music OR concert OR Hollywood) when:{$timeParam}";
                $queries[] = "(celebrity news OR film festival OR new movie releases OR music chart) when:{$timeParam}";
            }
        } elseif ($topic === 'SCIENCE') {
            if ($isAr) {
                $queries[] = "(فضاء OR وكالة ناسا OR اكتشاف علمي OR فلك OR كواكب OR أحافير) when:{$timeParam}";
                $queries[] = "(أبحاث علمية OR فيزياء OR كيمياء OR بيئة OR احتباس حراري OR مناخ) when:{$timeParam}";
                $queries[] = "(استكشاف الفضاء OR تلسكوب جيمس ويب OR كواكب جديدة OR اكتشافات) when:{$timeParam}";
            } else {
                $queries[] = "(science OR NASA OR space discovery OR astronomy OR planet OR fossil) when:{$timeParam}";
                $queries[] = "(scientific research OR physics OR chemistry OR climate change OR biology) when:{$timeParam}";
                $queries[] = "(James Webb telescope OR space mission OR climate discoveries OR quantum physics) when:{$timeParam}";
            }
        } elseif ($topic === 'NATION') {
            $cName = !empty($countryName) ? $countryName : ($country === 'EG' ? 'مصر' : ($country === 'SA' ? 'السعودية' : ($country === 'AE' ? 'الإمارات' : 'Nation')));
            if ($isAr) {
                $queries[] = "({$cName} OR الحكومة OR مجلس الوزراء OR قرارات OR وزارة) when:{$timeParam}";
                $queries[] = "({$cName} أخبار عاجلة OR تصريحات OR مشاريع OR تنمية) when:{$timeParam}";
                $queries[] = "({$cName} الاقتصاد الوطني OR البرلمان OR الموازنة العامة) when:{$timeParam}";
            } else {
                $queries[] = "({$cName} OR government OR cabinet OR national OR parliament) when:{$timeParam}";
                $queries[] = "({$cName} national news OR breaking news OR policy) when:{$timeParam}";
            }
        } else {
            // WORLD / GENERAL
            if ($isAr) {
                $queries[] = "(أخبار العالم OR دولي OR الأمم المتحدة OR البيت الأبيض OR قمة) when:{$timeParam}";
                $queries[] = "(عاجل دولي OR علاقات دولية OR مفاوضات OR أزمة عالمية) when:{$timeParam}";
                $queries[] = "(السياسة العالمية OR قمة دولية OR مجلس الأمن OR صراعات دولية) when:{$timeParam}";
            } else {
                $queries[] = "(world news OR international OR United Nations OR global summit) when:{$timeParam}";
                $queries[] = "(global politics OR breaking international OR foreign affairs) when:{$timeParam}";
            }
        }

        return $queries;
    }

    /**
     * Fetch multiple RSS URLs in parallel using Http::pool
     */
    protected function fetchFromUrlsParallel(array $urls, array $existingNews = []): array
    {
        if (empty($urls)) return $existingNews;

        try {
            $responses = Http::pool(function ($pool) use ($urls) {
                $reqs = [];
                foreach ($urls as $idx => $url) {
                    $reqs[$idx] = $pool->as("req_{$idx}")
                        ->timeout(6)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (compatible; VidaNexus/1.0; +https://vidanexus.ai)',
                            'Accept' => 'application/rss+xml, application/xml, text/xml',
                        ])
                        ->get($url);
                }
                return $reqs;
            });

            foreach ($urls as $idx => $url) {
                $resp = $responses["req_{$idx}"] ?? null;
                if ($resp && $resp->successful()) {
                    $xml = @simplexml_load_string($resp->body());
                    if ($xml && isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            $mapped = GoogleNewsRss::mapRssItem($item);
                            if ($mapped === null) continue;

                            $link = $mapped['link'];
                            if (isset($existingNews[$link])) continue;

                            $seoData = $this->analyzeSeoPotential(
                                $mapped['title'],
                                $mapped['description'],
                                $mapped['pubDate'],
                                $mapped['source']
                            );

                            $existingNews[$link] = array_merge($mapped, $seoData);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("[NewsMonitor] Parallel Fetch Error: " . $e->getMessage());
        }

        return $existingNews;
    }

    /**
     * Parse time window string to seconds
     */
    protected function parseTimeWindow($window)
    {
        $map = [
            '1h' => 3600,
            '3h' => 10800,
            '6h' => 21600,
            '12h' => 43200,
            '24h' => 86400,
            '48h' => 172800,
            '7d' => 604800,
        ];
        return $map[$window] ?? 43200; // Default 12h
    }

    /**
     * Keep articles whose pubDate falls within maxAge seconds (48h hard cap elsewhere).
     *
     * @param  array<int, array<string, mixed>>  $articles
     * @return array<int, array<string, mixed>>
     */
    protected function collectFreshArticles(array $articles, int $maxAge, string $scrapedAt): array
    {
        $maxAge = min($maxAge, 172800);
        $cutoffTime = time() - $maxAge;
        $now = time();
        $fresh = [];

        foreach ($articles as $item) {
            $pubTime = strtotime((string) ($item['pubDate'] ?? ''));
            if (! $pubTime) {
                $item['scraped_at'] = $scrapedAt;
                $fresh[] = $item;

                continue;
            }
            // Some feeds ship future-dated pubDate values; clamp for freshness math.
            if ($pubTime > $now) {
                $pubTime = $now;
            }
            if ($pubTime >= $cutoffTime) {
                $item['scraped_at'] = $scrapedAt;
                $fresh[] = $item;
            }
        }

        return $fresh;
    }

    /**
     * Helper to fetch and merge news from a specific RSS URL.
     *
     * Each item also captures the publisher's homepage URL (Google News stores
     * it as the "url" attribute on <source>). We need it so the relevance
     * filter can verify the article's origin country via TLD / known-publisher
     * lookup — Google's redirect <link> alone is not enough.
     */
    protected function fetchFromUrl($url, $existingNews = [])
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; VidaNexus/1.0; +https://vidanexus.ai)',
                    'Accept' => 'application/rss+xml, application/xml, text/xml',
                ])
                ->get($url);
            if ($response->failed()) return $existingNews;

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) return $existingNews;

            foreach ($xml->channel->item as $item) {
                $mapped = GoogleNewsRss::mapRssItem($item);
                if ($mapped === null) {
                    continue;
                }

                $link = $mapped['link'];
                if (isset($existingNews[$link])) {
                    continue;
                }

                $seoData = $this->analyzeSeoPotential(
                    $mapped['title'],
                    $mapped['description'],
                    $mapped['pubDate'],
                    $mapped['source']
                );

                $existingNews[$link] = array_merge($mapped, $seoData);
            }
        } catch (\Exception $e) {
            Log::error("Global News Monitor Fetch Error: " . $e->getMessage());
        }

        return $existingNews;
    }

    /**
     * Apply strict country + topic relevance filtering.
     *
     * Filters can be disabled via admin toggles
     * (`global-news-monitor_strict_country_filter`,
     *  `global-news-monitor_strict_topic_filter`). When both pass, the article
     *  is kept; otherwise it is dropped before ranking/display.
     *
     * Per requirement, no off-country / off-topic articles ever leak through —
     * the empty state in the UI is preferred over showing unrelated news. If
     * strict filtering removes everything we log a warning so it's diagnosable
     * from the admin tool error log.
     *
     * @param  array<string, array<string, mixed>>  $articles  link => article
     * @return array<int, array<string, mixed>>
     */
    protected function applyRelevanceFilter(array $articles, string $country, string $topic, string $lang, bool $relaxCountry = false): array
    {
        if (empty($articles)) {
            return [];
        }

        $strictCountry = $relaxCountry ? false : (bool) \App\Models\Setting::get('global-news-monitor_strict_country_filter', 1);
        $strictTopic = (bool) \App\Models\Setting::get('global-news-monitor_strict_topic_filter', 1);

        if (! $strictCountry && ! $strictTopic) {
            return array_values($articles);
        }

        $passed = [];

        foreach ($articles as $item) {
            $countryOk = $strictCountry ? $this->articleMatchesCountry($item, $country) : true;
            $topicOk = $strictTopic ? $this->articleMatchesTopic($item, $topic, $lang) : true;

            if ($countryOk && $topicOk) {
                $passed[] = $item;
            }
        }

        if (empty($passed) && ! $relaxCountry && (bool) \App\Models\Setting::get('global-news-monitor_strict_country_filter', 1)) {
            Log::warning("NewsMonitor: strict country filter removed all articles for {$country}/{$topic} (raw count: ".count($articles).'). Trying regional feed fallback.');

            return $this->applyRelevanceFilter($articles, $country, $topic, $lang, true);
        }

        if (empty($passed)) {
            Log::warning("NewsMonitor: relevance filter removed all articles for {$country}/{$topic} (raw count: ".count($articles).').');
        }

        return $passed;
    }

    /**
     * Verify the article was published by an outlet from the requested country
     * (via TLD or known-publisher list), or — when published by an
     * international wire — that the article content references the country.
     */
    /**
     * Verify the article was published by an outlet from the requested country
     * (via TLD or known-publisher list), or — when published by an
     * international wire / regional outlet — that the article content references the country.
     */
    protected function articleMatchesCountry(array $item, string $country): bool
    {
        $country = strtoupper($country);
        $sourceUrl = mb_strtolower((string) ($item['source_url'] ?? ''));
        $sourceName = mb_strtolower((string) ($item['source'] ?? ''));
        $link = mb_strtolower((string) ($item['link'] ?? ''));
        $linkHost = mb_strtolower((string) parse_url($link, PHP_URL_HOST));
        $combined = trim($sourceUrl.' '.$sourceName.' '.$linkHost.' '.$link);

        $map = $this->countrySourceMap();
        $entry = $map[$country] ?? null;

        // 1. Exact match: Check if outlet belongs to the requested country
        if ($entry !== null) {
            foreach ($entry['tlds'] as $tld) {
                if ($tld !== '' && (str_contains($sourceUrl, $tld) || str_contains($linkHost, $tld))) {
                    return true;
                }
            }
            foreach ($entry['publishers'] as $pub) {
                if ($pub !== '' && str_contains($combined, $pub)) {
                    return true;
                }
            }
        } else {
            $cc = strtolower($country);
            if ($cc !== '' && preg_match('/\.'.preg_quote($cc, '/').'(\/|$|:)/', $sourceUrl)) {
                return true;
            }
        }

        // 2. Reject if the publisher is explicitly registered to ANOTHER specific country
        foreach ($map as $otherCountry => $otherEntry) {
            if ($otherCountry === $country) continue;
            foreach ($otherEntry['tlds'] as $tld) {
                if ($tld !== '' && (str_contains($sourceUrl, $tld) || str_contains($linkHost, $tld))) {
                    return false;
                }
            }
            foreach ($otherEntry['publishers'] as $pub) {
                if ($pub !== '' && str_contains($combined, $pub)) {
                    return false;
                }
            }
        }

        // 3. For International Wires / Unbound Publishers: Allow ONLY if article mentions the requested country
        $aliases = $this->countryAliases()[$country] ?? [];
        $haystack = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['description'] ?? ''));
        foreach ($aliases as $alias) {
            if ($alias !== '' && mb_strpos($haystack, mb_strtolower($alias)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Negative exclude keywords for each topic to prevent off-topic contamination
     */
    protected function topicNegativeKeywords(): array
    {
        return [
            'TECHNOLOGY' => [
                'ar' => ['أمطار', 'سيول', 'طقس', 'الأرصاد', 'وفاة شخص', 'حادث تصادم', 'قتلى', 'جريمة', 'زلزال', 'سعر جرام', 'سعر الدولار', 'أهداف مباراة', 'تشكيل المنتخب', 'خفاش', 'جنازة', 'عزاء', 'إعدام', 'محاكمة', 'خسوف القمر'],
                'en' => ['weather forecast', 'heavy rain', 'earthquake', 'car crash', 'murder', 'shooting', 'gold price', 'dollar rate', 'match highlights', 'team lineup', 'funeral'],
            ],
            'BUSINESS' => [
                'ar' => ['أهداف مباراة', 'تشكيل منتخب', 'وفاة فنان', 'خفاش', 'طقس غدا', 'أمطار غزيرة', 'زلزال مدمر', 'حادث قطار'],
                'en' => ['match highlights', 'team lineup', 'celebrity dating', 'bat in mouth', 'heavy rain', 'fatal crash'],
            ],
            'HEALTH' => [
                'ar' => ['أهداف مباراة', 'سعر الدولار', 'سهم البورصة', 'فيلم جديد', 'إيرادات السينما', 'مهرجان سينمائي'],
                'en' => ['box office', 'movie trailer', 'stock market crash', 'premier league score'],
            ],
            'SPORTS' => [
                'ar' => ['سعر الفائدة', 'التضخم الاقتصادي', 'تطبيق ذكي', 'هاتف جديد', 'أسعار الذهب'],
                'en' => ['stock dividend', 'interest rate hike', 'cyberattack', 'software update'],
            ],
            'SCIENCE' => [
                'ar' => ['أهداف مباراة', 'سعر جرام الذهب', 'مسلسل رمضاني', 'طلاق فنانة'],
                'en' => ['premier league', 'celebrity divorce', 'gold jewelry price'],
            ],
            'ENTERTAINMENT' => [
                'ar' => ['سعر الفائدة', 'التضخم', 'أهداف مباراة', 'زلزال بقوة', 'سعر الدولار'],
                'en' => ['interest rates', 'inflation surge', 'match score', 'earthquake magnitude'],
            ],
        ];
    }

    /**
     * Verify the article actually belongs to the selected topic by scanning
     * its title + description for topic keywords and enforcing negative exclusions.
     */
    protected function articleMatchesTopic(array $item, string $topic, string $lang): bool
    {
        $topic = strtoupper($topic);

        if (in_array($topic, ['GENERAL', 'TOP_STORIES', 'WORLD', 'NATION'], true)) {
            return true;
        }

        $haystack = ' ' . mb_strtolower(($item['title'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . ($item['source'] ?? '')) . ' ';

        // 1. Check Negative Exclude Keywords for this topic
        $negativesMap = $this->topicNegativeKeywords();
        $negatives = $negativesMap[$topic] ?? null;
        if ($negatives !== null) {
            $negHits = $this->countKeywordHits($haystack, $negatives['ar'] ?? [])
                + $this->countKeywordHits($haystack, $negatives['en'] ?? []);
            if ($negHits > 0) {
                return false;
            }
        }

        // 2. Check Positive Topic Keywords
        $keywordsMap = $this->topicKeywordMap();
        $topicKeywords = $keywordsMap[$topic] ?? null;
        if ($topicKeywords === null) {
            return true;
        }

        $primary = $this->countKeywordHits($haystack, $topicKeywords['ar'] ?? [])
            + $this->countKeywordHits($haystack, $topicKeywords['en'] ?? []);

        if ($primary === 0) {
            return false;
        }

        // 3. Reject when another category dominates the article more strongly than the requested topic
        $bestOther = 0;
        foreach ($keywordsMap as $otherTopic => $sets) {
            if ($otherTopic === $topic) continue;
            $otherHits = $this->countKeywordHits($haystack, $sets['ar'] ?? [])
                + $this->countKeywordHits($haystack, $sets['en'] ?? []);
            if ($otherHits > $bestOther) {
                $bestOther = $otherHits;
            }
        }

        return $primary >= $bestOther;
    }

    /**
     * Count keyword hits for a haystack using a multi-word friendly
     * substring search. Each keyword counts at most once.
     */
    protected function countKeywordHits(string $haystack, array $keywords): int
    {
        $hits = 0;
        foreach ($keywords as $kw) {
            $kw = mb_strtolower(trim((string) $kw));
            if ($kw === '' || mb_strlen($kw) < 2) {
                continue;
            }
            if (mb_strpos($haystack, $kw) !== false) {
                $hits++;
            }
        }
        return $hits;
    }

    /**
     * Detect international wire services that don't sit under a country TLD.
     * These are allowed to pass the country filter only when the article
     * content references the requested country.
     */
    protected function isInternationalWire(string $combined): bool
    {
        $wires = [
            'reuters', 'apnews', 'associated press', 'afp.com', 'bbc.com', 'bbc.co.uk',
            'cnn.com', 'bloomberg', 'aljazeera', 'al jazeera', 'france24', 'rt.com',
            'dw.com', 'sputnik', 'euronews', 'voanews', 'wsj.com', 'ft.com',
            'nytimes', 'washingtonpost', 'guardian', 'independent.co.uk', 'forbes',
        ];
        foreach ($wires as $w) {
            if (str_contains($combined, $w)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Country → {tlds, publishers} mapping used by the strict country filter.
     * Admin can append/override entries via the
     * `global-news-monitor_country_source_overrides` setting using the format:
     *   EG: youm7, almasryalyoum, masrawy
     *   SA: sabq, okaz
     */
    protected function countrySourceMap(): array
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $base = [
            'EG' => ['tlds' => ['.eg'], 'publishers' => ['youm7', 'almasryalyoum', 'shorouk', 'shorouknews', 'ahram', 'al-ahram', 'masrawy', 'elwatan', 'elwatannews', 'sada', 'almal', 'akhbarak', 'elbalad', 'al-bawaba', 'dostor', 'cairo24', 'cairoeye', 'mbc.net', 'rosaelyoussef', 'akhbarelyom']],
            'SA' => ['tlds' => ['.sa'], 'publishers' => ['sabq', 'okaz', 'al-madina', 'aljazirah', 'al-jazirah', 'alyaum', 'alriyadh', 'alwatan-sa', 'spa.gov', 'arabnews', 'al-arabiya', 'alarabiya', 'alekhbariya', 'al-eqt', 'aleqt']],
            'AE' => ['tlds' => ['.ae'], 'publishers' => ['emarat', 'gulfnews', 'thenational', 'khaleejtimes', 'albayan', 'alittihad', 'wam.ae', 'alroeya', 'sharjah24', '24.ae']],
            'KW' => ['tlds' => ['.kw'], 'publishers' => ['alqabas', 'alanba', 'alwatan-kw', 'kuna.net', 'alrai.com', 'alseyassah']],
            'QA' => ['tlds' => ['.qa'], 'publishers' => ['al-sharq', 'gulf-times', 'lusailnews', 'raya.com', 'al-watan', 'qatar-tribune']],
            'BH' => ['tlds' => ['.bh'], 'publishers' => ['alayam', 'akhbar-alkhaleej', 'gdnonline', 'albiladpress', 'bna.bh']],
            'OM' => ['tlds' => ['.om'], 'publishers' => ['omandaily', 'shabiba', 'alroya', 'omanobserver', 'timesofoman', 'atheer.om']],
            'IQ' => ['tlds' => ['.iq'], 'publishers' => ['alsabaah', 'azzaman', 'alsumaria', 'rudaw', 'shafaq', 'ina.iq', 'almadapaper', 'baghdadtoday']],
            'JO' => ['tlds' => ['.jo'], 'publishers' => ['alghad', 'alrai', 'addustour', 'jordannews', 'roya', 'petra.gov.jo', 'ammonnews', 'khaberni', 'sarayanews']],
            'LB' => ['tlds' => ['.lb'], 'publishers' => ['annahar', 'almodon', 'lebanon24', 'lbcgroup', 'mtv.com.lb', 'nna-leb', 'aljadeed', 'aliwaa', 'al-akhbar', 'addiyar', 'janoubia']],
            'MA' => ['tlds' => ['.ma'], 'publishers' => ['hespress', 'akhbarona', 'le360', 'aujourdhui', 'maroc-hebdo', 'lematin', 'leconomiste', '2m.ma', 'medi1news', 'goud', 'febrayer']],
            'DZ' => ['tlds' => ['.dz'], 'publishers' => ['echoroukonline', 'elkhabar', 'elmoudjahid', 'liberte-algerie', 'aps.dz', 'tsa-algerie', 'algeriepatriotique']],
            'TN' => ['tlds' => ['.tn'], 'publishers' => ['mosaiquefm', 'tunisienumerique', 'lapresse-tn', 'leaders.com.tn', 'businessnews.com.tn', 'webdo', 'jawharafm', 'shemsfm', 'tap.info']],
            'LY' => ['tlds' => ['.ly'], 'publishers' => ['alwasat', 'ean-libya', 'libyaherald', 'lana.gov.ly', '218tv']],
            'PS' => ['tlds' => ['.ps'], 'publishers' => ['paltoday', 'maannews', 'wafa.ps', 'pnn.ps', 'palinfo', 'felesteen', 'sama-news', 'safa.ps', 'shehab', 'qudsn']],
            'SY' => ['tlds' => ['.sy'], 'publishers' => ['sana.sy', 'thawra', 'champress', 'tishreen', 'al-watan']],
            'YE' => ['tlds' => ['.ye'], 'publishers' => ['saba', 'alyemen-alaraby', 'yemenat', 'almasdaronline', 'yemen-press', '26sep']],
            'US' => ['tlds' => ['.us', 'usa.gov'], 'publishers' => ['cnn.com', 'foxnews', 'nytimes', 'washingtonpost', 'usatoday', 'wsj', 'nbcnews', 'abcnews', 'cbsnews', 'npr.org', 'politico', 'thehill', 'axios', 'bloomberg', 'businessinsider', 'time.com', 'newsweek', 'theatlantic', 'vox.com', 'buzzfeednews', 'huffpost', 'msnbc']],
            'GB' => ['tlds' => ['.uk', 'co.uk'], 'publishers' => ['bbc.co.uk', 'bbc.com/news/uk', 'theguardian', 'telegraph.co.uk', 'thetimes.co.uk', 'independent.co.uk', 'mirror.co.uk', 'dailymail.co.uk', 'sky.com/news', 'metro.co.uk', 'standard.co.uk', 'express.co.uk', 'thesun.co.uk', 'inews.co.uk', 'ft.com/uk']],
            'FR' => ['tlds' => ['.fr'], 'publishers' => ['lemonde', 'lefigaro', 'liberation', 'leparisien', 'rfi.fr', 'france24', 'francetv', 'bfmtv', 'lexpress', 'lepoint', 'leshop', 'ouest-france', 'sudouest', 'la-croix', 'humanite', '20minutes']],
            'PL' => ['tlds' => ['.pl'], 'publishers' => ['onet.pl', 'wp.pl', 'interia.pl', 'tvn24', 'rmf24', 'rp.pl', 'gazeta.pl', 'fakt.pl', 'wprost.pl', 'polskieradio', 'polsatnews', 'tvp.info', 'wyborcza', 'do-rzeczy', 'niezalezna']],
            'TR' => ['tlds' => ['.tr'], 'publishers' => ['hurriyet', 'milliyet', 'sabah', 'haberturk', 'ntv.com.tr', 'cnnturk', 'yenisafak', 'sozcu', 'cumhuriyet', 'trthaber', 'aa.com.tr', 'dunya']],
            'DE' => ['tlds' => ['.de'], 'publishers' => ['spiegel', 'bild.de', 'faz.net', 'sueddeutsche', 'zeit.de', 'tagesschau', 'n-tv', 'welt.de', 'focus.de', 'handelsblatt', 'stern.de']],
            'IN' => ['tlds' => ['.in'], 'publishers' => ['timesofindia', 'indiatimes', 'hindustantimes', 'ndtv', 'thehindu', 'indianexpress', 'livemint', 'moneycontrol', 'firstpost', 'news18']],
            'BR' => ['tlds' => ['.br'], 'publishers' => ['globo.com', 'uol.com.br', 'folha.uol', 'estadao', 'g1.globo', 'r7.com', 'terra.com.br', 'ig.com.br', 'cnnbrasil']],
            'JP' => ['tlds' => ['.jp'], 'publishers' => ['nhk.or.jp', 'asahi.com', 'mainichi.jp', 'yomiuri.co.jp', 'nikkei', 'jiji.com', 'kyodonews']],
            'SD' => ['tlds' => ['.sd'], 'publishers' => ['sudanakhbar', 'sudantribune', 'sudaneseonline', 'alrakoba', 'suna.sd']],
            'CA' => ['tlds' => ['.ca'], 'publishers' => ['cbc.ca', 'globalnews.ca', 'theglobeandmail', 'nationalpost', 'ctvnews', 'torontosun', 'montrealgazette']],
            'AU' => ['tlds' => ['.au'], 'publishers' => ['abc.net.au', 'news.com.au', 'theaustralian', 'smh.com.au', 'theage.com.au', 'nine.com.au', 'skynews.com.au']],
            'ES' => ['tlds' => ['.es'], 'publishers' => ['elpais.com', 'elmundo.es', 'abc.es', 'lavanguardia', 'elconfidencial', '20minutos', 'rtve.es']],
            'IT' => ['tlds' => ['.it'], 'publishers' => ['corriere.it', 'repubblica.it', 'ansa.it', 'ilsole24ore', 'lastampa.it', 'ilgiornale', 'rainews']],
        ];

        $overridesText = (string) \App\Models\Setting::get('global-news-monitor_country_source_overrides', '');
        if (trim($overridesText) !== '') {
            foreach (preg_split('/\r?\n/', $overridesText) as $line) {
                $line = trim($line);
                if ($line === '' || ! str_contains($line, ':')) {
                    continue;
                }
                [$code, $rest] = explode(':', $line, 2);
                $code = strtoupper(trim($code));
                if ($code === '') continue;
                $extra = array_filter(array_map(fn($p) => mb_strtolower(trim($p)), preg_split('/[,;]+/', $rest)));
                if (! isset($base[$code])) {
                    $base[$code] = ['tlds' => [], 'publishers' => []];
                }
                $base[$code]['publishers'] = array_values(array_unique(array_merge($base[$code]['publishers'], $extra)));
            }
        }

        return $cached = $base;
    }

    /**
     * Country aliases (Arabic + English) used to verify international-wire
     * coverage of a country.
     */
    protected function countryAliases(): array
    {
        return [
            'EG' => ['مصر', 'مصري', 'مصرية', 'القاهرة', 'الجيزة', 'الإسكندرية', 'السيسي', 'egypt', 'egyptian', 'cairo', 'alexandria', 'giza', 'sisi'],
            'SA' => ['السعودية', 'سعودي', 'سعودية', 'الرياض', 'جدة', 'مكة', 'المدينة', 'محمد بن سلمان', 'بن سلمان', 'saudi arabia', 'saudi', 'riyadh', 'jeddah', 'mecca', 'medina', 'mbs'],
            'AE' => ['الإمارات', 'إماراتي', 'إماراتية', 'أبوظبي', 'دبي', 'الشارقة', 'العين', 'uae', 'emirates', 'emirati', 'abu dhabi', 'dubai', 'sharjah'],
            'KW' => ['الكويت', 'كويتي', 'كويتية', 'kuwait', 'kuwaiti'],
            'QA' => ['قطر', 'قطري', 'قطرية', 'الدوحة', 'qatar', 'qatari', 'doha'],
            'BH' => ['البحرين', 'بحريني', 'بحرينية', 'المنامة', 'bahrain', 'manama'],
            'OM' => ['عمان', 'عماني', 'عمانية', 'مسقط', 'oman', 'omani', 'muscat'],
            'IQ' => ['العراق', 'عراقي', 'عراقية', 'بغداد', 'البصرة', 'الموصل', 'iraq', 'iraqi', 'baghdad', 'basra', 'mosul'],
            'JO' => ['الأردن', 'أردني', 'أردنية', 'عمان', 'jordan', 'jordanian', 'amman'],
            'LB' => ['لبنان', 'لبناني', 'لبنانية', 'بيروت', 'lebanon', 'lebanese', 'beirut'],
            'MA' => ['المغرب', 'مغربي', 'مغربية', 'الرباط', 'الدار البيضاء', 'مراكش', 'فاس', 'morocco', 'moroccan', 'rabat', 'casablanca', 'marrakech', 'fes'],
            'DZ' => ['الجزائر', 'جزائري', 'جزائرية', 'algeria', 'algerian', 'algiers'],
            'TN' => ['تونس', 'تونسي', 'تونسية', 'tunisia', 'tunisian', 'tunis'],
            'LY' => ['ليبيا', 'ليبي', 'ليبية', 'طرابلس', 'بنغازي', 'libya', 'libyan', 'tripoli', 'benghazi'],
            'PS' => ['فلسطين', 'فلسطيني', 'فلسطينية', 'غزة', 'الضفة', 'القدس', 'palestine', 'palestinian', 'gaza', 'west bank', 'jerusalem'],
            'SY' => ['سوريا', 'سوري', 'سورية', 'دمشق', 'حلب', 'syria', 'syrian', 'damascus', 'aleppo'],
            'YE' => ['اليمن', 'يمني', 'يمنية', 'صنعاء', 'عدن', 'yemen', 'yemeni', 'sanaa', 'aden'],
            'US' => ['الولايات المتحدة', 'أمريكا', 'أمريكي', 'أمريكية', 'واشنطن', 'نيويورك', 'البيت الأبيض', 'usa', 'u.s.', 'united states', 'america', 'american', 'washington', 'new york', 'white house'],
            'GB' => ['بريطانيا', 'بريطاني', 'بريطانية', 'لندن', 'المملكة المتحدة', 'uk', 'britain', 'british', 'london', 'united kingdom', 'england', 'english'],
            'FR' => ['فرنسا', 'فرنسي', 'فرنسية', 'باريس', 'france', 'french', 'paris'],
            'PL' => ['بولندا', 'بولندي', 'بولندية', 'وارسو', 'poland', 'polish', 'warsaw'],
            'TR' => ['تركيا', 'تركي', 'تركية', 'إسطنبول', 'أنقرة', 'turkey', 'turkish', 'istanbul', 'ankara'],
            'DE' => ['ألمانيا', 'ألماني', 'ألمانية', 'برلين', 'germany', 'german', 'berlin'],
            'IN' => ['الهند', 'هندي', 'هندية', 'مومباي', 'دلهي', 'india', 'indian', 'mumbai', 'delhi', 'new delhi'],
            'BR' => ['البرازيل', 'برازيلي', 'برازيلية', 'ساو باولو', 'ريو', 'brazil', 'brazilian', 'sao paulo', 'rio de janeiro'],
            'JP' => ['اليابان', 'ياباني', 'يابانية', 'طوكيو', 'japan', 'japanese', 'tokyo'],
            'SD' => ['السودان', 'سوداني', 'سودانية', 'الخرطوم', 'sudan', 'sudanese', 'khartoum'],
            'CA' => ['كندا', 'كندي', 'كندية', 'أوتاوا', 'تورونتو', 'canada', 'canadian', 'ottawa', 'toronto'],
            'AU' => ['أستراليا', 'أسترالي', 'أسترالية', 'سيدني', 'ملبورن', 'australia', 'australian', 'sydney', 'melbourne'],
            'ES' => ['إسبانيا', 'إسباني', 'إسبانية', 'مدريد', 'برشلونة', 'spain', 'spanish', 'madrid', 'barcelona'],
            'IT' => ['إيطاليا', 'إيطالي', 'إيطالية', 'روما', 'ميلانو', 'italy', 'italian', 'rome', 'milan'],
        ];
    }

    /**
     * Topic keyword map (Arabic + English) for strict topic filtering.
     */
    protected function topicKeywordMap(): array
    {
        return [
            'TECHNOLOGY' => [
                'ar' => ['تكنولوجيا', 'تقنية', 'تقني', 'تطبيق', 'تطبيقات', 'هاتف', 'هواتف', 'جوال', 'موبايل', 'ذكي', 'حاسوب', 'كمبيوتر', 'لاب توب', 'إنترنت', 'برمجة', 'مبرمج', 'ذكاء اصطناعي', 'الذكاء الاصطناعي', 'روبوت', 'برنامج', 'سيليكون', 'تشات جي بي تي', 'بلوكتشين', 'كريبتو', 'عملة رقمية', 'بيتكوين', 'إيثريوم', 'سايبر', 'هاكر', 'اختراق', 'سامسونج', 'آبل', 'أبل', 'مايكروسوفت', 'جوجل', 'ميتا', 'تويتر', 'فيسبوك', 'إنستغرام', 'تيك توك', 'يوتيوب', 'تسلا', 'إنفيديا', 'سبيس إكس', 'سيارة كهربائية', 'بطاقة رسومية'],
                'en' => ['tech', 'technology', 'ai ', ' ai,', 'artificial intelligence', 'machine learning', ' ml ', 'software', 'hardware', ' app ', ' apps ', 'phone', 'smartphone', 'iphone', 'android', 'computer', 'laptop', 'internet', 'cyber', 'startup', 'algorithm', 'chip', 'silicon', 'apple ', 'google', 'microsoft', 'meta ', 'samsung', 'nvidia', 'tesla', 'openai', 'chatgpt', 'gpt-', 'crypto', 'bitcoin', 'ethereum', 'blockchain', 'nft', ' gpu', ' cpu', 'cloud computing', 'saas', 'tiktok', 'youtube', 'twitter', 'instagram', 'facebook', 'whatsapp', 'spacex', 'gadget', 'wearable'],
            ],
            'SPORTS' => [
                'ar' => ['رياضة', 'رياضي', 'مباراة', 'مباريات', 'دوري', 'كأس', 'منتخب', 'منتخبات', 'ميدالية', 'ميداليات', 'هدف', 'أهداف', 'لاعب', 'لاعبة', 'مدرب', 'فريق', 'فرق', 'كرة القدم', 'كرة السلة', 'كرة اليد', 'كرة الطائرة', 'تنس', 'سباحة', 'الزمالك', 'الأهلي', 'بايرن', 'ريال مدريد', 'برشلونة', 'ليفربول', 'تشيلسي', 'مانشستر', 'فيفا', 'كاف', 'بطولة', 'صلاح', 'محمد صلاح', 'الدوري', 'بريميرليج', 'تشامبيونزليج', 'الأولمبياد'],
                'en' => ['sport', 'sports', 'football', 'soccer', 'basketball', 'tennis', 'cricket', 'baseball', 'rugby', 'golf', 'olympic', 'champion', 'tournament', 'league', 'premier league', ' cup', 'goal ', 'striker', 'midfielder', 'defender', 'goalkeeper', 'player', 'coach', 'manager', 'match', 'matches', 'nba', 'nfl', 'nhl', 'mlb', 'fifa', 'uefa', 'champions league', 'world cup', 'medal', 'athlete', 'racing', 'formula 1', ' f1 ', 'super bowl'],
            ],
            'BUSINESS' => [
                'ar' => ['اقتصاد', 'اقتصادي', 'اقتصادية', 'أعمال', 'بورصة', 'سوق المال', 'سوق الأسهم', 'سهم', 'أسهم', 'سندات', 'استثمار', 'مستثمر', 'بنك', 'بنوك', 'مصرف', 'مصارف', 'تمويل', 'صفقة', 'صفقات', 'أرباح', 'إيرادات', 'تضخم', 'فائدة', 'دولار', 'يورو', 'ذهب', 'نفط', 'بترول', 'غاز طبيعي', 'صادرات', 'واردات', 'ميزانية', 'الناتج المحلي', 'صندوق النقد', 'البنك الدولي', 'الفيدرالي', 'شركة', 'شركات', 'أوبك', 'بنك مركزي', 'تجارة', 'ضرائب', 'الجنيه', 'الريال', 'الدرهم'],
                'en' => ['economy', 'economic', 'economics', 'business', 'market', 'markets', 'stocks', 'shares', 'bonds', 'investment', 'investor', 'bank', 'banking', 'finance', 'financial', 'deal', 'deals', 'profit', 'revenue', 'earnings', 'inflation', 'interest rate', ' fed ', 'federal reserve', ' imf ', 'world bank', ' gdp', 'dollar', 'euro', ' yen', 'gold', 'oil ', 'crude', ' gas ', 'commodities', 'export', 'import', 'budget', 'fiscal', ' ipo', 'merger', 'acquisition', ' m&a', 'company', 'corporate', ' ceo', 'opec', 'wall street', 'currency', 'trade', ' tax '],
            ],
            'ENTERTAINMENT' => [
                'ar' => ['ترفيه', 'فن ', 'فنان', 'فنانة', 'ممثل', 'ممثلة', 'مغني', 'مغنية', 'فيلم', 'أفلام', 'مسلسل', 'مسلسلات', 'حفل', 'حفلة', 'سينما', 'مهرجان', 'مهرجانات', 'مسرح', 'مسرحية', 'موسيقى', 'أغنية', 'أغنيات', 'ألبوم', 'هوليوود', 'بوليوود', 'نتفليكس', 'ديزني', 'النجم', 'النجمة', 'كان السينمائي', 'البندقية', 'أوسكار', 'إيمي', 'دراما', 'كوميديا', 'موسم رمضاني', 'مسلسلات رمضان'],
                'en' => ['entertainment', 'celebrity', 'celebrities', 'actor', 'actress', 'singer', 'rapper', 'movie', 'movies', 'film ', 'films', 'tv show', 'series', 'concert', 'cinema', 'festival', 'theater', 'theatre', 'music', 'song', 'songs', 'album', 'hollywood', 'bollywood', 'netflix', 'disney', ' hbo', 'amazon prime', 'oscar', 'emmy', 'grammy', 'cannes', 'box office', 'premiere', 'streaming', 'spotify', 'apple music', 'soundtrack', 'red carpet'],
            ],
            'HEALTH' => [
                'ar' => ['صحة', 'صحي', 'صحية', 'طب', 'طبي', 'طبية', 'دواء', 'أدوية', 'علاج', 'مرض', 'أمراض', 'وباء', 'فيروس', 'بكتيريا', 'لقاح', 'مستشفى', 'مستشفيات', 'طبيب', 'أطباء', 'الصحة العامة', 'كوفيد', 'كورونا', 'سرطان', 'القلب', 'سكري', 'ضغط الدم', 'تطعيم', 'منظمة الصحة', 'تأمين صحي', 'وزارة الصحة', 'فحص', 'تحاليل', 'جراحة', 'مناعة', 'تغذية'],
                'en' => ['health', 'medical', 'medicine', 'doctor', 'doctors', 'patient', 'patients', 'disease', 'virus', 'bacteria', 'epidemic', 'pandemic', 'covid', 'corona', 'cancer', 'diabetes', 'cardiac', 'hospital', 'hospitals', 'clinic', 'vaccine', 'vaccination', ' who ', 'world health', ' fda ', ' cdc ', 'pharma', 'pharmaceutical', 'drug', 'therapy', 'treatment', 'mental health', 'wellness', 'nutrition', 'surgery', 'surgeon', 'symptom', 'diagnosis'],
            ],
            'SCIENCE' => [
                'ar' => ['علم', 'علوم', 'علماء', 'دراسة', 'بحث', 'باحث', 'أبحاث', 'فضاء', 'القمر', 'كوكب', 'مذنب', 'كويكب', 'مجرة', 'ناسا', 'سبيس إكس', 'فلك', 'فيزياء', 'كيمياء', 'بيولوجيا', 'علم النفس', 'علم الاجتماع', 'تجربة', 'اكتشاف', 'اكتشف', 'علماء الآثار', 'حفريات', 'ديناصور', 'مناخ', 'احتباس حراري', 'تغير المناخ', 'بيئة', 'علم البيئة', 'جينات', 'الحمض النووي'],
                'en' => ['science', 'scientific', 'scientist', 'scientists', 'research', 'researcher', 'study', 'studies', 'space', ' moon', ' mars', 'planet', 'asteroid', 'comet', 'galaxy', 'nasa', 'spacex', 'astronomy', 'physics', 'chemistry', 'biology', 'psychology', 'sociology', 'experiment', 'discovery', 'archaeology', 'fossil', 'dinosaur', 'climate', 'global warming', 'climate change', 'environment', 'ecology', 'evolution', 'genetic', ' dna ', 'cern', 'telescope', 'satellite'],
            ],
        ];
    }

    /**
     * ══════════════════════════════════════════════════════════════
     *  RANKING OPPORTUNITY ENGINE v2.0
     *  Real scoring based on: Trend Velocity, Freshness, SERP 
     *  Saturation, Authority Gap, Sentiment, and Entity Extraction
     * ══════════════════════════════════════════════════════════════
     */
    protected function analyzeSeoPotential($title, $desc, $pubDate, $source)
    {
        $text = mb_strtolower($title . ' ' . $desc);
        $titleLower = mb_strtolower($title);
        
        // ─── 1. SOURCE AUTHORITY ANALYSIS ───
        $majorAuthorityText = \App\Models\Setting::get('global-news-monitor_major_authority_sources', "سكاي نيوز\nالجزيرة\nالعربية\nرويترز\nفرانس 24\nالشرق الأوسط\nbbc\ncnn\nreuters\nny times\nassociated press\nal jazeera");
        $midAuthorityText = \App\Models\Setting::get('global-news-monitor_mid_authority_sources', "اليوم السابع\nالبيان\nالخليج\nالوطن\nالمصري اليوم\nالشروق\nعكاظ\nسبق\nforbes\ntechcrunch\nwired\nverge");
        
        $majorAuthoritySources = array_map('trim', explode("\n", mb_strtolower($majorAuthorityText)));
        $midAuthoritySources = array_map('trim', explode("\n", mb_strtolower($midAuthorityText)));
        
        $sourceLower = mb_strtolower($source);
        $authorityLevel = 'low'; // low = opportunity for you!
        foreach ($majorAuthoritySources as $auth) {
            if (!empty($auth) && str_contains($sourceLower, $auth)) { $authorityLevel = 'major'; break; }
        }
        if ($authorityLevel === 'low') {
            foreach ($midAuthoritySources as $auth) {
                if (!empty($auth) && str_contains($sourceLower, $auth)) { $authorityLevel = 'mid'; break; }
            }
        }
        
        // ─── 2. FRESHNESS SCORE (25% weight) ───
        $ageHours = max(0, (time() - strtotime($pubDate)) / 3600);
        $freshnessScore = 0;
        if ($ageHours < 0.5) $freshnessScore = 100;
        elseif ($ageHours < 1) $freshnessScore = 90;
        elseif ($ageHours < 2) $freshnessScore = 75;
        elseif ($ageHours < 6) $freshnessScore = 55;
        elseif ($ageHours < 12) $freshnessScore = 35;
        elseif ($ageHours < 24) $freshnessScore = 20;
        else $freshnessScore = 5;

        // ─── 3. AUTHORITY GAP SCORE (15% weight) ───
        $authorityGapScore = match($authorityLevel) {
            'major' => 20, 
            'mid'   => 55,
            'low'   => 90,
            default => 50,
        };
        
        // ─── 4. VIRALITY SIGNALS ───
        $viralityScore = 0;
        $breakingKeywords = ['عاجل', 'انفراد', 'حصري', 'خاص', 'لأول مرة', 'breaking', 'exclusive', 'just in', 'urgent', 'developing'];
        foreach ($breakingKeywords as $bk) { if (str_contains($text, $bk)) { $viralityScore += 20; break; } }
        
        $viralTopics = ['وفاة', 'مقتل', 'زلزال', 'انفجار', 'اعتقال', 'استقالة', 'إقالة', 'فضيحة', 'تسريب', 'death', 'earthquake', 'explosion', 'arrest', 'scandal', 'crash'];
        foreach ($viralTopics as $vt) { if (str_contains($text, $vt)) { $viralityScore += 15; break; } }
        
        $curiosityTriggers = ['لماذا', 'كيف', 'ما حقيقة', 'هل يمكن', 'السبب', 'الحقيقة', 'المفاجأة', 'why', 'how', 'truth behind', 'shocking'];
        foreach ($curiosityTriggers as $ct) { if (str_contains($text, $ct)) { $viralityScore += 10; break; } }
        
        if (preg_match('/\d+/', $title)) $viralityScore += 5;
        $viralityScore = min(100, $viralityScore);
        
        // ─── 5. CONTENT SATURATION ESTIMATE ───
        $titleWords = array_filter(preg_split('/\s+/u', $titleLower), fn($w) => mb_strlen($w, 'UTF-8') >= 3);
        $uniqueWordCount = count(array_unique($titleWords));
        $specificityScore = min(100, $uniqueWordCount * 12);
        $serpSaturationScore = max(20, $specificityScore);
        
        // ─── 6. DYNAMIC COMPOSITE SCORING ───
        $weightVirality  = (int) \App\Models\Setting::get('global-news-monitor_weight_virality', 35);
        $weightFreshness = (int) \App\Models\Setting::get('global-news-monitor_weight_freshness', 25);
        $weightSerp      = (int) \App\Models\Setting::get('global-news-monitor_weight_serp', 25);
        $weightAuthority = (int) \App\Models\Setting::get('global-news-monitor_weight_authority', 15);
        
        $thresholdHigh     = (int) \App\Models\Setting::get('global-news-monitor_threshold_high', 70);
        $thresholdModerate = (int) \App\Models\Setting::get('global-news-monitor_threshold_moderate', 45);

        $rankingOpportunity = (int) round(
            ($viralityScore       * ($weightVirality / 100)) + 
            ($freshnessScore      * ($weightFreshness / 100)) + 
            ($serpSaturationScore * ($weightSerp / 100)) + 
            ($authorityGapScore   * ($weightAuthority / 100))
        );
        
        // Rescale if weights don't sum to exactly 100 (Safety)
        $totalWeight = $weightVirality + $weightFreshness + $weightSerp + $weightAuthority;
        if ($totalWeight > 0 && $totalWeight != 100) {
            $rankingOpportunity = (int) round(($rankingOpportunity / $totalWeight) * 100);
        }

        $rankingOpportunity = min(100, max(0, $rankingOpportunity));
        
        // Classify opportunity level dynamically
        $opportunityLevel = 'low';
        if ($rankingOpportunity >= $thresholdHigh) $opportunityLevel = 'high';
        elseif ($rankingOpportunity >= $thresholdModerate) $opportunityLevel = 'moderate';
        
        // Determine trend direction based on freshness + virality
        $trendDirection = 'stable';
        if ($ageHours < 2 && $viralityScore >= 30) $trendDirection = 'rising_fast';
        elseif ($ageHours < 6 && $viralityScore >= 15) $trendDirection = 'rising';
        elseif ($ageHours > 12) $trendDirection = 'declining';
        
        // ─── 7. ENHANCED SENTIMENT ANALYSIS ───
        $sentiment = 'neutral';
        $positiveKeywords = [
            'إنجاز', 'ارتفاع', 'نمو', 'نجاح', 'تطور', 'اتفاق', 'تعاون', 'افتتاح', 'فوز', 'أرباح',
            'تحسن', 'انتصار', 'إطلاق', 'اعتماد', 'شراكة', 'تمويل', 'رقم قياسي', 'زيادة', 'ابتكار', 'جائزة',
            'success', 'growth', 'launch', 'win', 'profit', 'record', 'breakthrough', 'partnership', 'innovation', 'award',
            'achievement', 'progress', 'deal', 'agreement', 'surge', 'milestone', 'upgrade',
        ];
        $negativeKeywords = [
            'أزمة', 'انهيار', 'انخفاض', 'وفاة', 'مقتل', 'انفجار', 'تراجع', 'خسارة', 'توقف', 'إضراب', 'شكوى',
            'كارثة', 'حريق', 'حرب', 'هجوم', 'اعتقال', 'فضيحة', 'تسريح', 'إفلاس', 'عقوبات', 'تهديد', 'اختراق',
            'crisis', 'crash', 'death', 'killed', 'explosion', 'decline', 'loss', 'collapse', 'war', 'attack',
            'arrest', 'scandal', 'layoff', 'bankruptcy', 'sanctions', 'threat', 'breach', 'fire', 'disaster',
        ];
        
        $posCount = 0; $negCount = 0;
        foreach ($positiveKeywords as $p) if (str_contains($text, $p)) $posCount++;
        foreach ($negativeKeywords as $n) if (str_contains($text, $n)) $negCount++;
        
        if ($posCount > $negCount) $sentiment = 'positive';
        elseif ($negCount > $posCount) $sentiment = 'negative';

        // ─── 8. IMPROVED ENTITY EXTRACTION ───
        $entities = [];
        $stopWords = [
            // Arabic
            'هذا', 'هذه', 'على', 'منذ', 'بعد', 'قبل', 'التي', 'الذي', 'الذين', 'الوطن',
            'عليه', 'عليها', 'يوم', 'خلال', 'حول', 'أمام', 'أكثر', 'يمكن', 'عبر', 'حيث',
            'أثناء', 'ضمن', 'وسط', 'صباح', 'مساء', 'اليوم', 'الآن', 'حتى', 'لكن',
            // English
            'about', 'after', 'their', 'these', 'those', 'could', 'would', 'should', 'where',
            'there', 'being', 'which', 'while', 'other', 'under', 'every', 'first', 'since',
        ];
        $words = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $title));
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) > 3 && !in_array(mb_strtolower($word), $stopWords)) {
                // Prefer words that start with uppercase (proper nouns) or Arabic words > 4 chars
                if (preg_match('/^\p{Lu}/u', $word) || mb_strlen($word) > 4) {
                    $entities[] = $word;
                }
            }
            if (count($entities) >= 5) break;
        }
        // Deduplicate
        $entities = array_values(array_unique($entities));
        $timeAgo = '';
        try {
            $timeAgo = \Carbon\Carbon::parse($pubDate)->diffForHumans();
        } catch (\Throwable $e) {
            $timeAgo = '';
        }

        return [
            'seo_score'           => $rankingOpportunity,
            'opportunity_level'   => $opportunityLevel,    // high / moderate / low
            'trend_direction'     => $trendDirection,       // rising_fast / rising / stable / declining
            'sentiment'           => $sentiment,
            'entities'            => $entities,
            'is_high_authorative' => ($authorityLevel !== 'low'),
            'authority_level'     => $authorityLevel,
            'freshness_score'     => $freshnessScore,
            'virality_score'      => $viralityScore,
            'age_hours'           => round($ageHours, 1),
            'time_ago'            => $timeAgo,
        ];
    }

    /**
     * ══════════════════════════════════════════════════════════════
     *  ON-DEMAND AI DEEP ANALYSIS (per article)
     *  Called via AJAX when user clicks "Analyze This"
     * ══════════════════════════════════════════════════════════════
     */
    public function analyzeArticleWithAI(string $title, string $description, string $country, string $lang, string $topic): array
    {
        $aiManager = app(\App\Core\AI\AIManager::class);
        
        $isArabic = ($lang === 'ar');
        
        // Fetch custom prompt from settings
        $customPrompt = \App\Models\Setting::get('global-news-monitor_ai_analysis_prompt', '');
        
        $langName = ($lang === 'ar') ? 'Arabic (العربية)' : 'English';
        $languageInstruction = "Crucial: Your entire response (ranking_reason, suggested_angle, suggested_keywords, etc.) MUST be in {$langName}.";

        $prompt = !empty($customPrompt) 
            ? str_replace(
                ['[Title]', '[Description]', '[Country]', '[Topic]', '[Lang]'],
                [$title, $description, $country, $topic, $lang],
                $customPrompt
            )
            : ($isArabic 
                ? "أنت محلل SEO محترف. حلّل هذا الخبر وأجب بصيغة JSON فقط بدون أي نص إضافي.\n\nالعنوان: {$title}\nالوصف: {$description}\nالدولة: {$country}\nالقسم: {$topic}\n\nأريد JSON بهذا الشكل بالضبط:\n{\n  \"ranking_opportunity\": \"high|moderate|low\",\n  \"ranking_reason\": \"سبب مختصر في سطر واحد بالعربية\",\n  \"suggested_angle\": \"زاوية محتوى فريدة مقترحة للتغطية بالعربية\",\n  \"suggested_keywords\": [\"كلمة1\", \"كلمة2\", \"كلمة3\", \"كلمة4\", \"كلمة5\"],\n  \"content_type\": \"مقال إخباري سريع|تحليل معمق|فيديو قصير|إنفوجرافيك\",\n  \"estimated_search_volume\": \"high|medium|low\",\n  \"competition_level\": \"high|medium|low\",\n  \"recommended_action\": \"اكتب الآن|راقب أولاً|تجاوز\"\n}"
                : "You are a professional SEO analyst. Analyze this news article and respond WITH JSON ONLY in English, no extra text.\n\nTitle: {$title}\nDescription: {$description}\nCountry: {$country}\nTopic: {$topic}\n\nReturn JSON in this EXACT format:\n{\n  \"ranking_opportunity\": \"high|moderate|low\",\n  \"ranking_reason\": \"Brief one-line reason\",\n  \"suggested_angle\": \"A unique content angle to cover this story\",\n  \"suggested_keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],\n  \"content_type\": \"quick news article|deep analysis|short video|infographic\",\n  \"estimated_search_volume\": \"high|medium|low\",\n  \"competition_level\": \"high|medium|low\",\n  \"recommended_action\": \"write now|monitor first|skip\"\n}");

        $prompt .= "\n\n" . $languageInstruction;

        try {
            $result = $aiManager->generate('global-news-monitor', $prompt, [
                'temperature' => 0.2,
                'max_tokens'  => 1000,
            ]);
            
            $responseText = $result['text'] ?? '';
            
            // Extract JSON from response (handle markdown code blocks)
            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $responseText, $matches)) {
                $responseText = trim($matches[1]);
            }
            $responseText = trim($responseText);
            
            $parsed = json_decode($responseText, true);
            
            if ($parsed && isset($parsed['ranking_opportunity'])) {
                return [
                    'success' => true,
                    'analysis' => $parsed,
                ];
            }
            
            Log::warning('[NewsMonitor AI] Failed to parse AI response: ' . substr($responseText, 0, 500));
            return ['success' => false, 'message' => 'AI response could not be parsed.'];
            
        } catch (\Exception $e) {
            Log::error('[NewsMonitor AI] Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check if a topic matches current Google Trends
     * Returns: array of matching trend titles (empty if no match)
     */
    public function checkGoogleTrends(string $keyword, string $region = 'EG'): array
    {
        $cacheKey = "google_trends_monitor_{$region}";
        
        $trends = Cache::remember($cacheKey, 600, function () use ($region) {
            try {
                $url = "https://trends.google.com/trending/rss?geo={$region}&sort=recency";
                $response = Http::timeout(8)->get($url);
                if ($response->failed()) return [];
                
                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->channel->item)) return [];
                
                $items = [];
                foreach ($xml->channel->item as $item) {
                    $items[] = [
                        'title'   => (string) $item->title,
                        'traffic' => (string) ($item->children('ht', true)->approx_traffic ?? ''),
                    ];
                }
                return $items;
            } catch (\Exception $e) {
                Log::warning('[NewsMonitor] Trends fetch failed: ' . $e->getMessage());
                return [];
            }
        });
        
        if (empty($trends)) return [];
        
        $keywordLower = mb_strtolower($keyword);
        $keywordWords = array_filter(preg_split('/\s+/u', $keywordLower), fn($w) => mb_strlen($w) >= 3);
        
        $matches = [];
        foreach ($trends as $trend) {
            $trendLower = mb_strtolower($trend['title']);
            
            // Check word overlap
            $trendWords = array_filter(preg_split('/\s+/u', $trendLower), fn($w) => mb_strlen($w) >= 3);
            $common = count(array_intersect($keywordWords, $trendWords));
            
            if ($common >= 1 || str_contains($trendLower, $keywordLower) || str_contains($keywordLower, $trendLower)) {
                $matches[] = $trend;
            }
        }
        
        return $matches;
    }

    /**
     * Extract keywords/entities from a single news title (rule-based NLP).
     *
     * @return array<int, string>
     */
    public function extractKeywordsFromTitle(string $title): array
    {
        $stopWords = [
            'هذا', 'هذه', 'على', 'منذ', 'بعد', 'قبل', 'التي', 'الذي', 'الذين', 'الوطن',
            'عليه', 'عليها', 'يوم', 'خلال', 'حول', 'أمام', 'أكثر', 'يمكن', 'عبر', 'حيث',
            'أثناء', 'ضمن', 'وسط', 'صباح', 'مساء', 'اليوم', 'الآن', 'حتى', 'لكن',
            'about', 'after', 'their', 'these', 'those', 'could', 'would', 'should', 'where',
            'there', 'being', 'which', 'while', 'other', 'under', 'every', 'first', 'since',
            'with', 'from', 'that', 'this', 'have', 'been', 'will', 'into', 'over', 'says',
        ];

        $entities = [];
        $words = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $title));
        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) > 3 && ! in_array(mb_strtolower($word), $stopWords, true)) {
                if (preg_match('/^\p{Lu}/u', $word) || mb_strlen($word) > 4) {
                    $entities[] = $word;
                }
            }
            if (count($entities) >= 8) {
                break;
            }
        }

        return array_values(array_unique($entities));
    }

    /**
     * Aggregate and deduplicate keywords across multiple titles.
     *
     * @param  array<int, string>  $titles
     * @return array<int, string>
     */
    public function extractKeywordsFromTitles(array $titles): array
    {
        $combined = [];
        foreach ($titles as $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }
            foreach ($this->extractKeywordsFromTitle($title) as $keyword) {
                $key = mb_strtolower($keyword);
                if (! isset($combined[$key])) {
                    $combined[$key] = $keyword;
                }
            }
        }

        return array_values($combined);
    }

    /**
     * Generate a unified content brief from multiple selected news titles.
     *
     * @param  array<int, string>  $titles
     * @param  array<int, string>  $keywords
     */
    public function generateMultiTitleBrief(array $titles, array $keywords, string $country, string $lang, string $topic): array
    {
        $aiManager = app(\App\Core\AI\AIManager::class);
        $isArabic = ($lang === 'ar');
        $langName = $isArabic ? 'Arabic (العربية)' : 'English';

        $titlesList = implode("\n", array_map(
            fn ($t, $i) => ($i + 1) . '. ' . $t,
            $titles,
            array_keys($titles)
        ));
        $keywordsList = implode(', ', $keywords);

        $jsonContract = '{
  "headline": "Suggested unified headline",
  "summary": "2-3 sentence overview tying the stories together",
  "key_themes": ["theme 1", "theme 2"],
  "target_audience": "Who should read this content",
  "content_outline": ["Section 1: ...", "Section 2: ...", "Section 3: ..."],
  "suggested_keywords": ["kw1", "kw2", "kw3"],
  "recommended_angle": "Unique editorial angle",
  "recommended_format": "news article|analysis|listicle|video script"
}';

        $prompt = $isArabic
            ? "أنت محرر محتوى محترف. أنشئ موجز محتوى موحد يربط بين العناوين الإخبارية المختارة.

العناوين المختارة:
{$titlesList}

الكلمات المفتاحية المستخرجة: {$keywordsList}
الدولة: {$country}
القسم: {$topic}

أجب بصيغة JSON فقط:
{$jsonContract}

Crucial: كل الحقول بالعربية."
            : "You are a professional content editor. Create a unified content brief that connects the selected news headlines.

Selected titles:
{$titlesList}

Extracted keywords: {$keywordsList}
Country: {$country}
Topic: {$topic}

Return JSON ONLY:
{$jsonContract}

Crucial: Response must be in {$langName}.";

        try {
            $result = $aiManager->generate('global-news-monitor', $prompt, [
                'temperature' => 0.35,
                'max_tokens'  => 1200,
            ]);

            $responseText = $result['text'] ?? '';
            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $responseText, $matches)) {
                $responseText = trim($matches[1]);
            }
            $responseText = trim($responseText);

            $parsed = json_decode($responseText, true);
            if ($parsed && isset($parsed['summary'])) {
                foreach (['key_themes', 'content_outline', 'suggested_keywords'] as $key) {
                    if (! isset($parsed[$key]) || ! is_array($parsed[$key])) {
                        $parsed[$key] = [];
                    }
                }

                return ['success' => true, 'brief' => $parsed];
            }

            Log::warning('[NewsMonitor Brief] Failed to parse AI response: ' . substr($responseText, 0, 500));

            return ['success' => false, 'message' => 'AI brief could not be parsed. Please try again.'];
        } catch (\Exception $e) {
            Log::error('[NewsMonitor Brief] Error: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
