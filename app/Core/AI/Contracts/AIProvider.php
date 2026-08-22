<?php

namespace App\Core\AI\Contracts;

interface AIProvider
{
    /**
     * Generate a response from the AI provider.
     *
     * @param string $prompt
     * @param array $options
     * @return array{text: string, input_tokens: int, output_tokens: int, raw_response: mixed}
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Whether this provider has the credentials it needs to attempt a call.
     *
     * Implementations should be cheap (no network call) — used by
     * App\Core\AI\AIManager to skip dead providers in the fallback chain
     * before spending the user's request budget on them.
     *
     * @param string|null $apiKeyOverride per-call key override (optional)
     */
    public function isConfigured(?string $apiKeyOverride = null): bool;
}
