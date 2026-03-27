<?php

namespace Modules\SEOAnalyzer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SEOAnalyzerService;
use Illuminate\Http\Request;

class SEOAnalyzerController extends Controller
{
    protected $seoService;

    public function __construct(SEOAnalyzerService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Display the SEO Analyzer dashboard.
     */
    public function index()
    {
        return view('seoanalyzer::index');
    }

    /**
     * Analyze a headline via AJAX.
     */
    public function analyzeHeadline(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canUseTool('seo-analyzer')) {
            $msg = $user->getLimitReachedMessage('محلل SEO الذكي', 'seo-analyzer');
            return response()->json(['status' => 'error', 'message' => $msg], 403);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            return response()->json(['status' => 'error', 'message' => 'رصيدك غير كافٍ لتحليل العناوين.'], 402);
        }

        $headline = $request->input('headline');
        $analysis = $this->seoService->analyzeHeadline($headline);
        
        $user->wallet->decrement('balance_credits', 1);
        \App\Models\AiUsage::create([
            'user_id' => $user->id,
            'tool' => 'seo-analyzer',
            'provider' => 'local',
            'model' => 'headline-analyzer',
            'status' => 'success',
        ]);
        
        return response()->json($analysis);
    }

    /**
     * Analyze content via AJAX.
     */
    public function analyzeContent(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->canUseTool('seo-analyzer')) {
            $msg = $user->getLimitReachedMessage('محلل SEO الذكي', 'seo-analyzer');
            return response()->json(['status' => 'error', 'message' => $msg], 403);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            return response()->json(['status' => 'error', 'message' => 'رصيدك غير كافٍ لتحليل المحتوى.'], 402);
        }

        $content = $request->input('content');
        $keyword = $request->input('keyword', '');
        $analysis = $this->seoService->analyzeContent($content, $keyword);
        
        $user->wallet->decrement('balance_credits', 1);
        \App\Models\AiUsage::create([
            'user_id' => $user->id,
            'tool' => 'seo-analyzer',
            'provider' => 'local',
            'model' => 'content-analyzer',
            'status' => 'success',
        ]);
        
        return response()->json($analysis);
    }
}
