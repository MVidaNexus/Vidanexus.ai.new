<?php

namespace Modules\SEOAuditor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class SEOAuditorController extends Controller
{
    public function index()
    {
        return view('seoauditor::index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->url;
        $domain = parse_url($url, PHP_URL_HOST);

        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/seo_audit.py';

        $result = Process::run([
            $pythonPath,
            $scriptPath,
            '--url', $url
        ]);

        if ($result->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => json_decode($result->output(), true)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'SEO Audit Failed: ' . $result->errorOutput()
        ], 500);
    }
}
