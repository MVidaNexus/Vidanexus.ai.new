<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProvider
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function generate(string $prompt, array $options = []): array
    {
        $apiKey = !empty($options['api_key']) ? $options['api_key'] : $this->apiKey;
        if (empty($apiKey)) {
            throw new \Exception("OpenAI API Key is missing.");
        }

        $url = "https://api.openai.com/v1/chat/completions";
        $response = Http::timeout(45)->withToken($apiKey)->post($url, [
            'model' => $options['model'] ?? 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $options['temperature'] ?? 0.8,
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'response_format' => ($options['json_mode'] ?? false) ? ['type' => 'json_object'] : null,
        ]);

        if ($response->failed()) {
            throw new \Exception("OpenAI API failed: " . ($response->json('error.message') ?? $response->reason()));
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
