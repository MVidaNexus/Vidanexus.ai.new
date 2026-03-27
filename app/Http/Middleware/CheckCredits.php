<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCredits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $wallet = $user->wallet;

        // If user has no wallet, they have 0 credits
        if (!$wallet || $wallet->balance_credits <= 0) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sorry, insufficient balance. Please recharge your balance to continue.'
                ], 402);
            }
            return redirect()->route('home')->with('error', 'Sorry, insufficient balance. Please recharge your balance to continue.');
        }

        return $next($request);
    }
}
