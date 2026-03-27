<?php

namespace App\Core\AI\Providers;

use App\Core\AI\Contracts\AIProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleProvider implements AIProvider
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getName(): string
    {
        return 'google';
    }

    public function generate(string $prompt, array $options = []): array
    {
        $apiKey = !empty($options['api_key']) ? $options['api_key'] : $this->apiKey;
        $model = $options['model'] ?? 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(45)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? 4096,
                'response_mime_type' => ($options['json_mode'] ?? false) ? 'application/json' : 'text/plain',
            ]
        ]);

        $candidates = $response->json('candidates');
        if (empty($candidates)) {
            Log::error("Gemini API Error: No candidates returned. Response: " . $response->body());
            throw new \Exception("Gemini API returned no results. This might be due to an invalid model name or safety filtering.");
        }

        $text = $candidates[0]['content']['parts'][0]['text'] ?? null;
        if ($text === null) {
            Log::error("Gemini API Error: No text parts found in candidates. Response: " . $response->body());
            throw new \Exception("Gemini API returned an empty response part.");
        }

        return [
            'text' => $text,
            'input_tokens' => 0, // Gemini doesn't return this easily in this format
            'output_tokens' => 0,
            'raw_response' => $response->body()
        ];
    }
}
