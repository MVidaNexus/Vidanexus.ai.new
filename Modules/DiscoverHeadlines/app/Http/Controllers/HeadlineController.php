<?php

namespace Modules\DiscoverHeadlines\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\AI\AIManager;
use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
use App\Models\Wallet;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HeadlineController extends Controller
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Show the headlines generation page.
     */
    public function index(Request $request)
    {
        $extra = [];
        $hasResults = session()->has('headlineResults') || session()->has('scoredHeadlines');
        
        if ($hasResults) {
            $extra = [
                'headlineResults' => session('headlineResults'),
                'scoredHeadlines' => session('scoredHeadlines'),
                'headlineKeyword' => session('headlineKeyword'),
                'headlineError'   => session('headlineError'),
                'headlineEmpty'   => session('headlineEmpty'),
                'prefilledKeyword' => $request->input('keyword') ?: session('headlineKeyword'),
            ];
            
            session()->forget(['headlineResults', 'scoredHeadlines', 'headlineKeyword', 'headlineError', 'headlineEmpty']);
        }

        $countryMap = $this->getCountryMap();
        $resolved = CountryRegistry::resolveRegion($request->get('country'), $countryMap, CountryRegistry::defaultRegion('en'));
        $region = $resolved['region'];
        $currentCountry = $resolved['country'];

        $data = array_merge([
            'currentCountry' => $currentCountry,
            'countryMap' => $countryMap,
            'region' => $region,
            'prefilledKeyword' => $extra['prefilledKeyword'] ?? $request->input('keyword'),
        ], $extra);

        return view('discoverheadlines::index', $data);
    }

    /**
     * Generate headlines using AI.
     */
    /**
     * Generate headlines using AI (Asynchronous Dispatcher).
     */
    public function generate(Request $request)
    {
        $type = $request->input('type', 'keyword');
        $countryMap = $this->getCountryMap();
        $resolved = CountryRegistry::resolveRegion($request->input('country'), $countryMap, CountryRegistry::defaultRegion());
        $region = $resolved['region'];
        $progressId = $request->input('progress_id') ?: 'hl_' . time();
        $variantsCount = $request->input('variants', 7);

        // Each mode must validate (and ultimately use) only its own input.
        // Previously a stale `keyword` URL parameter would silently override
        // the user's pasted content because both fields were always sent and
        // the prompt's `[Keyword]` placeholder pulled the headline away from
        // the actual context. Force-clearing the inactive field server-side
        // makes the mode authoritative regardless of what the form posted.
        $request->validate([
            'type' => 'required|string|in:keyword,content',
            'variants' => 'nullable|integer|min:3|max:15',
            'country' => 'nullable|string|size:2',
            'keyword' => $type === 'keyword' ? 'required|string|max:255' : 'nullable|string|max:255',
            'content' => $type === 'content' ? 'required|string|min:50' : 'nullable|string',
        ]);

        $keyword = $type === 'keyword' ? (string) $request->input('keyword') : '';
        $content = $type === 'content' ? (string) $request->input('content') : '';

        // Credit Check
        $user = auth()->user();

        if (!$user->canUseTool('discover-headlines')) {
            $msg = $user->getLimitReachedMessage('Discover Headlines', 'discover-headlines');
            return response()->json(['status' => 'error', 'message' => $msg], 403);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            $msg = 'Insufficient balance to generate headlines.';
            return response()->json(['status' => 'error', 'message' => $msg], 402);
        }

        // Initialize Progress
        Cache::put("gen_progress_{$progressId}", [
            'stage' => 'starting',
            'message' => 'Job queued. Starting background intelligence engine...'
        ], 300);

        // Dispatch Job
        \Modules\DiscoverHeadlines\Jobs\GenerateHeadlinesJob::dispatch($user->id, [
            'keyword' => $keyword,
            'content' => $content,
            'type' => $type,
            'country' => $region,
            'variants' => $variantsCount,
            'progress_id' => $progressId,
        ]);

        return response()->json([
            'status' => 'processing',
            'message' => 'Intelligence extraction started in the background.',
            'progress_id' => $progressId
        ]);
    }


    /**
     * Get Progress Polling
     */
    public function getProgress($id)
    {
        $data = Cache::get("gen_progress_{$id}");
        
        if (!$data) {
            return response()->json(['stage' => 'starting', 'message' => 'Starting...']);
        }

        // Ensure UTF-8 compliance for JSON
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = iconv('UTF-8', 'UTF-8//IGNORE', $item);
            }
        });

        return response()->json($data);
    }

    /**
     * Fetch news items from Google News RSS with fallback strategies.
     */
    protected function fetchNewsContext($keyword, $region, $progressId = null)
    {
        $region = CountryRegistry::normalizeCode($region) ?: CountryRegistry::defaultRegion();
        $lang = CountryRegistry::langFor($region);

        $cacheKey = 'headline_news_v2_' . $region . '_' . md5(mb_strtolower(trim($keyword)));
        $cached = Cache::get($cacheKey);
        if ($cached) {
            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'searching',
                    'message' => 'News retrieved from cache ⚡'
                ], 300);
            }
            return $cached;
        }

        // Tiered Strategy: Google News RSS (Staged by time for maximum relevance)
        $tempContext = "";
        $configuredWindow = Setting::get("discover-headlines_rss_time_window", '12h');
        
        // Build windows: prioritized start with configured window, then fall back to broader ones
        $windows = ["when:{$configuredWindow}", 'when:24h', 'when:7d', 'when:30d', 'broad'];
        // Remove duplicates if configured window is already in the list
        $windows = array_values(array_unique($windows));
        
        foreach ($windows as $window) {
            $timeParam = ($window === 'broad') ? "" : " " . $window;
            $url = GoogleNewsRss::searchUrl($keyword.$timeParam, $region, $lang);
            
            try {
                if ($progressId) {
                    Cache::put("gen_progress_{$progressId}", [
                        'stage' => 'searching',
                        'message' => "Searching news archive ({$window})..."
                    ], 300);
                }
                
                $response = Http::timeout(8)->get($url);
                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            $tempContext .= "- " . (string)$item->title . " (المصدر: " . (string)$item->source . ")\n";
                            if (substr_count($tempContext, "\n") >= 15) break;
                        }
                    }
                }
                
                // If we found enough context, stop searching deeper windows
                if (substr_count($tempContext, "\n") >= 5) {
                    Log::info("[Headlines] Found context in window: " . $window);
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("RSS Fetch Error ($window): " . $e->getMessage());
            }
        }

        $context = $tempContext;
        if (!empty($context)) {
            $ttl = Setting::get("discover-headlines_cache_ttl", 1800);
            Cache::put($cacheKey, $context, (int) $ttl);
            return $context;
        }
        
        // Fallback Strategy: s.jina.ai (If API key exists or via public proxy if allowed)
        // For now, we'll keep it simple as the user reported empty results, 
        // and the fallback "موضوع البحث: ..." in generate() handles the UX.
        
        return $context;
    }

    /**
     * The "Laws": Scoring headlines based on 12 criteria.
     */
    protected function scoreHeadlines($headlinesData, $keyword = '')
    {
        $scored = [];

        foreach ($headlinesData as $data) {
            $headline = $data['headline'] ?? '';
            if (empty($headline) || mb_strlen($headline) < 8) continue;
            
            $score = 40; // Base score
            $feedback = [];
            $len = mb_strlen($headline);

            // 1. Length (Dynamic limits from Admin)
            $minChars = (int) Setting::get("discover-headlines_min_chars", 55);
            $maxChars = (int) Setting::get("discover-headlines_max_chars", 85);
            $margin = 15; // Info range margin

            if ($len >= $minChars && $len <= $maxChars) {
                $score += 20;
                $feedback[] = ['type' => 'success', 'text' => 'Ideal Discover Length (' . $len . ' chars)'];
            } elseif ($len >= ($minChars - $margin) && $len <= ($maxChars + $margin)) {
                $score += 10;
                $feedback[] = ['type' => 'info', 'text' => 'Acceptable Length'];
            } else {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => 'Sub-optimal Length'];
            }

            // 2. Keyword & Entities (RELEVANCE CORE)
            $isRelevant = false;
            if (!empty($keyword)) {
                $keywordLower = mb_strtolower(trim($keyword));
                $headlineLower = mb_strtolower($headline);
                
                // 2a. Direct Match (+30)
                if (mb_stripos($headline, $keywordLower) !== false) {
                    $score += 30;
                    $feedback[] = ['type' => 'success', 'text' => 'Target Keyword Included (+30)'];
                    $isRelevant = true;
                } else {
                    // 2b. Fuzzy Word Overlap (At least 60% of words must appear)
                    $keywordWords = array_filter(explode(' ', $keywordLower), fn($w) => mb_strlen($w) > 2);
                    $matchCount = 0;
                    foreach ($keywordWords as $word) {
                        if (mb_stripos($headlineLower, $word) !== false) $matchCount++;
                    }
                    
                    $ratio = count($keywordWords) > 0 ? $matchCount / count($keywordWords) : 0;
                    if ($ratio >= 0.6) {
                        $score += 15;
                        $feedback[] = ['type' => 'info', 'text' => 'Strong Topical Relevance'];
                        $isRelevant = true;
                    }
                }
            }

            // 2c. SEVERE RELEVANCE PENALTY (-50)
            if (!$isRelevant && !empty($keyword)) {
                $score -= 50;
                $feedback[] = ['type' => 'danger', 'text' => 'Irrelevant Content Penalty (-50)'];
            }
            
            if (!empty($data['entities'])) {
                $score += 10;
                $feedback[] = ['type' => 'success', 'text' => 'Entity Mapping (+10)'];
            }

            // 3. Sentiment & Engagement
            $sentiment = strtolower($data['sentiment'] ?? '');
            if (in_array($sentiment, ['surprise', 'positive', 'breaking', 'urgent'])) {
                $score += 10;
                $feedback[] = ['type' => 'success', 'text' => 'High-Engagement Sentiment'];
            }

            // 4. Power Words (Dynamic list from Admin)
            $powerWordsRaw = Setting::get("discover-headlines_power_words", "يكشف, يفاجئ, يُعلن, يحسم, يتراجع, يصدر, عاجل, حصري, حقيقة, سر, رسمياً");
            $powerWords = array_map('trim', explode(',', $powerWordsRaw));

            foreach ($powerWords as $word) {
                if (empty($word)) continue;
                if (mb_stripos($headline, $word) !== false) {
                    $score += 5;
                    $feedback[] = ['type' => 'success', 'text' => 'Action Verb: ' . $word];
                    break; // Only award once
                }
            }

            // 5. Semantic Density (LSI Keywords)
            if (!empty($data['lsi_keywords'])) {
                $score += 5;
                $feedback[] = ['type' => 'success', 'text' => 'Semantic SEO (LSI Presence)'];
            }

            // 6. Clickbait Penalty
            $clickbait = ['لن تصدق', 'شاهد قبل الحذف', 'فضيحة', 'اضغط هنا', 'you won\'t believe', 'watch before deleted', 'scandal', 'click here'];
            foreach ($clickbait as $cb) {
                if (mb_stripos($headline, $cb) !== false) {
                    $score -= 30;
                    $feedback[] = ['type' => 'danger', 'text' => 'Clickbait Penalty'];
                }
            }

            $finalScore = max(0, min(100, $score));
            $scored[] = array_merge($data, [
                'score' => $finalScore,
                'grade' => $this->gradeHeadline($finalScore),
                'feedback' => $feedback,
            ]);
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return $scored;
    }

    protected function gradeHeadline($score)
    {
        if ($score >= 85) return ['label' => 'EXCELLENT — Discover Ready', 'color' => 'green', 'emoji' => '🔥'];
        if ($score >= 70) return ['label' => 'VERY GOOD', 'color' => 'green', 'emoji' => '✅'];
        if ($score >= 55) return ['label' => 'GOOD', 'color' => 'blue', 'emoji' => '👍'];
        if ($score >= 40) return ['label' => 'FAIR — Needs Improvement', 'color' => 'yellow', 'emoji' => '⚡'];
        return ['label' => 'POOR — Not Fit for Discover', 'color' => 'red', 'emoji' => '⚠️'];
    }

    protected function getDiscoverRules($isArabic = true)
    {
        if ($isArabic) {
            return "🔹 قواعد امتثال Google Discover (صارمة جداً):\n" .
                   "1. **الارتباط التام (Strict Relevance):** يجب أن يكون العنوان مرتبطاً حصرياً وبالكامل بالكلمة المستهدفة [Keyword]. ارفض أي سياق خارجي.\n" .
                   "2. **الكيان أولاً (Entity-First):** إعطاء الأولوية لأسماء الأشخاص، الأماكن، أو المنظمات في بداية العنوان.\n" .
                   "3. **تطابق الـ Sentiment:** يجب أن يعكس العنوان الشعور الحقيقي للمحتوى لجذب الجمهور المستهدف.\n" .
                   "4. **السياق الدلالي (Semantic Context):** استخدم كيانات مرتبطة لتعزيز Topical Authority.\n" .
                   "5. **منع التضليل:** التزم بالحقائق المذكورة في السياق المرفق فقط.";
        }

        return "🔹 Google Discover STRICT Compliance Rules:\n" .
               "1. **Strict Relevance Mandate:** The headline MUST be exclusively about [Keyword]. Reject unrelated context.\n" .
               "2. **Entity-First Headlines:** Prioritize names of people, places, or organizations at the start.\n" .
               "3. **Sentiment Alignment:** Ensure the headline tone matches the content's emotional core.\n" .
               "4. **Semantic Authority:** Use related entities to build Topical Authority.\n" .
               "5. **No Clickbait:** Stick strictly to facts found in the provided context.";
    }

    protected function getDefaultHeadlinesStyle($isArabic = true)
    {
        if ($isArabic) {
            return "أنت محرر أول في وكالة أنباء كبرى ومتخصص في خوارزميات Google Discover و Google News Showcase. الهدف هو صياغة عناوين صحفية احترافية تحقق أعلى نسبة نقر (CTR) في Discover بدون أي شكل من أشكال الخداع.
    
🔹 الكلمة المستهدفة: [Keyword]

🔹 السياق الإخباري (حلل كل التفاصيل بدقة واستخرج الحقائق الأساسية):
[NewsContext]

🔹 المعايير الاحترافية للصياغة:
1. **Entity-First:** ابدأ دائماً بالكيان الأهم.
2. **Curiosity Gap الذكي:** أعطِ فكرة واضحة عن المحتوى دون كشف كل شيء.
3. **أرقام دقيقة:** أضف أرقام ونسب إذا توفرت.
4. **أفعال حركية:** يكشف، يُعلن، يحسم.
5. **Anti-Clickbait صارم:** التزم بالحقائق.
6. **إشارات حداثة:** استخدم الآن، عاجل (عند الملاءمة).
7. **أقواس تصنيفية:** [تقرير] أو [حصري].

🚨 تحذير صارم:
- الالتزام التام بإصدار النتيجة باللغة العربية حصراً.
- الموضوع الأساسي لكل العناوين يجب أن يكون [Keyword].";
        }

        return "You are a Senior Editor at a major news agency specializing in Google Discover algorithms. Your goal is to draft professional headlines that achieve the highest CTR in Discover without any deceit.

🔹 Target Keyword: [Keyword]

🔹 News Context (Analyze carefully for facts):
[NewsContext]

🔹 Professional Drafting Standards:
1. **Entity-First:** Start with the most important entity.
2. **Smart Curiosity Gap:** Give a clear idea without spoiling everything.
3. **Precise Numbers:** Add numbers if available in context.
4. **Action Verbs:** reveals, announces, settles.
5. **Strict Anti-Clickbait:** Stick to facts.
6. **Freshness:** Use 'Now' or 'Urgent' if appropriate.
7. **Bracket Tags:** Use [Report] or [Exclusive] where fitting.

🚨 STRICT WARNING:
- You MUST output the results entirely in ENGLISH exclusively.
- The core subject of every single headline MUST be [Keyword].";
    }

    protected function getHeadlinesTechnicalWrapper($count, $region, $isArabic = true)
    {
        $jsonExample = [
            'headlines' => [
                [
                    'headline' => 'Sample Headline text here',
                    'sentiment' => 'Positive/Surprise/Neutral',
                    'entities' => ['Entity1', 'Entity2'],
                    'lsi_keywords' => ['keyword1', 'keyword2'],
                    'thumbnail_suggestion' => 'Description of the perfect complementary image',
                ]
            ]
        ];
        $jsonStr = json_encode($jsonExample, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return "🔹 FINAL OUTPUT REQUIREMENTS (STRICT JSON ONLY):
- Generate EXACTLY {$count} headlines.
- Output ONLY a valid JSON object following the structure below.
- NO conversational text, NO intro, NO markdown outside the JSON.
- **LANGUAGE ENFORCEMENT: All headline text, sentiment, and suggestions MUST be in " . ($isArabic ? "ARABIC" : "ENGLISH") . ".**
- **Target Length: EACH headline MUST be between 55 and 85 characters long.**
- MANDATORY Angle Diversity: Every headline MUST take a unique narrative angle.

🔹 MANDATORY JSON STRUCTURE:
{$jsonStr}";
    }

    protected function getCountryMap(): array
    {
        // Use the global registry so admin-disabled countries don't show up
        // in Discover Headlines either.
        return CountryRegistry::globalMap();
    }
}
