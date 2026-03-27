<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;

class ToolController extends Controller
{
    /**
     * Show the public marketing landing page (Marketplace view).
     */
    public function index()
    {
        $allTools = config('tools.all_tools', []);
        $user = auth()->user();

        $tools = collect($allTools)->map(function ($tool) use ($user) {
            $isAvailable = (bool) Setting::get("tool_available_{$tool['slug']}", false);
            $isOwned = $user ? $user->ownsTool($tool['slug']) : false;

            // can_use in the marketing view means "Is this tool online in the system?"
            $tool['can_use'] = $isAvailable;
            $tool['is_owned'] = $isOwned;
            $tool['is_available'] = $isAvailable;

            $tool['unlock_price'] = (int) Setting::get("tool_unlock_price_{$tool['slug']}", $tool['unlock_price'] ?? 99);
            $tool['bonus_credits'] = (int) Setting::get("tool_bonus_credits_{$tool['slug']}", $tool['initial_bonus_credits'] ?? 10);
            $tool['credit_cost'] = (int) Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 1);

            return $tool;
        })->values();

        return view('welcome', compact('tools'));
    }

    /**
     * Show public tool details.
     */
    public function show($slug)
    {
        $allTools = config('tools.all_tools', []);
        $toolData = collect($allTools)->where('slug', $slug)->first();

        if (!$toolData) {
            abort(404);
        }

        $tool = $toolData;
        $accessible = false;
        $isAvailable = (bool) Setting::get("tool_available_{$slug}", false);
        $isOwned = false;

        if (auth()->check()) {
            if (auth()->user()->isAdmin()) {
                $accessible = true;
                $isOwned = true;
            } else {
                $isOwned = auth()->user()->ownsTool($slug);
                $accessible = $isAvailable && $isOwned;
            }
        }

        $tool['is_owned'] = $isOwned;
        $tool['is_available'] = $isAvailable;
        $tool['unlock_price'] = (int) Setting::get("tool_unlock_price_{$slug}", $tool['unlock_price'] ?? 99);
        $tool['bonus_credits'] = (int) Setting::get("tool_bonus_credits_{$slug}", $tool['initial_bonus_credits'] ?? 10);
        $tool['credit_cost'] = (int) Setting::get("tool_credit_cost_{$slug}", $tool['credit_cost_per_action'] ?? 1);

        return view('tool-details', compact('tool', 'accessible', 'isOwned', 'isAvailable'));
    }

    public function pricing()
    {
        $user = auth()->user();
        $tools = collect(config('tools.all_tools', []))->map(function ($tool) use ($user) {
            $tool['unlock_price'] = (int) Setting::get("tool_unlock_price_{$tool['slug']}", $tool['unlock_price'] ?? 99);
            $tool['credit_cost'] = (int) Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 1);
            $tool['is_owned'] = $user ? $user->ownsTool($tool['slug']) : false;
            return $tool;
        });

        return view('pricing', compact('tools'));
    }
}
