<?php

namespace Tests\Unit\AI;

use App\Core\AI\Providers\OpenRouterProvider;
use Tests\TestCase;

class OpenRouterProviderTest extends TestCase
{
    /**
     * OpenRouter periodically retires pinned model aliases (most recently
     * `google/gemini-2.0-flash-001`). When that happens admin-saved tool
     * configurations would start failing with HTTP 404 "No endpoints found
     * for X" — silently breaking every tool still pointed at the dead name.
     * The provider rewrites known-dead aliases to a current equivalent so
     * existing tools keep working until the operator updates the setting.
     */
    public function test_it_rewrites_retired_aliases(): void
    {
        $provider = new OpenRouterProvider('test-key-12345');

        $this->assertSame('google/gemini-2.5-flash', $provider->resolveModel('google/gemini-2.0-flash-001'));
        $this->assertSame('google/gemini-2.5-flash', $provider->resolveModel('GOOGLE/GEMINI-2.0-FLASH'));
        $this->assertSame('google/gemini-2.5-flash-lite', $provider->resolveModel('google/gemini-2.0-flash-lite-001'));
        $this->assertSame('google/gemini-flash-latest', $provider->resolveModel('google/gemini-1.5-flash-latest'));
    }

    public function test_it_passes_through_live_model_names_unchanged(): void
    {
        $provider = new OpenRouterProvider('test-key-12345');

        $this->assertSame('google/gemini-2.5-flash', $provider->resolveModel('google/gemini-2.5-flash'));
        $this->assertSame('openai/gpt-4o-mini', $provider->resolveModel('openai/gpt-4o-mini'));
        $this->assertSame('anthropic/claude-3.5-sonnet', $provider->resolveModel('anthropic/claude-3.5-sonnet'));
    }

    public function test_it_returns_a_safe_default_for_empty_input(): void
    {
        $provider = new OpenRouterProvider('test-key-12345');

        $this->assertSame('google/gemini-2.5-flash', $provider->resolveModel(''));
        $this->assertSame('google/gemini-2.5-flash', $provider->resolveModel('   '));
    }
}
