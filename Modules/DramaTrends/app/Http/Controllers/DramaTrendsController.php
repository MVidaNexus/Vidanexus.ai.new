<?php

namespace Modules\DramaTrends\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DramaTrends\Services\DramaTrendsService;
use Illuminate\Support\Facades\Log;

class DramaTrendsController extends Controller
{
    private DramaTrendsService $service;

    public function __construct()
    {
        $this->service = new DramaTrendsService();
    }

    /**
     * Main Dashboard view (Trends + Report tabs).
     */
    public function index()
    {
        return view('dramatrends::index');
    }

    /**
     * Report view.
     */
    public function report()
    {
        return view('dramatrends::report');
    }

    /**
     * Series Management admin page.
     */
    public function management()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this page.');
        }
        return view('dramatrends::management');
    }

    /**
     * Fetch combined Drama Trends data (WATCH IT + Google Trends).
     */
    public function dramaTrends(Request $request)
    {
        set_time_limit(120);
        $user = auth()->user();

        if (!$user->canUseTool('drama-trends')) {
            $msg = $user->getLimitReachedMessage('Drama Trends', 'drama-trends');
            return response()->json(['error' => $msg], 403);
        }

        if (!$user->wallet || $user->wallet->balance_credits < 1) {
            return response()->json(['error' => 'Insufficient balance for Drama tracking.'], 402);
        }

        $startDate = $request->input('startDate', '2026-02-19');
        $endDate   = $request->input('endDate', '2026-03-19');

        // Safety: Prevent empty strings or "null" from breaking the fetch
        if (empty($startDate) || $startDate === 'null') $startDate = '2026-02-19';
        if (empty($endDate) || $endDate === 'null')   $endDate   = '2026-03-19';

        $forceRefresh = $request->boolean('forceRefresh', false);

        $data = $this->service->fetchRamadanTrends($startDate, $endDate, $forceRefresh);

        if (!isset($data['error'])) {
            $user->wallet->decrement('balance_credits', 1);
            \App\Models\AiUsage::create([
                'user_id'  => $user->id,
                'tool'     => 'drama-trends',
                'provider' => 'api',
                'model'    => 'drama-trends',
                'status'   => 'success',
            ]);
        }

        return response()->json($data);
    }

    /**
     * GET series list.
     */
    public function getSeries()
    {
        return response()->json($this->service->loadSeries());
    }

    /**
     * POST save series list (admin only).
     */
    public function saveSeries(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $series = $request->input('series', []);

        // Validate
        if (!is_array($series) || count($series) === 0) {
            return response()->json(['error' => 'At least one series must be added.'], 422);
        }

        foreach ($series as &$s) {
            if (empty($s['name'])) {
                return response()->json(['error' => 'Series name is required for each item.'], 422);
            }
            $s['lead']          = $s['lead'] ?? '';
            $s['company']       = $s['company'] ?? '';
            $s['searchKeyword'] = $s['searchKeyword'] ?? '';
            $s['isBaseline']    = (bool)($s['isBaseline'] ?? false);
        }

        // Ensure exactly one baseline
        $hasBaseline = false;
        foreach ($series as $s) {
            if ($s['isBaseline']) {
                $hasBaseline = true;
                break;
            }
        }
        if (!$hasBaseline && count($series) > 0) {
            $series[0]['isBaseline'] = true;
        }

        $this->service->saveSeries($series);

        return response()->json(['success' => true, 'message' => 'Series saved successfully.']);
    }

    /**
     * GET current WATCH IT ranking.
     */
    public function getWatchItRanking()
    {
        return response()->json($this->service->loadWatchItRanking());
    }

    /**
     * POST save WATCH IT ranking (admin only).
     */
    public function saveWatchItRanking(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ranking = $request->input('ranking', []);
        if (!is_array($ranking) || count($ranking) === 0) {
            return response()->json(['error' => 'Ranking is required.'], 422);
        }

        // Ensure we only get Arabic name strings
        $ranking = array_values(array_filter($ranking, fn($v) => is_string($v) && trim($v) !== ''));

        $this->service->saveWatchItRanking($ranking);
        $this->service->clearCache();

        return response()->json(['success' => true, 'message' => 'WATCH IT ranking saved successfully.']);
    }
    /**
     * Fetch detailed trends for a specific series.
     */
    public function seriesDetails(Request $request)
    {
        $name = $request->input('name');
        $startDate = $request->input('startDate', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));

        if (!$name) {
            return response()->json(['error' => 'Series name is required.'], 422);
        }

        $seriesList = $this->service->loadSeries();
        $target = null;
        foreach ($seriesList as $s) {
            if ($s['name'] === $name) {
                $target = $s;
                break;
            }
        }

        if (!$target) {
            return response()->json(['error' => 'Series not found.'], 404);
        }

        $keyword = !empty($target['searchKeyword']) ? $target['searchKeyword'] : $target['name'];
        $details = $this->service->fetchDetailedTrendsForKeyword($keyword, $startDate, $endDate);

        return response()->json($details);
    }

    /**
     * Upload Google Trends CSV files and merge their data.
     */
    public function uploadCSV(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'csv_files' => 'required|array',
            'csv_files.*' => 'file|mimes:csv,txt|max:2048',
        ]);

        $files = $request->file('csv_files');
        if (empty($files)) {
            return response()->json(['error' => 'No files sent.'], 400);
        }

        try {
            $parsedData = $this->service->importCsvFiles($files);
            $this->service->clearCache(); // Force regeneration

            return response()->json([
                'success' => true,
                'message' => 'Imported and processed ' . count($files) . ' files successfully.',
                'data' => $parsedData
            ]);
        } catch (\Exception $e) {
            Log::error("DramaTrends CSV Import Error: " . $e->getMessage());
            return response()->json(['error' => 'Error during processing: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear CSV data and return to automatic API mode.
     */
    public function clearCSV()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $file = storage_path('app/drama-trends-cache/csv_trends_data.json');
        if (file_exists($file)) {
            @unlink($file);
        }
        
        $this->service->clearCache(); // Force regeneration

        return response()->json([
            'success' => true,
            'message' => 'CSV data cleared successfully. Returned to automatic mode.'
        ]);
    }

    /**
     * POST save NotebookLM report override (admin only).
     */
    public function saveNotebookOverride(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $override = $request->input('override', []);
        if (!is_array($override)) {
            return response()->json(['error' => 'Invalid data format.'], 422);
        }

        $this->service->saveNotebookOverride($override);

        return response()->json([
            'success' => true,
            'message' => 'NotebookLM report saved successfully.'
        ]);
    }

    /**
     * DELETE clear NotebookLM report override (admin only).
     */
    public function clearNotebookOverride()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $file = storage_path('app/drama-trends-cache/notebook_override.json');
        if (file_exists($file)) {
            @unlink($file);
        }
        
        $this->service->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'NotebookLM report cleared. Returned to automatic results.'
        ]);
    }
}
