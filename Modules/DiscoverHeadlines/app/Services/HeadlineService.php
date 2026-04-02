<?php

namespace Modules\DiscoverHeadlines\Services;

use App\Core\AI\AIManager;
use App\Models\Setting;
use App\Models\User;
use App\Models\AiUsage;
use App\Models\ToolError;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HeadlineService
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Generate headlines for a given keyword or content.
     */
    public function generate($userId, $params)
    {
        $user = User::find($userId);
        if (!$user) return ['status' => 'error', 'message' => 'User not found'];

        $keyword = $params['keyword'] ?? '';
        $content = $params['content'] ?? '';
        $type = $params['type'] ?? 'keyword';
        $region = strtoupper($params['country'] ?? 'EG');
        $progressId = $params['progress_id'] ?? null;
        $variantsCount = $params['variants'] ?? 7;

        if ($progressId) {
            $this->updateProgress($progressId, 'starting', 'Analyzing inputs and preparing brain...');
        }

        try {
            // 1. Prepare Context
            if ($type === 'keyword') {
                if ($progressId) {
                    $this->updateProgress($progressId, 'searching', 'Searching Google for latest trends...');
                }
                
                $newsContext = $this->fetchNewsContext($keyword, $region, $progressId);
                
                if (empty($newsContext)) {
                    $newsContext = "موضوع البحث: " . ($keyword ?: 'عام');
                }
            } else {
                $newsContext = $content;
            }

            if ($progressId) {
                $this->updateProgress($progressId, 'ai_processing', 'Synthesizing headlines with Neural Engine...');
            }

            // 2. Prepare Detailed Prompt
            $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $keyword . $content);
            $discoverRules = $this->getDiscoverRules($isArabic);
            $technicalWrapper = $this->getHeadlinesTechnicalWrapper($variantsCount, $region, $isArabic);

            $dbProvider = Setting::get("discover-headlines_provider", 'openrouter');
            $dbModel = Setting::get("discover-headlines_model", 'google/gemini-2.0-flash-001');
            $dbPrompt = Setting::get("discover-headlines_prompt");

            if ($dbPrompt) {
                $finalPrompt = str_replace(
                    ['[Keyword]', '[keyword]', '[NewsContext]', '[variants]'], 
                    [$keyword, $keyword, $newsContext, $variantsCount], 
                    $dbPrompt
                );
                $finalPrompt .= "\n\n" . $technicalWrapper;
            } else {
                // Default Prompt Building
                $userStyle = $this->getDefaultHeadlinesStyle($isArabic);
                $sysRole = $isArabic ? "أنت محترف صياغة عناوين إخبارية." : "You are a professional news headline specialist.";
                $prompt = "{$sysRole}\n\n" . $userStyle . "\n\n" . $discoverRules . "\n\n" . $technicalWrapper;
                $prompt = str_replace(['[Keyword]', '[keyword]'], $keyword, $prompt);
                $prompt = str_replace('[NewsContext]', $newsContext, $prompt);
                $finalPrompt = $prompt;
            }

            // AI Routing Config
            $aiChain = Setting::get("discover-headlines_ai_chain", []);
            $aiConfig = [
                'provider' => $dbProvider,
                'model' => $dbModel,
                'temperature' => 0.8,
            ];
            if (!empty($aiChain)) $aiConfig['chain'] = $aiChain;

            // Call AI
            $aiResponse = $this->aiManager->generate('discover-headlines', $finalPrompt, $aiConfig);
            $generatedText = $aiResponse['text'];
            
            // Cleaning Markdown
            if (preg_match('/```(?:json|markdown|text|)?\s*(.*?)\s*```/s', $generatedText, $matches)) {
                $generatedText = $matches[1];
            }
            $generatedText = trim($generatedText);

            // Extract Results
            $extracted = $this->extractHeadlines($generatedText);
            
            // 4. Advanced Scoring
            $scoredHeadlines = $this->scoreHeadlines($extracted, $keyword ?? '');

            // 5. Billing logic ONLY on success
            if (!empty($scoredHeadlines)) {
                if ($user->wallet) {
                    $user->wallet->decrement('balance_credits', 1);
                }
                
                AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => 'discover-headlines',
                    'provider' => $dbProvider,
                    'model' => $dbModel,
                    'status' => 'success',
                ]);
            } else {
                ToolError::log('discover-headlines', new \Exception("AI produced 0 scored headlines. Raw response: " . substr($generatedText, 0, 100)), 'Content Formatting', $user->id);
            }

            $finalResult = [
                'status' => 'success',
                'headlines' => $generatedText,
                'scored' => $scoredHeadlines,
                'keyword' => $keyword,
                'type' => $type
            ];

            if ($progressId) {
                $progressData = array_merge(['stage' => 'completed', 'message' => 'Headlines generated!'], $finalResult);
                Cache::put("gen_progress_{$progressId}", $progressData, 1200);
            }

            return $finalResult;

        } catch (\Exception $e) {
            Log::error("HeadlineService Error: " . $e->getMessage());
            if ($progressId) {
                $this->updateProgress($progressId, 'error', 'Operation failed: ' . $e->getMessage());
            }
            throw $e;
        }
    }

    protected function extractHeadlines($text)
    {
        $extracted = [];
        $jsonTarget = $text;
        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
            $jsonTarget = $matches[0];
        }

        $decoded = @json_decode($jsonTarget, true);
        
        if (is_array($decoded) && (isset($decoded['headlines']) || isset($decoded[0]))) {
            $items = $decoded['headlines'] ?? $decoded;
            foreach ($items as $item) {
                if (is_array($item)) {
                    $extracted[] = [
                        'headline' => $item['headline'] ?? $item['title'] ?? $item['text'] ?? $item['keyword'] ?? '',
                        'sentiment' => $item['sentiment'] ?? 'Neutral',
                        'entities' => $item['entities'] ?? $item['keywords'] ?? [],
                        'lsi_keywords' => $item['lsi_keywords'] ?? $item['lsi'] ?? [],
                        'thumbnail_suggestion' => $item['thumbnail_suggestion'] ?? $item['thumbnail'] ?? $item['visual_angle'] ?? '',
                    ];
                }
            }
        } else {
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || mb_strlen($line) < 10) continue;
                $extracted[] = [
                    'headline' => preg_replace('/^\d+[\.\)]\s*/', '', $line),
                    'sentiment' => 'Factual',
                    'entities' => [], 'lsi_keywords' => [], 'thumbnail_suggestion' => '',
                ];
            }
        }
        return $extracted;
    }

    protected function fetchNewsContext($keyword, $region, $progressId = null)
    {
        $countryMap = config('keywords.countries', ['EG' => ['name' => 'مصر', 'flag' => '🇪🇬', 'lang' => 'ar']]);
        $countryData = $countryMap[$region] ?? ['lang' => 'ar'];
        $lang = $countryData['lang'] ?? 'ar';
        $ceid = "{$region}:{$lang}";

        $cacheKey = 'headline_news_v2_' . $region . '_' . md5(mb_strtolower(trim($keyword)));
        $cached = Cache::get($cacheKey);
        if ($cached) {
            if ($progressId) $this->updateProgress($progressId, 'searching', 'News retrieved from sync engine ⚡');
            return $cached;
        }

        $tempContext = "";
        $configuredWindow = Setting::get("discover-headlines_rss_time_window", '12h');
        $windows = array_values(array_unique(["when:{$configuredWindow}", 'when:24h', 'when:7d', 'broad']));
        
        foreach ($windows as $window) {
            $timeParam = ($window === 'broad') ? "" : " " . $window;
            $url = "https://news.google.com/rss/search?q=" . urlencode($keyword . $timeParam) . "&hl={$lang}&gl={$region}&ceid={$ceid}";
            try {
                if ($progressId) $this->updateProgress($progressId, 'searching', "Scouring the news archive ({$window})...");
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
                if (substr_count($tempContext, "\n") >= 5) break;
            } catch (\Exception $e) { }
        }

        if (!empty($tempContext)) {
            $ttl = Setting::get("discover-headlines_cache_ttl", 1800);
            Cache::put($cacheKey, $tempContext, (int) $ttl);
            return $tempContext;
        }
        return "";
    }

    protected function scoreHeadlines($headlinesData, $keyword = '')
    {
        $scored = [];
        foreach ($headlinesData as $data) {
            $headline = $data['headline'] ?? '';
            if (empty($headline) || mb_strlen($headline) < 8) continue;
            
            $score = 40; 
            $feedback = [];
            $len = mb_strlen($headline);
            $minChars = (int) Setting::get("discover-headlines_min_chars", 55);
            $maxChars = (int) Setting::get("discover-headlines_max_chars", 85);

            if ($len >= $minChars && $len <= $maxChars) {
                $score += 20;
                $feedback[] = ['type' => 'success', 'text' => 'Ideal Discover Length (' . $len . ' chars)'];
            } else {
                $score -= 15;
                $feedback[] = ['type' => 'danger', 'text' => 'Sub-optimal Length'];
            }

            if (!empty($keyword)) {
                $keywordLower = mb_strtolower(trim($keyword));
                if (mb_stripos($headline, $keywordLower) !== false) {
                    $score += 30;
                    $feedback[] = ['type' => 'success', 'text' => 'Target Keyword Included (+30)'];
                } else {
                    $score -= 30;
                    $feedback[] = ['type' => 'danger', 'text' => 'Missing Keyword Context (-30)'];
                }
            }
            
            if (!empty($data['entities'])) {
                $score += 10;
                $feedback[] = ['type' => 'success', 'text' => 'Entity Mapping (+10)'];
            }

            $powerWordsRaw = Setting::get("discover-headlines_power_words", "يكشف, يفاجئ, يُعلن, يحسم, يتراجع, يصدر, عاجل, حصري, حقيقة, سر, رسمياً");
            $powerWords = array_map('trim', explode(',', $powerWordsRaw));
            foreach ($powerWords as $word) {
                if (!empty($word) && mb_stripos($headline, $word) !== false) {
                    $score += 5;
                    break;
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
        if ($score >= 85) return ['label' => 'EXCELLENT', 'color' => 'green', 'emoji' => '🔥'];
        if ($score >= 70) return ['label' => 'VERY GOOD', 'color' => 'green', 'emoji' => '✅'];
        if ($score >= 55) return ['label' => 'GOOD', 'color' => 'blue', 'emoji' => '👍'];
        return ['label' => 'POOR', 'color' => 'red', 'emoji' => '⚠️'];
    }

    protected function getDiscoverRules($isArabic = true)
    {
        return $isArabic ? "🔹 قواعد امتثال Google Discover..." : "🔹 Google Discover Rules...";
    }

    protected function getDefaultHeadlinesStyle($isArabic = true)
    {
        return $isArabic ? "أنت محرر أول..." : "You are a Senior Editor...";
    }

    protected function getHeadlinesTechnicalWrapper($count, $region, $isArabic = true)
    {
        $jsonStr = '{"headlines":[{"headline":"...","sentiment":"...","entities":[],"lsi_keywords":[],"thumbnail_suggestion":"..."}]}';
        return "🔹 FINAL OUTPUT REQUIREMENTS (STRICT JSON ONLY):\n- Generate {$count} headlines.\n- JSON STRUCTURE:\n" . $jsonStr;
    }

    protected function updateProgress($id, $stage, $message)
    {
        Cache::put("gen_progress_{$id}", ['stage' => $stage, 'message' => $message], 300);
    }
}
