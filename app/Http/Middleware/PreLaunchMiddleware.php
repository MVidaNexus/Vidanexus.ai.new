<?php

namespace App\Http\Middleware;

/**
 * Pre-Launch Middleware
 *
 * This middleware shields the application from public access during pre-launch.
 */

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreLaunchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if prelaunch mode is enabled in config
        if (!config('vidanexus.prelaunch', true)) {
            return $next($request);
        }

        // 1. Check for Bypass Token via URL (?preview=vn_secret_2026)
        $bypassToken = config('vidanexus.prelaunch_bypass_token');
        $previewInput = $request->query('preview');

        if ($previewInput && $previewInput === $bypassToken) {
            if ($request->hasSession()) {
                $request->session()->put('prelaunch_bypass', true);
                // Redirect to the same URL without the query param to "clean" the URL and trigger the next check
                return redirect($request->fullUrlWithoutQuery('preview'));
            }
        }

        // 2. Check Session for existing bypass
        if ($request->hasSession() && $request->session()->get('prelaunch_bypass')) {
            return $next($request);
        }

        // 3. Allow access to admin, login, register, and specific bypass routes
        $allowedRoutes = [
            'admin*',
            'login',
            'register',
            'forgot-password',
            'reset-password*',
            'api/waitlist',
            'coming-soon',
            '_debugbar*',
        ];

        foreach ($allowedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // 4. Allow all authenticated users (User or Admin)
        if (auth()->check()) {
            return $next($request);
        }

        // 5. Default: Show the Shield (Coming Soon)
        return response()->view('coming-soon');
    }
}
