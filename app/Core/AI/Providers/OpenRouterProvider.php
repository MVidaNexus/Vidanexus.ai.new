<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements AIProvider
{
    /**
     * Retired / renamed OpenRouter model aliases mapped to a still-supported
     * equivalent. OpenRouter occasionally removes specific pinned versions
     * (e.g. `google/gemini-2.0-flash-001`) which then 404 with
     * "No endpoints found for ...". Keeping admin-configured tools pointed
     * at dead aliases would silently break them; instead we rewrite to the
     * current canonical model and log a deprecation warning so ops can
     * update the stored configuration.
     */
    protected const DEAD_MODEL_ALIASES = [
        'google/gemini-2.0-flash-001'      => 'google/gemini-2.5-flash',
        'google/gemini-2.0-flash'          => 'google/gemini-2.5-flash',
        'google/gemini-2.0-flash-exp'      => 'google/gemini-2.5-flash',
        'google/gemini-2.0-flash-lite-001' => 'google/gemini-2.5-flash-lite',
        'google/gemini-2.0-flash-lite'     => 'google/gemini-2.5-flash-lite',
        'google/gemini-1.5-flash-latest'   => 'google/gemini-flash-latest',
        'google/gemini-1.5-pro-latest'     => 'google/gemini-2.5-pro',
    ];

    protected const DEFAULT_MODEL = 'google/gemini-2.5-flash';

    public function __construct(protected string $apiKey) {}

    public function getName(): string
    {
        return 'openrouter';
    }

    public function isConfigured(?string $apiKeyOverride = null): bool
    {
        $key = $apiKeyOverride ?: $this->apiKey;
        return is_string($key) && trim($key) !== '' && strlen(trim($key)) >= 10;
    }

    public function generate(string $prompt, array $options = []): array
    {
        $apiKey = !empty($options['api_key']) ? $options['api_key'] : $this->apiKey;

        if (!$this->isConfigured($apiKey)) {
            throw new AIProviderConfigurationException('OpenRouter provider is not configured.');
        }

        $requestedModel = $options['model'] ?? self::DEFAULT_MODEL;
        $model = $this->resolveModel((string) $requestedModel);
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $payload = array_filter([
            'model' => $model,
            'messages' => $this->buildMessages($prompt, $options),
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'response_format' => ($options['json_mode'] ?? false) ? ['type' => 'json_object'] : null,
        ], fn ($v) => $v !== null);

        try {
            $response = Http::timeout((int) ($options['timeout'] ?? 45))
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'HTTP-Referer' => config('app.url', 'https://vidanexus.ai'),
                    'X-Title' => $options['tool_name'] ?? config('app.name', 'VidaNexus'),
                ])
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('ai.openrouter.network_error', ['exception' => $e->getMessage()]);
            throw new AIProviderFailureException('OpenRouter network error: '.$e->getMessage(), [], $e);
        }

        if ($response->failed()) {
            $msg = $response->json('error.message') ?: $response->reason();
            Log::warning('ai.openrouter.api_error', ['status' => $response->status(), 'body' => $response->body(), 'model' => $model]);

            if (str_contains((string) $msg, 'No endpoints found')) {
                throw new AIProviderFailureException(
                    "OpenRouter has no available endpoints for model '{$model}'. Pick a different model in settings."
                );
            }

            throw new AIProviderFailureException("OpenRouter API failed ({$response->status()}): {$msg}");
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

    protected function buildMessages(string $prompt, array $options): array
    {
        $messages = [];
        if (!empty($options['system_prompt'])) {
            $messages[] = ['role' => 'system', 'content' => $options['system_prompt']];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];
        return $messages;
    }

    /**
     * Rewrite any retired model aliases to a currently supported one. The
     * rewrite is logged at warning level so ops can update the underlying
     * Setting and stop relying on the alias layer.
     */
    public function resolveModel(string $model): string
    {
        $trimmed = trim($model);
        if ($trimmed === '') {
            return self::DEFAULT_MODEL;
        }

        $key = strtolower($trimmed);
        if (isset(self::DEAD_MODEL_ALIASES[$key])) {
            $rewritten = self::DEAD_MODEL_ALIASES[$key];
            Log::warning('ai.openrouter.model_alias_rewritten', [
                'requested' => $trimmed,
                'rewritten_to' => $rewritten,
                'reason' => 'OpenRouter retired the requested model alias — update the admin setting.',
            ]);
            return $rewritten;
        }

        return $trimmed;
    }
}
