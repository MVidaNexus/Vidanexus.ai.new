<?php

namespace App\Support;

/**
 * Slugs for GenericIntelligenceController routes, derived from config/tools.php (all_tools[].route).
 * Add or change tools in config only; routes stay in sync.
 */
class GenericToolRoutes
{
    public static function marketingSlugs(): array
    {
        return self::slugsForRoutePrefix('dashboard.marketing.');
    }

    public static function seoSlugs(): array
    {
        return self::slugsForRoutePrefix('dashboard.seo.');
    }

    /**
     * @return list<string>
     */
    protected static function slugsForRoutePrefix(string $prefix): array
    {
        $slugs = [];
        foreach (config('tools.all_tools', []) as $tool) {
            $route = $tool['route'] ?? '';
            if (! is_string($route) || ! str_starts_with($route, $prefix)) {
                continue;
            }
            $slug = substr($route, strlen($prefix));
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }

        return $slugs;
    }
}
