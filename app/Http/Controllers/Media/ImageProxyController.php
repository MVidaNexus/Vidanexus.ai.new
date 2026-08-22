<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageProxyController extends Controller
{
    public function show(Request $request)
    {
        $url = trim((string) $request->query('url', ''));
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid image URL.');
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (! $host || ! preg_match('/\.(googleusercontent|ggpht|gstatic|google|ytimg|youtube)\./i', $host)) {
            abort(403, 'Image host not allowed.');
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; VidaNexus/1.0)',
                'Accept' => 'image/*,*/*',
            ])->timeout(8)->get($url);

            if ($response->failed()) {
                Log::warning('[ImageProxy] Upstream failed', ['url' => $url, 'status' => $response->status()]);

                return response()->file(public_path('assets/logo.png'));
            }

            $contentType = $response->header('Content-Type') ?: 'image/jpeg';

            return response($response->body(), 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (\Throwable $e) {
            Log::warning('[ImageProxy] Exception', ['url' => $url, 'error' => $e->getMessage()]);

            return response()->file(public_path('assets/logo.png'));
        }
    }
}
