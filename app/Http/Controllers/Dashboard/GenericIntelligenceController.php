<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\GenerateGenericToolRequest;
use App\Services\Tools\GenericToolGenerationService;
use Illuminate\Http\JsonResponse;

class GenericIntelligenceController extends Controller
{
    public function __construct(
        protected GenericToolGenerationService $genericToolGeneration
    ) {}

    public function show(string $slug)
    {
        $allTools = config('tools.all_tools', []);
        $tool = collect($allTools)->firstWhere('slug', $slug);

        if (! $tool) {
            abort(404);
        }

        $tool = (array) $tool;

        return view('dashboard.generic-tool', compact('tool'));
    }

    public function generate(GenerateGenericToolRequest $request, string $slug): JsonResponse
    {
        $user = $request->user();

        $result = $this->genericToolGeneration->generate(
            $user,
            $slug,
            $request->validated('input'),
            $request->validated('context')
        );

        if (! $result['ok']) {
            return response()->json($result['body'], $result['status']);
        }

        // Refresh the wallet so the post-deduction balance is returned to
        // the client. The live-credits JS module reads this `balance` key
        // to animate the navbar chip without a page refresh.
        $balance = (float) ($user->wallet()->value('balance_credits') ?? 0);

        return response()->json([
            'status' => 'success',
            'response' => $result['response'],
            'balance' => $balance,
            'credits_balance' => $balance,
        ]);
    }
}
