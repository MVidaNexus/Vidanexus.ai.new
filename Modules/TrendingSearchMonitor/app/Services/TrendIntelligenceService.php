<?php

namespace Modules\TrendingSearchMonitor\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class TrendIntelligenceService
{
    /**
     * AI Trend Intelligence Logic
     * Analyzes a trend title and returns its story, monetization score, and strategy.
     *
     * @param  array<int, array{title?: string, summary?: string, source?: string, date?: string}>  $articles
     */
    public function analyzeTrendWithAI(string $trendTitle, string $region, string $lang, string $platform = 'google', array $articles = []): array
    {
        $aiManager = app(\App\Core\AI\AIManager::class);
        $langName = ($lang === 'ar') ? 'Arabic (العربية)' : 'English';

        $platformName = [
            'google' => 'Google Search',
            'x' => 'Twitter (X)',
            'twitter' => 'Twitter (X)',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
        ][$platform] ?? 'Search Engines';

        $articlesContext = $this->formatArticlesContext($articles);
        $currentTime = now()->toDayDateTimeString();
        $dateContext = "\nReference Date: " . $currentTime;

        $dbPrompt = Setting::get('trending-search-monitor_ai_analysis_prompt', '');

        if (! empty($dbPrompt)) {
            $prompt = str_ireplace(
                ['[Trend]', '[Country]', '[Lang]', '[Platform]', '[Headlines]', '[Articles]', '[Date]'],
                [$trendTitle, $region, $langName, $platformName, $articlesContext, $articlesContext, $dateContext],
                $dbPrompt
            );
        } else {
            $jsonContract = '{
                     "why_trending": "Actual reason for the trend based STRICTLY on current news",
                     "key_reasons": ["reason 1", "reason 2", "reason 3"],
                     "opportunity_score": 85,
                     "content_strategy": "Primary unique angle for the story",
                     "content_angles": ["angle 1", "angle 2"],
                     "sentiment": "positive|negative|neutral",
                     "related_topics": ["topic 1", "topic 2"],
                     "viral_velocity": "high|medium|low",
                     "difficulty_score": 30
                   }';

            $prompt = ($lang === 'ar')
                ? "أنت خبير سيو ومحلل تريندات محترف.
                   قاعدة صارمة: يجب أن يكون تحليلك مبنياً بنسبة 100% على المقالات الإخبارية المقدمة أدناه. تجاهل أي معلومات عامة سابقة عن الشخصية أو الموضوع إذا كانت تخالف السياق الحالي.

                   التريند: {$trendTitle}
                   المنصة: {$platformName}
                   المنطقة: {$region}
                   {$dateContext}
                   {$articlesContext}

                   الأهداف:
                   1. لماذا هذا التريند رائج الآن؟ (حلل الخبر الحالي الموجود في المقالات فقط).
                   2. أسباب رئيسية للانتشار (key_reasons) مستمدة من محتوى الأخبار.
                   3. زوايا محتوى مقترحة (content_angles) تتفوق على ما هو منشور حالياً.
                   4. تحليل المشاعر (sentiment) والمواضيع/الكيانات ذات الصلة (related_topics).
                   5. درجة صعوبة المنافسة (0-100).

                   يجب أن تكون الإجابة بصيغة JSON فقط بهذا الشكل:
                   {$jsonContract}

                   Crucial: Your entire response MUST be in {$langName}."
                : "You are a senior SEO strategist and Trend Analyst.
                   STRICT RULE: Your analysis MUST be 100% derived from the news articles provided below. IGNORE general biographical or historical knowledge. Focus only on the current event.

                   Trend: {$trendTitle}
                   Platform: {$platformName}
                   Region: {$region}
                   {$dateContext}
                   {$articlesContext}

                   Goals:
                   1. Why is this trending? (Analyze the SPECIFIC event in the articles).
                   2. List key_reasons for virality derived from article content.
                   3. Suggest content_angles publishers can use to outrank existing coverage.
                   4. Provide sentiment analysis and related_topics/entities.
                   5. Difficulty score (0-100).

                   Return ONLY a JSON object in this format:
                   {$jsonContract}

                   Crucial: Your entire response MUST be in {$langName}.";
        }

        try {
            $result = $aiManager->generate('trending-search-monitor', $prompt, [
                'temperature' => 0.4,
                'max_tokens'  => 1200,
            ]);

            $responseText = $result['text'] ?? '';

            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $responseText, $matches)) {
                $responseText = trim($matches[1]);
            }
            $responseText = trim($responseText);

            $parsed = json_decode($responseText, true);

            if ($parsed && isset($parsed['why_trending'])) {
                $parsed = $this->normalizeAnalysis($parsed);

                return [
                    'success' => true,
                    'analysis' => $parsed,
                ];
            }

            Log::warning('[TrendIntelligence AI] Failed to parse response: ' . substr($responseText, 0, 500));

            return ['success' => false, 'message' => 'AI analysis formatting error. Please try again.'];
        } catch (\Exception $e) {
            Log::error('[TrendIntelligence AI] Error: ' . $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param  array<int, array{title?: string, summary?: string, source?: string, date?: string}>  $articles
     */
    protected function formatArticlesContext(array $articles): string
    {
        if (empty($articles)) {
            return '';
        }

        $lines = [];
        foreach (array_slice($articles, 0, 3) as $index => $article) {
            if (! is_array($article)) {
                continue;
            }
            $title = trim((string) ($article['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $source = trim((string) ($article['source'] ?? ''));
            $summary = trim((string) ($article['summary'] ?? $article['snippet'] ?? ''));
            $date = trim((string) ($article['date'] ?? ''));

            $line = ($index + 1) . '. ' . $title;
            if ($source !== '') {
                $line .= " (Source: {$source})";
            }
            if ($date !== '') {
                $line .= " [{$date}]";
            }
            if ($summary !== '') {
                $line .= "\n   Summary: {$summary}";
            }
            $lines[] = $line;
        }

        if (empty($lines)) {
            return '';
        }

        return "\nRelated News Articles (FACTS — analyze ONLY these):\n" . implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @return array<string, mixed>
     */
    protected function normalizeAnalysis(array $parsed): array
    {
        foreach (['key_reasons', 'content_angles', 'related_topics'] as $listKey) {
            if (! isset($parsed[$listKey]) || ! is_array($parsed[$listKey])) {
                $parsed[$listKey] = [];
            }
            $parsed[$listKey] = array_values(array_filter(array_map(
                fn ($v) => trim((string) $v),
                $parsed[$listKey]
            )));
        }

        if (empty($parsed['content_angles']) && ! empty($parsed['content_strategy'])) {
            $parsed['content_angles'] = [trim((string) $parsed['content_strategy'])];
        }

        $parsed['opportunity_score'] = (int) ($parsed['opportunity_score'] ?? 0);
        $parsed['difficulty_score'] = (int) ($parsed['difficulty_score'] ?? 0);
        $parsed['sentiment'] = $parsed['sentiment'] ?? 'neutral';
        $parsed['viral_velocity'] = $parsed['viral_velocity'] ?? 'medium';

        return $parsed;
    }

    /**
     * Helper to estimate opportunity score without AI (offline mode)
     */
    public function getEstimatedScore(string $title): int
    {
        $score = 50;
        $titleLower = mb_strtolower($title);

        $highValue = ['سعر', 'موعد', 'شراء', 'تطبيق', 'فرصة', 'price', 'buy', 'how to', 'best', 'sale'];
        foreach ($highValue as $hw) {
            if (str_contains($titleLower, $hw)) {
                $score += 15;
                break;
            }
        }

        return min(100, $score);
    }
}
