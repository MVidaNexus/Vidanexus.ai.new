<?php

namespace Modules\FolioOCR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class FolioOCRController extends Controller
{
    public function index()
    {
        return view('folio-ocr::index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('ocr_uploads', 'public');
        $absolutePath = storage_path('app/public/' . $path);

        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/ocr_bridge.py';

        $result = Process::run([
            $pythonPath,
            $scriptPath,
            '--input', $absolutePath
        ]);

        if ($result->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => json_decode($result->output(), true)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'OCR Processing Failed: ' . $result->errorOutput()
        ], 500);
    }
}
