<?php

namespace App\Core\AI;

use App\Core\AI\Contracts\AIProvider;
use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

/**
 * AIManager — central dispatcher with provider fallback chain.
 *
 * Behavior:
 *  - The effective chain is built in this order:
 *      1. Tool-specific chain stored in Setting('{tool}_ai_chain')
 *         (admin preference; each entry can pin its own model and api_key).
 *      2. Global default chain config('vidanexus.ai.failover_order'),
 *         appended after the per-tool preferences. We de-duplicate against
 *         the per-tool chain BY PROVIDER, but only when that earlier entry
 *         already used a system-level credential (no per-entry api_key). If
 *         the per-tool chain pinned a custom api_key, the "same provider,
 *         system key" attempt from the global tail is meaningfully different
 *         — and usually the one that succeeds when the admin's stored key is
 *         stale — so we keep it.
 *      3. Hardcoded safe default (openai → google → openrouter) if both
 *         (1) and (2) are empty.
 *    Admins can opt out of step (2) per tool by setting
 *    Setting('{tool}_ai_chain_strict') = true (or passing
 *    options['strict_chain'] = true at the call site), in which case ONLY
 *    the per-tool chain is tried.
 *  - For each candidate, we check `isConfigured()` BEFORE calling the API.
 *    Unconfigured providers are skipped without consuming the request slot.
 *  - On success, the response gets standardized fields:
 *      provider_used, model_used, fallback_applied, attempts.
 *  - On total failure, we throw {@see AIProviderFailureException} with the
 *    full attempt list so callers can return structured error envelopes
 *    (instead of HTTP 500s).
 */
class AIManager
{
    /** @var array<string, AIProvider> */
    protected array $providers = [];

