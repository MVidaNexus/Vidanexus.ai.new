<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\AI\AIManager;
use Illuminate\Support\Facades\Log;

class GenericIntelligenceController extends Controller
{
    protected $aiManager;

    public function __construct(AIManager $aiManager)
    {
        $this->aiManager = $aiManager;
    }

    /**
     * Show the generic tool interface based on slug.
     */
    public function show($slug)
    {
        $allTools = config('tools.all_tools', []);
        $tool = collect($allTools)->firstWhere('slug', $slug);

        if (!$tool) {
            abort(404);
        }

        $tool = (array)$tool;

        return view('dashboard.generic-tool', compact('tool'));
    }

    /**
     * Handle AI generation for generic tools.
     */
    public function generate(Request $request, $slug)
    {
        $allTools = config('tools.all_tools', []);
        $tool = collect($allTools)->where('slug', $slug)->first();

        if (!$tool) {
            return response()->json(['status' => 'error', 'message' => 'Tool not found.'], 404);
        }

        $tool = (array)$tool;
        $user = auth()->user();

        // Marketplace: Ownership check
        if (!$user->isAdmin() && !$user->ownsTool($slug)) {
            $unlockPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$slug}", $tool['unlock_price'] ?? 99);
            return response()->json([
                'status' => 'error',
                'error' => 'tool_locked',
                'message' => 'You need to unlock "' . $tool['name'] . '" first.',
                'unlock_price' => $unlockPrice,
            ], 403);
        }

        // Marketplace: Credit guard
        if (!$user->isAdmin() && !$user->canUseTool($slug)) {
            $creditCost = $user->getToolCreditCost($slug);
            return response()->json([
                'status' => 'error',
                'error' => 'insufficient_credits',
                'message' => 'Insufficient credits. You need ' . $creditCost . ' CRS to use this tool.',
                'required' => $creditCost,
                'balance' => $user->wallet ? $user->wallet->balance_credits : 0,
            ], 402);
        }

        $request->validate([
            'input' => 'required|string',
            'context' => 'nullable|array'
        ]);

        $prompt = "You are an expert AI agent specializing in {$tool['name']}.\n";
        $prompt .= "Tool Description: {$tool['description']}\n\n";
        $prompt .= "User Input: {$request->input}\n";
        
        if ($request->context) {
            $prompt .= "Additional Context: " . json_encode($request->context) . "\n";
        }

        $prompt .= "\nProvide highly professional, actionable, and comprehensive output based on the input.";

        try {
            $response = $this->aiManager->generateResponse($prompt, $slug);

            // Deduct credits on success
            if (!$user->isAdmin()) {
                $user->deductToolCredits($slug);

                // Log the transaction
                $creditCost = $user->getToolCreditCost($slug);
                if ($creditCost > 0 && $user->wallet) {
                    \App\Models\Transaction::create([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'wallet_id' => $user->wallet->id,
                        'type' => 'deduction',
                        'amount' => $creditCost,
                        'tool_name' => $tool['name'],
                        'idempotency_key' => 'USE_' . $slug . '_' . $user->id . '_' . time(),
                    ]);
                }
            }

            // Log AI usage
            \App\Models\AiUsage::create([
                'user_id' => $user->id,
                'tool' => $slug,
                'status' => 'success',
            ]);

            return response()->json(['status' => 'success', 'response' => $response]);
        } catch (\Exception $e) {
            Log::error("Generic Tool Generation Error ({$slug}): " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'AI processing failed.'], 500);
        }
    }

}
