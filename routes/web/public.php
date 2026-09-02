<?php

use App\Http\Controllers\Web\ToolController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ToolController::class, 'index'])->name('home');
Route::get('/tools/{slug}', [ToolController::class, 'show'])->name('tools.show');
Route::get('/tool/{slug}', fn (string $slug) => redirect()->route('tools.show', ['slug' => $slug], 301));
Route::get('/pricing', [ToolController::class, 'pricing'])->name('pricing');
Route::get('/help-center', fn () => view('pages.help-center'))->name('help-center');
Route::get('/terms', fn () => view('pages.terms'))->name('terms');
Route::get('/privacy', fn () => view('pages.privacy'))->name('privacy');
Route::get('/refund', fn () => view('pages.refund'))->name('refund');
Route::get('/shipping', fn () => view('pages.shipping'))->name('shipping');

Route::get('/sitemap.xml', [\App\Http\Controllers\Web\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Web\SitemapController::class, 'robots'])->name('robots');

Route::get('/api-docs', fn () => view('pages.api-docs'))->name('api-docs');

// PWA Core Routes (Manifest, Service Worker, Offline Fallback, Client Script)
Route::get('/manifest.json', function () {
    $path = public_path('manifest.json');
    if (! file_exists($path)) {
        $path = base_path('manifest.json');
    }

    return response()->file($path, [
        'Content-Type' => 'application/manifest+json; charset=utf-8',
        'Cache-Control' => 'public, max-age=86400',
    ]);
});

Route::get('/site.webmanifest', function () {
    $path = public_path('site.webmanifest');
    if (! file_exists($path)) {
        $path = base_path('site.webmanifest');
    }

    return response()->file($path, [
        'Content-Type' => 'application/manifest+json; charset=utf-8',
        'Cache-Control' => 'public, max-age=86400',
    ]);
});

Route::get('/sw.js', function () {
    $path = public_path('sw.js');
    if (! file_exists($path)) {
        $path = base_path('sw.js');
    }

    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
});

Route::get('/offline.html', function () {
    $path = public_path('offline.html');
    if (! file_exists($path)) {
        $path = base_path('offline.html');
    }

    return response()->file($path, [
        'Content-Type' => 'text/html; charset=utf-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
});

Route::get('/js/pwa.js', function () {
    $path = public_path('js/pwa.js');
    if (! file_exists($path)) {
        $path = base_path('js/pwa.js');
    }

    return response()->file($path, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'public, max-age=86400',
    ]);
});

Route::get('/media/image-proxy', [\App\Http\Controllers\Media\ImageProxyController::class, 'show'])
    ->middleware('auth')
    ->name('media.image-proxy');
