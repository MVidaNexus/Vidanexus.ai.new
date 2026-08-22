<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\AnalyzeNLPRequest;
use App\Services\NLPAnalysisService;
use Illuminate\Http\JsonResponse;

class NLPController extends Controller
{
    public function index()
    {
        return view('dashboard.nlp.index');
    }

    public function analyze(AnalyzeNLPRequest $request, NLPAnalysisService $service): JsonResponse
    {
        try {
            $analysis = $service->analyze(
                $request->validated('content'),
                $request->validated('target_keyword')
            );

            return response()->json(['status' => 'success', 'data' => $analysis]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
