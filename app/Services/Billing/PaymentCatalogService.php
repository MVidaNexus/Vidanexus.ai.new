<?php

namespace App\Services\Billing;

use App\Models\Setting;

class PaymentCatalogService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getPackages(): array
    {
        $defaultPackages = [
            'lite' => ['name' => 'Lite Dash', 'credits' => '100', 'price' => '35'],
            'standard' => ['name' => 'Creator Pack', 'credits' => '500', 'price' => '150'],
            'pro' => ['name' => 'Agency Pro', 'credits' => '2500', 'price' => '650'],
            'enterprise' => ['name' => 'Power Node', 'credits' => '10000', 'price' => '2250'],
        ];
        $savedPackagesJson = Setting::get('marketplace_packages');
        $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);

        foreach ($packages as $k => $pkg) {
            $basePrice = (float) str_replace(',', '', $pkg['price']);
            $discount = isset($pkg['discount']) ? (float) $pkg['discount'] : 0;
            $salePrice = $discount > 0 ? $basePrice - ($basePrice * ($discount / 100)) : $basePrice;
            $packages[$k]['final_price'] = $salePrice;
            $packages[$k]['credits_num'] = (int) str_replace(',', '', $pkg['credits']);
        }

        return $packages;
    }

    /**
     * Line item for payment review page (tool unlock).
     *
     * @return array<string, mixed>|null
     */
    public function resolveToolDisplayItem(string $slug): ?array
    {
        $toolConfig = collect(config('tools.all_tools', []))->where('slug', $slug)->first();
        if (! $toolConfig) {
            return null;
        }

        $unlockPrice = (int) Setting::get("tool_unlock_price_{$slug}", $toolConfig['unlock_price'] ?? 99);

        return [
            'name' => $toolConfig['name'],
            'tagline' => $toolConfig['tagline'] ?? $toolConfig['name'],
            'icon' => $toolConfig['icon'] ?? 'fa-cube',
            'color' => $toolConfig['color'] ?? 'var(--primary-cyan)',
            'price' => $unlockPrice,
            'credits' => (int) Setting::get("tool_bonus_credits_{$slug}", $toolConfig['initial_bonus_credits'] ?? 10),
        ];
    }

    /**
     * Minimal line item for Fawaterk (tool).
     *
     * @return array{name: string, price: int}|null
     */
    public function resolveToolCheckoutItem(string $slug): ?array
    {
        $toolConfig = collect(config('tools.all_tools', []))->where('slug', $slug)->first();
        if (! $toolConfig) {
            return null;
        }
        $unlockPrice = (int) Setting::get("tool_unlock_price_{$slug}", $toolConfig['unlock_price'] ?? 99);

        return ['name' => $toolConfig['name'], 'price' => $unlockPrice];
    }
}
