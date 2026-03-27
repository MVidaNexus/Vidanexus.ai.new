<?php

namespace Modules\WebToApp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class WebToAppController extends Controller
{
    public function index()
    {
        return view('webtoapp::index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'app_name' => 'required|string|max:255',
            'package_name' => 'required|string|regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/i',
        ]);

        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/app_gen.py';

        $outputZip = 'web_to_app_' . time() . '.zip';
        $absoluteOutputPath = storage_path('app/public/web_to_app/' . $outputZip);

        if (!file_exists(dirname($absoluteOutputPath))) {
            mkdir(dirname($absoluteOutputPath), 0755, true);
        }

        $result = Process::run([
            $pythonPath,
            $scriptPath,
            '--url', $request->url,
            '--name', $request->app_name,
            '--package', $request->package_name,
            '--output', $absoluteOutputPath
        ]);

        if ($result->successful()) {
            return response()->json([
                'status' => 'success',
                'download_url' => asset('storage/web_to_app/' . $outputZip)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Generation Failed: ' . $result->errorOutput()
        ], 500);
    }
}
