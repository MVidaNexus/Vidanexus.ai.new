<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Campaign;
use App\Models\Article;
use App\Services\CampaignService;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class HeadlineController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Show the headlines settings page.
     */
    public function settings()
    {
        $settings = Setting::getAllSettings();
        return view('dashboard.headlines.settings', compact('settings'));
    }

    /**
     * Update headline specific settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'headlines_ai_provider' => 'nullable|string|in:gemini,openrouter',
            'headlines_gemini_model' => 'nullable|string|max:100',
            'headlines_openrouter_model' => 'nullable|string|max:255',
            'discover_headlines_prompt' => 'nullable|string',
            'discover_content_prompt' => 'nullable|string',
            'headlines_gemini_key' => 'nullable|string|max:255',
            'headlines_openrouter_key' => 'nullable|string|max:5000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'text', 'headlines');
        }

        Setting::clearCache();

        return back()->with('success', 'تم تحديث إعدادات العناوين بنجاح.');
    }

    /**
     * Show the headlines generation page.
     */
    public function index(Request $request)
    {
        // Pick up results from session (Flash only — one-time display)
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
            
            // CRITICAL: Forget session data immediately so it doesn't persist on next navigation
            session()->forget(['headlineResults', 'headlineResultsB64', 'scoredHeadlines', 'headlineKeyword', 'headlineError', 'headlineEmpty']);
        }

        // Fetch settings and determine region
        $settings = Setting::getAllSettings();
        $defaultRegion = $settings['keywords_google_trends_region'] ?? 'EG';
        $region = strtoupper($request->get('country', $request->get('keywords_google_trends_region', $defaultRegion)));
        
        $countryMap = config('keywords.countries', []);
        if (!isset($countryMap[$region])) {
            $region = $defaultRegion;
        }
        
        $currentCountry = $countryMap[$region];
        $currentCountry['code'] = $region;

        // Fetch trending suggestions for the UI based on selected country
        $trendingSuggestions = $this->getTrendingSuggestions($region);
        
        // Pick up active progress from session (important for WAF fallback)
        $activeProgressId = session('active_headline_progress_id');
        
        $extra['trendingSuggestions'] = $trendingSuggestions;
        $extra['currentCountry'] = $currentCountry;
        $extra['activeProgressId'] = $activeProgressId; // Pass to view
        $extra['countryMap'] = $countryMap;
        $extra['region'] = $region;

        return $this->renderIndex($request, $extra);
    }

    /**
     * Helper to render the index view with optional results
     */
    private function renderIndex(Request $request, array $extra = [])
    {
        $allSettings = Setting::getAllSettings();
        $provider = $allSettings['headlines_ai_provider'] ?? 'gemini';
        $hasKey = false;
        
        if ($provider === 'gemini' && !empty($allSettings['headlines_gemini_key'] ?? '')) {
            $hasKey = true;
        } elseif ($provider === 'openrouter' && !empty($allSettings['headlines_openrouter_key'] ?? '')) {
            $hasKey = true;
        }

        // Prioritize keyword from $extra (flashed from redirect), fallback to request
        $prefilledKeyword = $extra['prefilledKeyword'] ?? $request->input('keyword', $request->get('keyword'));
        $categories = Category::orderBy('name')->get();
        
        // Ensure we have country data
        $countryMap = $extra['countryMap'] ?? config('keywords.countries', []);
        $region = $extra['region'] ?? ($request->input('country') ?? 'EG');
        $currentCountry = $extra['currentCountry'] ?? ($countryMap[strtoupper($region)] ?? ['name' => 'مصر', 'flag' => '🇪🇬', 'code' => 'EG']);

        $data = compact('hasKey', 'prefilledKeyword', 'categories', 'currentCountry', 'countryMap', 'region');
        $data = array_merge($data, $extra);

        return response()
            ->view('dashboard.headlines.index', $data)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function generate(Request $request)
    {
        $keyword = $request->input('keyword');
        $content = $request->input('content');
        $type = $request->input('type', 'keyword');
        $region = strtoupper($request->input('country', 'EG'));
        $progressId = $request->input('progress_id');
        $isAjax = $request->ajax() || $request->wantsJson();
        
        Log::info("[Headlines] Generation started", [
            'type' => $type,
            'keyword' => $keyword,
            'region' => $region,
            'progress_id' => $progressId,
            'is_ajax' => $isAjax
        ]);

        if ($progressId) {
            session(['active_headline_progress_id' => $progressId]);
            Cache::put("gen_progress_{$progressId}", [
                'stage' => 'starting',
                'message' => 'جاري بدء العملية وتحليل المدخلات...'
            ], 300);
        }

        $request->validate([
            'keyword' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|string|in:keyword,content',
            'variants' => 'nullable|integer|min:3|max:15',
        ]);

        $variantsCount = $request->input('variants', 7);

        if ($type === 'keyword' && empty($keyword)) {
            $msg = 'يرجى إدخال كلمة مفتاحية.';
            if (!$isAjax) return redirect()->back()->with(['headlineError' => $msg]);
            return response()->json(['status' => 'error', 'message' => $msg], 400);
        }

        if ($type === 'content' && empty($content)) {
            $msg = 'يرجى إدخال المحتوى المطلوب استخراج العناوين منه.';
            if (!$isAjax) return redirect()->back()->with(['headlineError' => $msg]);
            return response()->json(['status' => 'error', 'message' => $msg], 400);
        }

        // 1. Prepare Context
        if ($type === 'keyword') {
            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'searching',
                    'message' => 'نبحث الآن عن أحدث المستجدات في جوجل...'
                ], 300);
            }
            
            $newsContext = $this->fetchNewsContext($keyword, $region, $progressId);
            
            if (empty($newsContext)) {
                $msg = 'لم يتم العثور على أخبار حديثة لهذا الموضوع محققة لشروط Google Discover.';
                if ($progressId) Cache::forget("gen_progress_{$progressId}");
                if (!$isAjax) return redirect()->back()->with(['headlineEmpty' => $msg, 'headlineKeyword' => $keyword]);
                return response()->json(['status' => 'empty', 'message' => $msg]);
            }
        } else {
            $newsContext = $content;
        }

        if ($progressId) {
            Cache::put("gen_progress_{$progressId}", [
                'stage' => 'ai_processing',
                'message' => 'جاري صياغة العناوين الإبداعية باستخدام الذكاء الاصطناعي...'
            ], 300);
        }

        // 2. Prepare Settings & Prompt
        $allSettings = Setting::getAllSettings();
        $discoverRules = $this->getDiscoverRules();

        if ($type === 'keyword') {
            $userStyle = $allSettings['discover_headlines_prompt'] ?? $this->getDefaultHeadlinesStyle();
            $prompt = "You are a professional news headline specialist. Follow the style and rules below:\n\n" . $userStyle;
            $prompt .= "\n\n" . $discoverRules;
            $prompt .= "\n\n" . $this->getHeadlinesTechnicalWrapper($variantsCount, $region);
            $prompt = str_replace(['[Keyword]', '[keyword]'], $keyword, $prompt);
            $prompt = str_replace('[NewsContext]', $newsContext, $prompt);
        } else {
            $prompt = "أنت محرر صحفي خبير في تحليل المحتوى الإخباري لـ Google Discover.\n" .
                      "مهمتك هي تحليل المحتوى التالي واستخراج {$variantsCount} عناوين احترافية مبنية على الحقائق ولكن بصياغة تجذب الملايين.\n\n" .
                      $discoverRules . "\n\n" .
                      "🔹 المحتوى المراد تحليله:\n" . $newsContext . "\n\n" .
                      $this->getHeadlinesTechnicalWrapper($variantsCount, $region);
        }

        // 3. Call AI
        try {
            $aiSettings = [
                'ai_provider_priority' => $allSettings['headlines_ai_provider'] ?? 'gemini',
                'gemini_default_model' => $allSettings['headlines_gemini_model'] ?? 'gemini-2.0-flash',
                'openrouter_default_model' => $allSettings['headlines_openrouter_model'] ?? '',
                'gemini_api_key' => $allSettings['headlines_gemini_key'] ?? '',
                'openrouter_api_key' => $allSettings['headlines_openrouter_key'] ?? '',
            ];

            // Validate API Key
            if ($aiSettings['ai_provider_priority'] === 'gemini' && empty($aiSettings['gemini_api_key'])) {
                throw new \Exception('مفتاح المحرك الذكي API غير مضبوط لخدمة العناوين.');
            }
            if ($aiSettings['ai_provider_priority'] === 'openrouter' && empty($aiSettings['openrouter_api_key'])) {
                throw new \Exception('مفتاح OpenRouter API غير مضبوط لخدمة العناوين.');
            }
            
            $generatedHeadlines = $this->campaignService->processTextWithAI($prompt, $keyword ?? 'Content Analysis', $newsContext, $prompt, $aiSettings);
            
            // CLEANING: Strip Markdown code blocks if any
            if (preg_match('/```(?:json|markdown|text|)?\s*(.*?)\s*```/s', $generatedHeadlines, $matches)) {
                $generatedHeadlines = $matches[1];
            }
            $generatedHeadlines = trim($generatedHeadlines);

            // ENCODING ENFORCEMENT: Ensure result is valid UTF-8
            if ($generatedHeadlines && !mb_check_encoding($generatedHeadlines, 'UTF-8')) {
                Log::warning("[Headlines] Invalid UTF-8 detected from AI. Attempting scrub.");
                $generatedHeadlines = mb_convert_encoding($generatedHeadlines, 'UTF-8', mb_detect_encoding($generatedHeadlines, 'UTF-8, ISO-8859-1, windows-1256', true) ?: 'UTF-8');
            }
            $generatedHeadlines = iconv('UTF-8', 'UTF-8//IGNORE', $generatedHeadlines);

            // DEEP LOGGING: Save raw output for manual inspection
            try {
                \Illuminate\Support\Facades\File::append(storage_path('logs/headline_raw.log'), 
                    "[" . now() . "] ID: {$progressId} | Type: {$type} | Keyword: {$keyword} | Bytes: " . strlen($generatedHeadlines) . "\n" . 
                    "RAW OUTPUT:\n{$generatedHeadlines}\n" . str_repeat('=', 40) . "\n"
                );
            } catch (\Exception $e) { }

            $scoredHeadlines = $this->scoreHeadlines($generatedHeadlines, $keyword ?? '');

            if ($progressId) {
                $finalData = [
                    'stage' => 'completed',
                    'message' => 'تم توليد العناوين بنجاح!',
                    'headlines' => $generatedHeadlines,
                    'scored' => $scoredHeadlines,
                    'keyword' => $keyword
                ];
                // Unified: One key for both progress and final results
                Cache::put("gen_progress_{$progressId}", $finalData, 1200);
                
                // Flash session data (one-time read only — won't persist on navigation)
                session()->flash('headlineResults', $generatedHeadlines);
                session()->flash('scoredHeadlines', $scoredHeadlines);
                session()->flash('headlineKeyword', $keyword);
                session()->forget('active_headline_progress_id');
            }

            Log::info("[Headlines] Generation completed", ['count' => count($scoredHeadlines), 'pid' => $progressId]);

            if (!$isAjax) {
                return redirect()->route('dashboard.headlines.index')->with([
                    'headlineResults' => $generatedHeadlines,
                    'scoredHeadlines' => $scoredHeadlines,
                    'headlineKeyword' => $keyword,
                ]);
            }

            $responseData = [
                'status' => 'success',
                'headlines' => $generatedHeadlines,
                'scored' => $scoredHeadlines,
                'keyword' => $keyword,
                'type' => $type
            ];

            // Final recursive sterilization for JSON UTF-8 compliance
            array_walk_recursive($responseData, function (&$item) {
                if (is_string($item)) {
                    $item = iconv('UTF-8', 'UTF-8//IGNORE', $item);
                }
            });

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error("Headline Generation Error: " . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            $userMessage = str_contains(mb_strtolower($errorMessage), 'quota') || str_contains($errorMessage, '429') 
                ? '⚠️ انتهت حصة استخدام الـ API (Quota).' 
                : 'فشل في توليد العناوين: ' . $errorMessage;
            
            if ($progressId) {
                session()->forget('active_headline_progress_id');
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'error',
                    'message' => $userMessage
                ], 300);
            }

            if (!$isAjax) return redirect()->back()->with(['headlineError' => $userMessage]);

            return response()->json(['status' => 'error', 'message' => $userMessage], 500);
        }
    }

    /**
     * Get the standardized Discover rules for prompting.
     * Based on actual Google Discover content policies and best practices.
     */
    protected function getDiscoverRules()
    {
        return "
🔹 قواعد الصياغة لـ Google Discover (معايير احترافية):

1. **Entity-First (الكيان أولاً):** ابدأ العنوان باسم الشخصية أو المؤسسة أو الحدث المشهور. Google Discover يعتمد على الكيانات Knowledge Graph.
   مثال صحيح: 'محمد صلاح يكشف عن قراره المفاجئ' ✅
   مثال خاطئ: 'قرار مفاجئ من لاعب كرة القدم' ❌

2. **Curiosity Gap المقبول:** اطرح سؤالاً أو جانباً غامضاً يثير الفضول، لكن العنوان يجب أن يعطي فكرة واضحة عن المحتوى.
   مقبول: 'السر وراء ارتفاع أسعار الذهب لمستويات تاريخية' ✅
   مرفوض: 'ما حدث اليوم لن تصدقه' ❌

3. **Freshness Signals (إشارات الحداثة):** استخدم كلمات تعكس الآنية: الآن، اليوم، لأول مرة، رسمياً، عاجل (فقط إن كان فعلاً عاجلاً).

4. **أرقام محددة:** الأرقام الدقيقة تزيد CTR بنسبة 36%. استخدم: نسب مئوية، مبالغ، ترتيب، تواريخ.
   مثال: '3 قرارات حاسمة من البنك المركزي تغيّر مسار الجنيه' ✅

5. **Action Verbs (أفعال حركية قوية):** يكشف، يُعلن، يحسم، يتراجع، يفاجئ، يصدر، يوافق، يرفض.
   تجنب الأفعال الضعيفة: يقول، يذكر، يتحدث.

6. **Emotional Hook بدون مبالغة:** استثمر مشاعر الدهشة والترقب والقلق بشكل مهني.
   مقبول: 'تطور مفاجئ' ✅ | مرفوض: 'صدمة تهز العالم' ❌

7. **E-E-A-T Compliance:** العنوان يجب أن يعكس خبرة ومصداقية. لا تبالغ ولا تختلق.

8. **Mobile-First:** العنوان يُعرض على الموبايل في ~85 حرف. تجاوز ذلك يعني قص العنوان.

9. **ممنوعات Discover الصارمة:**
   - لا تستخدم: لن تصدق، شاهد قبل الحذف، فضيحة، اضغط هنا
   - لا تستخدم Emoji أبداً في العناوين
   - لا تبالغ في علامات التعجب أو الاستفهام (!!! أو ???)
   - لا تكرر كلمات في نفس العنوان

10. **أقواس مربعة للتصنيف:** استخدام [تقرير]، [حصري]، [تحليل]، [فيديو] يزيد CTR.

11. **التنوع في الأنماط:** نوّع بين:
    - عنوان تقريري (خبر مباشر)
    - عنوان تحليلي (لماذا/كيف)
    - عنوان تصريح أول مرة
    - عنوان رقمي (3 أسباب، 5 تطورات)

12. **Anti-Clickbait:** كل عنوان يجب أن يكون له أصل في السياق الإخباري. لا تختلق معلومات.";
    }

    /**
     * Fetch news items from Google News RSS with fallback strategies.
     * Enhanced with Jina AI Search fallback for better context.
     * Results are cached for 15 minutes.
     */
    protected function fetchNewsContext($keyword, $region = 'EG', $progressId = null)
    {
        $region = strtoupper($region);
        $countryMap = config('keywords.countries', []);
        $countryData = $countryMap[$region] ?? ['lang' => 'ar'];
        $lang = $countryData['lang'] ?? 'ar';
        
        // Check cache first (15 min TTL)
        $cacheKey = 'headline_news_v2_' . $region . '_' . md5(mb_strtolower(trim($keyword)));
        $cached = Cache::get($cacheKey);
        if ($cached) {
            Log::info("Headline System: Using cached news for [{$region}]: " . $keyword);
            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'searching',
                    'message' => 'تم استرجاع الأخبار من الذاكرة المؤقتة ⚡'
                ], 300);
            }
            return $cached;
        }

        $ceid = "{$region}:{$lang}";
        
        // Strategy 1: Google News RSS (Fastest)
        $rssStrategies = [
            'exact' => "https://news.google.com/rss/search?q=" . urlencode('"' . $keyword . '" when:24h') . "&hl={$lang}&gl={$region}&ceid={$ceid}",
            'broad' => "https://news.google.com/rss/search?q=" . urlencode($keyword) . "&hl={$lang}&gl={$region}&ceid={$ceid}",
        ];

        $context = "";
        foreach ($rssStrategies as $type => $url) {
            try {
                if ($progressId) {
                    Cache::put("gen_progress_{$progressId}", [
                        'stage' => 'searching',
                        'message' => ($type === 'exact' ? 'نبحث في أهم المصادر الإخبارية...' : 'نبحث بشكل موسع عن سياق الخبر...')
                    ], 300);
                }
                
                $response = Http::timeout(10)->get($url);
                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        $count = 0;
                        foreach ($xml->channel->item as $item) {
                            $context .= "- " . (string)$item->title . " (المصدر: " . (string)$item->source . ")\n";
                            if (++$count >= 15) break;
                        }

                        if (!empty($context)) {
                            Log::info("Headline System: Success with RSS strategy $type");
                            Cache::put($cacheKey, $context, 900);
                            return $context;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("RSS Fetch Error ($type): " . $e->getMessage());
            }
        }

        // Strategy 2: Jina AI Search (Deep Context) - If RSS fails or returns little
        try {
            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'searching',
                    'message' => 'جاري استخدام محرك بحث ذكي للوصول لمعلومات أعمق... 🔍'
                ], 300);
            }

            $searchUrl = "https://s.jina.ai/" . urlencode($keyword . " اخبار اليوم " . $region);
            Log::info("Headline System: Falling back to Jina Search: " . $searchUrl);
            
            $response = Http::timeout(20)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($searchUrl);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data'])) {
                    $context = "🔹 معلومات مستخرجة من البحث المتقدم:\n\n";
                    foreach (array_slice($data['data'], 0, 5) as $result) {
                        $context .= "• " . ($result['title'] ?? 'خبر') . ": " . Str::limit($result['description'] ?? $result['content'] ?? '', 300) . "\n\n";
                    }
                    
                    if (!empty($context)) {
                        Cache::put($cacheKey, $context, 900);
                        return $context;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Jina Search Error: " . $e->getMessage());
        }

        return '';
    }

    protected function getDefaultHeadlinesStyle()
    {
        return "أنت محرر أول في وكالة أنباء كبرى ومتخصص في خوارزميات Google Discover و Google News Showcase.
الهدف هو صياغة عناوين صحفية احترافية تحقق أعلى نسبة نقر (CTR) في Discover بدون أي شكل من أشكال الخداع.

🔹 الكلمة المستهدفة: [Keyword]

🔹 السياق الإخباري (حلل كل التفاصيل بدقة واستخرج الحقائق الأساسية):
[NewsContext]

🔹 المعايير الاحترافية للصياغة:
1. **Entity-First:** ابدأ دائماً بالكيان الأهم (اسم الشخصية، المؤسسة، أو الحدث).
2. **Curiosity Gap الذكي:** لا تكشف كل شيء، لكن أعطِ فكرة واضحة عن المحتوى.
3. **أرقام دقيقة:** أضف أرقام ونسب من السياق كلما أمكن (تزيد CTR بنسبة 36%).
4. **أفعال حركية:** يكشف، يُعلن، يحسم، يفاجئ، يصدر (تجنب: يقول، يذكر).
5. **Anti-Clickbait صارم:** كل كلمة في العنوان يجب أن يكون لها أصل في السياق.
6. **إشارات حداثة:** استخدم: الآن، اليوم، لأول مرة، رسمياً (عند الملاءمة).
7. **أقواس تصنيفية:** أضف [تقرير] أو [حصري] أو [تحليل] لعنوان واحد على الأقل.

🔹 الأنماط المطلوبة (يجب التنويع بينها):
- خبر مباشر قوي (Entity + فعل حركي + تفصيل)
- تحليل/تفسير (لماذا/كيف + حقيقة مدهشة)
- تصريح حصري (اسم + يكشف لأول مرة)
- رقمي (عدد + حقائق مثيرة)";
    }

    protected function getHeadlinesTechnicalWrapper($count = 7, $region = 'EG')
    {
        $countryMap = config('keywords.countries', []);
        $lang = $countryMap[strtoupper($region)]['lang'] ?? 'ar';
        $langName = ($lang === 'en') ? 'English' : (($lang === 'tr') ? 'Turkish' : 'Arabic');

        return "🔹 FINAL OUTPUT REQUIREMENTS (STRICT — أي انحراف = رفض):
- Generate EXACTLY {$count} headlines.
- Output ONLY the headlines, nothing else.
- NO intro text, NO numbering, NO quotes, NO markdown, NO asterisks, NO emoji.
- ONE HEADLINE PER LINE.
- Headlines MUST be in {$langName}.
- **Target Length: EACH headline MUST be between 55 and 85 characters (محسوب بالأحرف العربية).**
- Headlines shorter than 45 or longer than 100 characters will be REJECTED.
- MANDATORY pattern variety across the {$count} headlines:
  • At least 1 headline: Entity-first factual (اسم + فعل حركي + تفصيل)
  • At least 1 headline: Analytical/Why (لماذا/كيف + حقيقة)
  • At least 1 headline: Number-driven (رقم + حقائق)
  • At least 1 headline: With bracket tag like [تقرير] or [تحليل] or [حصري]
- Do NOT use the same sentence structure for all headlines.
- Do NOT repeat words across multiple headlines.";
    }

    protected function getContentTechnicalWrapper()
    {
        return "🔹 HIDDEN SYSTEM REQUIREMENTS:
- Output MUST be in Arabic.
- Output ONLY the body content.
- Use clean HTML tags: <p>, <h2>, <h3>, <ul>, <li>, <table>.
- NO markdown, NO title, NO intro like \"Here is the article\".
- The content must be ready for web publishing.";
    }

    public function generateAutoContent(Request $request)
    {
        $request->validate([
            'headline' => 'required|string',
            'keyword' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'progress_id' => 'nullable|string',
            'source_content' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $progressId = $request->progress_id;
        $updateProgress = function($stage, $msg) use ($progressId) {
            if ($progressId) {
                \Illuminate\Support\Facades\Cache::put("gen_progress_{$progressId}", [
                    'stage' => $stage,
                    'message' => $msg,
                    'timestamp' => now()->timestamp
                ], 300);
            }
        };

        $updateProgress('starting', 'جاري التحضير لبدء التوليد...');

        $headline = $request->headline;
        $keyword = $request->keyword;
        $categoryId = $request->category_id;

        $sourceContent = $request->source_content;
        $region = strtoupper($request->get('country', 'EG'));
        $countryMap = config('keywords.countries', []);
        $lang = $countryMap[$region]['lang'] ?? 'ar';
        $ceid = "{$region}:{$lang}";

        // OPTIMIZATION: Increase limits + parallel processing
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        try {
            $headlineSettings = Setting::getAllSettings();
            $templateCampaign = Campaign::whereNotNull('ai_title_prompt')->latest()->first();

            if (!$templateCampaign) {
                return response()->json(['success' => false, 'message' => 'يرجى إعداد حملة واحدة على الأقل تحتوي على برومبتات AI لاستخدامها كقالب للهيكل.'], 400);
            }

            $extracted = null;
            $resolvedUrl = '';
            $success = false;
            $xml = null;

            if ($sourceContent) {
                $updateProgress('extracting', 'جاري استخدام المحتوى المقدم...');
                $extracted = [
                    'content' => $sourceContent,
                    'image' => null,
                    'title' => $headline
                ];
                $success = true;
                $resolvedUrl = 'manually_provided_content';
            } else {
                // 1. Find a source for this headline
                $cleanHeadline = trim(preg_replace('/\[.*?\]/', '', $headline));
                $searchQuery = ($keyword ? $keyword . ' ' : '') . $cleanHeadline;
                
                // Try Search Strategies
                $strategies = [
                    "https://news.google.com/rss/search?q=" . urlencode('"' . $cleanHeadline . '"') . "&hl={$lang}&gl={$region}&ceid={$ceid}", // Exact Headline
                    "https://news.google.com/rss/search?q=" . urlencode($searchQuery) . "&hl={$lang}&gl={$region}&ceid={$ceid}", // Keyword + Headline
                    "https://news.google.com/rss/search?q=" . urlencode($cleanHeadline) . "&hl={$lang}&gl={$region}&ceid={$ceid}", // Headline Only (Broad)
                ];

                $updateProgress('searching', 'جاري البحث في المصادر المتاحة...');

                // Extract significant keywords from headline for similarity verification
                $cleanHeadlineForKeywords = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $cleanHeadline);
                $headlineKeywords = array_filter(preg_split('/\s+/u', mb_strtolower($cleanHeadlineForKeywords)), function($w) {
                    return mb_strlen($w) > 3; // Use longer words for better match quality
                });

                $smartCampaign = clone $templateCampaign;
                $smartCampaign->source_selector = null;
                $smartCampaign->title_selector = null;
                $smartCampaign->image_selector = null;
                $smartCampaign->max_age_hours = null;
                $smartCampaign->use_python = true;

                $extractionErrors = [];
                $itemsAttempted = 0;
                // OPTIMIZATION: Increased from 5 to 8 attempts for better success rate
                $maxAttempts = 8;

                foreach ($strategies as $index => $url) {
                    if ($success) break;
                    
                    try {
                        $response = Http::timeout(10)->get($url);
                        if (!$response->successful()) continue;
                        
                        $tempXml = @simplexml_load_string($response->body());
                        if (!$tempXml || !isset($tempXml->channel->item)) continue;
                        
                        if (!$xml) $xml = $tempXml; // Store for contextual search later

                        foreach ($tempXml->channel->item as $item) {
                            if ($success || $itemsAttempted >= $maxAttempts) break;

                            $sourceUrl = (string)$item->link;
                            $sourceTitle = (string)$item->title;

                            // 1. Verify Title Similarity (Soft Check)
                            $titleMatchCount = 0;
                            if (!empty($headlineKeywords)) {
                                foreach ($headlineKeywords as $kw) {
                                    if (mb_stripos($sourceTitle, $kw) !== false) $titleMatchCount++;
                                }
                            }

                            // Relaxed Check: if it's the exact match strategy (index 0) or we have some similarity
                            if ($index > 0 && $titleMatchCount < 1 && !empty($headlineKeywords)) {
                                Log::info("Auto Content: Skipping low similarity item: " . $sourceTitle);
                                continue;
                            }

                            $itemsAttempted++;
                            $updateProgress('extracting', "جاري فحص المصدر ($itemsAttempted من $maxAttempts)...");

                            // Use FAST MODE to avoid timeouts
                            $resUrl = $this->campaignService->resolveGoogleNewsUrl($sourceUrl, $sourceTitle, true);
                            
                            if (!$resUrl || str_contains($resUrl, 'google.com')) {
                                $itemSource = $item->source;
                                $realSourceUrl = (isset($itemSource['url'])) ? (string)$itemSource['url'] : null;
                                if ($realSourceUrl) {
                                    $resUrl = $this->campaignService->findArticleOnSource($realSourceUrl, $sourceTitle);
                                }
                            }

                            if ($resUrl && !str_contains($resUrl, 'google.com')) {
                                $extractedResult = $this->campaignService->extractFullContent($resUrl, $smartCampaign);
                                if (!empty($extractedResult['content']) && strlen($extractedResult['content']) >= 100) {
                                    $extracted = $extractedResult;
                                    $resolvedUrl = $resUrl;
                                    $success = true;
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Headline Source Strategy Error: " . $e->getMessage());
                        $extractionErrors[] = "خطأ في البحث/الاستخراج: " . $e->getMessage();
                    }
                }
            }

            if (empty($success)) {
                $detailedMsg = "فشل استخراج المحتوى بعد محاولة $itemsAttempted مصادر. يرجى التأكد من توفر أخبار حديثة لهذا العنوان أو توفير المحتوى مباشرة.";
                Log::warning("Auto Content Extraction Failed: " . json_encode($extractionErrors));
                return response()->json(['success' => false, 'message' => $detailedMsg], 400);
            }

            // NEW: Gather search results information for broader context
            $searchInfo = "";
            if ($xml) {
                $resultsCount = 0;
                foreach ($xml->channel->item as $item) {
                    $itemTitle = (string)$item->title;
                    $itemDesc = strip_tags((string)$item->description);
                    // Simple cleaning of common junk in RSS descriptions
                    $itemDesc = preg_replace('/<.*?>/', '', $itemDesc);
                    $searchInfo .= "Info " . ($resultsCount + 1) . ":\n- Title: " . $itemTitle . "\n- Summary: " . $itemDesc . "\n\n";
                    $resultsCount++;
                    if ($resultsCount >= 5) break;
                }
            }

            // 3. AI Process
            $updateProgress('ai_processing', 'جاري صياغة المقال بالذكاء الاصطناعي...');
            $headlineSettings = Setting::getAllSettings();
            
            // Map settings to AI keys used by CampaignService
            $aiSettings = [
                'ai_provider_priority' => $headlineSettings['headlines_ai_provider'] ?? 'gemini',
                'gemini_api_key' => $headlineSettings['headlines_gemini_key'] ?? '',
                'gemini_default_model' => $headlineSettings['headlines_gemini_model'] ?? 'gemini-2.0-flash',
                'openrouter_api_key' => $headlineSettings['headlines_openrouter_key'] ?? '',
                'openrouter_default_model' => $headlineSettings['headlines_openrouter_model'] ?? '',
            ];

            // Article Title: Use the selected headline directly as requested (no extra AI step)
            $newTitle = trim(preg_replace('/\[.*?\]/', '', $headline)); 
            
            $contentPrompt = $headlineSettings['discover_content_prompt'] ?? $this->getDefaultContentPrompt();
            $contentPrompt .= "\n\n" . $this->getContentTechnicalWrapper(); 
            $contentPrompt .= "\n\n🔹 ADDITIONAL INFORMATION FROM SEARCH RESULTS (Inject facts from here if useful):\n" . $searchInfo;

            try {
                $newContent = $this->campaignService->processTextWithAI($extracted['content'], $headline, $extracted['content'], $contentPrompt, $aiSettings);
                if (!$newContent) {
                    return response()->json(['success' => false, 'message' => 'فشل الذكاء الاصطناعي في إعادة صياغة المحتوى. يرجى التأكد من مفاتيح الـ API.'], 400);
                }
            } catch (\Throwable $aiEx) {
                 return response()->json(['success' => false, 'message' => 'خطأ في معالجة الذكاء الاصطناعي: ' . $aiEx->getMessage()], 400);
            }

            // 4. Save
            $finalTitle = $newTitle;
            
            $updateProgress('image_optimizing', 'جاري تحضير وتحسين الصور...');

            try {
                $optimizedImage = null;
                if ($extracted['image']) {
                    $imageOptimizer = app(ImageOptimizerService::class);
                    $imageSlug = Str::slug($finalTitle, '-', 'ar');
                    $optimizedImage = $imageOptimizer->featured($extracted['image'], $imageSlug ?: time());
                    if ($optimizedImage) $optimizedImage = str_replace(asset('storage/'), '', $optimizedImage);
                }

                $updateProgress('saving', 'جاري الحفظ النهائي كمحرر...');

                $finalContent = str_replace(
                    ['[matched_content]', '[original_content]', '[source_link]', '[matched_image]'],
                    [$newContent, $extracted['content'], $resolvedUrl, $extracted['image'] ?? ''],
                    $templateCampaign->template_content
                );

                // Ensure Unique Slug (Arabic Friendly - First 4 words like the rest of the system)
                $words = preg_split('/\s+/u', trim($finalTitle), -1, PREG_SPLIT_NO_EMPTY);
                $first4Words = implode('-', array_slice($words, 0, 4));
                $baseSlug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $first4Words);
                $baseSlug = preg_replace('/-+/', '-', $baseSlug);
                $baseSlug = trim($baseSlug, '-');

                $slug = $baseSlug;
                $count = 1;
                while (Article::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }

                $article = Article::create([
                    'title' => $finalTitle,
                    'slug' => $slug,
                    'content' => $finalContent,
                    'featured_image' => $optimizedImage,
                    'category_id' => $categoryId,
                    'author_id' => auth('admin')->id() ?: $templateCampaign->author_id,
                    'status' => 'draft',
                    'source_name' => 'Discover Radar',
                    'source_url' => $resolvedUrl,
                    'published_at' => now(),
                ]);

                Log::info("Headline Auto Content Success: Article ID " . $article->id);

                $updateProgress('completed', 'برافو! تم توليد المقال بنجاح.');

                return response()->json([
                    'success' => true, 
                    'message' => '✅ تم توليد المقال بنجاح.', 
                    'edit_url' => route('dashboard.articles.edit', $article->id)
                ]);
            } catch (\Throwable $saveEx) {
                Log::error("Headline Save Error: " . $saveEx->getMessage() . "\n" . $saveEx->getTraceAsString());
                return response()->json(['success' => false, 'message' => 'خطأ في حفظ المقال في قاعدة البيانات: ' . $saveEx->getMessage()], 500);
            }

        } catch (\Throwable $e) {
            Log::error("Headline Auto Content Technical Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'خطأ تقني غير متوقع: ' . $e->getMessage()], 500);
        }
    }

    protected function getDefaultContentPrompt()
    {
        return "أنت كاتب محتوى محترف وخبير في تحسين المحتوى لـ Google Discover.
مهمتك هي إعادة صياغة النص الإخباري المقدم إليك ليكون مشوقاً، حصرياً، ومتوافقاً تماماً مع المعايير الصحفية.

الالتزامات:
1. صياغة بأسلوب صحفي جذاب (Mobile-first).
2. الحفاظ على دقة المعلومات الواردة في النص الأصلي.
3. تقسيم المقال إلى فقرات قصيرة سهلة القراءة.
4. إضافة عناوين فرعية (H2, H3) غنية بالكلمات المفتاحية.
5. استخدام أسلوب السرد الذي يشد القارئ من السطر الأول.
6. تجنب الحشو الكلمات المكررة.
7. التأكد من أن النص خالي من الأخطاء الإملائية والنحوية.

هيكل المقال المطلوب:
- مقدمة قوية ومختصرة تشرح أهمية الخبر.
- تفاصيل الخبر بأسلوب شيق.
- خلفية عن الموضوع أو معلومات إضافية مفيدة للقارئ.
- خاتمة تلخص الموقف.

ملاحظات هامة:
- لا تضف أي روابط خارجية.
- لا تضف أي رموز تعبيرية (Emojis).
- اجعل اللغة العربية فصحى وبسيطة.
- المقال يجب أن يكون أطول من 300 كلمة إذا سمح المحتوى الأصلي بذلك.

العنوان الذي يجب التركيز عليه في السياق: [original_title]";
    }

    public function getProgress($id)
    {
        $data = \Illuminate\Support\Facades\Cache::get("gen_progress_{$id}");
        
        Log::debug("[Headlines] Polling progress ID: {$id}", ['has_data' => !!$data, 'stage' => $data['stage'] ?? 'N/A']);

        $responseData = $data ?: [
            'stage' => 'waiting', 
            'message' => 'جاري بدء العملية...',
            'id' => $id
        ];

        array_walk_recursive($responseData, function (&$item) {
            if (is_string($item)) {
                $item = iconv('UTF-8', 'UTF-8//IGNORE', $item);
            }
        });

        return response()->json($responseData);
    }

    /**
     * Score headlines for SEO and Google Discover engagement potential.
     * Enhanced with 12 professional scoring criteria based on actual Discover best practices.
     */
    protected function scoreHeadlines($headlinesText, $keyword = '')
    {
        // CRITICAL: Strip Unicode line/paragraph separators (U+2028, U+2029) BEFORE splitting.
        // These invisible chars appear in AI output and cause \R to split inside Arabic words.
        $headlinesText = preg_replace('/[\x{2028}\x{2029}]/u', '', $headlinesText);
        
        $lines = explode("\n", str_replace("\r\n", "\n", str_replace("\r", "\n", trim($headlinesText))));
        $lines = array_filter($lines, function($l) { return mb_strlen(trim($l)) > 4; });
        $scored = [];

        foreach ($lines as $idx => $headline) {
            $headline = trim($headline);
            
            // SAFE Cleaning: Only strip clearly numbered/bulleted prefixes
            // DO NOT use broad character class regex — they destroy Arabic text
            $headline = preg_replace('/^\s*\d{1,2}[\.\)]\s+/u', '', $headline);  // "1. " or "2) "
            $headline = preg_replace('/^\s*[\-\*•]\s+/u', '', $headline);         // "- " or "* " or "• "
            $headline = preg_replace('/\*\*/u', '', $headline);                    // Remove markdown bold **
            $headline = preg_replace('/\.+$/u', '', trim($headline));               // Strip trailing periods
            
            if (empty($headline) || mb_strlen($headline) < 8) continue;

            $score = 50; // Starting base
            $feedback = [];
            $len = mb_strlen($headline);

            // === 1. Length (Golden Zone: 55-85 for Discover mobile display) ===
            if ($len >= 55 && $len <= 85) {
                $score += 20;
                $feedback[] = ['type' => 'success', 'text' => 'طول Discover مثالي (' . $len . ' حرف)'];
            } elseif ($len >= 45 && $len <= 100) {
                $score += 12;
                $feedback[] = ['type' => 'info', 'text' => 'طول مقبول (' . $len . ' حرف)'];
            } elseif ($len < 40) {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => 'قصير جداً — Discover يفضل 55+ حرف (' . $len . ')'];
            } elseif ($len > 100) {
                $score -= 12;
                $feedback[] = ['type' => 'danger', 'text' => 'طويل جداً — سيُقص على الموبايل (' . $len . ' حرف)'];
            }

            // === 2. Keyword Presence ===
            if (!empty($keyword)) {
                $kwLower = mb_strtolower(trim($keyword));
                $hlLower = mb_strtolower($headline);
                if (mb_strpos($hlLower, $kwLower) !== false) {
                    $score += 10;
                    $feedback[] = ['type' => 'success', 'text' => 'يحتوي على الكلمة المفتاحية'];
                }
            }

            // === 3. Power Words & Action Verbs ===
            $powerWords = [
                'يكشف', 'يفاجئ', 'يُعلن', 'يحسم', 'يتراجع', 'يصدر', 'يوافق', 'يرفض',
                'مثير', 'عاجل', 'حصري', 'خطير', 'يخرج عن صمته', 'لأول مرة',
                'تحذير', 'حقيقة', 'سر', 'مفاجأة', 'يقلب الموازين', 'تطور مفاجئ',
                'القصة الكاملة', 'ماذا حدث', 'رسمياً', 'تفاصيل'
            ];
            $matchCount = 0;
            foreach ($powerWords as $word) {
                if (mb_stripos($headline, $word) !== false) {
                    $matchCount++;
                    if ($matchCount <= 3) {
                        $score += 5;
                        $feedback[] = ['type' => 'success', 'text' => 'فعل حركي: «' . $word . '»'];
                    }
                }
            }

            // === 4. Curiosity Gap (Question words) ===
            if (preg_match('/(لماذا|كيف|ماذا|هل|سبب|من هو|من هي|ما الذي)/u', $headline)) {
                $score += 8;
                $feedback[] = ['type' => 'success', 'text' => 'فجوة فضول (Curiosity Gap)'];
            }

            // === 5. Numbers in Headline (CTR booster) ===
            if (preg_match('/\d+/', $headline)) {
                $score += 8;
                $feedback[] = ['type' => 'success', 'text' => 'يحتوي على أرقام — يزيد CTR بنسبة 36%'];
            }

            // === 6. Entity-First Pattern ===
            // Check if headline starts with a proper noun / known entity pattern
            $firstWord = mb_substr($headline, 0, mb_strpos($headline, ' ') ?: mb_strlen($headline));
            $entityPatterns = ['ال', 'عبد', 'محمد', 'أحمد', 'رئيس', 'وزير'];
            $isEntityFirst = preg_match('/^[\p{Lu}]/u', $headline); // Latin capital
            if (!$isEntityFirst) {
                foreach ($entityPatterns as $ep) {
                    if (mb_strpos($firstWord, $ep) === 0) { $isEntityFirst = true; break; }
                }
            }
            if ($isEntityFirst) {
                $score += 7;
                $feedback[] = ['type' => 'success', 'text' => 'يبدأ بكيان/اسم — Entity-First'];
            }

            // === 7. Freshness Signals ===
            if (preg_match('/(الآن|اليوم|لأول مرة|رسمياً|عاجل|الساعة|منذ قليل)/u', $headline)) {
                $score += 5;
                $feedback[] = ['type' => 'success', 'text' => 'إشارة حداثة (Freshness Signal)'];
            }

            // === 8. Bracket Tags [تقرير] [حصري] ===
            if (preg_match('/\[.+?\]/u', $headline)) {
                $score += 5;
                $feedback[] = ['type' => 'success', 'text' => 'تصنيف بأقواس — يزيد CTR'];
            }

            // === 9. PENALTY: Clickbait (expanded blacklist) ===
            $clickbaitPhrases = [
                'لن تصدق', 'شاهد قبل الحذف', 'فضيحة', 'اضغط هنا',
                'صدمة', 'كارثة', 'مهزلة', 'فاجعة', 'زلزال',
                'يهز العالم', 'ستبكي', 'ممنوع', 'سري للغاية'
            ];
            foreach ($clickbaitPhrases as $cb) {
                if (mb_stripos($headline, $cb) !== false) {
                    $score -= 20;
                    $feedback[] = ['type' => 'danger', 'text' => 'Clickbait: «' . $cb . '» — Google يعاقب عليه'];
                    break;
                }
            }

            // === 10. PENALTY: Emoji in headline ===
            if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F900}-\x{1F9FF}\x{200D}\x{20E3}\x{E0020}-\x{E007F}]/u', $headline)) {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => 'يحتوي Emoji — ممنوع في عناوين Discover'];
            }

            // === 11. PENALTY: Excessive punctuation ===
            if (preg_match('/[!؟?]{2,}/', $headline)) {
                $score -= 10;
                $feedback[] = ['type' => 'warning', 'text' => 'علامات ترقيم مبالغ فيها'];
            }

            // === 12. PENALTY: Word Repetition ===
            $words = preg_split('/\s+/u', mb_strtolower($headline));
            $wordCounts = array_count_values($words);
            $hasRepeat = false;
            foreach ($wordCounts as $w => $c) {
                if ($c > 1 && mb_strlen($w) > 2) { $hasRepeat = true; break; }
            }
            if ($hasRepeat) {
                $score -= 8;
                $feedback[] = ['type' => 'warning', 'text' => 'تكرار كلمات في العنوان'];
            }

            $finalScore = max(0, min(100, $score));
            $scored[] = [
                'headline' => $headline,
                'score' => $finalScore,
                'grade' => $this->gradeHeadline($finalScore),
                'feedback' => $feedback,
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return $scored;
    }

    protected function gradeHeadline($score)
    {
        if ($score >= 85) return ['label' => 'ممتاز — Discover Ready', 'color' => 'green', 'emoji' => '🔥'];
        if ($score >= 70) return ['label' => 'جيد جداً', 'color' => 'green', 'emoji' => '✅'];
        if ($score >= 55) return ['label' => 'جيد', 'color' => 'blue', 'emoji' => '👍'];
        if ($score >= 40) return ['label' => 'مقبول — يحتاج تحسين', 'color' => 'yellow', 'emoji' => '⚡'];
        return ['label' => 'ضعيف — لا يناسب Discover', 'color' => 'red', 'emoji' => '⚠️'];
    }

    /**
     * Suggest trending keywords for the UI.
     */
    public function suggestKeywords()
    {
        $cacheKey = 'headline_trending_suggestions';
        $suggestions = Cache::get($cacheKey);

        if (!$suggestions) {
            $suggestions = [];

            try {
                // Fetch from Google Trends RSS (Arabic, Egypt)
                $url = 'https://trends.google.com/trending/rss?geo=EG';
                $response = Http::timeout(8)->get($url);

                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            $title = (string)$item->title;
                            if (!empty($title)) {
                                $traffic = '';
                                // Try to get approximate traffic
                                $ns = $item->getNamespaces(true);
                                if (isset($ns['ht'])) {
                                    $htData = $item->children($ns['ht']);
                                    if (isset($htData->approx_traffic)) {
                                        $traffic = (string)$htData->approx_traffic;
                                    }
                                }
                                $suggestions[] = [
                                    'keyword' => $title,
                                    'traffic' => $traffic,
                                ];
                            }
                            if (count($suggestions) >= 12) break;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Headline Suggestions Error: ' . $e->getMessage());
            }

            // Fallback: use recent keywords from the database if available
            if (empty($suggestions)) {
                try {
                    $keywords = \App\Models\Keyword::orderByDesc('created_at')->take(12)->pluck('keyword')->toArray();
                    foreach ($keywords as $kw) {
                        $suggestions[] = ['keyword' => $kw, 'traffic' => ''];
                    }
                } catch (\Exception $e) {
                    // Silently fail
                }
            }

            // Cache for 30 minutes
            if (!empty($suggestions)) {
                Cache::put($cacheKey, $suggestions, 1800);
            }
        }

        array_walk_recursive($suggestions, function (&$item) {
            if (is_string($item)) {
                $item = iconv('UTF-8', 'UTF-8//IGNORE', $item);
            }
        });

        return response()->json($suggestions);
    }

    /**
     * Get trending suggestions for initial page load (non-AJAX).
     */
    protected function getTrendingSuggestions($region = 'EG')
    {
        $region = strtoupper($region);
        $cacheKey = 'headline_trending_suggestions_' . $region;
        $suggestions = Cache::get($cacheKey);

        if (!$suggestions) {
            try {
                $url = 'https://trends.google.com/trending/rss?geo=' . $region;
                $response = Http::timeout(5)->get($url);

                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        $suggestions = [];
                        foreach ($xml->channel->item as $item) {
                            $title = (string)$item->title;
                            if (!empty($title)) {
                                $traffic = '';
                                $ns = $item->getNamespaces(true);
                                if (isset($ns['ht'])) {
                                    $htData = $item->children($ns['ht']);
                                    if (isset($htData->approx_traffic)) {
                                        $traffic = (string)$htData->approx_traffic;
                                    }
                                }
                                $suggestions[] = ['keyword' => $title, 'traffic' => $traffic];
                            }
                            if (count($suggestions) >= 10) break;
                        }
                        if (!empty($suggestions)) {
                            Cache::put($cacheKey, $suggestions, 1800);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Trending Suggestions Error: ' . $e->getMessage());
            }

            // Fallback: use recent keywords from the database if available
            if (empty($suggestions)) {
                try {
                    $suggestions = \App\Models\Keyword::orderByDesc('created_at')
                        ->take(12)
                        ->select(['keyword'])
                        ->get()
                        ->map(fn($k) => ['keyword' => $k->keyword, 'traffic' => ''])
                        ->toArray();
                } catch (\Exception $e) {
                    $suggestions = [];
                }
            }

            if (!empty($suggestions)) {
                Cache::put($cacheKey, $suggestions, 1800);
            }
        }

        return $suggestions ?? [];
    }
}
