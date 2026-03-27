<?php

namespace Modules\MoneyPrinter\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class MoneyPrinterController extends Controller
{
    public function index()
    {
        return view('moneyprinter::index');
    }

    public function run(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:500',
            'platform' => 'required|in:twitter,shorts,affiliate',
        ]);

        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/money_bridge.py';

        $result = Process::run([
            $pythonPath,
            $scriptPath,
            '--topic', $request->topic,
            '--platform', $request->platform
        ]);

        if ($result->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => json_decode($result->output(), true)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Automation Failed: ' . $result->errorOutput()
        ], 500);
    }
}
