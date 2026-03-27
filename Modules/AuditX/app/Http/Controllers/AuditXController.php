<?php

namespace Modules\AuditX\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class AuditXController extends Controller
{
    public function index()
    {
        return view('auditx::index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->url;
        
        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/audit_bridge.py';

        $result = Process::run([
            $pythonPath,
            $scriptPath,
            '--url', $url,
            '--type', 'cro'
        ]);

        if ($result->successful()) {
            return response()->json([
                'status' => 'success',
                'report' => $result->output()
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Audit Failed: ' . $result->errorOutput()
        ], 500);
    }
}
