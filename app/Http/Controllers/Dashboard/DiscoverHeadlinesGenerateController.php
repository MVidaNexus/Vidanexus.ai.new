<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Support\CountryRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\DiscoverHeadlines\Jobs\GenerateHeadlinesJob;

class DiscoverHeadlinesGenerateController extends Controller
{
    public function __invoke(Request $request)
    {
        ini_set('max_execution_time', 600);
        set_time_limit(600);

        $keyword = $request->input('keyword');
        $content = $request->input('content');
        $type = $request->input('type', 'keyword');
        $progressId = $request->input('progress_id') ?: 'hl_'.time();
        $variantsCount = $request->input('variants', 7);

        $validator = Validator::make($request->all(), [
            'keyword' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:100000',
            'type' => 'required|string|in:keyword,content',
            'variants' => 'nullable|integer|min:3|max:15',
            'country' => 'nullable|string|size:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
        }

        $resolved = CountryRegistry::resolveRegion(
            $request->input('country'),
            CountryRegistry::globalMap(),
            CountryRegistry::defaultRegion()
        );
        $region = $resolved['region'];

        $user = auth()->user();

        if (! $user->canUseTool('discover-headlines')) {
            $msg = $user->getLimitReachedMessage('Discover Headlines', 'discover-headlines');

            return response()->json(['status' => 'error', 'message' => $msg], 403);
        }

        if (! $user->wallet || $user->wallet->balance_credits < 1) {
            $msg = 'Insufficient balance to generate headlines.';

            return response()->json(['status' => 'error', 'message' => $msg], 402);
        }

        Cache::put("gen_progress_{$progressId}", [
            'stage' => 'starting',
            'message' => 'Job queued. Starting fast inline intelligence engine...',
        ], 300);

        try {
            GenerateHeadlinesJob::dispatchSync($user->id, [
                'keyword' => $keyword,
                'content' => $content,
                'type' => $type,
                'country' => $region,
                'variants' => $variantsCount,
                'progress_id' => $progressId,
            ]);

            Log::info("[DiscoverHeadlines] Sync successful for {$progressId}");
        } catch (\Throwable $e) {
            Log::error('[DiscoverHeadlines] FastSync failed: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ غير متوقع أثناء المعالجة.',
                'progress_id' => $progressId,
            ], 500);
        }

        return response()->json([
            'status' => 'processing',
            'message' => 'Intelligence extraction started inline.',
            'progress_id' => $progressId,
        ]);
    }
}
