<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use App\Core\AI\Exceptions\AIProviderConfigurationException;
use App\Core\AI\Exceptions\AIProviderFailureException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleProvider implements AIProvider
{
    /**
     * Google's recommended generative model versions. Model resolution is
     * normalized via {@see resolveModel()} so callers can pass either:
     *   - a Google-native name like `gemini-1.5-flash` or `gemini-2.0-flash`
     *   - an OpenRouter-style name like `google/gemini-2.0-flash-001`
     *   - a partially-versioned alias like `gemini-2.0-flash-001`
     *
     * In every case we map back to the canonical Google name supported by
     * generativelanguage.googleapis.com.
     */
    protected const MODEL_ALIASES = [
        'google/gemini-2.0-flash-001' => 'gemini-2.0-flash',
        'google/gemini-2.0-flash' => 'gemini-2.0-flash',
        'gemini-2.0-flash-001' => 'gemini-2.0-flash',
        'gemini-2.0-flash-lite-001' => 'gemini-2.0-flash-lite',
        'google/gemini-1.5-flash-latest' => 'gemini-1.5-flash',
        'google/gemini-1.5-pro-latest' => 'gemini-1.5-pro',
    ];

    protected const DEFAULT_MODEL = 'gemini-1.5-flash';

    public function __construct(protected string $apiKey) {}

    public function getName(): string
    {
        return 'google';
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
            throw new AIProviderConfigurationException('Google Gemini provider is not configured.');
        }

        $model = $this->resolveModel($options['model'] ?? null);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => $this->buildContents($prompt, $options),
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 4096,
                'response_mime_type' => ($options['json_mode'] ?? false) ? 'application/json' : 'text/plain',
            ],
        ];

        if (!empty($options['system_prompt'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $options['system_prompt']]],
            ];
        }

        try {
            $response = Http::timeout((int) ($options['timeout'] ?? 45))
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('ai.google.network_error', ['exception' => $e->getMessage(), 'model' => $model]);
            throw new AIProviderFailureException('Gemini network error: '.$e->getMessage(), [], $e);
        }

        if ($response->failed()) {
            $msg = $response->json('error.message') ?: $response->reason();
            $code = $response->json('error.code') ?: $response->status();

            // 404 here usually means "model not found / not available in this region"
            // — historically surfaced as the cryptic "No endpoints found for ..." error.
            if ($response->status() === 404 || str_contains((string) $msg, 'not found') || str_contains((string) $msg, 'No endpoints')) {
                Log::warning('ai.google.model_not_found', ['model' => $model, 'message' => $msg]);
                throw new AIProviderFailureException(
                    "Gemini model '{$model}' is not available. Please pick a different model in settings."
                );
            }

            Log::warning('ai.google.api_error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new AIProviderFailureException("Gemini API failed ({$code}): {$msg}");
        }

        $candidates = $response->json('candidates');
        if (empty($candidates)) {
            $reason = $response->json('promptFeedback.blockReason') ?: 'unknown';
            Log::warning('ai.google.empty_candidates', ['reason' => $reason, 'body' => $response->body()]);
            throw new AIProviderFailureException(
                "Gemini returned no results (reason: {$reason}). The prompt may have been blocked by safety filters."
            );
        }

        $text = $candidates[0]['content']['parts'][0]['text'] ?? null;
        if ($text === null) {
            Log::warning('ai.google.empty_text', ['body' => $response->body()]);
            throw new AIProviderFailureException('Gemini returned an empty response.');
        }

        $usage = $response->json('usageMetadata', []);

        return [
            'text' => $text,
            'input_tokens' => (int) ($usage['promptTokenCount'] ?? 0),
            'output_tokens' => (int) ($usage['candidatesTokenCount'] ?? 0),
            'raw_response' => $response->body(),
        ];
    }

    /**
     * Map any incoming model name to a canonical Google model. Falls back
     * to {@see DEFAULT_MODEL} so a typo never bubbles up as a 404.
     */
    public function resolveModel(?string $model): string
    {
        if ($model === null || trim($model) === '') {
            return self::DEFAULT_MODEL;
        }

        $key = strtolower(trim($model));

        if (array_key_exists($key, self::MODEL_ALIASES)) {
            return self::MODEL_ALIASES[$key];
        }

        // Strip any "google/" prefix we don't recognise.
        if (str_contains($key, '/')) {
            [$prefix, $name] = explode('/', $key, 2);
            if ($prefix === 'google') {
                $key = $name;
            }
        }

        // Strip trailing version like `-001`.
        $key = preg_replace('/-\d{3,}$/', '', $key) ?? $key;

        if (str_contains($key, 'gemini')) {
            return $key;
        }

        return self::DEFAULT_MODEL;
    }

    protected function buildContents(string $prompt, array $options): array
    {
        return [
            [
                'parts' => [['text' => $prompt]],
            ],
        ];
    }
}
