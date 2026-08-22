<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ToolMarketplacePresenter;

class ToolController extends Controller
{
    public function index()
    {
        $allTools = config('tools.all_tools', []);
        $user = auth()->user();

        $tools = ToolMarketplacePresenter::forPublicListing($allTools, $user);

        return view('welcome', compact('tools'));
    }

    public function show(string $slug)
    {
        $allTools = config('tools.all_tools', []);
        $toolData = collect($allTools)->firstWhere('slug', $slug);

        if (! $toolData) {
            abort(404);
        }

        $tool = $toolData;
        $settings = ToolMarketplacePresenter::settingsMap();
        $isAvailable = ToolMarketplacePresenter::boolSetting($settings, "tool_available_{$slug}", false);
        $isOwned = false;
        $accessible = false;

        if (auth()->check()) {
            $u = auth()->user();
            $u->loadMissing('ownedTools');
            if ($u->isAdmin()) {
                $accessible = true;
                $isOwned = true;
            } else {
                $isOwned = $u->ownsTool($slug);
                $accessible = $isAvailable && $isOwned;
            }
        }

        $tool['is_owned'] = $isOwned;
        $tool['is_available'] = $isAvailable;
        $tool['unlock_price'] = ToolMarketplacePresenter::intSetting($settings, "tool_unlock_price_{$slug}", (int) ($tool['unlock_price'] ?? 99));
        $tool['bonus_credits'] = ToolMarketplacePresenter::intSetting($settings, "tool_bonus_credits_{$slug}", (int) ($tool['initial_bonus_credits'] ?? 10));
        $tool['credit_cost'] = ToolMarketplacePresenter::intSetting($settings, "tool_credit_cost_{$slug}", (int) ($tool['credit_cost_per_action'] ?? 1));

        return view('tool-details', compact('tool', 'accessible', 'isOwned', 'isAvailable'));
    }

    public function pricing()
    {
        $user = auth()->user();
        if ($user) {
            $user->loadMissing('ownedTools');
        }

        $tools = ToolMarketplacePresenter::forPricing(config('tools.all_tools', []), $user);

        return view('pricing', compact('tools'));
    }
}
