<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditsBalanceController extends Controller
{
    /**
     * Returns the current authenticated user's wallet CRS balance.
     *
     * The dashboard live-credits JS module hits this endpoint after any
     * action whose JSON response does not already carry the new balance,
     * so the navbar chip and dashboard widgets can reflect the new value
     * without a full page refresh.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['balance' => 0, 'formatted' => '0.00'], 401);
        }

        $user->loadMissing('wallet');

        $balance = (float) ($user->wallet->balance_credits ?? 0);

        return response()->json([
            'balance' => $balance,
            'formatted' => number_format($balance, 2),
        ]);
    }
}
