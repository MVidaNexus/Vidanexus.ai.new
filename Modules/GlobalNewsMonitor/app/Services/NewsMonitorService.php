<?php

namespace Modules\GlobalNewsMonitor\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NewsMonitorService
{
    /**
     * Fetch News from Google News RSS — Topic-Pure Direct Pull
     * Pulls ONLY from the specific Google News topic RSS for the selected section.
     * This mirrors the exact experience of browsing Google News topics directly.
     */
    public function fetchGoogleNews($country = 'EG', $topic = 'WORLD', $lang = 'ar', $timeWindow = '12h', $countryName = '')
    {
        $country = strtoupper($country);
        $topic = strtoupper($topic);

        $rawNews = [];
        
        // ─── PRIMARY SOURCE: Google News Topic RSS (Exactly like Google News) ───
        // This is the ONLY source to ensure topic purity — no mixing of unrelated news
        if ($topic === 'GENERAL' || $topic === 'TOP_STORIES') {
            $url = "https://news.google.com/rss?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        } else {
            $url = "https://news.google.com/rss/headlines/section/topic/{$topic}?hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
        }
        $rawNews = $this->fetchFromUrl($url, $rawNews);
        Log::info("NewsMonitor [{$topic}]: Topic RSS returned " . count($rawNews) . " articles for {$country}");

        // Topic-pure search fallback — ONLY if Topic RSS returned very few results
        // Uses the exact Google News topic name to maintain topic relevance
        if (count($rawNews) < 5 && $topic !== 'GENERAL' && $topic !== 'TOP_STORIES') {
            $topicSearchMap = [
                'WORLD'         => ['ar' => 'أخبار دولية',      'en' => 'world news'],
                'NATION'        => ['ar' => 'أخبار محلية',       'en' => 'national news'],
                'BUSINESS'      => ['ar' => 'تجارة وأعمال',      'en' => 'business news'],
                'TECHNOLOGY'    => ['ar' => 'تكنولوجيا',         'en' => 'technology news'],
                'ENTERTAINMENT' => ['ar' => 'فن وترفيه',         'en' => 'entertainment news'],
                'SPORTS'        => ['ar' => 'رياضة',             'en' => 'sports news'],
                'SCIENCE'       => ['ar' => 'علوم',              'en' => 'science news'],
                'HEALTH'        => ['ar' => 'صحة وطب',           'en' => 'health news'],
            ];
            
            $searchTerm = $topicSearchMap[$topic][$lang] ?? $topicSearchMap[$topic]['en'] ?? $topic;
            $urlSearch = "https://news.google.com/rss/search?q=" . urlencode($searchTerm) . "&hl={$lang}&gl={$country}&ceid={$country}:{$lang}";
            $rawNews = $this->fetchFromUrl($urlSearch, $rawNews);
            Log::info("NewsMonitor [{$topic}]: Topic-pure search fallback → total now " . count($rawNews) . " for {$country}");
        }

        // Freshness Filter: Only keep articles within the configured time window
        $maxAge = max($this->parseTimeWindow($timeWindow), 86400); // minimum 24h
        $cutoffTime = time() - $maxAge;

        $freshNews = [];
        foreach ($rawNews as $link => $item) {
            $pubTime = strtotime($item['pubDate']);
            if ($pubTime && $pubTime >= $cutoffTime) {
                $freshNews[] = $item;
            }
        }

        // Sort from newest to oldest
        usort($freshNews, function($a, $b) {
            return strtotime($b['pubDate']) <=> strtotime($a['pubDate']);
        });

        // Deduplication by Title
        $seenTitles = [];
        $finalNews = [];
        foreach ($freshNews as $item) {
            $titleKey = mb_strtolower(preg_replace('/\s+/', '', $item['title']));
            if (!in_array($titleKey, $seenTitles)) {
                $seenTitles[] = $titleKey;
                $finalNews[] = $item;
            }
            if (count($finalNews) >= 100) break;
        }

        Log::info("NewsMonitor [{$topic}]: Final count = " . count($finalNews) . " articles for {$country}");
        return $finalNews;
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
        if (count($entities) > 4) $entities = array_slice($entities, 0, 4);

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
}
