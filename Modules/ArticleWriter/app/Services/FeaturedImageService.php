<?php

namespace Modules\ArticleWriter\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Setting;

class FeaturedImageService
{
    protected const DEFAULT_MODEL = 'google/gemini-2.5-flash-image';
    protected const TIMEOUT = 45;

    /**
     * Resolve OpenRouter API Key.
     */
    protected function getApiKey(): ?string
    {
        $key = Setting::get('openrouter_api_key')
            ?: config('services.openrouter.api_key')
            ?: env('OPENROUTER_API_KEY')
            ?: env('OPEN_ROUTER_API_KEY');

        return !empty($key) ? trim($key) : null;
    }

    /**
     * Build an intelligent visual prompt tailored for Google Discover (16:9 editorial aesthetic).
     */
    public function buildPrompt(string $title, string $topic, string $language = 'ar'): string
    {
        $subject = !empty($title) ? $title : $topic;

        // Clean subject from punctuation, dates, filler words
        $cleanSubject = trim(preg_replace('/[0-9]{4}|[:\-\–—\(\)\[\]«»"\'\.\,\!\?]/u', ' ', $subject));
        $cleanSubject = preg_replace('/\s+/u', ' ', $cleanSubject);

        $prompt = "Create a breathtaking, professional editorial header image suitable for a high-end publication about: {$cleanSubject}. ";
        $prompt .= "Style: Award-winning photojournalism, stunning cinematic lighting, modern depth of field, sharp focus, 8k resolution, authentic and natural atmosphere. ";
        $prompt .= "Format & Composition: Wide landscape 16:9 aspect ratio, perfectly balanced composition designed for digital news feeds. ";
        $prompt .= "STRICT NEGATIVE CONSTRAINTS: Absolutely NO text, NO letters, NO words, NO typography, NO watermark, NO logo, NO blurry faces, NO distorted hands or bodies.";

        return $prompt;
    }

    /**
     * Generate an AI featured image for an article.
     *
     * @return array{url: string, path: string, filename: string, prompt: string, alt_text: string}|null
     */
    public function generateForArticle(string $title, string $topic, string $slug = '', string $language = 'ar'): ?array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            Log::warning('[FeaturedImageService] OpenRouter API key is missing.');
            return null;
        }

        $prompt = $this->buildPrompt($title, $topic, $language);
        $model = Setting::get('article-writer_image_model', self::DEFAULT_MODEL);

        try {
            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'HTTP-Referer' => config('app.url', 'https://vidanexus.ai'),
                    'X-Title' => 'VidaNexus Featured Image Generator',
                    'Accept' => 'application/json',
                ])
                ->timeout(self::TIMEOUT)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('[FeaturedImageService] OpenRouter API failure', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $images = $data['choices'][0]['message']['images'] ?? [];

            if (empty($images)) {
                Log::warning('[FeaturedImageService] No images returned in response', [
                    'response' => $data,
                ]);
                return null;
            }

            $imageUrlOrBase64 = $images[0]['image_url']['url'] ?? '';
            if (empty($imageUrlOrBase64)) {
                return null;
            }

            // Extract binary data from base64 data URL
            $binaryData = null;
            if (str_starts_with($imageUrlOrBase64, 'data:image/')) {
                $parts = explode(',', $imageUrlOrBase64, 2);
                if (isset($parts[1])) {
                    $binaryData = base64_decode($parts[1]);
                }
            } else {
                // If it was a direct external URL
                $fetch = Http::timeout(15)->get($imageUrlOrBase64);
                if ($fetch->successful()) {
                    $binaryData = $fetch->body();
                }
            }

            if (empty($binaryData)) {
                Log::warning('[FeaturedImageService] Failed to decode image binary data.');
                return null;
            }

            // Ensure public directory exists
            $storageDir = 'article-images';
            if (!Storage::disk('public')->exists($storageDir)) {
                Storage::disk('public')->makeDirectory($storageDir);
            }

            // Safe SEO filename
            $safeBase = !empty($slug) ? Str::slug($slug) : Str::slug($title ?: $topic);
            if (empty($safeBase)) {
                $safeBase = 'article-featured';
            }
            $filename = mb_substr($safeBase, 0, 50) . '-' . time() . '-' . Str::random(4) . '.png';
            $storagePath = $storageDir . '/' . $filename;

            Storage::disk('public')->put($storagePath, $binaryData);
            $publicUrl = asset('storage/' . $storagePath);

            return [
                'url' => $publicUrl,
                'path' => $storagePath,
                'filename' => $filename,
                'prompt' => $prompt,
                'alt_text' => $title ?: $topic,
            ];

        } catch (\Throwable $e) {
            Log::error('[FeaturedImageService] Exception during image generation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
