<?php

namespace App\Core\AI\Contracts;

interface AIProvider
{
    /**
     * Generate a response from the AI provider.
     *
     * @param string $prompt
     * @param array $options
     * @return array {text: string, input_tokens: int, output_tokens: int, raw_response: mixed}
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
