<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\ToolError;
use App\Models\RoadmapFeature;
use Illuminate\Support\Facades\DB;

class HorizonController extends Controller
{
    private function getToolDefinitions(): array
    {
        return config('tools.all_tools', []);
    }

    public function index()
    {
        $tools = collect($this->getToolDefinitions())->map(function ($tool) {
            $tool['usage_count'] = DB::table('ai_usages')->where('tool', $tool['slug'])->count();
            $tool['today_usage'] = DB::table('ai_usages')->where('tool', $tool['slug'])->whereDate('created_at', today())->count();
            // New statistic: Purchase count (Marketplace sales)
            $tool['purchase_count'] = DB::table('user_tools')->where('tool_slug', $tool['slug'])->count();
            return $tool;
        });

        $stats = [
            'total_users' => User::where('role', '!=', 'admin')->orWhereNull('role')->count(),
            'paid_users' => User::where(function($query) {
                $query->where('role', '!=', 'admin')->orWhereNull('role');
            })->whereHas('ownedTools')->count(),
            'total_requests' => DB::table('ai_usages')->count(),
        ];

        return view('admin.horizon.dashboard', compact('tools', 'stats'));
    }

    public function show($slug)
    {
        $tool = collect($this->getToolDefinitions())->where('slug', $slug)->first();
        if (!$tool) abort(404);

        $settings = [
            'prompt' => Setting::get("{$slug}_prompt", ''),
            'provider' => Setting::get("{$slug}_provider", config('vidanexus.ai.default_provider')),
            'model' => Setting::get("{$slug}_model", 'gpt-4o-mini'),
            'api_key' => Setting::get("{$slug}_api_key", ''),
            'is_active' => (bool)Setting::get("tool_available_{$slug}", true),
        ];

        // Specific settings for Competitor X-Ray (SerpAPI)
        if ($slug === 'competitor-xray') {
            $settings['serpapi_key'] = Setting::get("{$slug}_serpapi_key", '');
        }

        // Specific settings for AI Keyword Radar
        if ($slug === 'ai-keyword-radar') {
            $settings['competitors'] = Setting::get("{$slug}_competitors", '');
            $settings['rss_feeds'] = Setting::get("{$slug}_rss_feeds", '');
        }

        // Specific settings for Global News Monitor
        if ($slug === 'global-news-monitor') {
            $settings['time_window'] = Setting::get("{$slug}_time_window", '12h');
            $settings['countries'] = Setting::get("{$slug}_countries", '[]');
            $settings['topics'] = Setting::get("{$slug}_topics", '[]');
            
            // Professional dynamic management of countries and topics
            $settings['available_countries'] = Setting::get("{$slug}_available_countries", "EG:مصر 🇪🇬\nSA:السعودية 🇸🇦\nAE:الإمارات 🇦🇪\nKW:الكويت 🇰🇼\nQA:قطر 🇶🇦\nBH:البحرين 🇧🇭\nOM:عمان 🇴🇲\nIQ:العراق 🇮🇶\nJO:الأردن 🇯🇴\nLB:لبنان 🇱🇧\nMA:المغرب 🇲🇦\nDZ:الجزائر 🇩🇿\nTN:تونس 🇹🇳\nLY:ليبيا 🇱🇾\nPS:فلسطين 🇵🇸\nSY:سوريا 🇸🇾\nYE:اليمن 🇾🇪\nUS:USA 🇺🇸\nGB:UK 🇬🇧\nFR:France 🇫🇷\nPL:Poland 🇵🇱");
            
            $settings['available_topics'] = Setting::get("{$slug}_available_topics", "GENERAL:أخبار عامة\nWORLD:عالمية\nNATION:محلية\nBUSINESS:أعمال\nTECHNOLOGY:تكنولوجيا\nENTERTAINMENT:ترفيه\nSPORTS:رياضة\nSCIENCE:علوم\nHEALTH:صحة");

            // Ranking Engine Weights
            $settings['weight_virality'] = Setting::get("{$slug}_weight_virality", 35);
            $settings['weight_freshness'] = Setting::get("{$slug}_weight_freshness", 25);
            $settings['weight_serp'] = Setting::get("{$slug}_weight_serp", 25);
            $settings['weight_authority'] = Setting::get("{$slug}_weight_authority", 15);

            // Opportunity Thresholds
            $settings['threshold_high'] = Setting::get("{$slug}_threshold_high", 70);
            $settings['threshold_moderate'] = Setting::get("{$slug}_threshold_moderate", 45);

            // AI Analysis Prompt
            $settings['ai_analysis_prompt'] = Setting::get("{$slug}_ai_analysis_prompt", '');

            // Performance
            $settings['cache_ttl'] = Setting::get("{$slug}_cache_ttl", 300);
            $settings['auto_refresh_seconds'] = Setting::get("{$slug}_auto_refresh_seconds", 300);
            $settings['max_articles_per_fetch'] = Setting::get("{$slug}_max_articles_per_fetch", 60);
            $settings['sync_credits'] = Setting::get("{$slug}_sync_credits", 1);
            $settings['ai_analysis_credits'] = Setting::get("{$slug}_ai_analysis_credits", 1);

            // Authority Sources
            $settings['major_authority_sources'] = Setting::get("{$slug}_major_authority_sources", "سكاي نيوز\nالجزيرة\nالعربية\nرويترز\nفرانس 24\nالشرق الأوسط\nbbc\ncnn\nreuters\nny times\nwashington post\nguardian\nbloomberg\nassociated press");
            $settings['mid_authority_sources'] = Setting::get("{$slug}_mid_authority_sources", "اليوم السابع\nالبيان\nالخليج\nالوطن\nالمصري اليوم\nالشروق\nعكاظ\nسبق\nforbes\ntechcrunch\nwired\nverge");
        }

        // Specific settings for Trending Search Monitor
        if ($slug === 'trending-search-monitor') {
            $settings['feed_type'] = Setting::get("{$slug}_feed_type", 'daily');
            $settings['category'] = Setting::get("{$slug}_category", 'all');
            $settings['countries'] = Setting::get("{$slug}_countries", '[]');
            $settings['available_countries'] = Setting::get("{$slug}_available_countries", "EG:مصر 🇪🇬\nSA:السعودية 🇸🇦\nAE:الإمارات 🇦🇪\nKW:الكويت 🇰🇼\nQA:قطر 🇶🇦\nBH:البحرين 🇧🇭\nOM:عمان 🇴🇲\nUS:USA 🇺🇸\nGB:UK 🇬🇧\nDE:Germany 🇩🇪\nFR:France 🇫🇷\nPL:Poland 🇵🇱");
            
            // AI Analysis
            $settings['ai_analysis_prompt'] = Setting::get("{$slug}_ai_analysis_prompt", '');
            $settings['ai_analysis_credits'] = Setting::get("{$slug}_ai_analysis_credits", 2);
            $settings['ai_model'] = Setting::get("{$slug}_ai_model", 'gpt-4o-mini');
            
            // Platform Toggles
            $settings['source_google_enabled'] = (bool)Setting::get("{$slug}_source_google_enabled", true);
            $settings['source_x_enabled'] = (bool)Setting::get("{$slug}_source_x_enabled", true);
            $settings['source_tiktok_enabled'] = (bool)Setting::get("{$slug}_source_tiktok_enabled", true);
            $settings['source_youtube_enabled'] = (bool)Setting::get("{$slug}_source_youtube_enabled", true);
            
            // TikTok External API (RapidAPI / Custom)
            $settings['tiktok_api_key'] = Setting::get("{$slug}_tiktok_api_key", '');
            $settings['tiktok_api_host'] = Setting::get("{$slug}_tiktok_api_host", 'tiktok-creative-center-api.p.rapidapi.com');
            $settings['tiktok_api_endpoint'] = Setting::get("{$slug}_tiktok_api_endpoint", '/api/trending/hashtag');
            
            // Performance
            $settings['cache_ttl'] = Setting::get("{$slug}_cache_ttl", 3600);
            $settings['sync_interval'] = Setting::get("{$slug}_sync_interval", 5);
            $settings['max_trends'] = Setting::get("{$slug}_max_trends", 50);
        }

        // Specific settings for Discover Headlines
        if ($slug === 'discover-headlines') {
            $settings['suggestions_prompt'] = Setting::get("{$slug}_suggestions_prompt", '');
            $settings['min_chars'] = Setting::get("{$slug}_min_chars", 55);
            $settings['max_chars'] = Setting::get("{$slug}_max_chars", 85);
            $settings['power_words'] = Setting::get("{$slug}_power_words", "يكشف, يفاجئ, يُعلن, يحسم, يتراجع, يصدر, عاجل, حصري, حقيقة, سر, رسمياً");
            $settings['rss_region'] = Setting::get("{$slug}_rss_region", 'EG');
            $settings['rss_time_window'] = Setting::get("{$slug}_rss_time_window", '12h');
            $settings['cache_ttl'] = Setting::get("{$slug}_cache_ttl", 1800);
        }

        // Specific settings for Article Writer
        if ($slug === 'article-writer') {
            $settings['available_languages'] = Setting::get("{$slug}_available_languages", "en:English 🇺🇸\nar:Arabic 🇸🇦\nes:Spanish 🇪🇸\nfr:French 🇫🇷\nde:German 🇩🇪\npt:Portuguese 🇧🇷\nit:Italian 🇮🇹\nnl:Dutch 🇳🇱\ntr:Turkish 🇹🇷");
            $settings['available_tones'] = Setting::get("{$slug}_available_tones", "professional:Professional\ninformative:Informative\ncasual:Casual & Friendly\nauthoritative:Authoritative Expert\ncreative:Creative & Engaging\npersuasive:Persuasive & Sales");
            $settings['available_audiences'] = Setting::get("{$slug}_available_audiences", "general:General Audience\nprofessionals:Industry Professionals\nbeginners:Beginners & Learners\nshoppers:Online Shoppers\nentrepreneurs:Entrepreneurs & Founders\nmarketers:Digital Marketers");
            $settings['default_tokens'] = Setting::get("{$slug}_max_tokens", 4000);
            $settings['default_word_count'] = Setting::get("{$slug}_default_word_count", 1500);
            $settings['available_components'] = Setting::get("{$slug}_available_components", "faq:FAQ Section\nsummary:Quick Summary\ntakeaways:Key Takeaways\nmeta:SEO Meta Tags");
            $settings['credit_cost'] = Setting::get("tool_credit_cost_{$slug}", 5);

            // Dedicated Prompt Fields
            $settings['prompt_title'] = Setting::get("{$slug}_prompt_title", "Generate a Google Discover-optimized headline for [keyword] in [language].\n\nRESEARCH CONTEXT:\n[news_context]\n\nRequirements:\n- 8-14 words for optimal Discover CTR\n- MUST use a power word (Breaking, Exclusive, Detailed, Revealed)\n- Focus on the LATEST angle from the research context\n- Include [year] and magnetic hooks.");

            $settings['prompt_body'] = Setting::get("{$slug}_prompt_body", "You are a master investigative journalist and SEO specialist. Write a [word_count]-word comprehensive article about [keyword] in [language].\n\n# ABSOLUTE TRUTH: REAL-TIME RESEARCH\n[news_context]\n\n# INSTRUCTIONS:\n1. Prioritize the [news_context] for ALL facts, dates, and names. This is your source of current truth for [year].\n2. Cover all angles: Current State, Expert Insights, and Future Outlook.\n3. Tone: [tone] | Audience: [audience]\n4. Structure with H1, H2, H3. Use tables for comparisons.\n5. Ensure E-E-A-T compliance by citing sources mentioned in the context.");

            $settings['prompt_summary'] = Setting::get("{$slug}_prompt_summary", "Immediately after the <h1> title, generate a Quick Summary in this format:\n<div class=\"quick-summary\">\n<p>[Write 3-5 sentences serving as an EXECUTIVE BRIEFING. Cover: what the topic is, why it matters critically in [year], the most important findings/insights, and what the reader gains by reading fully. Write so a busy executive could stop here and still walk away informed. Include at least one specific data point or statistic.]</p>\n</div>");

            $settings['prompt_takeaways'] = Setting::get("{$slug}_prompt_takeaways", "After the Quick Summary, generate a Key Takeaways section:\n<div class=\"key-takeaways\">\n<h2>Key Takeaways</h2>\n<ul>\n<li><strong>[Insight Label]</strong>: [One sentence with a SPECIFIC data point, percentage, or actionable insight — not generic statements]</li>\n</ul>\n</div>\nGenerate 6-8 takeaways. Each MUST contain a concrete number, named entity, or specific actionable step. Generic takeaways like 'This topic is important' are FORBIDDEN.");

            $settings['prompt_faq'] = Setting::get("{$slug}_prompt_faq", "Generate a schema-ready FAQ section with 5-7 questions. Requirements:\n- Questions must simulate Google's 'People Also Ask' — use REAL search queries people type\n- Cover different intents: what, how, why, when, cost, comparison, alternatives, best practices\n- Lead EVERY answer with the direct answer in the FIRST sentence, then elaborate in 2-3 more sentences\n- Include at least one data point or expert reference in each answer\n- NEVER use generic questions like 'What is [topic]?' — be SPECIFIC and high-intent\n\nFormat:\n<div class=\"faq-section\">\n<h2>Frequently Asked Questions</h2>\n<h3>[Specific, natural-language question matching real search behavior]?</h3>\n<p>[Direct answer first. Then supporting evidence, context, or example. 2-4 sentences total.]</p>\n</div>");

            $settings['prompt_meta'] = Setting::get("{$slug}_prompt_meta", "After ALL article content, output these metadata tags on separate lines:\n\n[TITLE]: [Your Google Discover-quality magnetic title — MAX 60 characters — must trigger curiosity + include primary keyword — use power words, numbers, or emotional hooks — this title should make people STOP scrolling]\n[META_DESCRIPTION]: [Conversion-focused description — MAX 155 characters — must include primary keyword, a compelling hook, and a subtle call-to-action like 'Learn more', 'Discover why', 'See the data']\n[FOCUS_KEYWORD]: [The exact primary keyword/phrase for SEO targeting]");

            // Tone Directives
            $settings['directive_professional'] = Setting::get("{$slug}_directive_professional", "Write in a polished, authoritative, business-professional voice. Use precise language, avoid slang, and maintain a confident yet approachable demeanor. Think: Harvard Business Review meets industry expert blog. Focus on utility and clarity.");
            $settings['directive_informative'] = Setting::get("{$slug}_directive_informative", "Write in a clear, educational, and well-structured voice. Prioritize clarity and comprehensiveness. Explain complex concepts simply without dumbing down the content. Think: an authoritative, modern encyclopedia with a helpful personality.");
            $settings['directive_casual'] = Setting::get("{$slug}_directive_casual", "Write in a warm, conversational, and relatable voice. Use contractions, rhetorical questions, and occasional humor. Make the reader feel like they're learning from a knowledgeable friend who knows the inside secrets. Keep it engaging but still high-value.");
            $settings['directive_authoritative'] = Setting::get("{$slug}_directive_authoritative", "Write with commanding expertise and thought leadership. Use industry terminology confidently, reference frameworks by name, cite studies and data points. Position every statement with unshakable authority and investigative journalist precision.");
            $settings['directive_creative'] = Setting::get("{$slug}_directive_creative", "Write with vivid storytelling, compelling analogies, and engaging narrative hooks. Make dry topics fascinating. Use metaphors, paint pictures with words, and create emotional resonance while maintaining high SEO value.");

            // Audience Directives
            $settings['directive_general'] = Setting::get("{$slug}_directive_general", "Write for a broad, educated audience with moderate familiarity with the topic. Explain specialized terms when first introduced. Balance depth with accessibility. Assume the reader is searching for reliable, comprehensive information.");
            $settings['directive_professionals'] = Setting::get("{$slug}_directive_professionals", "Write for experienced industry professionals who already understand the fundamentals. Skip the basics, go deep into advanced strategies, nuanced insights, and expert-level optimization techniques. Use industry-standard shorthand and advanced concepts.");
            $settings['directive_beginners'] = Setting::get("{$slug}_directive_beginners", "Write for complete beginners who are encountering this topic for the first time. Define every key term, use simple analogies, provide step-by-step guidance, and build concepts progressively. Make the reader feel empowered and informed, not overwhelmed.");
            $settings['directive_shoppers'] = Setting::get("{$slug}_directive_shoppers", "Write for buyers in the research/comparison phase. Focus on features vs. benefits, pros and cons, pricing considerations, and clear recommendations. Include comparison elements and decision-making frameworks to help them make a confident purchase.");
            $settings['directive_marketers'] = Setting::get("{$slug}_directive_marketers", "Write for digital marketing professionals. Include ROI-focused insights, campaign strategies, platform-specific tips, and data-driven recommendations with measurable outcomes. Focus on what works in the current algorithm landscape.");

            // Grounding Settings
            $settings['live_search_enabled'] = Setting::get("{$slug}_live_search_enabled", true);
            $settings['live_search_limit'] = Setting::get("{$slug}_live_search_limit", 15);
        }

        $fromDate = request('from_date', now()->subDays(30)->toDateString());
        $toDate = request('to_date', now()->toDateString());

        $subscribersQuery = User::whereHas('aiUsages', function($query) use ($slug, $fromDate, $toDate) {
            $query->where('tool', $slug);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
        })->with(['wallet'])->withCount(['aiUsages' => function($query) use ($slug, $fromDate, $toDate) {
            $query->where('tool', $slug);
            if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $query->whereDate('created_at', '<=', $toDate);
        }]);

        $subscribers = $subscribersQuery->orderBy('ai_usages_count', 'desc')->paginate(10);

        $stats = [
            'today_usage' => DB::table('ai_usages')->where('tool', $slug)->whereDate('created_at', today())->count(),
            'this_month_usage' => DB::table('ai_usages')->where('tool', $slug)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'lifetime_usage' => DB::table('ai_usages')->where('tool', $slug)->count(),
            'purchase_count' => DB::table('user_tools')->where('tool_slug', $slug)->count(),
            'filtered_usage' => null,
        ];

        if ($fromDate || $toDate) {
            $filteredQuery = DB::table('ai_usages')->where('tool', $slug);
            if ($fromDate) $filteredQuery->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $filteredQuery->whereDate('created_at', '<=', $toDate);
            $stats['filtered_usage'] = $filteredQuery->count();
        }

        $viewName = "admin.horizon.tools.{$slug}";
        if (!view()->exists($viewName)) {
            $viewName = 'admin.horizon.tool';
        }

        $toolErrors = ToolError::where('tool_slug', $slug)->with('user')->latest()->paginate(15, ['*'], 'errors_page');

        return view($viewName, compact('tool', 'settings', 'subscribers', 'stats', 'fromDate', 'toDate', 'toolErrors'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'prompt' => 'nullable|string',
            'providers' => 'nullable|array',
            'models' => 'nullable|array',
            'api_keys' => 'nullable|array',
            'competitors' => 'nullable|string',
            'rss_feeds' => 'nullable|string',
        ]);

        Setting::set("{$slug}_prompt", $request->prompt, 'textarea', 'tool_settings');

        // Marketplace pricing (admin-overridable per-tool)
        if ($request->has('unlock_price')) {
            Setting::set("tool_unlock_price_{$slug}", (int) $request->unlock_price, 'integer', 'tool_settings');
        }
        if ($request->has('credit_cost')) {
            Setting::set("tool_credit_cost_{$slug}", (int) $request->credit_cost, 'integer', 'tool_settings');
        }
        if ($request->has('bonus_credits')) {
            Setting::set("tool_bonus_credits_{$slug}", (int) $request->bonus_credits, 'integer', 'tool_settings');
        }

        $aiChain = [];
        $providers = $request->input('providers', []);
        $models = $request->input('models', []);
        $apiKeys = $request->input('api_keys', []);

        if (is_array($providers)) {
            foreach ($providers as $index => $provider) {
                if (empty($provider)) continue;
                $aiChain[] = [
                    'provider' => $provider,
                    'model' => $models[$index] ?? null,
                    'api_key' => $apiKeys[$index] ?? null,
                ];
            }
        }
        
        if (!empty($aiChain)) {
            Setting::set("{$slug}_ai_chain", json_encode($aiChain), 'json', 'tool_settings');
        }


        if ($slug === 'competitor-xray') {
            Setting::set("{$slug}_serpapi_key", $request->serpapi_key, 'password', 'tool_settings');
        }

        if ($slug === 'ai-keyword-radar') {
            Setting::set("{$slug}_competitors", $request->competitors, 'textarea', 'tool_settings');
            Setting::set("{$slug}_rss_feeds", $request->rss_feeds, 'textarea', 'tool_settings');
            Setting::set("{$slug}_serpapi_key", $request->serpapi_key, 'password', 'tool_settings');
            
            // Advanced Settings
            if ($request->has('strategies')) {
                Setting::set("{$slug}_strategies", $request->strategies, 'text', 'tool_settings');
            }
            if ($request->has('scraping_depth')) {
                Setting::set("{$slug}_scraping_depth", $request->scraping_depth, 'number', 'tool_settings');
            }
            if ($request->has('sync_credits')) {
                Setting::set("{$slug}_sync_credits", $request->sync_credits, 'number', 'tool_settings');
            }
            if ($request->has('min_chars')) {
                Setting::set("{$slug}_min_chars", $request->min_chars, 'number', 'tool_settings');
            }
            if ($request->has('min_words')) {
                Setting::set("{$slug}_min_words", $request->min_words, 'number', 'tool_settings');
            }
            if ($request->has('max_words')) {
                Setting::set("{$slug}_max_words", $request->max_words, 'number', 'tool_settings');
            }
            if ($request->has('similarity_threshold')) {
                Setting::set("{$slug}_similarity_threshold", $request->similarity_threshold, 'number', 'tool_settings');
            }

            // Clear relevant caches
            \Illuminate\Support\Facades\Cache::forget('competitor_urls_ar');
            \Illuminate\Support\Facades\Cache::forget('competitor_urls_en');
            \Illuminate\Support\Facades\Cache::forget('rss_news_feeds_ar');
            \Illuminate\Support\Facades\Cache::forget('rss_news_feeds_en');
        }

        if ($slug === 'global-news-monitor') {
            Setting::set("{$slug}_time_window", $request->input('time_window', '12h'), 'text', 'tool_settings');
            Setting::set("{$slug}_countries", json_encode($request->input('countries', [])), 'textarea', 'tool_settings');
            Setting::set("{$slug}_topics", json_encode($request->input('topics', [])), 'textarea', 'tool_settings');
            
            // Professional controls: update available lists
            if ($request->has('available_countries')) {
                Setting::set("{$slug}_available_countries", $request->available_countries, 'textarea', 'tool_settings');
            }
            if ($request->has('available_topics')) {
                Setting::set("{$slug}_available_topics", $request->available_topics, 'textarea', 'tool_settings');
            }

            // Ranking Engine Weights
            if ($request->has('weight_virality')) {
                Setting::set("{$slug}_weight_virality", (int)$request->weight_virality, 'number', 'tool_settings');
            }
            if ($request->has('weight_freshness')) {
                Setting::set("{$slug}_weight_freshness", (int)$request->weight_freshness, 'number', 'tool_settings');
            }
            if ($request->has('weight_serp')) {
                Setting::set("{$slug}_weight_serp", (int)$request->weight_serp, 'number', 'tool_settings');
            }
            if ($request->has('weight_authority')) {
                Setting::set("{$slug}_weight_authority", (int)$request->weight_authority, 'number', 'tool_settings');
            }

            // Opportunity Thresholds
            if ($request->has('threshold_high')) {
                Setting::set("{$slug}_threshold_high", (int)$request->threshold_high, 'number', 'tool_settings');
            }
            if ($request->has('threshold_moderate')) {
                Setting::set("{$slug}_threshold_moderate", (int)$request->threshold_moderate, 'number', 'tool_settings');
            }

            // AI Analysis Prompt
            if ($request->has('ai_analysis_prompt')) {
                Setting::set("{$slug}_ai_analysis_prompt", $request->ai_analysis_prompt, 'textarea', 'tool_settings');
            }

            // Performance
            if ($request->has('cache_ttl')) {
                Setting::set("{$slug}_cache_ttl", (int)$request->cache_ttl, 'number', 'tool_settings');
            }
            if ($request->has('auto_refresh_seconds')) {
                Setting::set("{$slug}_auto_refresh_seconds", (int)$request->auto_refresh_seconds, 'number', 'tool_settings');
            }
            if ($request->has('max_articles_per_fetch')) {
                Setting::set("{$slug}_max_articles_per_fetch", (int)$request->max_articles_per_fetch, 'number', 'tool_settings');
            }
            if ($request->has('sync_credits')) {
                Setting::set("{$slug}_sync_credits", (int)$request->sync_credits, 'number', 'tool_settings');
            }
            if ($request->has('ai_analysis_credits')) {
                Setting::set("{$slug}_ai_analysis_credits", (int)$request->ai_analysis_credits, 'number', 'tool_settings');
            }

            // Authority Sources
            if ($request->has('major_authority_sources')) {
                Setting::set("{$slug}_major_authority_sources", $request->major_authority_sources, 'textarea', 'tool_settings');
            }
            if ($request->has('mid_authority_sources')) {
                Setting::set("{$slug}_mid_authority_sources", $request->mid_authority_sources, 'textarea', 'tool_settings');
            }
            
            // Clear news cache so new settings take effect
            $keys = \Illuminate\Support\Facades\Cache::getStore();
            // Simple approach: flush all news-related cache keys
            foreach (['EG','SA','AE','KW','QA','BH','OM','IQ','JO','LB','MA','DZ','TN','LY','PS','SY','YE','US','GB','FR','PL'] as $cc) {
                foreach (['GENERAL','WORLD','NATION','BUSINESS','TECHNOLOGY','ENTERTAINMENT','SPORTS','SCIENCE','HEALTH'] as $tp) {
                    \Illuminate\Support\Facades\Cache::forget("google_news_radar_{{$cc}}_{$tp}");
                }
            }
        }

        if ($slug === 'discover-headlines') {
            if ($request->has('suggestions_prompt')) {
                Setting::set("{$slug}_suggestions_prompt", $request->suggestions_prompt, 'textarea', 'tool_settings');
            }
            if ($request->has('min_chars')) {
                Setting::set("{$slug}_min_chars", $request->min_chars, 'number', 'tool_settings');
            }
            if ($request->has('max_chars')) {
                Setting::set("{$slug}_max_chars", $request->max_chars, 'number', 'tool_settings');
            }
            if ($request->has('power_words')) {
                Setting::set("{$slug}_power_words", $request->power_words, 'textarea', 'tool_settings');
            }
            if ($request->has('rss_region')) {
                Setting::set("{$slug}_rss_region", $request->rss_region, 'text', 'tool_settings');
            }
            if ($request->has('rss_time_window')) {
                Setting::set("{$slug}_rss_time_window", $request->rss_time_window, 'text', 'tool_settings');
            }
            if ($request->has('cache_ttl')) {
                Setting::set("{$slug}_cache_ttl", $request->cache_ttl, 'number', 'tool_settings');
            }
            
            if ($request->has('is_active')) {
                Setting::set("tool_available_{$slug}", $request->is_active == '1', 'boolean', 'tool_settings');
            }
        }

        if ($slug === 'trending-search-monitor') {
            Setting::set("{$slug}_feed_type", $request->input('feed_type', 'daily'), 'text', 'tool_settings');
            Setting::set("{$slug}_category", $request->input('category', 'all'), 'text', 'tool_settings');
            Setting::set("{$slug}_countries", json_encode($request->input('countries', [])), 'textarea', 'tool_settings');
            
            if ($request->has('available_countries')) {
                Setting::set("{$slug}_available_countries", $request->available_countries, 'textarea', 'tool_settings');
            }

            // AI Intelligence
            if ($request->has('ai_analysis_prompt')) {
                Setting::set("{$slug}_ai_analysis_prompt", $request->ai_analysis_prompt, 'textarea', 'tool_settings');
            }
            if ($request->has('ai_analysis_credits')) {
                Setting::set("{$slug}_ai_analysis_credits", (int)$request->ai_analysis_credits, 'number', 'tool_settings');
            }
            if ($request->has('ai_model')) {
                Setting::set("{$slug}_ai_model", $request->ai_model, 'text', 'tool_settings');
            }

            // Platform Toggles
            Setting::set("{$slug}_source_google_enabled", $request->has('source_google_enabled'), 'boolean', 'tool_settings');
            Setting::set("{$slug}_source_x_enabled", $request->has('source_x_enabled'), 'boolean', 'tool_settings');
            Setting::set("{$slug}_source_tiktok_enabled", $request->has('source_tiktok_enabled'), 'boolean', 'tool_settings');
            Setting::set("{$slug}_source_youtube_enabled", $request->has('source_youtube_enabled'), 'boolean', 'tool_settings');

            // TikTok External API
            if ($request->has('tiktok_api_key')) {
                Setting::set("{$slug}_tiktok_api_key", $request->tiktok_api_key, 'text', 'tool_settings');
            }
            if ($request->has('tiktok_api_host')) {
                Setting::set("{$slug}_tiktok_api_host", $request->tiktok_api_host, 'text', 'tool_settings');
            }
            if ($request->has('tiktok_api_endpoint')) {
                Setting::set("{$slug}_tiktok_api_endpoint", $request->tiktok_api_endpoint, 'text', 'tool_settings');
            }

            // Performance
            if ($request->has('cache_ttl')) {
                Setting::set("{$slug}_cache_ttl", (int)$request->cache_ttl, 'number', 'tool_settings');
            }
            if ($request->has('sync_interval')) {
                Setting::set("{$slug}_sync_interval", (int)$request->sync_interval, 'number', 'tool_settings');
            }
            if ($request->has('max_trends')) {
                Setting::set("{$slug}_max_trends", (int)$request->max_trends, 'number', 'tool_settings');
            }
            
            // Clear API trends cache so the new settings take effect immediately
            \Illuminate\Support\Facades\Cache::forget('trending_suggestions_cache_keys');
            // Also clear TikTok cache for all countries
            foreach (['EG','SA','AE','KW','QA','BH','OM','US','GB','DE','FR','PL'] as $cc) {
                \Illuminate\Support\Facades\Cache::forget("trending_tiktok_{$cc}");
            }
        }
        if ($slug === 'article-writer') {
            if ($request->has('available_languages')) {
                Setting::set("{$slug}_available_languages", $request->available_languages, 'textarea', 'tool_settings');
            }
            if ($request->has('available_tones')) {
                Setting::set("{$slug}_available_tones", $request->available_tones, 'textarea', 'tool_settings');
            }
            if ($request->has('available_audiences')) {
                Setting::set("{$slug}_available_audiences", $request->available_audiences, 'textarea', 'tool_settings');
            }
            if ($request->has('max_tokens')) {
                Setting::set("{$slug}_max_tokens", (int)$request->max_tokens, 'number', 'tool_settings');
            }
            if ($request->has('default_word_count')) {
                Setting::set("{$slug}_default_word_count", (int)$request->default_word_count, 'number', 'tool_settings');
            }
            if ($request->has('available_components')) {
                Setting::set("{$slug}_available_components", $request->available_components, 'textarea', 'tool_settings');
            }
            if ($request->has('is_active')) {
                Setting::set("tool_available_{$slug}", $request->is_active == '1', 'boolean', 'tool_settings');
            }

            if ($request->has('live_search_enabled')) {
                Setting::set("{$slug}_live_search_enabled", $request->live_search_enabled == '1', 'boolean', 'tool_settings');
            }
            if ($request->has('live_search_limit')) {
                Setting::set("{$slug}_live_search_limit", (int)$request->live_search_limit, 'number', 'tool_settings');
            }

            // Save Dedicated Prompt Protocols
            $promptFields = [
                'prompt_title', 'prompt_body', 'prompt_summary', 'prompt_takeaways', 'prompt_faq', 'prompt_meta',
                'directive_professional', 'directive_informative', 'directive_casual', 'directive_authoritative', 'directive_creative',
                'directive_general', 'directive_professionals', 'directive_beginners', 'directive_shoppers', 'directive_marketers'
            ];
            foreach ($promptFields as $field) {
                if ($request->has($field)) {
                    Setting::set("{$slug}_{$field}", $request->input($field), 'textarea', 'tool_settings');
                }
            }
        }

        $tool = collect($this->getToolDefinitions())->where('slug', $slug)->first();
        return back()->with('success', "Configuration for " . ($tool['name'] ?? $slug) . " updated successfully.");
    }

    public function roadmap()
    {
        $features = RoadmapFeature::orderBy('order')->orderBy('created_at', 'desc')->get();
        return view('admin.horizon.todo', compact('features'));
    }

    public function roadmapStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        RoadmapFeature::create($data);

        return back()->with('success', 'Roadmap feature added successfully.');
    }

    public function roadmapUpdate(Request $request, $id)
    {
        $feature = RoadmapFeature::findOrFail($id);
        
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $feature->update($data);

        return back()->with('success', 'Roadmap feature updated successfully.');
    }

    public function roadmapDestroy($id)
    {
        $feature = RoadmapFeature::findOrFail($id);
        $feature->delete();

        return back()->with('success', 'Roadmap feature deleted successfully.');
    }
}
