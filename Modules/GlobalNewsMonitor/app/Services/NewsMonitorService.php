<?php

namespace Modules\GlobalNewsMonitor\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NewsMonitorService
{
    /**
     * Fetch News from Google News RSS
     */
    public function fetchGoogleNews($country = 'EG', $topic = 'WORLD', $lang = 'ar', $timeWindow = '12h', $countryName = '')
    {
        $country = strtoupper($country);
        $topic = strtoupper($topic);
        
        // Topic to Search Query Mapping (High-Precision synonyms)
        $isArabic = ($lang === 'ar');
        
        $topicQueries = $isArabic ? [
            'WORLD' => 'أخبار العالم OR دولية OR "أحداث عالمية" OR "عاجل دولي" OR دبلوماسية',
            'NATION' => 'أخبار محلية OR عاجل OR حوادث OR "أخبار الوطن"',
            'BUSINESS' => 'اقتصاد OR أعمال OR شركات OR استثمار OR أسواق OR بنوك',
            'TECHNOLOGY' => 'تكنولوجيا OR تقنية OR ذكاء اصطناعي OR تطبيقات OR آبل OR جوجل OR مايكروسوفت',
            'ENTERTAINMENT' => 'فن OR مشاهير OR سينما OR أفلام OR مسلسلات OR نجوم',
            'SPORTS' => 'رياضة OR كورة OR دوري أبطال OR مباريات OR أهداف OR فيفا',
            'SCIENCE' => 'علوم OR فضاء OR اكتشافات OR ناسا OR بحث علمي',
            'HEALTH' => 'صحة OR طب OR طبيب OR أمراض OR علاج OR رعاية صحية',
        ] : [
            'WORLD' => 'World News OR International OR Global Events OR Breaking News OR Diplomacy',
            'NATION' => 'Local News OR National OR Breaking OR Home News',
            'BUSINESS' => 'Business OR Economy OR Markets OR Finance OR Investing OR Startup OR Banking',
            'TECHNOLOGY' => 'Technology OR Tech OR AI OR Artificial Intelligence OR Software OR Apple OR Google OR Microsoft',
            'ENTERTAINMENT' => 'Entertainment OR Movies OR Celebrity OR Music OR Cinema OR TV Shows OR Netflix',
            'SPORTS' => 'Sports OR Football OR Soccer OR NBA OR Premier League OR Champions League OR Match',
            'SCIENCE' => 'Science OR Research OR Space OR Discovery OR Environment OR NASA',
            'HEALTH' => 'Health OR Medicine OR Wellness OR Hospitals OR Treatment OR Medical News',
        ];
        
        $hasTranslation = in_array($lang, ['ar', 'en']);

        // Cross-Category Inhibition Map (Hard blocks for specific sections)
        $inhibitionKeywords = $isArabic ? [
            'ENTERTAINMENT' => ['مباراة', 'دوري', 'كورة', 'هدف', 'مدرب', 'لاعب', 'نادي'],
            'TECHNOLOGY' => ['وزير', 'رئيس', 'مقتل', 'جريمة', 'حرب', 'اعتقال'],
            'BUSINESS' => ['مهرجان', 'حفلة', 'فيلم', 'مسلسل', 'مباراة'],
        ] : [
            'ENTERTAINMENT' => ['match', 'league', 'score', 'football', 'soccer', 'nba', 'player', 'team'],
            'TECHNOLOGY' => ['minister', 'president', 'police', 'crime', 'murder', 'war', 'killed'],
            'BUSINESS' => ['entertainment', 'concert', 'movie', 'actor', 'football'],
        ];

        // High-Ambiguity Terms (Penalty unless verified)
        $ambiguityTerms = $isArabic 
            ? ['نجوم', 'بطل', 'أسطورة', 'ملك', 'ملكة', 'نمو', 'تراجع']
            : ['stars', 'hero', 'legend', 'king', 'queen'];

        // Geography Database for Isolation (Radical Strictness)
        $geoDb = [
            'EG' => ['name' => 'مصر', 'cities' => ['القاهرة', 'الإسكندرية', 'الجيزة']],
            'SA' => ['name' => 'السعودية', 'cities' => ['الرياض', 'جدة', 'مكة', 'المدينة']],
            'AE' => ['name' => 'الإمارات', 'cities' => ['دبي', 'أبوظبي', 'الشارقة']],
            'DZ' => ['name' => 'الجزائر', 'cities' => ['الجزائر', 'وهران', 'قسنطينة']],
            'MA' => ['name' => 'المغرب', 'cities' => ['الرباط', 'الدار البيضاء', 'مراكش']],
            'LY' => ['name' => 'ليبيا', 'cities' => ['طرابلس', 'بنغازي']],
            'TN' => ['name' => 'تونس', 'cities' => ['تونس العاصمة', 'سوسة']],
            'PS' => ['name' => 'فلسطين', 'cities' => ['غزة', 'القدس', 'رام الله']],
            'LB' => ['name' => 'لبنان', 'cities' => ['بيروت', 'طرابلس']],
            'SY' => ['name' => 'سوريا', 'cities' => ['دمشق', 'حلب']],
            'JO' => ['name' => 'الأردن', 'cities' => ['عمان', 'إربد']],
            'IQ' => ['name' => 'العراق', 'cities' => ['بغداد', 'البصرة', 'الموصل']],
            'KW' => ['name' => 'الكويت', 'cities' => ['الكويت العاصمة']],
            'QA' => ['name' => 'قطر', 'cities' => ['الدوحة']],
            'BH' => ['name' => 'البحرين', 'cities' => ['المنامة']],
            'OM' => ['name' => 'عمان', 'cities' => ['مسقط', 'صلالة']],
            'YE' => ['name' => 'اليمن', 'cities' => ['صنعاء', 'عدن', 'المكلا']],
            'US' => ['name' => 'USA', 'cities' => ['New York', 'Washington', 'California', 'Texas', 'Florida', 'Chicago', 'America']],
            'GB' => ['name' => 'UK', 'cities' => ['London', 'Manchester', 'United Kingdom', 'England', 'Birmingham']],
            'FR' => ['name' => 'France', 'cities' => ['Paris', 'Lyon', 'Marseille', 'French']],
            'PL' => ['name' => 'Poland', 'cities' => ['Warsaw', 'Krakow', 'Lodz', 'Wroclaw', 'Poznan', 'Gdansk']],
        ];

        $targetCountry = $geoDb[$country] ?? ['name' => $countryName, 'cities' => []];
        $allArabicCountries = array_column($geoDb, 'name');
        $blacklistCountries = array_diff($allArabicCountries, [$targetCountry['name']]);

        $searchQuery = $topicQueries[$topic] ?? $topic;
        $rawNews = [];
        
        // Strategy 1: Topic-Specific Headlines (Official Categorical RSS)
        if ($topic === 'GENERAL') {
            $urlTopic = "https://news.google.com/rss?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        } else {
            $urlTopic = "https://news.google.com/rss/headlines/section/topic/{$topic}?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        }
        $rawNews = $this->fetchFromUrl($urlTopic, $rawNews);

        // Strategy 2: Keyword-Based Search (with Tiered Time Windows & Soft Geo-Anchoring)
        // Skip keyword search for unsupported languages (like Polish) to prevent zero-results from English keyword injection.
        if ($hasTranslation) {
            $windows = [$timeWindow, '24h', '48h', '7d'];
            foreach ($windows as $window) {
                $geoPrefix = !empty($targetCountry['name']) ? "{$targetCountry['name']} " : "";
                $cleanGeoPrefix = preg_replace('/[\x{1F1E6}-\x{1F1FF}]{2}/u', '', $geoPrefix);
                $urlSearch = "https://news.google.com/rss/search?q=" . urlencode(trim($cleanGeoPrefix) . " ({$searchQuery}) when:{$window}") . "&hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
                $rawNews = $this->fetchFromUrl($urlSearch, $rawNews);
                if (count($rawNews) >= 60) break;
            }
        }

        // Strategy 3: RADICAL CROSS-CATEGORY VALIDATION
        $validatedNews = [];
        $topicKeywords = array_map(function($k) {
            return mb_strtolower(trim(str_replace(['(', ')', '"'], '', $k)));
        }, explode(' OR ', $topicQueries[$topic] ?? ''));

        $blockKeywords = $inhibitionKeywords[$topic] ?? [];
        
        foreach ($rawNews as $link => $item) {
            $geoScore = 0;
            $topicalScore = 0;
            $titleText = mb_strtolower($item['title']);
            $descText = mb_strtolower($item['description']);
            $fullText = $titleText . ' ' . $descText;
            
            // 1. Geographic Check
            $cleanTargetName = preg_replace('/[\x{1F1E6}-\x{1F1FF}]{2}/u', '', $targetCountry['name']);
            if (!empty($cleanTargetName) && str_contains($fullText, mb_strtolower(trim($cleanTargetName)))) {
                $geoScore += 15;
            }
            foreach ($targetCountry['cities'] as $city) {
                if (str_contains($fullText, mb_strtolower($city))) {
                    $geoScore += 10;
                }
            }
            
            // 2. Cross-Category INHIBITION (Hard Block)
            foreach ($blockKeywords as $block) {
                if (str_contains($fullText, $block)) {
                    // Critical Leak Detection: If matching a block word from a competing niche, discard immediately.
                    continue 2; 
                }
            }

            // 3. Ambiguity Resolution
            $ambiguityDetected = false;
            foreach ($ambiguityTerms as $term) {
                if (str_contains($fullText, $term)) {
                    $ambiguityDetected = true;
                    break;
                }
            }

            // 4. ULTRA-STRICT MANDATORY TOPICAL CHECK
            $isSpecialized = !in_array($topic, ['WORLD', 'NATION']);
            $hasPrimaryQualifier = false;

            foreach ($topicKeywords as $kw) {
                if (str_contains($titleText, $kw)) {
                    // Only count as primary if it's not a generic ambiguity term
                    if (!in_array($kw, $ambiguityTerms)) {
                        $hasPrimaryQualifier = true;
                        $topicalScore += 25;
                    } else {
                        $topicalScore += 5; // Weak match
                    }
                } elseif (str_contains($descText, $kw)) {
                    $topicalScore += 5;
                }
            }

            // --- RADICAL ACCURACY POLICY ---
            // Only apply strict policy if we have a translation dictionary for this language
            if ($hasTranslation) {
                if ($isSpecialized) {
                    // Specialized category MUST have at least one UNAMBIGUOUS primary qualifier in the Title
                    if (!$hasPrimaryQualifier) {
                        continue; 
                    }
                    // Penalty for general noise (Politics) in Tech/Entertainment/Health
                    $noiseKeywords = $isArabic ? ['وزير', 'رئيس', 'حكومة'] : ['minister', 'president', 'government'];
                    foreach ($noiseKeywords as $noise) {
                        if (str_contains($fullText, $noise)) {
                            $topicalScore -= 20;
                        }
                    }
                    
                    if ($topicalScore < 15) {
                        continue;
                    }
                }
                
                if (($geoScore + $topicalScore) < 5 && $topic !== 'WORLD') {
                    continue;
                }
            }

            $validatedNews[] = $item;
        }

        // Final Sort from newest to oldest based on pubDate
        usort($validatedNews, function($a, $b) {
            return strtotime($b['pubDate']) <=> strtotime($a['pubDate']);
        });

        // Final Deduplication by Title
        $seenTitles = [];
        $finalNews = [];
        foreach ($validatedNews as $item) {
            $titleKey = mb_strtolower(preg_replace('/\s+/', '', $item['title']));
            if (!in_array($titleKey, $seenTitles)) {
                $seenTitles[] = $titleKey;
                $finalNews[] = $item;
            }
            if (count($finalNews) >= 60) break;
        }

        return $finalNews;
    }

    /**
     * Helper to fetch and merge news from a specific RSS URL
     */
    protected function fetchFromUrl($url, $existingNews = [])
    {
        try {
            $response = Http::timeout(8)->get($url);
            if ($response->failed()) return $existingNews;

            $xml = @simplexml_load_string($response->body());
            if (!$xml || !isset($xml->channel->item)) return $existingNews;

            foreach ($xml->channel->item as $item) {
                $link = (string) $item->link;
                if (isset($existingNews[$link])) continue; // Deduplicate by URL

                $title = (string) $item->title;
                $description = (string) $item->description;
                $cleanTitle = preg_replace('/\s*[-|–—]\s*[^-|–—]*$/u', '', $title);
                $sourceName = (string) ($item->source ?? '');
                
                // --- PROFESSIONAL SEO ANALYSIS ENGINE ---
                $seoData = $this->analyzeSeoPotential($cleanTitle, $description, (string)$item->pubDate, $sourceName);

                $existingNews[$link] = array_merge([
                    'title' => trim($cleanTitle),
                    'link' => $link,
                    'pubDate' => (string) $item->pubDate,
                    'source' => $sourceName,
                    'description' => $description,
                ], $seoData);
            }
        } catch (\Exception $e) {
            Log::error("Global News Monitor Fetch Error: " . $e->getMessage());
        }

        return $existingNews;
    }

    /**
     * Advanced SEO Analytics: Scoring, Sentiment, and Entities
     */
    protected function analyzeSeoPotential($title, $desc, $pubDate, $source)
    {
        $text = mb_strtolower($title . ' ' . $desc);
        
        // 1. Authority Sources (Weighting)
        $authoritySources = [
            'سكاي نيوز', 'الجزيرة', 'العربية', 'رويترز', 'bbc', 'cnn', 'فرانس 24', 'الشرق', 'اندبندنت', 'اليوم السابع', 'البيان', 'الخليج',
            'reuters', 'ny times', 'washington post', 'guardian', 'bloomberg', 'forbes', 'techcrunch', 'wired', 'verge'
        ];
        $isAuthority = false;
        foreach ($authoritySources as $auth) {
            if (str_contains(mb_strtolower($source), $auth)) {
                $isAuthority = true;
                break;
            }
        }

        // 2. SEO Scoring (1-100)
        $score = 40; // Baseline
        $ageHours = (time() - strtotime($pubDate)) / 3600;
        
        if ($ageHours < 1) $score += 30;
        elseif ($ageHours < 6) $score += 20;
        elseif ($ageHours < 24) $score += 10;
        
        if ($isAuthority) $score += 15;
        if (str_contains($text, 'عاجل') || str_contains($text, 'انفراد')) $score += 15;
        
        // 3. Sentiment Analysis (Heuristic)
        $sentiment = 'neutral';
        $positive = ['إنجاز', 'ارتفاع', 'نمو', 'نجاح', 'تطور', 'اتفاق', 'تعاون', 'افتتاح', 'فوز', 'أرباح'];
        $negative = ['أزمة', 'انهيار', 'انخفاض', 'وفاة', 'مقتل', 'انفجار', 'تراجع', 'خسارة', 'توقف', 'إضراب', 'شكوى'];
        
        $posCount = 0; $negCount = 0;
        foreach ($positive as $p) if (str_contains($text, $p)) $posCount++;
        foreach ($negative as $n) if (str_contains($text, $n)) $negCount++;
        
        if ($posCount > $negCount) $sentiment = 'positive';
        elseif ($negCount > $posCount) $sentiment = 'negative';

        // 4. Entity Extraction (Key Concepts)
        $entities = [];
        // Extract 2-3 significant words (longer than 4 chars, not common)
        $words = explode(' ', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $title));
        foreach ($words as $word) {
            if (mb_strlen($word) > 4 && !in_array($word, ['هذا', 'على', 'منذ', 'بعد', 'قبل'])) {
                $entities[] = $word;
            }
            if (count($entities) >= 3) break;
        }

        return [
            'seo_score' => min(100, $score),
            'sentiment' => $sentiment,
            'entities' => $entities,
            'is_high_authorative' => $isAuthority
        ];
    }
}
