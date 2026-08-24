<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiUsage;
use App\Models\Setting;
use App\Support\ToolApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\AIKeywordRadar\Models\Keyword;
use Modules\AIKeywordRadar\Services\KeywordService;
use Modules\AIKeywordRadar\Support\KeywordPayload;

class AiKeywordRadarSyncController extends Controller
{
    public function __invoke(Request $request, KeywordService $keywordService)
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $user = auth()->user();
        $slug = 'ai-keyword-radar';
        $lang = $request->get('lang', 'ar');
        $timeFilter = $request->get('time_filter', '60m');
        $boxId = $request->get('box_id');

        Log::info('[Keyword Radar Sync] Request received', [
            'user_id' => $user->id,
            'lang' => $lang,
            'time_filter' => $timeFilter,
            'box_id' => $boxId,
        ]);

        if (! $user->canUseTool($slug)) {
            $cost = $user->getToolCreditCost($slug);
            $hasOwnership = $user->ownsTool($slug);
            $code = $hasOwnership ? ToolApiResponse::INSUFFICIENT_CREDITS : ToolApiResponse::TOOL_LOCKED;
            $msg = $hasOwnership
                ? ToolApiResponse::userMessage(ToolApiResponse::INSUFFICIENT_CREDITS)
                : $user->getLimitReachedMessage('Keyword Radar', $slug);

            Log::warning('[Keyword Radar Sync] Credit/ownership check failed', [
                'user_id' => $user->id,
                'code' => $code,
                'required' => $cost,
            ]);

            if ($request->ajax()) {
                return ToolApiResponse::error($code, $msg, $hasOwnership ? 402 : 403);
            }

            return back()->with('error', $msg);
        }

        $user->refresh();
        $competitorCheck = $this->validateCompetitorsConfigured($user, $lang, $boxId);
        if ($competitorCheck !== null) {
            Log::warning('[Keyword Radar Sync] Validation failed', [
                'user_id' => $user->id,
                'lang' => $lang,
                'message' => $competitorCheck,
            ]);

            if ($request->ajax()) {
                return ToolApiResponse::error(
                    ToolApiResponse::VALIDATION_ERROR,
                    $competitorCheck,
                    422
                );
            }

            return back()->with('error', $competitorCheck);
        }

        $category = $boxId ? "Target:{$boxId}" : 'Target';

        if (KeywordPayload::isSyncLocked($user->id, $lang, $boxId)) {
            $remain = KeywordPayload::syncLockRemainingSeconds($user->id, $lang, $boxId);
            Log::info('[Keyword Radar Sync] Blocked — lock active', [
                'user_id' => $user->id,
                'lang' => $lang,
                'lock_remaining_seconds' => $remain,
            ]);

            if ($request->ajax()) {
                return ToolApiResponse::error(
                    ToolApiResponse::ALREADY_PROCESSING,
                    ToolApiResponse::userMessage(ToolApiResponse::ALREADY_PROCESSING),
                    429,
                    [
                        'sync_running' => true,
                        'lock_remaining_seconds' => $remain,
                    ]
                );
            }

            return back()->with('error', ToolApiResponse::userMessage(ToolApiResponse::ALREADY_PROCESSING));
        }

        KeywordPayload::acquireSyncLock($user->id, $lang, $boxId);

