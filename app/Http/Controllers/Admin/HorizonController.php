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
            
            // Clear news cache so new settings take effect
            $keys = \Illuminate\Support\Facades\Cache::getStore();
            // Simple approach: flush all news-related cache keys
            foreach (['EG','SA','AE','KW','QA','BH','OM','IQ','JO','LB','MA','DZ','TN','LY','PS','SY','YE','US','GB','FR','PL'] as $cc) {
                foreach (['GENERAL','WORLD','NATION','BUSINESS','TECHNOLOGY','ENTERTAINMENT','SPORTS','SCIENCE','HEALTH'] as $tp) {
                    \Illuminate\Support\Facades\Cache::forget("google_news_radar_{{$cc}}_{$tp}");
                }
            }
        }

        if ($slug === 'trending-search-monitor') {
            Setting::set("{$slug}_feed_type", $request->input('feed_type', 'daily'), 'text', 'tool_settings');
            Setting::set("{$slug}_category", $request->input('category', 'all'), 'text', 'tool_settings');
            Setting::set("{$slug}_countries", json_encode($request->input('countries', [])), 'textarea', 'tool_settings');
            
            if ($request->has('available_countries')) {
                Setting::set("{$slug}_available_countries", $request->available_countries, 'textarea', 'tool_settings');
            }
            
            // Clear API trends cache so the new settings take effect immediately
            \Illuminate\Support\Facades\Cache::forget('trending_suggestions_cache_keys');
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
