<?php

namespace Modules\AIOOptimizer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class AIOOptimizerController extends Controller
{
    public function index()
    {
        return view('aiooptimizer::index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->url;
        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/aio_optimizer.py';

        $result = Process::timeout(120)->run([
            $pythonPath,
            $scriptPath,
            '--url', $url
        ]);

        if ($result->successful()) {
            $data = json_decode($result->output(), true);
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'data' => $data
                ]);
            }
            return response()->json([
                'status' => 'error',
                'message' => $data['message'] ?? 'Unknown analysis error'
            ], 500);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'AIO Analysis Failed: ' . $result->errorOutput()
        ], 500);
    }
}
