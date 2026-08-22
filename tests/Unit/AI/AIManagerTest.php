<?php

namespace Tests\Unit\AI;

use App\Core\AI\AIManager;
use App\Core\AI\Contracts\AIProvider;
use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AIManagerTest extends TestCase
{
    public function test_it_returns_first_successful_provider_with_metadata(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: true));
        $manager->registerProvider($this->makeProvider('google', success: false));

        config(['vidanexus.ai.failover_order' => ['openai', 'google']]);

        $result = $manager->generate('test-tool', 'hello world', ['model' => 'gpt-4o-mini']);

        $this->assertSame('openai', $result['provider_used']);
        $this->assertFalse($result['fallback_applied']);
        $this->assertSame('hello world (from openai)', $result['text']);
    }

    public function test_it_falls_back_to_next_provider_on_failure(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: false));
        $manager->registerProvider($this->makeProvider('google', success: true));

        config(['vidanexus.ai.failover_order' => ['openai', 'google']]);

        $result = $manager->generate('test-tool', 'fallback prompt');

        $this->assertSame('google', $result['provider_used']);
        $this->assertTrue($result['fallback_applied']);
        $this->assertCount(1, $result['attempts']);
    }

    public function test_it_skips_unconfigured_providers_without_calling_them(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', configured: false));
        $manager->registerProvider($this->makeProvider('google', success: true));

        config(['vidanexus.ai.failover_order' => ['openai', 'google']]);

        $result = $manager->generate('test-tool', 'hello');

        $this->assertSame('google', $result['provider_used']);
        $this->assertNotEmpty($result['attempts']);
        $this->assertSame('openai', $result['attempts'][0]['provider']);
    }

    public function test_it_throws_provider_failure_when_chain_is_exhausted(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: false));
        $manager->registerProvider($this->makeProvider('google', success: false));

        config(['vidanexus.ai.failover_order' => ['openai', 'google']]);

        $this->expectException(AIProviderFailureException::class);
        $this->expectExceptionMessage('AI provider is currently unavailable');

        $manager->generate('test-tool', 'hello');
    }

    public function test_health_check_reports_per_provider_status(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', configured: true));
        $manager->registerProvider($this->makeProvider('google', configured: false));

        $report = $manager->healthCheck();

        $this->assertTrue($report['openai']['configured']);
        $this->assertFalse($report['google']['configured']);
    }

    /**
     * A per-tool chain stored in Setting('{tool}_ai_chain') should not isolate
     * a tool from the global failover. When the admin's preferred provider
     * fails, the manager must continue into the configured failover_order so a
     * misconfigured preference never takes the tool offline.
     */
    public function test_per_tool_chain_falls_back_to_global_failover_on_failure(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: false));
        $manager->registerProvider($this->makeProvider('google', success: true));
        $manager->registerProvider($this->makeProvider('openrouter', success: true));

        config(['vidanexus.ai.failover_order' => ['openai', 'google', 'openrouter']]);

        $this->primeToolChain('test-tool', [
            ['provider' => 'openai', 'model' => 'gpt-4o-mini'],
        ]);

        $result = $manager->generate('test-tool', 'hello');

        $this->assertSame('google', $result['provider_used']);
        $this->assertTrue($result['fallback_applied']);
        $this->assertCount(1, $result['attempts']);
        $this->assertSame('openai', $result['attempts'][0]['provider']);
    }

    /**
     * The global failover should be appended after the per-tool chain WITHOUT
     * duplicating any provider the admin already listed.
     */
    public function test_per_tool_chain_dedupes_against_global_failover(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: false));
        $manager->registerProvider($this->makeProvider('google', success: false));
        $manager->registerProvider($this->makeProvider('openrouter', success: true));

        config(['vidanexus.ai.failover_order' => ['openai', 'google', 'openrouter']]);

        $this->primeToolChain('test-tool', [
            ['provider' => 'google', 'model' => 'gemini-1.5-flash'],
            ['provider' => 'openai', 'model' => 'gpt-4o-mini'],
        ]);

        $result = $manager->generate('test-tool', 'hello');

        $this->assertSame('openrouter', $result['provider_used']);
        // google + openai from per-tool chain (each tried exactly once),
        // then openrouter from the global tail — no duplicate attempts.
        $this->assertCount(2, $result['attempts']);
        $this->assertSame(['google', 'openai'], array_column($result['attempts'], 'provider'));
    }

    /**
     * Regression: when the per-tool chain pins a custom api_key for a
     * provider (admin pasted a per-tool key in the Horizon UI) and that key
     * is stale, the global tail must still get a chance to retry the SAME
     * provider with the system-level credential. Otherwise a single dead
     * per-tool key takes the tool offline even though the global env key
     * is fine. (Discovered debugging article-writer's stuck OpenRouter key.)
     */
    public function test_global_failover_retries_provider_when_per_tool_key_was_custom(): void
    {
        $manager = new AIManager();

        // Build a stub that succeeds only when the api_key passed at call
        // time matches the system value. Mimics the real "old key is dead,
        // env key works" scenario.
        $manager->registerProvider(new class implements AIProvider {
            public function getName(): string { return 'openrouter'; }
            public function isConfigured(?string $apiKeyOverride = null): bool
            {
                return ! empty($apiKeyOverride);
            }
            public function generate(string $prompt, array $options = []): array
            {
                $key = (string) ($options['api_key'] ?? '');
                if ($key === 'system-key') {
                    return ['text' => 'ok via system', 'input_tokens' => 1, 'output_tokens' => 1, 'raw_response' => '{}'];
                }
                throw new AIProviderFailureException("rejected key: {$key}");
            }
        });

        config(['vidanexus.ai.failover_order' => ['openrouter']]);

        $this->primeToolChain('test-tool', [
            ['provider' => 'openrouter', 'model' => 'foo', 'api_key' => 'STALE-KEY-XYZ'],
        ]);
        // The system-key value the manager picks up from Setting() (not env).
        $this->primeSetting('openrouter_api_key', 'system-key', 'text');

        $result = $manager->generate('test-tool', 'hello');

        $this->assertSame('openrouter', $result['provider_used']);
        $this->assertTrue($result['fallback_applied']);
        // Two queue entries: the stale per-tool one (failed) + the
        // system-key retry (succeeded).
        $this->assertCount(1, $result['attempts'], 'global tail must retry openrouter with the env/system key');
    }

    /**
     * Strict mode lets admins opt out of the global tail for compliance reasons
     * (e.g. "this tool may ONLY use Gemini"). When every per-tool provider
     * fails in strict mode, the request fails — no silent fallback.
     */
    public function test_strict_mode_disables_global_failover(): void
    {
        $manager = new AIManager();
        $manager->registerProvider($this->makeProvider('openai', success: false));
        $manager->registerProvider($this->makeProvider('google', success: true));

        config(['vidanexus.ai.failover_order' => ['openai', 'google']]);

        $this->primeToolChain('test-tool', [
            ['provider' => 'openai', 'model' => 'gpt-4o-mini'],
        ]);
        $this->primeSetting('test-tool_ai_chain_strict', true, 'boolean');

        $this->expectException(AIProviderFailureException::class);
        $manager->generate('test-tool', 'hello');
    }

    /**
     * Cache-prime a per-tool AI chain so `Setting::get` returns it without
     * touching the database. The unit suite uses the array cache driver, so
     * a cache hit short-circuits the Eloquent query inside Setting::get().
     */
    private function primeToolChain(string $tool, array $chain): void
    {
        $this->primeSetting("{$tool}_ai_chain", json_encode($chain), 'json');
    }

    private function primeSetting(string $key, mixed $value, string $type): void
    {
        $setting = new Setting();
        $setting->setRawAttributes([
            'key' => $key,
            'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
            'type' => $type,
        ]);
        Cache::put("setting_{$key}", $setting, 3600);
    }

    /**
     * Build a stub AIProvider whose `generate()` either returns canned text
     * or throws AIProviderFailureException.
     */
    private function makeProvider(string $name, bool $success = true, bool $configured = true): AIProvider
    {
        return new class($name, $success, $configured) implements AIProvider {
            public function __construct(
                private string $name,
                private bool $success,
                private bool $configured,
            ) {}

            public function getName(): string { return $this->name; }

            public function isConfigured(?string $apiKeyOverride = null): bool
            {
                return $this->configured;
            }

            public function generate(string $prompt, array $options = []): array
            {
                if (!$this->configured) {
                    throw new AIProviderConfigurationException("{$this->name} not configured");
                }
                if (!$this->success) {
                    throw new AIProviderFailureException("{$this->name} stubbed failure");
                }
                return [
                    'text' => $prompt . " (from {$this->name})",
                    'input_tokens' => 1,
                    'output_tokens' => 2,
                    'raw_response' => '{}',
                ];
            }
        };
    }
}
