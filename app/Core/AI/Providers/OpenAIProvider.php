<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProvider
{
    public function __construct(protected string $apiKey) {}

    public function getName(): string
    {
        return 'openai';
    }

    /**
     * Health check — used by AIManager to decide whether to attempt this
     * provider. Avoids tying credit-spending requests to broken config.
     */
    public function isConfigured(?string $apiKeyOverride = null): bool
    {
        $key = $apiKeyOverride ?: $this->apiKey;
        return is_string($key) && trim($key) !== '' && strlen(trim($key)) >= 10;
    }

    public function generate(string $prompt, array $options = []): array
    {
        $apiKey = !empty($options['api_key']) ? $options['api_key'] : $this->apiKey;

        if (!$this->isConfigured($apiKey)) {
            throw new AIProviderConfigurationException('OpenAI provider is not configured.');
        }

        $url = 'https://api.openai.com/v1/chat/completions';
        $payload = array_filter([
            'model' => $options['model'] ?? 'gpt-4o-mini',
            'messages' => $this->buildMessages($prompt, $options),
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'response_format' => ($options['json_mode'] ?? false) ? ['type' => 'json_object'] : null,
        ], fn ($v) => $v !== null);

        try {
            $response = Http::timeout((int) ($options['timeout'] ?? 45))
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withToken($apiKey)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('ai.openai.network_error', ['exception' => $e->getMessage()]);
            throw new AIProviderFailureException('OpenAI network error: '.$e->getMessage(), [], $e);
        }

        if ($response->failed()) {
            $msg = $response->json('error.message') ?: $response->reason();
            Log::warning('ai.openai.api_error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new AIProviderFailureException("OpenAI API failed ({$response->status()}): {$msg}");
        }

        $text = $response->json('choices.0.message.content');
        $usage = $response->json('usage', ['prompt_tokens' => 0, 'completion_tokens' => 0]);

        return [
            'text' => $text ?? '',
            'input_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'raw_response' => $response->body(),
        ];
    }

    /**
     * Build the messages array. When a `system_prompt` is supplied via
     * options, it lands in a separate `system` role — the only place that
     * is allowed to dictate behavior. User input is always wrapped in the
     * `user` role and never mixed with the system prompt.
     */
    protected function buildMessages(string $prompt, array $options): array
    {
        $messages = [];
        if (!empty($options['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $options['system_prompt']];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];
        return $messages;
    }
}
