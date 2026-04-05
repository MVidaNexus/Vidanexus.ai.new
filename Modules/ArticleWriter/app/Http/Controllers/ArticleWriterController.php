<?php

namespace Modules\ArticleWriter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ArticleWriter\Services\ArticleWriterService;
use Modules\ArticleWriter\Models\ArticleHistory;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class ArticleWriterController extends Controller
{
    protected $service;

    public function __construct(ArticleWriterService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the tool page and history snippets.
     */
    public function index()
    {
        $user = auth()->user();
        $history = ArticleHistory::where('user_id', $user->id)->latest()->take(10)->get();
        
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
        $request->validate([
            'keyword' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'tone' => 'required|string',
            'audience' => 'required|string',
            'word_count' => 'nullable|integer|min:300|max:5000',
            'components' => 'nullable|array',
        ]);

        $user = auth()->user();

        // 1. Credit Check (Universal Pattern)
        $cost = (int) Setting::get('tool_credit_cost_article-writer', 5);
        if (!$user->wallet || $user->wallet->balance_credits < $cost) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Insufficient credits. You need ' . $cost . ' credits to generate this article.'
            ], 402);
        }

        try {
            // 2. Generate Article via Service
            $result = $this->service->generateArticle($request->all());

            if (empty($result['text'])) {
                throw new \Exception("AI generated empty content.");
            }

            // 3. Parse Metadata (Title, Meta Description, Focus Keyword) from payload
            $text = $result['text'];
            $title = $this->parseTag($text, 'TITLE');
            $metaDesc = $this->parseTag($text, 'META_DESCRIPTION') ?: $this->parseTag($text, 'META');
            $focusKeyword = $this->parseTag($text, 'FOCUS_KEYWORD');
            
            // Clean up ALL metadata tags from content to make it clean HTML
            $cleanContent = preg_replace('/\[TITLE\]:.*?(\n|$)/i', '', $text);
            $cleanContent = preg_replace('/\[META_DESCRIPTION\]:.*?(\n|$)/i', '', $cleanContent);
            $cleanContent = preg_replace('/\[META\]:.*?(\n|$)/i', '', $cleanContent);
            $cleanContent = preg_replace('/\[FOCUS_KEYWORD\]:.*?(\n|$)/i', '', $cleanContent);
            $cleanContent = preg_replace('/```html\s*/i', '', $cleanContent);
            $cleanContent = preg_replace('/```\s*$/i', '', $cleanContent);
            $cleanContent = trim($cleanContent);

            // 4. Save to History
            $history = ArticleHistory::create([
                'user_id' => $user->id,
                'topic' => $request->keyword,
                'title' => $title ?: $request->keyword,
                'meta_description' => $metaDesc,
                'content' => $cleanContent,
                'provider' => $result['provider'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown',
                'language' => $request->language,
                'word_count' => str_word_count(strip_tags($cleanContent)),
                'seo_data' => [
                    'input_tokens' => $result['input_tokens'] ?? 0,
                    'output_tokens' => $result['output_tokens'] ?? 0,
                    'tone' => $request->tone,
                    'audience' => $request->audience,
                    'components' => $request->components ?? [],
                    'focus_keyword' => $focusKeyword,
                    'full_raw' => $text
                ]
            ]);

            // 5. Deduct Credits
            $user->wallet->decrement('balance_credits', $cost);

            // 6. Log Activity Log
            \App\Models\AiUsage::create([
                'user_id' => $user->id,
                'tool' => 'article-writer',
                'provider' => $result['provider'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown',
                'status' => 'success',
            ]);

            return response()->json([
                'status' => 'success',
                'article' => $history
            ]);

        } catch (\Exception $e) {
            \App\Models\ToolError::log('article-writer', $e, 'Generation Failed', $user->id);
            return response()->json([
                'status' => 'error',
                'message' => 'Article generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $article = ArticleHistory::where('user_id', auth()->id())->findOrFail($id);
        return response()->json($article);
    }

    public function history()
    {
        $history = ArticleHistory::where('user_id', auth()->id())->latest()->paginate(10);
        return response()->json($history);
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