    public function registerProvider(AIProvider $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /** @return array<string, AIProvider> */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Run a generation request through the fallback chain.
     *
     * @param string $tool      Tool slug (e.g. "article-writer") — drives per-tool overrides.
     * @param string $prompt    User-facing prompt (already sanitized by the security layer).
     * @param array  $options   model/temperature/max_tokens/system_prompt/api_key/provider/timeout
     *
     * @return array{
     *     text:string,
     *     input_tokens:int,
     *     output_tokens:int,
     *     raw_response:mixed,
     *     provider_used:string,
     *     model_used:string,
     *     fallback_applied:bool,
     *     attempts:array<int, array{provider:string, model:?string, error:string}>
     * }
     */
    public function generate(string $tool, string $prompt, array $options = []): array
    {
        $chain = $this->resolveChain($tool, $options);
        $attempts = [];
        $primaryProvider = $chain[0]['provider'] ?? 'openai';

        foreach ($chain as $index => $link) {
            $providerName = $link['provider'];
            $provider = $this->providers[$providerName] ?? null;

            if (! $provider) {
                $attempts[] = [
                    'provider' => $providerName,
                    'model' => $link['model'] ?? null,
                    'error' => 'Provider is not registered.',
                ];
                continue;
            }

            $currentOptions = $options;
            $currentOptions['model'] = $link['model'] ?? ($options['model'] ?? null);
            $currentOptions['model'] = $this->normalizeModelForProvider($providerName, $currentOptions['model']);

            $apiKey = $link['api_key']
                ?? ($options['api_key'] ?? null)
                ?? $this->resolveApiKeyFromSettings($providerName);

            if ($apiKey !== null) {
                $currentOptions['api_key'] = $apiKey;
            }

            // Skip providers that can't possibly succeed — saves a network roundtrip.
            if (! $provider->isConfigured($apiKey)) {
                $attempts[] = [
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? null,
                    'error' => "{$providerName} provider is not configured.",
                ];

                Log::info('ai.provider_skipped', [
                    'tool' => $tool,
                    'provider' => $providerName,
                    'reason' => 'unconfigured',
                ]);
                continue;
            }

            $startedAt = microtime(true);

            try {
                Log::info('ai.attempt', [
                    'tool' => $tool,
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? 'default',
                    'attempt' => $index + 1,
                ]);

                $response = $provider->generate($prompt, $currentOptions);

                $latency = (int) ((microtime(true) - $startedAt) * 1000);

                Log::info('ai.success', [
                    'tool' => $tool,
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? 'default',
                    'latency_ms' => $latency,
                    'fallback_applied' => $index > 0,
                    'input_tokens' => $response['input_tokens'] ?? 0,
                    'output_tokens' => $response['output_tokens'] ?? 0,
                ]);

                return array_merge($response, [
                    'provider_used' => $providerName,
                    'model_used' => $currentOptions['model'] ?? 'default',
                    'fallback_applied' => $index > 0,
                    'attempts' => $attempts,
                ]);
            } catch (AIProviderConfigurationException $e) {
                Log::warning('ai.config_error', [
                    'tool' => $tool,
                    'provider' => $providerName,
                    'error' => $e->getMessage(),
                ]);

                $attempts[] = [
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? null,
                    'error' => $e->getMessage(),
                ];
                continue;
            } catch (\Throwable $e) {
                Log::warning('ai.failover', [
                    'tool' => $tool,
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? null,
                    'error' => $e->getMessage(),
                ]);

                $attempts[] = [
                    'provider' => $providerName,
                    'model' => $currentOptions['model'] ?? null,
                    'error' => $e->getMessage(),
                ];
                continue;
            }
        }

        Log::error('ai.all_failed', [
            'tool' => $tool,
            'attempts' => $attempts,
        ]);

        throw new AIProviderFailureException(
            'AI provider is currently unavailable. Please try again later.',
            $attempts
        );
    }

    /**
     * Convenient wrapper that returns just the text response. Older
     * controllers expect this signature.
     */
    public function generateResponse(string $prompt, string $tool, array $options = []): string
    {
        $response = $this->generate($tool, $prompt, $options);
        return $response['text'] ?? '';
    }

    /**
     * Run a quick health check on every registered provider. Returns a
     * structured map suitable for an admin dashboard.
     *
     * Health is "configured" only — we do NOT make a real API call here so
     * this remains free to run on every request.
     *
     * @return array<string, array{configured:bool, has_api_key:bool}>
     */
    public function healthCheck(): array
    {
        $report = [];
        foreach ($this->providers as $name => $provider) {
            $key = $this->resolveApiKeyFromSettings($name);
            $report[$name] = [
                'configured' => $provider->isConfigured($key),
                'has_api_key' => !empty($key),
            ];
        }
        return $report;
    }

    /**
     * Build the ordered list of {provider, model, api_key?} candidates for a tool.
     *
     * The list is the concatenation of:
     *   1. The per-tool chain stored in Setting('{tool}_ai_chain') (admin preference).
     *   2. The global failover chain config('vidanexus.ai.failover_order'),
     *      minus any provider already tried in step (1).
     *
     * Step (2) is skipped when strict mode is enabled — either via
     * Setting('{tool}_ai_chain_strict') = true or options['strict_chain'] = true.
     * Strict mode is opt-in; the default is to ALWAYS fall back to the global
     * chain so a single misconfigured per-tool preference does not take the
     * tool offline.
     *
     * @return array<int, array{provider:string, model:?string, api_key:?string}>
     */
    protected function resolveChain(string $tool, array $options): array
    {
        $chain = [];

        // 1. Per-tool override (admin preference).
        $rawChain = Setting::get("{$tool}_ai_chain");
        $toolChain = is_array($rawChain)
            ? $rawChain
            : (is_string($rawChain) && $rawChain !== '' ? json_decode($rawChain, true) : null);

        if (is_array($toolChain) && count($toolChain) > 0) {
            $chain = array_values(array_map(fn ($link) => [
                'provider' => (string) ($link['provider'] ?? ''),
                'model' => $link['model'] ?? null,
                'api_key' => $link['api_key'] ?? null,
            ], array_filter($toolChain, fn ($l) => !empty($l['provider']))));
        }

        // 2. Global failover — appended unless the caller / admin opted out.
        //    `Setting::get` returns the raw stored value (string|bool|null),
        //    so we normalise it through filter_var to accept "1"/"true"/"yes".
        $strict = !empty($options['strict_chain'])
            || filter_var(Setting::get("{$tool}_ai_chain_strict", false), FILTER_VALIDATE_BOOLEAN);

        $defaultChain = (array) config(
            'vidanexus.ai.failover_order',
            ['openai', 'google', 'openrouter']
        );

        // Pull the explicit-requested provider to the front of the global tail.
        if (!empty($options['provider'])) {
            $defaultChain = array_values(array_unique(array_merge([$options['provider']], $defaultChain)));
        }

        if (! $strict) {
            foreach ($defaultChain as $name) {
                $name = (string) $name;
                if ($name === '') {
                    continue;
                }

                // De-dup against the per-tool chain by provider name — but
                // only when the per-tool entry already used a SYSTEM-level
                // credential (no per-entry api_key). If the per-tool chain
                // pinned its own api_key for this provider, the "same
                // provider, system key" attempt is meaningfully different
                // (and usually the one that actually works when the admin's
                // stored key is stale), so we still want to queue it.
                $alreadyTriedWithSystemKey = false;
                foreach ($chain as $existing) {
                    if (($existing['provider'] ?? '') === $name && empty($existing['api_key'])) {
                        $alreadyTriedWithSystemKey = true;
                        break;
                    }
                }
                if ($alreadyTriedWithSystemKey) {
                    continue;
                }

                $chain[] = [
                    'provider' => $name,
                    'model' => $options['model'] ?? null,
                    'api_key' => null,
                ];
            }
        }

        // 3. Defensive fallback — if the per-tool chain is empty AND strict
        //    mode was on (a misconfiguration), still use the global chain so
        //    generation never silently no-ops on an empty list.
        if (empty($chain)) {
            $chain = array_values(array_map(fn ($name) => [
                'provider' => (string) $name,
                'model' => $options['model'] ?? null,
                'api_key' => null,
            ], $defaultChain));
        }

        return $chain;
    }

    protected function normalizeModelForProvider(string $provider, ?string $model): string
    {
        // Google has its own resolver — defer to it so we share one source of truth.
        if ($provider === 'google' && isset($this->providers['google']) && method_exists($this->providers['google'], 'resolveModel')) {
            return $this->providers['google']->resolveModel($model);
        }

        if (!$model) {
            return $this->getDefaultModelForProvider($provider);
        }

        // Handle OpenRouter-style names (e.g. google/gemini-2.0-flash-001).
        if (str_contains($model, '/')) {
            if ($provider === 'openrouter') {
                return $model;
            }

            [$prefix, $name] = explode('/', $model, 2);

            if ($provider === 'openai' && ($prefix === 'openai' || str_contains($name, 'gpt'))) {
                return $name;
            }

            if ($provider === 'anthropic' && ($prefix === 'anthropic' || str_contains($name, 'claude'))) {
                return $name;
            }
        }

        // Cross-provider model leakage — normalize to the provider's default.
        $modelLower = strtolower($model);
        if ($provider === 'openai' && (str_contains($modelLower, 'gemini') || str_contains($modelLower, 'claude'))) {
            return $this->getDefaultModelForProvider($provider);
        }
        if ($provider === 'anthropic' && (str_contains($modelLower, 'gpt') || str_contains($modelLower, 'gemini'))) {
            return $this->getDefaultModelForProvider($provider);
        }

        return $model;
    }

    protected function resolveApiKeyFromSettings(string $providerName): ?string
    {
        $settingKey = match ($providerName) {
            'openrouter' => 'openrouter_api_key',
            'google' => 'gemini_api_key',
            'openai' => 'openai_api_key',
            'anthropic' => 'anthropic_api_key',
            default => null,
        };

        if (! $settingKey) {
            return null;
        }

        $key = trim((string) (Setting::get($settingKey) ?? ''));

        if ($key === '' && $providerName === 'google') {
            $key = trim((string) (Setting::get('google_api_key') ?? ''));
        }

        // Fallback to config or env when the DB value is empty or a stub placeholder.
        if ($key === '' || strlen($key) < 5) {
            $configKey = match ($providerName) {
                'google', 'gemini' => 'services.gemini.api_key',
                'openai' => 'services.openai.api_key',
                'openrouter' => 'services.openrouter.api_key',
                'anthropic' => 'services.anthropic.api_key',
                default => "services.{$providerName}.api_key",
            };
            $key = trim((string) (config($configKey) ?: (env(strtoupper($settingKey)) ?: '')));

            if ($key === '' && $providerName === 'openrouter') {
                $key = trim((string) (config('services.openrouter.api_key') ?: (env('OPEN_ROUTER_API_KEY') ?: '')));
            }
        }

        return $key !== '' ? $key : null;
    }

    protected function getDefaultModelForProvider(string $provider): string
    {
        return match ($provider) {
            'google' => 'gemini-1.5-flash',
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-3-haiku-20240307',
            // OpenRouter retired the `google/gemini-2.0-flash-001` alias.
            // OpenRouterProvider::resolveModel() also rewrites legacy values,
            // but bump the default here so fresh tools land on a live model.
            'openrouter' => 'google/gemini-2.5-flash',
            default => 'default',
        };
    }
}