        try {
            Log::info('[Keyword Radar Sync] Starting fetch', [
                'user_id' => $user->id,
                'lang' => $lang,
                'time_filter' => $timeFilter,
            ]);

            $result = $keywordService->syncKeywords(500, $lang, $user->id, $timeFilter, $boxId);
            $saved = (int) ($result['saved'] ?? 0);
            $headlines = (int) ($result['headlines'] ?? 0);

            if ($saved > 0) {
                if (! $user->deductToolCredits($slug)) {
                    Log::critical('[Keyword Radar Sync] Credits could not be deducted after successful sync', [
                        'user_id' => $user->id,
                        'slug' => $slug,
                        'saved' => $saved,
                    ]);
                } else {
                    Log::info('[Keyword Radar Sync] Credits deducted after successful sync', [
                        'user_id' => $user->id,
                        'saved' => $saved,
                    ]);
                }

                AiUsage::create([
                    'user_id' => $user->id,
                    'tool' => $slug,
                    'provider' => 'sync',
                    'model' => 'competitor-sync',
                    'status' => 'success',
                ]);
            }

            $listQuery = Keyword::where('user_id', $user->id)
                ->where('category', $category)
                ->where('lang', $lang);
            KeywordPayload::applyRetentionScope($listQuery);
            $keywords = KeywordPayload::fromCollection(
                $listQuery->orderByRaw('COALESCE(synced_at, published_at, created_at) DESC')
                    ->take(500)
                    ->get()
            );

            $cacheKey = $boxId ? "target_keywords_{$user->id}_{$boxId}" : "target_keywords_{$user->id}_{$lang}";
            Cache::put($cacheKey, $keywords, 3600);

            $visibleCount = count($keywords);
            if ($saved > 0) {
                $statusMsg = "Success! Found {$saved} new trend(s) from {$headlines} headline(s). Showing {$visibleCount} in radar.";
            } elseif ($visibleCount > 0) {
                $statusMsg = "Scan complete. {$visibleCount} keyword(s) match your retention window ({$headlines} headlines scanned).";
            } elseif ($headlines > 0) {
                $statusMsg = 'Headlines were found but no keywords passed quality filters. Try Last 24h or All Time.';
            } else {
                $statusMsg = 'No recent headlines found from your competitor sources for this time range. Try Last 24h or All Time.';
            }

            Log::info('[Keyword Radar Sync] Completed', [
                'user_id' => $user->id,
                'saved' => $saved,
                'headlines' => $headlines,
                'visible' => $visibleCount,
            ]);

            if ($request->ajax()) {
                $user->load('wallet');

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'message' => $statusMsg,
                    'current_count' => count($keywords),
                    'new_count' => $saved,
                    'keywords' => $keywords,
                    'lang' => $lang,
                    'box_id' => $boxId,
                    'headlines' => $headlines,
                    'balance' => (float) ($user->wallet->balance_credits ?? 0),
                ]);
            }

            return back()->with('success', $statusMsg);
        } catch (\Throwable $e) {
            Log::error('[Keyword Radar Sync] Fetch failed', [
                'user_id' => $user->id,
                'lang' => $lang,
                'time_filter' => $timeFilter,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = ToolApiResponse::userMessage(ToolApiResponse::FETCH_FAILED);

            if ($request->ajax()) {
                return ToolApiResponse::error(
                    ToolApiResponse::FETCH_FAILED,
                    $message,
                    500
                );
            }

            return back()->with('error', $message);
        } finally {
            KeywordPayload::releaseSyncLock($user->id, $lang, $boxId);
        }
    }

    private function validateCompetitorsConfigured($user, string $lang, ?string $boxId): ?string
    {
        $settings = $user->settings ?? [];

        if ($boxId) {
            foreach ($settings['keywords_custom_boxes'] ?? [] as $box) {
                if (($box['id'] ?? '') === $boxId) {
                    if (empty(trim($box['competitors'] ?? ''))) {
                        return 'No competitors found in this box. Please add competitor URLs first.';
                    }

                    return null;
                }
            }

            return 'Custom box not found.';
        }

        $userCompetitors = ($lang === 'en')
            ? ($settings['keywords_competitors_en'] ?? '')
            : ($settings['keywords_competitors'] ?? '');

        $globalCompetitors = Setting::get('ai-keyword-radar_competitors', '');

        if (empty(trim($userCompetitors)) && empty(trim($globalCompetitors))) {
            return 'No competitors found. Please add competitor website links in Radar Settings (or contact admin for global sources) before syncing.';
        }

        return null;
    }
}
