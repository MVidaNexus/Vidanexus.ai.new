<?php

namespace App\Services\Dashboard;

use App\Models\Setting;
use App\Models\User;
use App\Services\ToolMarketplacePresenter;
use Illuminate\Support\Facades\DB;

class DashboardViewService
{
    /**
     * @return array{user: User, walletBalance: float|int, tools: array, currentPlan: string, invoices: \Illuminate\Support\Collection, ownedCount: int, accessibleCount: int, totalTools: int}
     */
    public function buildIndexData(User $user): array
    {
        $user->load([
            'wallet',
            'ownedTools',
            'invoices' => fn ($q) => $q->latest()->limit(10),
        ]);

        $walletBalance = $user->wallet ? $user->wallet->balance_credits : 0;
        $invoices = $user->invoices;

        $settings = ToolMarketplacePresenter::settingsMap();
        $ownedSlugs = ToolMarketplacePresenter::ownedActiveSlugs($user);

        $todayUsage = DB::table('ai_usages')
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->select('tool', DB::raw('count(*) as cnt'))
            ->groupBy('tool')
            ->pluck('cnt', 'tool');

        $allToolsData = config('tools.all_tools', []);

        $tools = collect($allToolsData)->map(function (array $tool) use ($user, $ownedSlugs, $todayUsage, $settings) {
            $slug = $tool['slug'];
            $isAvailable = ToolMarketplacePresenter::boolSetting($settings, "tool_available_{$slug}", false);
            $isOwned = in_array($slug, $ownedSlugs, true);

            if ($user->isAdmin()) {
                $tool['accessible'] = true;
                $tool['is_available'] = true;
                $tool['is_owned'] = true;
            } else {
                $tool['is_available'] = $isAvailable;
                $tool['is_owned'] = $isOwned;
                $tool['accessible'] = $isOwned && $isAvailable;
            }

            $tool['unlock_price'] = ToolMarketplacePresenter::intSetting($settings, "tool_unlock_price_{$slug}", (int) ($tool['unlock_price'] ?? 99));
            $tool['credit_cost'] = ToolMarketplacePresenter::intSetting($settings, "tool_credit_cost_{$slug}", (int) ($tool['credit_cost_per_action'] ?? 1));
            $tool['bonus_credits'] = ToolMarketplacePresenter::intSetting($settings, "tool_bonus_credits_{$slug}", (int) ($tool['initial_bonus_credits'] ?? 10));
            $tool['required_label'] = ucfirst($tool['required_tier'] ?? 'Marketplace');
            $tool['used_today'] = (int) ($todayUsage[$slug] ?? 0);

            return $tool;
        })->sortByDesc('accessible')->values()->toArray();

        $ownedCount = collect($tools)->where('is_owned', true)->count();

        $welcomeCredits = (float) Setting::get('plan_credits_beginner', 0);

        return [
            'user' => $user,
            'walletBalance' => $walletBalance,
            'welcomeCredits' => $welcomeCredits,
            'tools' => $tools,
            'currentPlan' => $user->currentPlan(),
            'invoices' => $invoices,
            'ownedCount' => $ownedCount,
            'accessibleCount' => $ownedCount,
            'totalTools' => count($tools),
        ];
    }
}
