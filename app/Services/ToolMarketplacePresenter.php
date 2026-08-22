<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Batches tool marketplace data: one Setting::getAllSettings() read and optional ownedTools eager load.
 */
class ToolMarketplacePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function settingsMap(): array
    {
        return Setting::getAllSettings();
    }

    /**
     * @return list<string>
     */
    public static function ownedActiveSlugs(User $user): array
    {
        if ($user->isAdmin()) {
            return collect(config('tools.all_tools', []))->pluck('slug')->all();
        }

        $user->loadMissing('ownedTools');

        return $user->ownedTools
            ->filter(fn ($t) => $t->isActive())
            ->pluck('tool_slug')
            ->unique()
            ->values()
            ->all();
    }

    public static function boolSetting(array $settings, string $key, bool $default = false): bool
    {
        return (bool) ($settings[$key] ?? $default);
    }

    public static function intSetting(array $settings, string $key, int $default): int
    {
        return (int) ($settings[$key] ?? $default);
    }

    /**
     * Welcome / marketing listing.
     *
     * @param  array<int, array<string, mixed>>  $allTools
     */
    public static function forPublicListing(iterable $allTools, ?User $user): Collection
    {
        $settings = self::settingsMap();
        $owned = $user ? self::ownedActiveSlugs($user) : [];

        return collect($allTools)->map(function (array $tool) use ($settings, $owned, $user) {
            $slug = $tool['slug'];
            $isAvailable = self::boolSetting($settings, "tool_available_{$slug}", false);
            $isOwned = $user && in_array($slug, $owned, true);

            $tool['can_use'] = $isAvailable;
            $tool['is_owned'] = $isOwned;
            $tool['is_available'] = $isAvailable;
            $tool['unlock_price'] = self::intSetting($settings, "tool_unlock_price_{$slug}", (int) ($tool['unlock_price'] ?? 99));
            $tool['bonus_credits'] = self::intSetting($settings, "tool_bonus_credits_{$slug}", (int) ($tool['initial_bonus_credits'] ?? 10));
            $tool['credit_cost'] = self::intSetting($settings, "tool_credit_cost_{$slug}", (int) ($tool['credit_cost_per_action'] ?? 1));

            return $tool;
        })->values();
    }

    /**
     * Pricing page (lighter than full dashboard row).
     *
     * @param  array<int, array<string, mixed>>  $allTools
     */
    public static function forPricing(iterable $allTools, ?User $user): Collection
    {
        $settings = self::settingsMap();
        $owned = $user ? self::ownedActiveSlugs($user) : [];

        return collect($allTools)->map(function (array $tool) use ($settings, $owned, $user) {
            $slug = $tool['slug'];
            $tool['unlock_price'] = self::intSetting($settings, "tool_unlock_price_{$slug}", (int) ($tool['unlock_price'] ?? 99));
            $tool['credit_cost'] = self::intSetting($settings, "tool_credit_cost_{$slug}", (int) ($tool['credit_cost_per_action'] ?? 1));
            $tool['is_owned'] = $user && in_array($slug, $owned, true);

            return $tool;
        });
    }
}
