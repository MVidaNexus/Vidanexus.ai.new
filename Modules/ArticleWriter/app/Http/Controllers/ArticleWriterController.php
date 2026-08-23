<?php

namespace Modules\ArticleWriter\Http\Controllers;

use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use App\Http\Controllers\Controller;
use App\Http\Responses\AIResponse;
use Illuminate\Http\Request;
use Modules\ArticleWriter\Services\ArticleWriterService;
use Modules\ArticleWriter\Services\SlugSuggester;
use Modules\ArticleWriter\Models\ArticleHistory;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ArticleWriterController extends Controller
{
    protected $service;
    protected SlugSuggester $slugger;

    public function __construct(ArticleWriterService $service, SlugSuggester $slugger)
    {
        $this->service = $service;
        $this->slugger = $slugger;
    }

    /**
     * Display the tool page and history snippets.
     */
    public function index()
    {
        $user = auth()->user();
        $history = ArticleHistory::where('user_id', $user->id)->latest()->take(10)->get()
            ->each->makeHidden(['model']);
        
        $settings = [
            'languages' => $this->parseSettings('article-writer_available_languages', "en:English 🇺🇸\nar:Arabic 🇸🇦"),
            'tones' => $this->parseSettings('article-writer_available_tones', "professional:Professional\ninformative:Informative\ncasual:Casual & Friendly\nauthoritative:Authoritative Expert\ncreative:Creative & Engaging"),
            'audiences' => $this->parseSettings('article-writer_available_audiences', "general:General Audience\nprofessionals:Industry Professionals\nbeginners:Beginners & Learners\nshoppers:Online Shoppers"),
            'components' => $this->parseSettings('article-writer_available_components', "faq:FAQ Section\nsummary:Quick Summary\ntakeaways:Key Takeaways\nmeta:SEO Meta Tags\ninternal_links:Internal Link Suggestions"),
            'credit_cost' => (int) Setting::get('tool_credit_cost_article-writer', 5),
            'default_word_count' => (int) Setting::get('article-writer_default_word_count', 1500),
        ];

        return view('articlewriter::index', compact('history', 'settings'));
    }

    /**
     * Handle article generation requests and billing.
     */
    public function store(Request $request)
    {
        // Strictly validate enums up front. The AI security middleware (route)
        // sanitized free-form fields before this validator runs.
        $request->validate([
            'keyword' => 'required|string|max:255',
            'language' => 'required|string|max:10|regex:/^[a-zA-Z\-]+$/',
            'tone' => 'required|string|max:64|regex:/^[a-zA-Z0-9_\- ]+$/',
            'audience' => 'required|string|max:64|regex:/^[a-zA-Z0-9_\- ]+$/',
            'word_count' => 'nullable|integer|min:300|max:5000',
            'components' => 'nullable|array',
            'components.*' => 'string|max:32|regex:/^[a-zA-Z0-9_\-]+$/',
            'additional_instructions' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();
        $slug = 'article-writer';

        // 1. Credit Check — canonical (wallet + bonus when allowed).
        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            return AIResponse::error(
                'INSUFFICIENT_CREDITS',
                'Insufficient credits. You need ' . $cost . ' credits to generate this article.',
                402,
                ['credits_required' => $cost]
            );
        }

        try {
            $result = $this->service->generateArticle($request->all());
            $parsed = $this->parseGeneratedArticle($result, $request);

            $parsedTitle = $parsed['title'] ?? $request->keyword ?? '';
            $parsedContent = $parsed['content'] ?? '';

            if (! $this->articleMatchesLanguage($parsedTitle, $parsedContent, $request->language)) {
                $retryPayload = $request->all();
                $retryPayload['additional_instructions'] = trim(($retryPayload['additional_instructions'] ?? '') . "\n\nSTRICT: Write the ENTIRE article including title in {$request->language} ONLY. No mixed languages.");
                $result = $this->service->generateArticle($retryPayload);
                $parsed = $this->parseGeneratedArticle($result, $request);
            }

            if (empty($parsed['content'])) {
                throw new AIProviderFailureException('AI generated empty content.');
            }

            $title = $parsed['title'] ?? $request->keyword ?? '';
            $metaDesc = $parsed['metaDesc'] ?? '';
            $focusKeyword = $parsed['focusKeyword'] ?? $request->keyword ?? '';
            $rawSlugEn = $parsed['rawSlugEn'] ?? null;
            $rawSlugAr = $parsed['rawSlugAr'] ?? null;
            $cleanContent = $parsed['content'] ?? '';

            // Build the suggested slugs. The AI normally provides them, but
            // we always re-sanitize and fall back to a deterministic local
            // generator so the SEO tab is never empty.
            $resolvedTitle = $title ?: $request->keyword;
            $slugEn = $rawSlugEn ? $this->slugger->sanitizeFromAi($rawSlugEn, $request->language) : '';
            $slugAr = $rawSlugAr ? $this->slugger->sanitizeFromAi($rawSlugAr, $request->language) : '';

            if ($slugEn === '') {
                $slugEn = $this->slugger->english($resolvedTitle);
            }
            if ($slugAr === '') {
                $slugAr = $this->slugger->arabic($resolvedTitle, $request->language);
            }

            // 4. Save to History
            $history = ArticleHistory::create([
                'user_id' => $user->id,
                'topic' => $request->keyword,
                'title' => $resolvedTitle,
                'meta_description' => $metaDesc,
                'content' => $cleanContent,
                'provider' => $result['provider_used'] ?? ($result['provider'] ?? 'unknown'),
                'model' => $result['model_used'] ?? ($result['model'] ?? 'unknown'),
                'language' => $request->language,
                'word_count' => str_word_count(strip_tags($cleanContent)),
                'seo_data' => [
                    'input_tokens' => $result['input_tokens'] ?? 0,
                    'output_tokens' => $result['output_tokens'] ?? 0,
                    'tone' => $request->tone,
                    'audience' => $request->audience,
                    'components' => $request->components ?? [],
                    'focus_keyword' => $focusKeyword,
                    'slug_en' => $slugEn,
                    'slug_ar' => $slugAr,
                    'site_domain' => $this->siteDomain(),
                    'full_raw' => $result['text'] ?? '',
                ],
            ]);

            // 5. Deduct Credits via the canonical service (wallet → bonus,
            // ledger entry + transaction + audit log).
            if (! $user->deductToolCredits($slug)) {
                \Illuminate\Support\Facades\Log::critical('[Article Writer] Credits could not be deducted after successful generation', [
                    'user_id' => $user->id,
                ]);
            }

            // 6. Log Activity Log
            \App\Models\AiUsage::create([
                'user_id' => $user->id,
                'tool' => $slug,
                'provider' => $result['provider_used'] ?? ($result['provider'] ?? 'unknown'),
                'model' => $result['model_used'] ?? ($result['model'] ?? 'unknown'),
                'status' => 'success',
            ]);

            $user->load('wallet');
            $history->makeHidden(['model']);

            return AIResponse::success([
                'article' => $history,
                'balance' => (float) ($user->wallet->balance_credits ?? 0),
                'provider_used' => $result['provider_used'] ?? ($result['provider'] ?? 'unknown'),
                'fallback_applied' => (bool) ($result['fallback_applied'] ?? false),
            ]);

        } catch (AIProviderConfigurationException $e) {
            \App\Models\ToolError::log('article-writer', $e, 'AI configuration error', $user->id);
            return AIResponse::fromException($e);
        } catch (AIProviderFailureException $e) {
            \App\Models\ToolError::log('article-writer', $e, 'AI provider failure', $user->id);
            return AIResponse::fromException($e);
        } catch (\Throwable $e) {
            \App\Models\ToolError::log('article-writer', $e, 'Generation Failed', $user->id);
            return AIResponse::fromException($e);
        }
    }

    public function show($id)
    {
        $article = ArticleHistory::where('user_id', auth()->id())->findOrFail($id);
        $article->makeHidden(['model']);

        return response()->json($article);
    }

    public function history()
    {
        $history = ArticleHistory::where('user_id', auth()->id())->latest()->paginate(10);
        $history->getCollection()->each->makeHidden(['model']);

        return response()->json($history);
    }

    protected function parseGeneratedArticle(array $result, Request $request): array
    {
        $text = $result['text'] ?? '';
        $title = $this->parseTag($text, 'TITLE');
        if (empty($title)) {
            if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $text, $h1Matches)) {
                $title = trim(strip_tags($h1Matches[1]));
            }
        }
        $title = $title ?: ($request->keyword ?? '');

        $metaDesc = $this->parseTag($text, 'META_DESCRIPTION') ?: $this->parseTag($text, 'META');
        if (empty($metaDesc)) {
            if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $text, $pMatches)) {
                $metaDesc = mb_substr(trim(strip_tags($pMatches[1])), 0, 160);
            }
        }

        $focusKeyword = $this->parseTag($text, 'FOCUS_KEYWORD') ?: ($request->keyword ?? '');
        $rawSlugEn = $this->parseTag($text, 'SLUG_EN');
        $rawSlugAr = $this->parseTag($text, 'SLUG_AR');

        $cleanContent = preg_replace('/\[TITLE\]:.*?(\n|$)/i', '', $text);
        $cleanContent = preg_replace('/\[META_DESCRIPTION\]:.*?(\n|$)/i', '', $cleanContent);
        $cleanContent = preg_replace('/\[META\]:.*?(\n|$)/i', '', $cleanContent);
        $cleanContent = preg_replace('/\[FOCUS_KEYWORD\]:.*?(\n|$)/i', '', $cleanContent);
        $cleanContent = preg_replace('/\[SLUG_EN\]:.*?(\n|$)/i', '', $cleanContent);
        $cleanContent = preg_replace('/\[SLUG_AR\]:.*?(\n|$)/i', '', $cleanContent);
        $cleanContent = preg_replace('/```html\s*/i', '', $cleanContent);
        $cleanContent = preg_replace('/```\s*$/i', '', $cleanContent);
        $cleanContent = trim($this->humanizeContent(trim($cleanContent)));

        return [
            'title' => $title,
            'metaDesc' => $metaDesc,
            'focusKeyword' => $focusKeyword,
            'rawSlugEn' => $rawSlugEn,
            'rawSlugAr' => $rawSlugAr,
            'content' => $cleanContent,
        ];
    }

    protected function articleMatchesLanguage(?string $title, ?string $content, ?string $lang): bool
    {
        $sample = strip_tags(($title ?? '') . ' ' . ($content ?? ''));
        if (trim($sample) === '') {
            return true;
        }

        $arabicChars = preg_match_all('/\p{Arabic}/u', $sample) ?: 0;
        $latinChars = preg_match_all('/\p{Latin}/u', $sample) ?: 0;
        $total = max(1, $arabicChars + $latinChars);

        return match ($lang) {
            'ar' => ($arabicChars / $total) >= 0.40,
            'fr' => ($latinChars / $total) >= 0.5,
            default => ($latinChars / $total) >= 0.5 && ($arabicChars / $total) < 0.15,
        };
    }

    public function destroy($id)
    {
        $article = ArticleHistory::where('user_id', auth()->id())->findOrFail($id);
        $article->delete();
        return response()->json(['status' => 'success']);
    }

    /**
     * Helpers for parsing tags and settings.
     */
    protected function parseTag($text, $tag)
    {
        preg_match("/\[{$tag}\]:\s*(.*)/i", $text, $matches);
        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    /**
     * Strip bullet / dot / dash leaks from inside <p>, <h*>, and <li>
     * text, where the AI often forgets the prompt and prefixes prose
     * with "• ", "- ", "* ", or "1. ". We deliberately leave the
     * insides of <ul>/<ol> alone — those are real, intentional lists.
     *
     * Also: collapse runs of >=3 short <p> tags that look like an
     * ad-hoc bulleted list into a real <ul> so the article view
     * renders consistently.
     */
    protected function humanizeContent(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        // 1. Strip leading bullet/dot characters from <p>, <h1..h6>, <li>.
        //    Pattern allowed at the start of inner text:
        //      • ● ◦ ▪ ■ □ * - – — → ► ✓ ✔ · ‣ , optionally followed by space.
        //      OR  "1.", "2)", "(3)" style numbering (we keep numbers in <ol> via the next step).
        $html = preg_replace_callback(
            '#(<(p|h[1-6]|li)[^>]*>)\s*(?:<(?:strong|em|b|i|span)[^>]*>\s*)?([\x{2022}\x{25CF}\x{25E6}\x{25AA}\x{25A0}\x{25A1}\x{2023}\*\-\x{2013}\x{2014}\x{2192}\x{25BA}\x{2713}\x{2714}\x{00B7}]+|\d+[\.\)]|\(\d+\))\s+#u',
            function ($m) {
                // Drop the bullet/numbering token and a single trailing space.
                return $m[1] . (isset($m[3]) ? '' : '');
            },
            $html
        ) ?? $html;

        // 2. Some models put bullets after an opening <strong>/<em> wrapper.
        //    Sweep again, this time also matching wrapper tags before the bullet.
        $html = preg_replace(
            '#(<(p|h[1-6]|li)[^>]*>\s*(?:<(?:strong|em|b|i|span)[^>]*>\s*)?)[\x{2022}\x{25CF}\x{25E6}\x{25AA}\x{25A0}\x{25A1}\x{2023}\*\x{2013}\x{2014}\x{2192}\x{25BA}\x{2713}\x{2714}\x{00B7}]\s+#u',
            '$1',
            $html
        ) ?? $html;

        // 3. Drop "leading dash" patterns that survive when authors do
        //    "<p>- This is a tip…</p>" — only when the dash is followed
        //    by a single space (don't touch em-dashes mid-sentence).
        $html = preg_replace(
            '#(<(p|h[1-6])[^>]*>)\s*[\-\x{2013}\x{2014}]\s+#u',
            '$1',
            $html
        ) ?? $html;

        // 4. Collapse stray "● " or "• " sequences that appear mid-paragraph
        //    (AI sometimes inserts them as "soft bullets" between phrases).
        $html = preg_replace(
            '/\s*[\x{2022}\x{25CF}\x{25E6}\x{25AA}\x{2023}\x{00B7}]\s+/u',
            ' ',
            $html
        ) ?? $html;

        return trim($html);
    }

    /**
     * Bare host part of APP_URL (e.g. "vidanexus.ai") for the SEO preview.
     */
    protected function siteDomain(): string
    {
        $url = (string) config('app.url', 'https://yoursite.com');
        $host = parse_url($url, PHP_URL_HOST);
        return $host ?: 'yoursite.com';
    }

    protected function parseSettings($key, $default)
    {
        $lines = explode("\n", trim(Setting::get($key, $default)));
        $parsed = [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$val, $label] = explode(':', $line, 2);
                $parsed[] = ['value' => trim($val), 'label' => trim($label)];
            }
        }
        return $parsed;
    }
}
