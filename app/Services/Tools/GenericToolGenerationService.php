<?php

namespace App\Services\Tools;

use App\Core\AI\AIManager;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\ToolMarketplacePresenter;
use Illuminate\Support\Facades\Log;

class GenericToolGenerationService
{
    public function __construct(
        protected AIManager $aiManager
    ) {}

    /**
     * @return array{ok: true, response: string}|array{ok: false, status: int, body: array<string, mixed>}
     */
    public function generate(User $user, string $slug, string $input, ?array $context): array
    {
        $allTools = config('tools.all_tools', []);
        $tool = collect($allTools)->firstWhere('slug', $slug);

        if (! $tool) {
            return ['ok' => false, 'status' => 404, 'body' => ['status' => 'error', 'message' => 'Tool not found.']];
        }

        $tool = (array) $tool;
        $user->loadMissing(['ownedTools', 'wallet']);

        $settings = ToolMarketplacePresenter::settingsMap();
        $ownedSlugs = ToolMarketplacePresenter::ownedActiveSlugs($user);

        if (! $user->isAdmin() && ! in_array($slug, $ownedSlugs, true)) {
            $unlockPrice = ToolMarketplacePresenter::intSetting($settings, "tool_unlock_price_{$slug}", (int) ($tool['unlock_price'] ?? 99));

            return [
                'ok' => false,
                'status' => 403,
                'body' => [
                    'status' => 'error',
                    'error' => 'tool_locked',
                    'message' => 'You need to unlock "'.$tool['name'].'" first.',
                    'unlock_price' => $unlockPrice,
                ],
            ];
        }

        if (! $user->isAdmin() && ! $user->canUseTool($slug)) {
            $creditCost = $user->getToolCreditCost($slug);

            return [
                'ok' => false,
                'status' => 402,
                'body' => [
                    'status' => 'error',
                    'error' => 'insufficient_credits',
                    'message' => 'Insufficient credits. You need '.$creditCost.' CRS to use this tool.',
                    'required' => $creditCost,
                    'balance' => $user->getDailyToolLimit($slug),
                ],
            ];
        }

        $prompt = "You are an expert AI agent specializing in {$tool['name']}.\n";
        $prompt .= "Tool Description: {$tool['description']}\n\n";
        $prompt .= 'User Input: '.$input."\n";

        if ($context) {
            $prompt .= 'Additional Context: '.json_encode($context)."\n";
        }

        $prompt .= "\nProvide highly professional, actionable, and comprehensive output based on the input.";

        try {
            $response = $this->aiManager->generateResponse($prompt, $slug);

            if (! $user->isAdmin()) {
                if (! $user->deductToolCredits($slug)) {
                    Log::critical('Credits could not be deducted after successful AI response', [
                        'user_id' => $user->id,
                        'slug' => $slug,
                    ]);
                }
            }

            AiUsage::create([
                'user_id' => $user->id,
                'tool' => $slug,
                'status' => 'success',
            ]);

            $freshBalance = (float) ($user->wallet()->value('balance_credits') ?? 0);
            return ['ok' => true, 'response' => $response, 'balance' => $freshBalance];
        } catch (\Exception $e) {
            Log::error("Generic Tool Generation Error ({$slug}): ".$e->getMessage());

            return [
                'ok' => false,
                'status' => 500,
                'body' => ['status' => 'error', 'message' => 'AI processing failed.'],
            ];
        }
    }
}
