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
        $slug = 'seo-analyzer';

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $msg = $hasOwnership
                ? "رصيدك غير كافٍ لتحليل العناوين. التكلفة: {$cost} CRS."
                : $user->getLimitReachedMessage('محلل SEO الذكي', $slug);
            return response()->json(['status' => 'error', 'message' => $msg], $hasOwnership ? 402 : 403);
        }

        $headline = $request->input('headline');
        $analysis = $this->seoService->analyzeHeadline($headline);

        if (! $user->deductToolCredits($slug)) {
            \Illuminate\Support\Facades\Log::critical('[SEO Analyzer] Credits could not be deducted after headline analysis', [
                'user_id' => $user->id,
            ]);
        }
        \App\Models\AiUsage::create([
            'user_id' => $user->id,
            'tool' => $slug,
            'provider' => 'local',
            'model' => 'headline-analyzer',
            'status' => 'success',
        ]);

        $user->load('wallet');
        if (is_array($analysis)) {
            $analysis['balance'] = (float) ($user->wallet->balance_credits ?? 0);
        }

        return response()->json($analysis);
    }

    /**
     * Analyze content via AJAX.
     */
    public function analyzeContent(Request $request)
    {
        $user = auth()->user();
        $slug = 'seo-analyzer';

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $msg = $hasOwnership
                ? "رصيدك غير كافٍ لتحليل المحتوى. التكلفة: {$cost} CRS."
                : $user->getLimitReachedMessage('محلل SEO الذكي', $slug);
            return response()->json(['status' => 'error', 'message' => $msg], $hasOwnership ? 402 : 403);
        }

        $content = $request->input('content');
        $keyword = $request->input('keyword', '');
        $analysis = $this->seoService->analyzeContent($content, $keyword);

        if (! $user->deductToolCredits($slug)) {
            \Illuminate\Support\Facades\Log::critical('[SEO Analyzer] Credits could not be deducted after content analysis', [
                'user_id' => $user->id,
            ]);
        }
        \App\Models\AiUsage::create([
            'user_id' => $user->id,
            'tool' => $slug,
            'provider' => 'local',
            'model' => 'content-analyzer',
            'status' => 'success',
        ]);

        $user->load('wallet');
        if (is_array($analysis)) {
            $analysis['balance'] = (float) ($user->wallet->balance_credits ?? 0);
        }

        return response()->json($analysis);
    }
}
