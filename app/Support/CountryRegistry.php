<?php

namespace App\Support;

/**
 * Single source of truth for ISO country codes used across AI tools (news, trends, headlines).
 */
class CountryRegistry
{
    /**
     * @return array<string, array{name: string, flag?: string, lang: string, timezone?: string}>
     */
    public static function baseMap(): array
    {
        return config('keywords.countries', []);
    }

    public static function normalizeCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return strlen($code) === 2 ? $code : '';
    }

    public static function isKnown(string $code): bool
    {
        $code = self::normalizeCode($code);

        return $code !== '' && isset(self::baseMap()[$code]);
    }

    public static function defaultRegion(?string $lang = null): string
    {
        if ($lang === 'en') {
            return self::normalizeCode(config('keywords.default_region_en', 'US')) ?: 'US';
        }

        return self::normalizeCode(config('keywords.default_region_ar', 'EG')) ?: 'EG';
    }

    public static function langFor(string $code): string
    {
        $code = self::normalizeCode($code);
        $base = self::baseMap();

        if ($code !== '' && isset($base[$code]['lang'])) {
            return (string) $base[$code]['lang'];
        }

        $googleNewsLocales = [
            'US' => 'en', 'GB' => 'en', 'CA' => 'en', 'AU' => 'en', 'IN' => 'en',
            'FR' => 'fr', 'DE' => 'de', 'ES' => 'es', 'IT' => 'it', 'PL' => 'pl',
            'BR' => 'pt-BR', 'JP' => 'ja', 'TR' => 'tr', 'MX' => 'es', 'AR' => 'es',
        ];

        if (isset($googleNewsLocales[$code])) {
            return $googleNewsLocales[$code];
        }

        return 'ar';
    }

    public static function googleNewsCeid(string $code): string
    {
        $code = self::normalizeCode($code);

        return $code !== '' ? "{$code}:".self::langFor($code) : 'EG:ar';
    }

    /**
     * Parse admin "CODE:Name flag" lines into a country map merged with config metadata.
     *
     * @return array<string, array{name: string, flag: string, lang: string, code: string}>
     */
    public static function parseAdminLines(string $text): array
    {
        $countryMap = [];

        foreach (explode("\n", trim($text)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) < 2) {
                continue;
            }

            $code = self::normalizeCode($parts[0]);
            if ($code === '') {
                continue;
            }

            $nameStr = trim($parts[1]);
            $flag = '';

            if (preg_match('/(.*?)\s*([\x{1F1E6}-\x{1F1FF}]{2}|[\p{So}\p{Sk}]+)$/u', $nameStr, $matches)) {
                $nameStr = trim($matches[1]);
                $flag = trim($matches[2]);
            }

            $baseConfig = self::baseMap()[$code] ?? [];

            $countryMap[$code] = [
                'name' => $nameStr ?: ($baseConfig['name'] ?? $code),
                'flag' => $flag ?: ($baseConfig['flag'] ?? '🌐'),
                'lang' => $baseConfig['lang'] ?? self::langFor($code),
                'code' => $code,
            ];
        }

        return $countryMap;
    }

    /**
     * Build a filtered country map from admin text + optional active-code whitelist.
     *
     * @param  list<string>|null  $activeCountryCodes
     * @return array<string, array{name: string, flag: string, lang: string, code?: string}>
     */
    public static function buildMap(
        ?string $availableCountriesText = null,
        ?array $activeCountryCodes = null,
        ?array $fallbackMap = null
    ): array {
        $fallbackMap = $fallbackMap ?? self::baseMap();

        if ($availableCountriesText !== null && trim($availableCountriesText) !== '') {
            $countryMap = self::parseAdminLines($availableCountriesText);
        } else {
            $countryMap = [];
            foreach ($fallbackMap as $code => $data) {
                $code = self::normalizeCode($code);
                if ($code === '') {
                    continue;
                }
                $countryMap[$code] = array_merge($data, ['code' => $code]);
            }
        }

        if (is_array($activeCountryCodes) && count($activeCountryCodes) > 0) {
            $filtered = [];
            foreach ($activeCountryCodes as $c) {
                $c = self::normalizeCode((string) $c);
                if ($c !== '' && isset($countryMap[$c])) {
                    $filtered[$c] = $countryMap[$c];
                }
            }
            if (count($filtered) > 0) {
                $countryMap = $filtered;
            }
        }

        return $countryMap;
    }

    /**
     * Resolve a request region against an allowed map; falls back to first map entry or EG/US.
     *
     * @return array{region: string, country: array{name: string, flag?: string, lang: string, code: string}}
     */
    public static function resolveRegion(?string $requested, array $countryMap, ?string $defaultCode = null): array
    {
        if (empty($countryMap)) {
            $defaultCode = self::normalizeCode($defaultCode) ?: self::defaultRegion();
            $base = self::baseMap()[$defaultCode] ?? ['name' => $defaultCode, 'flag' => '🌐', 'lang' => self::langFor($defaultCode)];

            return [
                'region' => $defaultCode,
                'country' => array_merge($base, ['code' => $defaultCode]),
            ];
        }

        $defaultCode = self::normalizeCode($defaultCode);
        if ($defaultCode === '' || ! isset($countryMap[$defaultCode])) {
            $defaultCode = isset($countryMap['EG']) ? 'EG' : array_key_first($countryMap);
        }

        $region = self::normalizeCode($requested);
        if ($region === '' || ! isset($countryMap[$region])) {
            $region = $defaultCode;
        }

        $country = $countryMap[$region];
        $country['code'] = $region;

        if (! isset($country['lang'])) {
            $country['lang'] = self::langFor($region);
        }

        return ['region' => $region, 'country' => $country];
    }

    /**
     * Validate and normalize a country code for API input; returns null when invalid.
     */
    public static function validateRequestCode(?string $code, ?array $allowedMap = null): ?string
    {
        $code = self::normalizeCode($code);
        if ($code === '') {
            return null;
        }

        if ($allowedMap !== null && ! isset($allowedMap[$code])) {
            return null;
        }

        if ($allowedMap === null && ! self::isKnown($code)) {
            return null;
        }

        return $code;
    }

    /**
     * Default admin multiline text from config (CODE:Name flag).
     */
    public static function defaultAdminLines(): string
    {
        $lines = [];
        foreach (self::baseMap() as $code => $data) {
            $lines[] = $code.':'.($data['name'] ?? $code).' '.($data['flag'] ?? '🌐');
        }

        return implode("\n", $lines);
    }

    // ─────────────────────────────────────────────────────────────────
    //  GLOBAL COUNTRY REGISTRY
    //
    //  Centralized, admin-managed source of truth for which countries
    //  are visible across every tool that uses CountryRegistry. A tool
    //  can still ship its own per-tool override list, but the global
    //  registry is what the dashboard / admin "Countries" tab controls.
    //
    //  Settings:
    //   - global_country_registry  (textarea, "CODE:Name flag" lines)
    //   - global_country_visibility (json array of enabled CODEs)
    // ─────────────────────────────────────────────────────────────────

    /** @var array<string, array<string, mixed>>|null */
    protected static ?array $globalMapCache = null;

    /**
     * Master country list (admin textarea or config fallback).
     * Returns the raw multiline text — useful for pre-filling textareas.
     */
    public static function globalLines(): string
    {
        $admin = (string) \App\Models\Setting::get('global_country_registry', '');
        $admin = trim($admin);

        return $admin !== '' ? $admin : self::defaultAdminLines();
    }

    /**
     * Codes that are globally visible. `null` means "no global filter
     * configured yet → show everything from the master list." An empty
     * saved array is also treated as "no filter" (safety net so an admin
     * who accidentally unticks everything doesn't wipe every dropdown).
     *
     * @return list<string>|null
     */
    public static function globalActiveCodes(): ?array
    {
        $raw = \App\Models\Setting::get('global_country_visibility', null);

        if ($raw === null || $raw === '' || $raw === '[]') {
            return null;
        }

        $codes = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($codes) || count($codes) === 0) {
            return null;
        }

        $clean = [];
        foreach ($codes as $c) {
            $code = self::normalizeCode((string) $c);
            if ($code !== '') {
                $clean[] = $code;
            }
        }

        return count($clean) > 0 ? $clean : null;
    }

    /**
     * Fully-resolved global country map: the master list filtered by
     * the global visibility array. This is what tools should use as
     * their "available countries" default.
     *
     * @return array<string, array{name: string, flag: string, lang: string, code: string}>
     */
    public static function globalMap(): array
    {
        if (self::$globalMapCache !== null) {
            return self::$globalMapCache;
        }

        return self::$globalMapCache = self::buildMap(self::globalLines(), self::globalActiveCodes());
    }

    /**
     * Build a tool-effective map: the tool may have its own admin textarea
     * (`{slug}_available_countries`) and/or its own active subset, but the
     * result is always intersected with the global visibility set so a
     * country hidden globally never leaks into a tool feed.
     *
     * @param  list<string>|null  $toolActiveCodes
     * @return array<string, array{name: string, flag: string, lang: string, code?: string}>
     */
    public static function effectiveMap(?string $toolAvailableText = null, ?array $toolActiveCodes = null): array
    {
        $globalActive = self::globalActiveCodes();

        $availableText = $toolAvailableText !== null && trim($toolAvailableText) !== ''
            ? $toolAvailableText
            : self::globalLines();

        $effectiveActive = $toolActiveCodes;

        // Intersect tool-active with global visibility so tools cannot
        // surface a country the admin disabled in the dashboard registry.
        if ($globalActive !== null) {
            if (is_array($effectiveActive) && count($effectiveActive) > 0) {
                $effectiveActive = array_values(array_intersect($effectiveActive, $globalActive));
            } else {
                $effectiveActive = $globalActive;
            }
        }

        return self::buildMap($availableText, $effectiveActive);
    }

    /**
     * Wipe the in-process global map cache. Call after writing
     * `global_country_registry` or `global_country_visibility`.
     */
    public static function clearGlobalCache(): void
    {
        self::$globalMapCache = null;
    }
}
