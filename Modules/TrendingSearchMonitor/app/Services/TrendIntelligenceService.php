<?php

namespace Modules\TrendingSearchMonitor\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class TrendIntelligenceService
{
    /**
     * AI Trend Intelligence Logic
     * Analyzes a trend title and returns its story, monetization score, and strategy.
     */
    public function analyzeTrendWithAI(string $trendTitle, string $region, string $lang, string $platform = 'google', array $headlines = []): array
    {
        $aiManager = app(\App\Core\AI\AIManager::class);
        $langName = ($lang === 'ar') ? 'Arabic (العربية)' : 'English';
        
        $platformName = [
            'google' => 'Google Search',
            'x' => 'Twitter (X)',
            'twitter' => 'Twitter (X)',
            'tiktok' => 'TikTok'
        ][$platform] ?? 'Search Engines';

        // ─── V2.7: Context-awareness (Inject Headlines) ───
        $headlinesContext = !empty($headlines) ? "\nRelated News Headlines (FACTS):\n- " . implode("\n- ", $headlines) : "";
        $currentTime = now()->toDayDateTimeString();
        $dateContext = "\nReference Date: " . $currentTime;

        // ─── V2.6: Dynamic Prompt Fetching ───
        $dbPrompt = Setting::get('trending-search-monitor_ai_analysis_prompt', '');
        
        if (!empty($dbPrompt)) {
            $prompt = str_ireplace(
                ['[Trend]', '[Country]', '[Lang]', '[Platform]', '[Headlines]', '[Date]'],
                [$trendTitle, $region, $langName, $platformName, $headlinesContext, $dateContext],
                $dbPrompt
            );
        } else {
            $prompt = ($lang === 'ar') 
                ? "أنت خبير سيو ومحلل تريندات محترف.
                   قاعدة صارمة: يجب أن يكون تحليلك مبنياً بنسبة 100% على العناوين الإخبارية المقدمة أدناه. تجاهل أي معلومات عامة سابقة عن الشخصية أو الموضوع إذا كانت تخالف السياق الحالي.
                   
                   التريند: {$trendTitle}
                   المنصة: {$platformName}
                   المنطقة: {$region}
                   {$dateContext}
                   {$headlinesContext}
                   
                   الأهداف:
                   1. لماذا هذا التريند رائج الآن؟ (حلل الخبر الحالي الموجود في العناوين فقط! مثال: لو العناوين عن خطوبة، لا تتكلم عن السياسة).
                   2. زاوية محتوى مقترحة (Angle) تتفوق على ما هو منشور حالياً وتستهدف الجمهور المهتم بهذا الخبر تحديداً.
                   3. درجة صعوبة المنافسة (0-100).
                   
                   يجب أن تكون الإجابة بصيغة JSON فقط بهذا الشكل:
                   {
                     \"why_trending\": \"اشرح السبب الحقيقي للتريند بناءً على الأخبار الحالية فقط\",
                     \"opportunity_score\": 85,
                     \"content_strategy\": \"زاوية المحتوى المقترحة للتفوق على المنافسين\",
                     \"sentiment\": \"positive|negative|neutral\",
                     \"viral_velocity\": \"high|medium|low\",
                     \"difficulty_score\": 30
                   }
                   
                   Crucial: Your entire response MUST be in {$langName}."
                : "You are a senior SEO strategist and Trend Analyst.
                   STRICT RULE: Your analysis MUST be 100% derived from the news headlines provided below. IGNORE general biographical or historical knowledge. Focus only on the current event.
                   
                   Trend: {$trendTitle}
                   Platform: {$platformName}
                   Region: {$region}
                   {$dateContext}
                   {$headlinesContext}
                   
                   Goals:
                   1. Why is this trending? (Analyze the SPECIFIC event in the headlines. i.e., if it's an engagement, don't talk about general career).
                   2. Unique content angle (Angle) for publishers to outrank existing coverage of this specific event.
                   3. Difficulty score (0-100).
                   
                   Return ONLY a JSON object in this format:
                   {
                     \"why_trending\": \"Actual reason for the trend based STRICTLY on current news headlines\",
                     \"opportunity_score\": 85,
                     \"content_strategy\": \"Unique angle for the story\",
                     \"sentiment\": \"positive|negative|neutral\",
                     \"viral_velocity\": \"high|medium|low\",
                     \"difficulty_score\": 30
                   }
                   
                   Crucial: Your entire response MUST be in {$langName}.";
        }

        try {
            $result = $aiManager->generate('trending-search-monitor', $prompt, [
                'temperature' => 0.4,
                'max_tokens'  => 1000,
            ]);
            
            $responseText = $result['text'] ?? '';
            
            // Extract JSON
            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $responseText, $matches)) {
                $responseText = trim($matches[1]);
            }
            $responseText = trim($responseText);
            
            $parsed = json_decode($responseText, true);
            
            if ($parsed && isset($parsed['opportunity_score'])) {
                return [
                    'success' => true,
                    'analysis' => $parsed,
                ];
            }
            
            Log::warning('[TrendIntelligence AI] Failed to parse response: ' . substr($responseText, 0, 500));
            return ['success' => false, 'message' => 'AI analysis formatting error.'];
            
        } catch (\Exception $e) {
            Log::error('[TrendIntelligence AI] Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Helper to estimate opportunity score without AI (offline mode)
     */
    public function getEstimatedScore(string $title): int
    {
        $score = 50; // base
        $titleLower = mb_strtolower($title);
        
        // High Value Keyword Patterns
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
