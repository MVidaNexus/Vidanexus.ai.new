<?php

namespace Modules\ImgCompress\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ImgCompressController extends Controller
{
    public function index()
    {
        return view('img-compress::index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,webp,avif,pdf|max:20480',
            'format' => 'required|string',
            'quality' => 'required|integer|min:1|max:100',
            'remove_bg' => 'nullable|boolean',
            'resize_width' => 'nullable|integer',
        ]);

        $outputFiles = [];
        $pythonPath = '/home/vidanexusai/python_tools/venv/bin/python3';
        $scriptPath = '/home/vidanexusai/python_tools/img_bridge.py';

        foreach ($request->file('files') as $file) {
            $path = $file->store('img_uploads', 'public');
            $absoluteInputPath = storage_path('app/public/' . $path);
            
            $outputFilename = pathinfo($path, PATHINFO_FILENAME) . '_processed.' . $request->format;
            $absoluteOutputPath = storage_path('app/public/img_processed/' . $outputFilename);
            
            if (!file_exists(dirname($absoluteOutputPath))) {
                mkdir(dirname($absoluteOutputPath), 0755, true);
            }

            $result = Process::run([
                $pythonPath,
                $scriptPath,
                '--input', $absoluteInputPath,
                '--output', $absoluteOutputPath,
                '--format', $request->format,
                '--quality', $request->quality,
                '--remove-bg', $request->remove_bg ? 'true' : 'false',
                '--resize-width', $request->resize_width ?? '0'
            ]);

            if ($result->successful()) {
                $outputFiles[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'download_url' => asset('storage/img_processed/' . $outputFilename),
                    'size' => filesize($absoluteOutputPath)
                ];
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Processing Failed for ' . $file->getClientOriginalName() . ': ' . $result->errorOutput()
                ], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'files' => $outputFiles
        ]);
    }
}
