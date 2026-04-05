<?php

namespace Modules\ArticleWriter\Services;

use App\Core\AI\AIManager;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ArticleWriterService
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Generate a full SEO-optimized article.
     */
    public function generateArticle(array $data)
    {
        $keyword = $data['keyword'] ?? '';
        $topic = $data['topic'] ?? $keyword;
        $lang = $data['language'] ?? 'en';
        $tone = $data['tone'] ?? 'professional';
        $targetAudience = $data['audience'] ?? 'general';
        $wordCount = $data['word_count'] ?? (int) Setting::get('article-writer_default_word_count', 1500);
        $components = $data['components'] ?? ['faq', 'summary', 'takeaways', 'meta'];

        // 1. Fetch Real-Time Grounding (Live Research)
        $newsContext = "";
        $isGroundingEnabled = Setting::get('article-writer_live_search_enabled', true);
        if ($isGroundingEnabled && !empty($keyword)) {
            $newsContext = $this->fetchNewsGrounding($keyword, $lang);
        }

        // 2. Build the Synthesis Protocol (Nuclear Prompt)
        $finalPrompt = $this->buildNuclearPrompt($keyword, $topic, $lang, $components, $wordCount, $tone, $targetAudience, $newsContext);

        // 2. Wrap with User Instruction if needed, or check for legacy wrapper
        $customWrapper = Setting::get('article-writer_prompt', ''); // Legacy/Global wrapper
        $maxTokens = (int) Setting::get('article-writer_max_tokens', 8000);
        $langName = $this->getLanguageName($lang);

        if (!empty($customWrapper)) {
            // If admin still uses the one-big-prompt box, we honor it but inject our synthesized parts if placeholders exist
            $replacements = [
                '[prompt]' => "GENERATE ARTICLE NOW for [keyword]",
                '[topic]' => $topic,
                '[keyword]' => $keyword,
                '[tone]' => $tone,
                '[audience]' => $targetAudience,
                '[language]' => $langName,
                '[word_count]' => $wordCount,
                '[components]' => implode(', ', $components),
                '[year]' => $this->getCurrentYear()
            ];
            $finalPrompt = str_replace(array_keys($replacements), array_values($replacements), $customWrapper) . "\n\n" . $finalPrompt;
        }

        // 3. Execute AI Generation
        $result = $this->aiManager->generate('article-writer', $finalPrompt, [
            'max_tokens' => $maxTokens,
        ]);

        return $result;
    }

    /**
     * Build the Nuclear-Grade SEO Prompt using dedicated admin modules.
     */
    protected function buildNuclearPrompt($keyword, $topic, $lang, array $components, int $wordCount, string $tone, string $audience, $newsContext = "")
    {
        $year = $this->getCurrentYear();
        $langName = $this->getLanguageName($lang);
        $slug = 'article-writer';

        // Replacements Map
        $vars = [
            '[keyword]' => $keyword,
            '[topic]' => $topic,
            '[language]' => $langName,
            '[tone]' => $this->getToneDirective($tone),
            '[audience]' => $this->getAudienceDirective($audience),
            '[word_count]' => $wordCount,
            '[year]' => $year,
            '[news_context]' => $newsContext,
        ];

        $prompt = "";

        // 0. Grounding Analysis (High Priority)
        if (!empty($newsContext)) {
            $prompt .= "# REAL-TIME RESEARCH & GROUNDING DATA\n";
            $prompt .= "You have been provided with the LATEST search results and news data for [keyword].\n";
            $prompt .= "STRICT REQUIREMENT: You MUST prioritize these facts over your internal training data. Use specific dates, names, and events from this context to ensure the article is 100% up-to-date for [year].\n\n";
            $prompt .= "LATEST NEWS CONTEXT:\n{$newsContext}\n\n";
        }

        // 1. Title Section (Mandatory)
        $titlePrompt = Setting::get("{$slug}_prompt_title", "Generate a Google Discover-optimized headline for [keyword] in [language]. Title should be 8-14 words, use power words, and trigger curiosity.");
        $prompt .= "# TITLE ENGINEERING PROTOCOL\n" . $this->replaceVars($titlePrompt, $vars) . "\n\n";

        // 2. Body Section (Mandatory)
        $bodyPrompt = Setting::get("{$slug}_prompt_body", "Write a [word_count]-word comprehensive SEO article about [keyword] in [language]. Use expert tone, cover all angles, and ensure E-E-A-T compliance.");
        $prompt .= "# CONTENT CORE PROTOCOL\n" . $this->replaceVars($bodyPrompt, $vars) . "\n\n";

        // 3. Components (Dynamic)
        if (in_array('summary', $components)) {
            $compPrompt = Setting::get("{$slug}_prompt_summary", "Generate a Quick Summary box immediately after H1.");
            $prompt .= "## COMPONENT: QUICK SUMMARY\n" . $this->replaceVars($compPrompt, $vars) . "\n\n";
        }

        if (in_array('takeaways', $components)) {
            $compPrompt = Setting::get("{$slug}_prompt_takeaways", "Generate a Key Takeaways section with 6-8 bullets.");
            $prompt .= "## COMPONENT: KEY TAKEAWAYS\n" . $this->replaceVars($compPrompt, $vars) . "\n\n";
        }

        if (in_array('faq', $components)) {
            $compPrompt = Setting::get("{$slug}_prompt_faq", "Generate a schema-ready FAQ section with 5-7 questions.");
            $prompt .= "## COMPONENT: FAQ SECTION\n" . $this->replaceVars($compPrompt, $vars) . "\n\n";
        }

        // 4. Meta Tags (Mandatory)
        $metaPrompt = Setting::get("{$slug}_prompt_meta", "Output [TITLE], [META_DESCRIPTION], and [FOCUS_KEYWORD] at the end.");
        $prompt .= "# METADATA PROTOCOL\n" . $this->replaceVars($metaPrompt, $vars) . "\n\n";

        // 5. Final Synthesis Rules
        $prompt .= "# OUTPUT FINALIZATION\n";
        $prompt .= "- Return CLEAN HTML only. No markdown fences.\n";
        $prompt .= "- Language: All output must be in native {$langName}.\n";
        $prompt .= "- Ensure headings (H1, H2, H3) are logical and hierarchy is perfect.\n";

        return $prompt;
    }

    /**
     * Helper to replace placeholders
     */
    protected function replaceVars(string $text, array $vars): string
    {
        return str_replace(array_keys($vars), array_values($vars), $text);
    }

    /**
     * Get tone-specific writing directive
     */
    protected function getToneDirective(string $tone): string
    {
        $custom = Setting::get("article-writer_directive_{$tone}", "");
        if (!empty($custom)) return $custom;

        $directives = [
            'professional' => 'Write in a polished, authoritative, business-professional voice. Use precise language, avoid slang, and maintain a confident yet approachable demeanor. Think: Harvard Business Review meets industry expert blog.',
            'informative' => 'Write in a clear, educational, and well-structured voice. Prioritize clarity and comprehensiveness. Explain complex concepts simply without dumbing down the content. Think: authoritative encyclopedia with personality.',
            'casual' => 'Write in a warm, conversational, and relatable voice. Use contractions, rhetorical questions, and occasional humor. Make the reader feel like they\'re learning from a knowledgeable friend. Think: popular blog with expert insights.',
            'authoritative' => 'Write with commanding expertise and thought leadership. Use industry jargon confidently, reference frameworks by name, cite studies and data points. Position every statement with unshakable authority. Think: leading industry analyst delivering insights.',
            'creative' => 'Write with vivid storytelling, compelling analogies, and engaging narrative hooks. Make dry topics fascinating. Use metaphors, paint pictures with words, and create emotional resonance. Think: award-winning feature journalism.',
        ];

        return $directives[$tone] ?? $directives['professional'];
    }

    /**
     * Get audience-specific writing directive
     */
    protected function getAudienceDirective(string $audience): string
    {
        $custom = Setting::get("article-writer_directive_{$audience}", "");
        if (!empty($custom)) return $custom;

        $directives = [
            'general' => 'Write for a broad, educated audience with moderate familiarity with the topic. Explain specialized terms when first introduced. Balance depth with accessibility.',
            'professionals' => 'Write for experienced industry professionals who already understand the fundamentals. Skip basics, go deep into advanced strategies, nuanced insights, and expert-level optimization techniques.',
            'beginners' => 'Write for complete beginners who are encountering this topic for the first time. Define every key term, use simple analogies, provide step-by-step guidance, and build concepts progressively.',
            'shoppers' => 'Write for buyers in the research/comparison phase. Focus on features vs. benefits, pros and cons, pricing considerations, and clear recommendations. Include comparison elements and decision-making frameworks.',
            'marketers' => 'Write for digital marketing professionals. Include ROI-focused insights, campaign strategies, platform-specific tips, and data-driven recommendations with measurable outcomes.',
            'developers' => 'Write for software developers and technical professionals. Include code-relevant context, technical specifications, integration considerations, and developer workflow tips.',
        ];

        return $directives[$audience] ?? $directives['general'];
    }

    /**
     * Fetch Live News Context from Google News
     */
    protected function fetchNewsGrounding(string $keyword, string $lang): string
    {
        // Guess region based on language
        $region = ($lang === 'ar') ? 'EG' : (($lang === 'es') ? 'ES' : (($lang === 'fr') ? 'FR' : 'US'));
        $ceid = "{$region}:{$lang}";

        $cacheKey = 'aw_grounding_' . md5(mb_strtolower(trim($keyword)) . $lang);
        $cached = Cache::get($cacheKey);
        if ($cached) return $cached;

        $tempContext = "";
        $limit = (int) Setting::get("article-writer_live_search_limit", 15);
        
        // Windows to check: recent first
        $windows = ['when:24h', 'when:7d', 'broad'];
        
        foreach ($windows as $window) {
            $timeParam = ($window === 'broad') ? "" : " " . $window;
            $url = "https://news.google.com/rss/search?q=" . urlencode($keyword . $timeParam) . "&hl={$lang}&gl={$region}&ceid={$ceid}";
            
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            $tempContext .= "- " . (string)$item->title . " (Source: " . (string)$item->source . ")\n";
                            if (substr_count($tempContext, "\n") >= $limit) break;
                        }
                    }
                }
                if (substr_count($tempContext, "\n") >= 5) break; 
            } catch (\Exception $e) {
                Log::warning("ArticleWriter Grounding Error: " . $e->getMessage());
            }
        }

        if (!empty($tempContext)) {
            Cache::put($cacheKey, $tempContext, 1800); // 30 min cache
        }
        
        return $tempContext;
    }

    protected function getLanguageName(string $code): string
    {
        $languages = [
            'en' => 'English',
            'ar' => 'Arabic',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
            'it' => 'Italian',
            'nl' => 'Dutch',
            'tr' => 'Turkish',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'ru' => 'Russian',
            'hi' => 'Hindi',
            'id' => 'Indonesian',
            'pl' => 'Polish',
        ];

        return $languages[$code] ?? ucfirst($code);
    }

    protected function getCurrentYear(): string
    {
        return date('Y');
    }

    protected function getMinWords(int $target): int
    {
        return (int) ($target * 0.9);
    }

    protected function getMaxWords(int $target): int
    {
        return (int) ($target * 1.1);
    }
}
