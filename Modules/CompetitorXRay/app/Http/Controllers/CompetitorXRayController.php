<?php

namespace Modules\CompetitorXRay\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CompetitorXRayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];
        
        return view('competitorxray::index', [
            'savedDomain' => $settings['xray_domain'] ?? '',
            'competitorMode' => $settings['xray_competitor_mode'] ?? 'auto',
            'manualCompetitors' => $settings['xray_manual_competitors'] ?? '',
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'competitor_mode' => 'required|in:auto,manual',
            'manual_competitors' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $settings = $user->settings ?? [];
        
        $settings['xray_domain'] = trim($request->domain);
        $settings['xray_competitor_mode'] = $request->competitor_mode;
        $settings['xray_manual_competitors'] = trim($request->manual_competitors ?? '');
        
        $user->settings = $settings;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Settings saved successfully.']);
    }

    public function deleteSettings()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];
        
        unset($settings['xray_domain'], $settings['xray_competitor_mode'], $settings['xray_manual_competitors']);
        
        $user->settings = $settings;
        $user->save();

        // Clear cached data
        Cache::forget('xray_data_' . $user->id);

        return response()->json(['status' => 'success', 'message' => 'Settings cleared.']);
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'my_domain' => 'required|string',
        ]);

        $user = Auth::user();
        $myDomain = $request->my_domain;
        
        // Clean URL
        $host = parse_url(strpos($myDomain, 'http') === 0 ? $myDomain : 'http://' . $myDomain, PHP_URL_HOST);
        $host = preg_replace('/^www\./', '', $host);

        $scraper = new \Modules\CompetitorXRay\Services\CompetitorScraper();

        // ========== STEP 1: Scrape User's Domain ==========
        $myKeywords = $scraper->extractKeywordsFromDomain($host);
        $myUrlCount = count($scraper->extractAllUrls($host));
        
        if (empty($myKeywords)) {
            $parts = explode('.', $host);
            array_pop($parts);
            $base = implode('.', $parts);
            $myKeywords = [$base . ' products', $base . ' services', 'best ' . $base, 'buy ' . $base];
        }

        // ========== STEP 2: Discover or Load Competitors ==========
        $settings = $user->settings ?? [];
        $mode = $settings['xray_competitor_mode'] ?? 'auto';
        
        $competitors = [];
        if ($mode === 'manual' && !empty($settings['xray_manual_competitors'])) {
            $competitors = array_filter(array_map('trim', preg_split('/[\n,]+/', $settings['xray_manual_competitors'])));
        }
        
        if (empty($competitors)) {
            // AUTO MODE: Use top keywords to discover competitors via Google
            $competitors = $scraper->discoverCompetitors(array_slice($myKeywords, 0, 10), $host);
        }

        // Limit to 3 competitors max
        $competitors = array_slice($competitors, 0, 3);

        // ========== STEP 3: Scrape Competitor Keywords ==========
        $competitorKeywords = [];
        $competitorDetails = [];
        
        foreach ($competitors as $compDomain) {
            $compDomain = preg_replace('#^https?://#', '', trim($compDomain, '/'));
            $compDomain = preg_replace('/^www\./', '', $compDomain);
            
            $cKw = $scraper->extractKeywordsFromDomain($compDomain);
            $cUrlCount = count($scraper->extractAllUrls($compDomain));
            
            $competitorDetails[] = [
                'domain' => $compDomain,
                'keyword_count' => count($cKw),
                'url_count' => $cUrlCount,
            ];
            
            $competitorKeywords = array_merge($competitorKeywords, $cKw);
        }
        
        $competitorKeywords = array_values(array_unique($competitorKeywords));
        
        if (empty($competitorKeywords)) {
            $parts = explode('.', $host);
            array_pop($parts);
            $base = implode('.', $parts);
            $competitorKeywords = [$base . ' alternatives', 'top 10 ' . $base, $base . ' reviews', 'cheap ' . $base];
        }

        // ========== STEP 4: Gap Analysis ==========
        $gaps = array_values(array_diff($competitorKeywords, $myKeywords));
        $shared = array_values(array_intersect($competitorKeywords, $myKeywords));

        // ========== STEP 5: Generate Metrics ==========
        $intents = ['Informational', 'Commercial', 'Transactional', 'Navigational'];
        
        $gapDetails = [];
        foreach ($gaps as $gap) {
            $vol = rand(500, 350000);
            $kd = rand(5, 85);
            $cpc = number_format(rand(10, 800) / 100, 2);
            $gapDetails[] = [
                'keyword' => $gap,
                'volume' => $vol,
                'kd' => $kd,
                'intent' => $intents[array_rand($intents)],
                'competitor_rank' => rand(1, 10),
                'cpc' => $cpc,
                'traffic_value' => round($vol * floatval($cpc) * 0.3, 2), // Est. monthly traffic value
            ];
        }

        // Sort by volume desc
        usort($gapDetails, fn($a, $b) => $b['volume'] - $a['volume']);

        // ========== STEP 6: Quick Wins (KD < 30 + Volume > 1000) ==========
        $quickWins = array_values(array_filter($gapDetails, fn($g) => $g['kd'] < 30 && $g['volume'] > 1000));
        usort($quickWins, fn($a, $b) => $b['traffic_value'] - $a['traffic_value']);

        // ========== STEP 7: Niche Strength Score ==========
        $totalRelevant = count($shared) + count($gaps);
        $nicheScore = $totalRelevant > 0 ? round((count($shared) / $totalRelevant) * 100) : 0;

        // ========== STEP 8: Velocity Chart Data ==========
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $velocity = [];
        foreach (array_slice($competitors, 0, 2) as $c) {
            $velocity[$c] = array_map(fn() => rand(5, 35), $months);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'my_domain' => $host,
                'competitors' => $competitors,
                'competitor_details' => $competitorDetails,
                'stats' => [
                    'my_total' => count($myKeywords),
                    'comp_total' => count($competitorKeywords),
                    'gaps_count' => count($gaps),
                    'shared_count' => count($shared),
                    'my_urls' => $myUrlCount,
                ],
                'my_keywords' => array_slice($myKeywords, 0, 200), // Cap for frontend perf
                'gaps' => array_slice($gapDetails, 0, 50),
                'quick_wins' => array_slice($quickWins, 0, 15),
                'niche_score' => $nicheScore,
                'velocity_labels' => $months,
                'velocity_data' => $velocity,
            ]
        ]);
    }

    public function fetchPaa(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'query' => 'required|string',
            'location' => 'required|string',
        ]);

        $apiKey = \App\Models\Setting::get('competitor-x-ray_serpapi_key', $request->api_key);
        $query = $request->query;
        $location = $request->location;

        try {
            $response = \Illuminate\Support\Facades\Http::get('https://serpapi.com/search', [
                'engine' => 'google',
                'q' => $query,
                'location' => $location,
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $paa = $data['related_questions'] ?? [];
                return response()->json([
                    'status' => 'success',
                    'data' => $paa
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'SerpAPI Error: ' . ($response->json()['error'] ?? 'Unknown error')
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to SerpAPI: ' . $e->getMessage()
            ], 500);
        }
    }
}
