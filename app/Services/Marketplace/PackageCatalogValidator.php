<?php

namespace App\Services\Marketplace;

use App\Models\SubscriptionPackage;
use App\Rules\ToolSlugExistsInConfig;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PackageCatalogValidator
{
    /**
     * Validate payload for creating/updating a subscription package and its tool rows (admin/API use).
     *
     * @param  array<string, mixed>  $packageAttributes  slug, name, prices, etc.
     * @param  list<array{tool_slug: string, credits_per_cycle: int}>  $tools
     * @return array{package: array<string, mixed>, tools: list<array{tool_slug: string, credits_per_cycle: int}>}
     */
    public function validatePackageDefinition(array $packageAttributes, array $tools): array
    {
        $validatedPackage = Validator::make($packageAttributes, [
            'slug' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ])->validate();

        if ($tools === []) {
            throw ValidationException::withMessages([
                'tools' => ['A package must include at least one AI tool.'],
            ]);
        }

        $seen = [];
        foreach ($tools as $i => $row) {
            $tv = Validator::make($row, [
                'tool_slug' => ['required', 'string', 'max:191', new ToolSlugExistsInConfig],
                'credits_per_cycle' => ['required', 'integer', 'min:1', 'max:1000000'],
            ]);
            $tv->validate();
            if (isset($seen[$row['tool_slug']])) {
                throw ValidationException::withMessages([
                    "tools.{$i}.tool_slug" => ['Duplicate tool in package.'],
                ]);
            }
            $seen[$row['tool_slug']] = true;
        }

        return ['package' => $validatedPackage, 'tools' => $tools];
    }

    /**
     * Ensure DB package tools still match config (call after tools.php changes).
     */
    public function assertPackageToolsAreKnown(SubscriptionPackage $package): void
    {
        $unknown = $package->tools->pluck('tool_slug')->filter(function ($slug) {
            return ! collect(config('tools.all_tools', []))->contains('slug', $slug);
        });

        if ($unknown->isNotEmpty()) {
            throw ValidationException::withMessages([
                'package_id' => ['Package references unknown tools: ' . $unknown->implode(', ')],
            ]);
        }
    }
}
