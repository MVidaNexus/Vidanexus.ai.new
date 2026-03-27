<?php

namespace Modules\DiscoverHeadlines\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\AI\AIManager;
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

        $region = strtoupper($request->get('country', 'US'));
        $countryMap = $this->getCountryMap();
        
        if (!isset($countryMap[$region])) {
            $region = 'US';
        }
        
        $currentCountry = $countryMap[$region];
        $currentCountry['code'] = $region;

        $trendingSuggestions = $this->getTrendingSuggestions($region);
        
        $data = array_merge([
            'trendingSuggestions' => $trendingSuggestions,
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
    public function generate(Request $request)
    {
        $keyword = $request->input('keyword');
        $content = $request->input('content');
        $type = $request->input('type', 'keyword');
        $region = strtoupper($request->input('country', 'EG'));
        $progressId = $request->input('progress_id');
        $isAjax = $request->ajax() || $request->wantsJson();
        
        $request->validate([
            'keyword' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|string|in:keyword,content',
            'variants' => 'nullable|integer|min:3|max:15',
        ]);

        $variantsCount = $request->input('variants', 7);

        // Credit Check
        $user = auth()->user();

        if (!$user->canUseTool('discover-headlines')) {
            $msg = $user->getLimitReachedMessage('Discover Headlines', 'discover-headlines');
            if ($isAjax) return response()->json(['status' => 'error', 'message' => $msg], 403);
            return redirect()->back()->with(['headlineError' => $msg]);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            $msg = 'Insufficient balance to generate headlines.';
            if (!$isAjax) return redirect()->back()->with(['headlineError' => $msg]);
            return response()->json(['status' => 'error', 'message' => $msg], 402);
        }

        if ($progressId) {
            Cache::put("gen_progress_{$progressId}", [
                'stage' => 'starting',
                'message' => 'Starting process and analyzing inputs...'
            ], 300);
        }

        try {
            // 1. Prepare Context
            if ($type === 'keyword') {
                if ($progressId) {
                    Cache::put("gen_progress_{$progressId}", [
                        'stage' => 'searching',
                        'message' => 'Searching Google for the latest updates...'
                    ], 300);
                }
                
                $newsContext = $this->fetchNewsContext($keyword, $region, $progressId);
                
                if (empty($newsContext)) {
                    // Fallback for VidaNexus as per user suggestion to avoid empty results
                    $newsContext = "موضوع البحث: " . ($keyword ?: 'عام');
                    Log::info("[Headlines] Using fallback context for keyword: " . $keyword);
                }
            } else {
                $newsContext = $content;
            }

            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'ai_processing',
                    'message' => 'Drafting creative headlines using Artificial Intelligence...'
                ], 300);
            }

            // 2. Prepare Detailed Prompt (Sync with EahelQesa Laws)
            $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $keyword . $content);
            $discoverRules = $this->getDiscoverRules($isArabic);
            $technicalWrapper = $this->getHeadlinesTechnicalWrapper($variantsCount, $region, $isArabic);

            if ($type === 'keyword') {
                $userStyle = $this->getDefaultHeadlinesStyle($isArabic);
                $sysRole = $isArabic ? "أنت محترف صياغة عناوين إخبارية." : "You are a professional news headline specialist. Follow the style and rules below:";
                $prompt = "{$sysRole}\n\n" . $userStyle;
                $prompt .= "\n\n" . $discoverRules;
                $prompt .= "\n\n" . $technicalWrapper;
                $prompt = str_replace(['[Keyword]', '[keyword]'], $keyword, $prompt);
                $prompt = str_replace('[NewsContext]', $newsContext, $prompt);
            } else {
                $sysRole = $isArabic ? "أنت محرر صحفي خبير في تحليل المحتوى الإخباري لـ Google Discover.\nمهمتك هي تحليل المحتوى التالي واستخراج {$variantsCount} عناوين احترافية مبنية على الحقائق ولكن بصياغة تجذب الملايين." 
                                     : "You are an expert journalist analyzing news content for Google Discover.\nYour task is to analyze the following content and extract {$variantsCount} factual but highly engaging headlines.";
                $prompt = "{$sysRole}\n\n" .
                          $discoverRules . "\n\n" .
                          ($isArabic ? "🔹 المحتوى المراد تحليله:\n" : "🔹 Content to analyze:\n") . $newsContext . "\n\n" .
                          $technicalWrapper;
            }

            // 3. Call AI Proxy (VidaNexus AIManager)
            $dbProvider = Setting::where('key', 'discover-headlines_provider')->first()?->value ?? 'openrouter';
            $dbModel = Setting::where('key', 'discover-headlines_model')->first()?->value ?? 'google/gemini-2.0-flash-001';
            $dbPrompt = Setting::where('key', 'discover-headlines_prompt')->first()?->value;

            if ($dbPrompt) {
                $finalPrompt = str_replace(
                    ['[Keyword]', '[keyword]', '[NewsContext]', '[variants]'], 
                    [$keyword, $keyword, $newsContext, $variantsCount], 
                    $dbPrompt
                );
                // Enforce technical requirements even with custom prompts
                $finalPrompt .= "\n\n" . $technicalWrapper;
            } else {
                $finalPrompt = $prompt;
            }

            $aiResponse = $this->aiManager->generate('discover-headlines', $finalPrompt, [
                'provider' => $dbProvider,
                'model' => $dbModel,
                'temperature' => 0.8,
            ]);

            $generatedText = $aiResponse['text'];
            
            // CLEANING: Strip Markdown code blocks if any
            if (preg_match('/```(?:json|markdown|text|)?\s*(.*?)\s*```/s', $generatedText, $matches)) {
                $generatedText = $matches[1];
            }
            $generatedText = trim($generatedText);

            // SUPPORT FOR JSON FORMAT (Handle array, object, or multi-line objects like Gemini sometimes returns)
            $extracted = [];
            $decoded = @json_decode($generatedText, true);
            
            if (is_array($decoded)) {
                // Case 1: Standard JSON Array (e.g., [{"headline": "..."}])
                foreach ($decoded as $item) {
                    if (is_array($item)) {
                        $val = $item['headline'] ?? $item['title'] ?? $item['text'] ?? $item['keyword'] ?? null;
                        if ($val) $extracted[] = $val;
                    } elseif (is_string($item)) {
                        $extracted[] = $item;
                    }
                }
            } else {
                // Case 2: Multi-line JSON objects (e.g., {"headline": "..."} \n {"headline": "..."})
                $lines = explode("\n", $generatedText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $lineDecoded = @json_decode($line, true);
                    if (is_array($lineDecoded)) {
                        $val = $lineDecoded['headline'] ?? $lineDecoded['title'] ?? $lineDecoded['text'] ?? null;
                        if ($val) $extracted[] = $val;
                    }
                }
            }

            if (!empty($extracted)) {
                $generatedText = implode("\n", $extracted);
            }

            // ENCODING ENFORCEMENT
            $generatedText = iconv('UTF-8', 'UTF-8//IGNORE', $generatedText);

            // 4. Advanced Scoring (The "Laws")
            $scoredHeadlines = $this->scoreHeadlines($generatedText, $keyword ?? '');

            // STRICT BILLING: Deduct Credit ONLY if AI actually produced scored headlines!
            if (!empty($scoredHeadlines)) {
                $user->wallet->decrement('balance_credits', 1);
                \App\Models\AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'discover-headlines',
                    'provider' => $dbProvider,
                    'model' => $dbModel,
                    'status' => 'success',
                ]);
            } else {
                \App\Models\ToolError::log('discover-headlines', new \Exception("AI produced 0 scored headlines. Raw response: " . substr($generatedText, 0, 100)), 'Content Formatting', $user->id);
            }

            if ($progressId) {
                $finalData = [
                    'stage' => 'completed',
                    'message' => 'Headlines generated successfully!',
                    'headlines' => $generatedText,
                    'scored' => $scoredHeadlines,
                    'keyword' => $keyword
                ];
                Cache::put("gen_progress_{$progressId}", $finalData, 1200);
            }

            Log::info("[Headlines] Generation completed successfully", ['pid' => $progressId]);

            return response()->json([
                'status' => 'success',
                'headlines' => $generatedText,
                'scored' => $scoredHeadlines,
                'keyword' => $keyword,
                'type' => $type
            ]);

        } catch (\Exception $e) {
            Log::error("Headline Generation Error: " . $e->getMessage());
            if ($progressId) {
                Cache::put("gen_progress_{$progressId}", [
                    'stage' => 'error',
                    'message' => 'Failed to generate headlines: ' . $e->getMessage()
                ], 300);
            }
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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
        $countryMap = $this->getCountryMap();
        $countryData = $countryMap[$region] ?? ['lang' => 'ar'];
        $lang = $countryData['lang'] ?? 'ar';
        $ceid = "{$region}:{$lang}";

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
        $windows = ['when:24h', 'when:7d', 'when:30d', 'broad'];
        
        foreach ($windows as $window) {
            $timeParam = ($window === 'broad') ? "" : " " . $window;
            $url = "https://news.google.com/rss/search?q=" . urlencode($keyword . $timeParam) . "&hl={$lang}&gl={$region}&ceid={$ceid}";
            
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
            Cache::put($cacheKey, $context, 1800);
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
    protected function scoreHeadlines($headlinesText, $keyword = '')
    {
        $headlinesText = preg_replace('/[\x{2028}\x{2029}]/u', '', $headlinesText);
        $lines = explode("\n", str_replace("\r\n", "\n", str_replace("\r", "\n", trim($headlinesText))));
        $lines = array_filter($lines, function($l) { return mb_strlen(trim($l)) > 4; });
        $scored = [];

        foreach ($lines as $line) {
            $headline = trim($line);
            
            // Clean prefixes and JSON noise
            $headline = preg_replace('/^\s*\d{1,2}[\.\)]\s+/u', '', $headline);
            $headline = preg_replace('/^\s*[\-\*•]\s+/u', '', $headline);
            
            // JSON stripping: If line looks like {"key": "value"}, try to extract the likely headline
            if (Str::startsWith($headline, '{') && Str::endsWith($headline, '}')) {
                $json = @json_decode($headline, true);
                if (is_array($json)) {
                    $headline = $json['headline'] ?? $json['title'] ?? $json['text'] ?? $headline;
                }
            }

            $headline = preg_replace('/\*\*/u', '', $headline);
            $headline = preg_replace('/\.+$/u', '', trim($headline));
            
            if (empty($headline) || mb_strlen($headline) < 8) continue;
            
            // Filter conversational filler/intro text
            if (preg_match('/^(إليك|فيما يلي|هذه|نعرض|عناوين|في سياق|بناءً على)/ui', $headline)) {
                continue;
            }

            $score = 50; 
            $feedback = [];
            $len = mb_strlen($headline);

            // 1. Length (55-85)
            if ($len >= 55 && $len <= 85) {
                $score += 20;
                $feedback[] = ['type' => 'success', 'text' => 'Ideal Discover Length (' . $len . ' chars)'];
            } elseif ($len >= 45 && $len <= 100) {
                $score += 12;
                $feedback[] = ['type' => 'info', 'text' => 'Acceptable Length (' . $len . ' chars)'];
            } else {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => $len < 40 ? 'Too Short' : 'Too Long'];
            }

            // 2. Keyword
            if (!empty($keyword) && mb_stripos($headline, $keyword) !== false) {
                $score += 10;
                $feedback[] = ['type' => 'success', 'text' => 'Contains Target Keyword'];
            }

            // 3. Power Words
            $powerWords = ['يكشف', 'يفاجئ', 'يُعلن', 'يحسم', 'يتراجع', 'يصدر', 'عاجل', 'حصري', 'حقيقة', 'سر', 'رسمياً', 'reveals', 'surprises', 'announces', 'declares', 'drops', 'issues', 'urgent', 'exclusive', 'truth', 'secret', 'officially'];
            foreach ($powerWords as $word) {
                if (mb_stripos($headline, $word) !== false) {
                    $score += 5;
                    $feedback[] = ['type' => 'success', 'text' => 'Action Verb: «' . $word . '»'];
                    break;
                }
            }

            // 4. Curiosity Gap
            if (preg_match('/(لماذا|كيف|ماذا|هل|سبب|بالفيديو|شاهد|why|how|what|is|reason|video|watch)/ui', $headline)) {
                $score += 8;
                $feedback[] = ['type' => 'success', 'text' => 'Curiosity Gap'];
            }

            // 5. Numbers
            if (preg_match('/\d+/', $headline)) {
                $score += 8;
                $feedback[] = ['type' => 'success', 'text' => 'Contains Numbers (Boosts CTR)'];
            }

            // 6. Entity-First
            $entityPatterns = ['ال', 'محمد', 'أحمد', 'رئيس', 'وزير', 'شركة', 'نادي', 'the', 'president', 'minister', 'company', 'club', 'mr', 'dr'];
            $firstWord = mb_strtolower(mb_substr($headline, 0, mb_strpos($headline, ' ') ?: mb_strlen($headline)));
            $isEntity = false;
            foreach ($entityPatterns as $ep) { if (mb_strpos($firstWord, mb_strtolower($ep)) === 0) { $isEntity = true; break; } }
            
            // Allow capitalized English words to count as Entity-first heavily if first letter is capitalized
            if (preg_match('/^[A-Z][a-z]+/', $headline)) {
                $isEntity = true;
            }

            if ($isEntity) {
                $score += 7;
                $feedback[] = ['type' => 'success', 'text' => 'Entity-First Headline'];
            }

            // 7. Freshness
            if (preg_match('/(الآن|اليوم|لأول مرة|عاجل|now|today|first time|breaking)/ui', $headline)) {
                $score += 5;
                $feedback[] = ['type' => 'success', 'text' => 'Freshness Signal'];
            }

            // 8. Brackets
            if (preg_match('/\[.+?\]/u', $headline)) {
                $score += 5;
                $feedback[] = ['type' => 'success', 'text' => 'Bracket Classification [Exclusive]'];
            }

            // 9. Clickbait Penalty
            $clickbait = ['لن تصدق', 'شاهد قبل الحذف', 'فضيحة', 'اضغط هنا', 'you won\'t believe', 'watch before deleted', 'scandal', 'click here'];
            foreach ($clickbait as $cb) {
                if (mb_stripos($headline, $cb) !== false) {
                    $score -= 20;
                    $feedback[] = ['type' => 'danger', 'text' => 'Clickbait Forbidden: «' . $cb . '»'];
                }
            }

            // 10. Emoji Penalty
            if (preg_match('/[\x{1F600}-\x{1F64F}]/u', $headline)) {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => 'Contains Emoji (Rejected)'];
            }

            // 11. Punctuation Penalty
            if (preg_match('/[!؟?]{2,}/', $headline)) {
                $score -= 10;
                $feedback[] = ['type' => 'warning', 'text' => 'Excessive Punctuation'];
            }

            // 12. Repetition Penalty
            $words = preg_split('/\s+/u', mb_strtolower($headline));
            if (count($words) !== count(array_unique($words))) {
                $score -= 8;
                $feedback[] = ['type' => 'warning', 'text' => 'Word Repetition'];
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
                   "1. **الفضول المبني على الحقائق:** اثارة الاهتمام باستخدام حقائق دقيقة من السياق. لا تستخدم العناوين المُضللة.\n" .
                   "2. **الكيان أولاً:** إعطاء الأولوية لأسماء الأشخاص، الأماكن، أو المنظمات في بداية العنوان.\n" .
                   "3. **قيمة مقترحة واضحة:** يجب أن يعرف القارئ تماماً ما سيقرأه.\n" .
                   "4. **لا للتهويل:** تجنب المبالغات التي لا يدعمها المصدر.\n" .
                   "5. **أنماط بـ CTR عالي:** استخدم 'السبب وراء'، 'كيف'، 'الكشف عن'، أو 'تفاصيل أولية'.";
        }

        return "🔹 Google Discover STRICT Compliance Rules:\n" .
               "1. **Fact-Based Curiosity:** Generate interest using specific facts from the context. NO vague clickbait (e.g. avoid 'You won't believe what happened').\n" .
               "2. **Entity-First Headlines:** Prioritize names of people, places, or organizations at the start.\n" .
               "3. **Clear Value Proposition:** The reader must know exactly what they are clicking into.\n" .
               "4. **No Sensationalism:** Avoid excessive adjectives or hyperbolic claims not found in the source.\n" .
               "5. **High CTR Patterns:** Use 'The reason why', 'How to', 'Secret revealed', or 'Initial details'.";
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
        return "🔹 FINAL OUTPUT REQUIREMENTS (STRICT):
- Generate EXACTLY {$count} headlines.
- Output ONLY the headlines, absolutely nothing else.
- DO NOT add conversational text.
- NO intro text, NO numbering, NO quotes, NO markdown, NO asterisks, NO emoji.
- ONE HEADLINE PER LINE.
- **LANGUAGE ENFORCEMENT: You MUST write the output in " . ($isArabic ? "ARABIC" : "ENGLISH") . ". This is ABSOLUTELY MANDATORY.**
- **Target Length: EACH headline MUST be between 55 and 85 characters long.**
- MANDATORY pattern variety across the {$count} headlines:
    • 1 Entity-first factual (Name + Action verb + Detail)
    • 1 Analytical/Why (Why/How + Surprising fact)
    • 1 Number-driven (Number + Exciting facts)
    • 1 With bracket tag (" . ($isArabic ? "[تقرير] أو [حصري]" : "[Report] or [Exclusive]") . ")
- **MANDATORY Angle Diversity**: Every headline MUST take a completely different narrative angle OF THE SAME TARGET KEYWORD. DO NOT jump to unrelated topics found in the context.";
    }

    protected function getTrendingSuggestions($region)
    {
        $cacheKey = 'headline_trending_' . $region;
        return Cache::remember($cacheKey, 10, function() use ($region) {
            try {
                $url = "https://trends.google.com/trending/rss?geo=" . $region . "&sort=recency";
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        $suggestions = [];
                        foreach ($xml->channel->item as $item) {
                            $suggestions[] = ['keyword' => (string)$item->title];
                            if (count($suggestions) >= 10) break;
                        }
                        return $suggestions;
                    }
                }
            } catch (\Exception $e) {}
            return [];
        });
    }

    protected function getCountryMap()
    {
        return config('keywords.countries', [
            'EG' => ['name' => 'مصر', 'flag' => '🇪🇬', 'lang' => 'ar']
        ]);
    }
}
