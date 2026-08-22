<?php

namespace App\Http\Middleware;

use App\Support\ToolApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ToolAccessMiddleware
{
    /**
     * Verify that the user owns the requested tool and has sufficient credits.
     *
     * Usage in routes: ->middleware('tool.access:ai-keyword-radar')
     */
    public function handle(Request $request, Closure $next, string $toolSlug): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return ToolApiResponse::error(
                    ToolApiResponse::AUTH_REQUIRED,
                    ToolApiResponse::userMessage(ToolApiResponse::AUTH_REQUIRED),
                    401
                );
            }

            return redirect('/login')->with('error', 'Please log in to access this tool.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->ownsTool($toolSlug)) {
            $toolConfig = collect(config('tools.all_tools', []))->where('slug', $toolSlug)->first();
            $unlockPrice = $toolConfig ? ($toolConfig['unlock_price'] ?? 0) : 0;

            if ($request->expectsJson()) {
                return ToolApiResponse::error(
                    ToolApiResponse::TOOL_LOCKED,
                    ToolApiResponse::userMessage(ToolApiResponse::TOOL_LOCKED),
                    403,
                    [
                        'unlock_price' => $unlockPrice,
                        'tool_slug' => $toolSlug,
                        'tool_name' => $toolConfig['name'] ?? $toolSlug,
                    ]
                );
            }

            return redirect('/dashboard')->with('error', 'You need to unlock "' . ($toolConfig['name'] ?? $toolSlug) . '" first. Price: ' . number_format($unlockPrice) . ' EGP');
        }

        if (! $user->canUseTool($toolSlug)) {
            $creditCost = $user->getToolCreditCost($toolSlug);

            if ($request->expectsJson()) {
                return ToolApiResponse::error(
                    ToolApiResponse::INSUFFICIENT_CREDITS,
                    ToolApiResponse::userMessage(ToolApiResponse::INSUFFICIENT_CREDITS),
                    402,
                    [
                        'required' => $creditCost,
                        'balance' => $user->wallet ? $user->wallet->balance_credits : 0,
                    ]
                );
            }

            return redirect('/dashboard#billing')->with('error', 'Insufficient credits. You need ' . $creditCost . ' CRS to use this tool. Please top up your wallet.');
        }

        return $next($request);
    }
}
