<?php

namespace Modules\DiscoverHeadlines\Services;

use App\Core\AI\AIManager;
use App\Support\CountryRegistry;
use App\Support\GoogleNewsRss;
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

        $type = $params['type'] ?? 'keyword';

        // Defense-in-depth: even if a caller (or a stale form field) sent
        // both `keyword` and `content`, the active mode wins. In content
        // mode the user's pasted text IS the source of truth — a leftover
        // keyword from a previous tab would otherwise override it via the
        // [Keyword] placeholder in the prompt and produce a headline that
        // has nothing to do with what they typed.
        $keyword = $type === 'keyword' ? (string) ($params['keyword'] ?? '') : '';
        $content = $type === 'content' ? (string) ($params['content'] ?? '') : '';

        $region = CountryRegistry::normalizeCode($params['country'] ?? null)
            ?: CountryRegistry::defaultRegion();
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

            // In keyword mode the user's typed keyword is what every prompt
            // rule refers to with [Keyword]. In content mode we have no
            // keyword — re-anchor [Keyword] to "the primary subject derived
            // from the context below" so the same rule set (especially the
            // Strict Relevance Mandate) makes the AI lock onto whatever the
            // CONTENT is actually about, not a stale or unrelated input.
            $promptKeyword = $keyword !== ''
                ? $keyword
                : ($isArabic
                    ? 'الموضوع الرئيسي المستخرج من السياق الإخباري أدناه'
                    : 'the primary subject derived from the news context below');

            $dbProvider = Setting::get("discover-headlines_provider", 'openrouter');
            $dbModel = Setting::get("discover-headlines_model", 'google/gemini-2.0-flash-001');
            $dbPrompt = Setting::get("discover-headlines_prompt");

            if ($dbPrompt) {
                $finalPrompt = str_replace(
                    ['[Keyword]', '[keyword]', '[NewsContext]', '[variants]'],
                    [$promptKeyword, $promptKeyword, $newsContext, $variantsCount],
                    $dbPrompt
                );
                $finalPrompt .= "\n\n" . $technicalWrapper;
            } else {
                // Default Prompt Building
                $userStyle = $this->getDefaultHeadlinesStyle($isArabic);
                $sysRole = $isArabic ? "أنت محترف صياغة عناوين إخبارية." : "You are a professional news headline specialist.";
                $prompt = "{$sysRole}\n\n" . $userStyle . "\n\n" . $discoverRules . "\n\n" . $technicalWrapper;
                $prompt = str_replace(['[Keyword]', '[keyword]'], $promptKeyword, $prompt);
                $prompt = str_replace('[NewsContext]', $newsContext, $prompt);
                $finalPrompt = $prompt;
            }

            // AI Routing Config — `json_mode` makes OpenAI / OpenRouter set
            // response_format=json_object so models that support structured
            // output never wrap their reply in prose or markdown. The
            // accompanying `system_prompt` reinforces that constraint for
            // models (e.g. Gemini via OpenRouter) that don't enforce the API
            // flag on the server side.
            $aiChain = Setting::get("discover-headlines_ai_chain", []);
            $aiConfig = [
                'provider' => $dbProvider,
                'model' => $dbModel,
                'temperature' => 0.8,
                'json_mode' => true,
                'system_prompt' => 'You are a strict JSON generator. Your entire response MUST be a single valid JSON object matching the schema described in the user message. Do not include markdown, prose, comments, or trailing text — emit JSON only.',
            ];
            if (!empty($aiChain)) $aiConfig['chain'] = $aiChain;

            // Call AI
            $aiResponse = $this->aiManager->generate('discover-headlines', $finalPrompt, $aiConfig);
            $generatedText = (string) ($aiResponse['text'] ?? '');

            // Strip the most common markdown fences the model might still emit
            // even with json_mode (some providers wrap JSON in ```json ... ```).
            if (preg_match('/```(?:json|markdown|text|)?\s*(.*?)\s*```/s', $generatedText, $matches)) {
                $generatedText = $matches[1];
            }
            $generatedText = trim($generatedText);

            // Extract Results
            $extracted = $this->extractHeadlines($generatedText);
            
            // 4. Advanced Scoring
            $scoredHeadlines = $this->scoreHeadlines($extracted, $keyword ?? '');

            // 5. Billing logic ONLY on success — route through the canonical
            // service so the admin "Action Cost" (tool_credit_cost_discover-headlines)
            // is respected and ledger/transactions/audit log get written.
            if (!empty($scoredHeadlines)) {
                if (! $user->deductToolCredits('discover-headlines')) {
                    Log::critical('[Discover Headlines] Credits could not be deducted after successful generation', [
                        'user_id' => $user->id,
                    ]);
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

            // Pull the post-deduction wallet balance so the polling
            // progress endpoint can hand it to credits-live.js and the
            // chip animates without an extra /credits/balance roundtrip.
            $user->load('wallet');
            $balance = (float) ($user->wallet->balance_credits ?? 0);

            $finalResult = [
                'status' => 'success',
                'headlines' => $generatedText,
                'scored' => $scoredHeadlines,
                'keyword' => $keyword,
                'type' => $type,
                'balance' => $balance,
            ];

            if ($progressId) {
                $progressData = array_merge(['stage' => 'completed', 'message' => 'Headlines generated!'], $finalResult);
                Cache::put("gen_progress_{$progressId}", $progressData, 1200);
            }

            return $finalResult;

        } catch (\Exception $e) {
            Log::error("HeadlineService Error: " . $e->getMessage());

            // Surface the last provider-level error to the frontend instead
            // of the generic "AI provider is currently unavailable" wrapper.
            // The full attempts array is logged via AIManager::ai.all_failed
            // for ops; users just need the actionable upstream message.
            $userMessage = 'Operation failed: ' . $e->getMessage();
            if ($e instanceof \App\Core\AI\Exceptions\AIProviderFailureException && !empty($e->attempts)) {
                // `$e->attempts` is a readonly property — end() takes its
                // argument by reference and would throw "Cannot modify
                // readonly property". Indexing by array_key_last avoids
                // mutating the internal pointer.
                $lastKey = array_key_last($e->attempts);
                $last = $lastKey !== null ? $e->attempts[$lastKey] : null;
                $providerMsg = is_array($last) ? trim((string) ($last['error'] ?? '')) : '';
                if ($providerMsg !== '') {
                    $userMessage = 'AI generation failed: ' . $providerMsg;
                }
            }

            if ($progressId) {
                $this->updateProgress($progressId, 'error', $userMessage);
            }
            throw $e;
        }
    }

    /**
     * Parse the AI response into a normalized list of headline objects.
     *
     * Models occasionally return JSON in messy ways (markdown fences, leading
     * prose, double-escaped quotes from over-eager structured-output coercion,
     * or even a JSON string containing JSON). We walk a small set of recovery
     * strategies before falling back to a plain-text line-split — and the
     * plain-text branch explicitly REJECTS lines that still look like JSON so
     * a single un-parseable blob never renders as a fake "headline" in the UI
     * (which was the bug behind the raw-JSON card users were seeing).
     */
    protected function extractHeadlines($text)
    {
        $decoded = $this->decodeJsonFlexible($text);

        if (is_array($decoded)) {
            return $this->normalizeHeadlineList($decoded);
        }

        // JSON parsing failed entirely. Log enough to diagnose without dumping
        // the full response (could be many KB).
        Log::warning('[Discover Headlines] AI response is not valid JSON', [
            'json_error' => json_last_error_msg(),
            'snippet' => mb_substr((string) $text, 0, 240),
            'length' => mb_strlen((string) $text),
        ]);

        return $this->extractHeadlinesPlainText($text);
    }

    /**
     * Try several decode strategies in order. Return the first array we get.
     * Strings that themselves contain JSON (double-encoded) are re-decoded.
     */
    protected function decodeJsonFlexible(string $text): mixed
    {
        $trimmed = trim($text);

        // Drop any markdown fence the upstream caller might have missed (we
        // also strip in HeadlineService::generate, but doing it here keeps the
        // parser usable from other call sites in the future).
        if (preg_match('/```(?:json|markdown|text)?\s*(.*?)\s*```/s', $trimmed, $m)) {
            $trimmed = trim($m[1]);
        }

        $candidates = [$trimmed];

        // Substring from the first { to the last } — handles "prose then JSON
        // then prose" output from chatty models. We deliberately don't use a
        // recursive brace regex because JSON string values can legally contain
        // unbalanced braces inside quoted strings, which broke the previous
        // implementation.
        $first = strpos($trimmed, '{');
        $last  = strrpos($trimmed, '}');
        if ($first !== false && $last !== false && $last > $first) {
            $candidates[] = substr($trimmed, $first, $last - $first + 1);
        }

        // Some models (especially when forced into structured-output mode but
        // then asked to "wrap in quotes for safety") emit \"foo\" instead of
        // "foo". stripslashes on the candidates recovers those.
        foreach (array_values($candidates) as $candidate) {
            $candidates[] = stripslashes($candidate);
        }

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $tryDecoded = json_decode($candidate, true);

            // Double-encoded: the decode returned a string whose contents are
            // still JSON. Decode one more level.
            if (is_string($tryDecoded)
                && (str_contains($tryDecoded, '"headlines"') || str_contains($tryDecoded, '"headline"'))
            ) {
                $tryDecoded = json_decode($tryDecoded, true);
            }

            if (is_array($tryDecoded)) {
                return $tryDecoded;
            }
        }

        return null;
    }

    /**
     * Map a decoded JSON structure into our canonical headline shape. Accepts
     * either {headlines: [...]} or a bare top-level list.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeHeadlineList(array $decoded): array
    {
        $items = $decoded['headlines']
            ?? $decoded['data']
            ?? $decoded['results']
            ?? $decoded;

        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $headline = (string) ($item['headline'] ?? $item['title'] ?? $item['text'] ?? $item['keyword'] ?? '');
            $headline = trim($headline);

            // Defense in depth — don't let an item whose `headline` field is
            // itself raw JSON sneak through (some models put the JSON inside
            // the headline field when confused).
            if ($headline === '' || $this->looksLikeJsonFragment($headline)) {
                continue;
            }

            $out[] = [
                'headline' => $headline,
                'sentiment' => (string) ($item['sentiment'] ?? 'Neutral'),
                'entities' => $this->asStringList($item['entities'] ?? $item['keywords'] ?? []),
                'lsi_keywords' => $this->asStringList($item['lsi_keywords'] ?? $item['lsi'] ?? []),
                'thumbnail_suggestion' => (string) ($item['thumbnail_suggestion'] ?? $item['thumbnail'] ?? $item['visual_angle'] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * Plain-text fallback for models that refused to emit JSON. Filters out
     * lines that look like JSON fragments so a partial response never renders
     * as a card.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function extractHeadlinesPlainText(string $text): array
    {
        $extracted = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            $len = mb_strlen($line);
            if ($len < 10 || $len > 200) {
                continue;
            }
            if ($this->looksLikeJsonFragment($line)) {
                continue;
            }
            $extracted[] = [
                'headline' => preg_replace('/^\d+[\.\)]\s*/', '', $line),
                'sentiment' => 'Factual',
                'entities' => [],
                'lsi_keywords' => [],
                'thumbnail_suggestion' => '',
            ];
        }
        return $extracted;
    }

    /**
     * Heuristic check — does this string look like it's part of a JSON
     * payload rather than a real human-readable headline?
     */
    protected function looksLikeJsonFragment(string $line): bool
    {
        if (preg_match('/[{}\[\]]/', $line) === 1) {
            return true;
        }
        if (preg_match('/"\s*(headline|headlines|sentiment|entities|lsi_keywords|thumbnail_suggestion)"\s*:/i', $line) === 1) {
            return true;
        }
        return false;
    }

    /**
     * Coerce an "anything-shaped" field into a list of trimmed strings. Some
     * models emit comma-separated strings, some emit arrays, some emit arrays
     * of objects with a `name` key — we accept all three.
     *
     * @param mixed $value
     * @return array<int, string>
     */
    protected function asStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value) ?: [];
        }
        if (! is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $s = trim($item);
                if ($s !== '') {
                    $out[] = $s;
                }
                continue;
            }
            if (is_array($item)) {
                $s = trim((string) ($item['name'] ?? $item['text'] ?? $item['value'] ?? ''));
                if ($s !== '') {
                    $out[] = $s;
                }
            }
        }
        return $out;
    }

    protected function fetchNewsContext($keyword, $region, $progressId = null)
    {
        $region = CountryRegistry::normalizeCode($region) ?: CountryRegistry::defaultRegion();
        $lang = CountryRegistry::langFor($region);

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
            $url = GoogleNewsRss::searchUrl($keyword.$timeParam, $region, $lang);
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
