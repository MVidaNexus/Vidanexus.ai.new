<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements AIProvider
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getName(): string
    {
        return 'openrouter';
    }

    public function generate(string $prompt, array $options = []): array
    {
        $apiKey = !empty($options['api_key']) ? $options['api_key'] : $this->apiKey;
        $model = $options['model'] ?? 'google/gemini-2.0-flash-001';
        $url = "https://openrouter.ai/api/v1/chat/completions";

        $response = Http::timeout(45)->withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'HTTP-Referer' => 'https://vidanexus.ai',
            'X-Title' => 'VidaNexus Headlines',
        ])->post($url, [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'response_format' => ($options['json_mode'] ?? false) ? ['type' => 'json_object'] : null,
        ]);

        if ($response->failed()) {
            Log::error("OpenRouter API Error: " . $response->body());
            throw new \Exception("OpenRouter API failed: " . ($response->json('error.message') ?? $response->reason()));
        }

        $text = $response->json('choices.0.message.content');
        $usage = $response->json('usage', ['prompt_tokens' => 0, 'completion_tokens' => 0]);

        return [
            'text' => $text,
            'input_tokens' => $usage['prompt_tokens'],
            'output_tokens' => $usage['completion_tokens'],
            'raw_response' => $response->body()
        ];
    }
}
