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

        // Strip common journalistic clickbait verbs and source attributions that confuse image models
        $cleanSubject = preg_replace('/\b(الدكتور\s+[^\s]+|د\.\s*[^\s]+|استشاري\s+[^\s]+)\b/u', '', $cleanSubject);
        $cleanSubject = preg_replace('/\b(يكشف\s+(الحقيقة|السر|التفاصيل)?|يحذر\s+(من)?|يوضح\s+(حقيقة)?|يؤكد|بيان\s+عاجل|تصريح\s+جديد)\b/u', '', $cleanSubject);
        $cleanSubject = preg_replace('/\b(النمر|الصقر|الأسد|الذئب|فهد|غزال|سيف)\b/u', '', $cleanSubject); // Common Arabic human family names often mistranslated to wild animals
        $cleanSubject = preg_replace('/\b(يتصدر\s+(الدوري|الترتيب|قمة\s+الدوري)?|بالعلامة\s+الكاملة|بعد\s+فوز\s+(مثير|صعب|ساحق)(\s+على)?|صدارة\s+الترتيب|قمة\s+الترتيب|فوز\s+(مثير|صعب|ساحق)|ريمونتادا)\b/u', '', $cleanSubject);
        $cleanSubject = trim(preg_replace('/\s+/u', ' ', $cleanSubject));

        if (empty($cleanSubject) || mb_strlen($cleanSubject, 'UTF-8') < 5) {
            $cleanSubject = !empty($topic) ? $topic : $title;
        }

        $isSports = (bool) preg_match('/مباراة|ماتش|دوري|كأس|فريق|منتخب|أهداف|اهداف|تشكيل|ترتيب|كوفنتري|سيتي|يونايتد|ليفربول|أرسنال|تشيلسي|توتنهام|ريال|برشلونة|أتلتيكو|بايرن|دورتموند|باريس|ميلان|إنتر|يوفنتوس|الأهلي|الزمالك|الهلال|النصر|الاتحاد|match|vs|fc|football|soccer|premier league/iu', $subject);
        $isCupFinal = (bool) preg_match('/نهائي\s+كأس|تتويج\s+باللقب|رفع\s+الكأس|كأس\s+العالم|cup\s+final|trophy\s+celebration/iu', $subject);
        $isCelebrityDeath = (bool) preg_match('/وفاة|رحيل|مصرع|موت|ينعى|وداع|جنازة|تشييع|death|passed away|tribute/iu', $subject)
            && (bool) preg_match('/ممثل|فنان|نجم|مغني|مخرج|كاتب|شاعر|إعلامي|صحفي|بطل|مسلسل|فيلم|actor|artist|celebrity|star/iu', $subject);

        $prompt = "Create a breathtaking, professional editorial header image suitable for a high-end publication about: {$cleanSubject}. ";
        $prompt .= "Style: Award-winning photojournalism, authentic documentary photography, stunning cinematic natural lighting, modern depth of field, sharp focus, 8k resolution, authentic and natural atmosphere. ";
        $prompt .= "Format & Composition: Wide landscape 16:9 aspect ratio, perfectly balanced composition designed for digital news feeds and Google Discover. ";
        $prompt .= "CRITICAL SEMANTIC CONSTRAINTS: ";
        $prompt .= "1. If the topic mentions any Arabic family name or personal title, this is a HUMAN PERSON or DOCTOR, NOT a wild animal! Under NO circumstances generate tigers, falcons, lions, or beasts unless the topic is explicitly about wildlife zoology. ";
        $prompt .= "2. For health, medical, wellness, or dietary topics: Focus on authentic, elegant, everyday lifestyle or clinical objects (such as a clear glass of cold water with natural water droplets on a table, fresh healthy foods, or modern medical wellness) rather than surreal fantasy or beasts. ";

        if ($isSports && !$isCupFinal) {
            $prompt .= "3. FOR FOOTBALL & SPORTS MATCHES: This is a standard regular league match during the season, NOT a trophy final! Depict authentic on-pitch live action: professional football team players standing together for an authentic pre-match team photo lineup on the green grass pitch before kickoff, or players in active match gameplay under stadium lights. ";
            $prompt .= "STRICT SPORTS BAN: Under NO circumstances generate a championship trophy, cup, podium, stage, medals, or falling confetti! The team is NOT winning a tournament; NEVER depict anyone lifting a trophy or cup! ";
        } elseif ($isCelebrityDeath) {
            $prompt .= "3. FOR CELEBRITY / ACTOR DEATH & MEMORIAL ARTICLES: Depict an elegant, dignified, high-end studio portrait or tribute photograph of the actor/artist (or a warm, respectful cinema/theater studio atmosphere with soft golden lighting, film reels, or stage backdrop). ";
            $prompt .= "STRICT MORBID BAN: Under NO circumstances generate coffins, dead bodies, historical funeral processions, graveyards, tombs, skeletons, or weeping crowds in ancient costumes! The image must be a respectful, modern, and dignified tribute celebrating their artistic persona, NEVER a coffin or funeral procession! ";
        }

        $negativeAdditions = '';
        if ($isSports && !$isCupFinal) {
            $negativeAdditions .= ', NO trophies, NO cups, NO confetti, NO championship podiums, NO medals';
        }
        if ($isCelebrityDeath) {
            $negativeAdditions .= ', NO coffins, NO caskets, NO dead bodies, NO corpses, NO funeral processions, NO graveyards, NO skeletons';
        }

        $prompt .= "STRICT NEGATIVE CONSTRAINTS: Absolutely NO text, NO letters, NO words, NO typography, NO watermark, NO logo, NO blurry faces, NO distorted hands or bodies, NO wild animals in health articles{$negativeAdditions}.";

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

            // Optimize and crop to Google Discover 16:9 (1200x675) in lightweight WebP format
            $optimized = $this->optimizeForGoogleDiscover($binaryData);
            $finalData = $optimized['data'];
            $extension = $optimized['extension'];

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
            $filename = mb_substr($safeBase, 0, 50) . '-' . time() . '-' . Str::random(4) . '.' . $extension;
            $storagePath = $storageDir . '/' . $filename;

            Storage::disk('public')->put($storagePath, $finalData);
            $publicUrl = asset('storage/' . $storagePath);

            return [
                'url' => $publicUrl,
                'path' => $storagePath,
                'filename' => $filename,
                'prompt' => $prompt,
                'alt_text' => $title ?: $topic,
                'width' => $optimized['width'],
                'height' => $optimized['height'],
                'mime' => $optimized['mime'],
                'size_bytes' => strlen($finalData),
            ];

        } catch (\Throwable $e) {
            Log::error('[FeaturedImageService] Exception during image generation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Convert and crop image to exact Google Discover 16:9 ratio (1200x675) in lightweight WebP format.
     *
     * @param string $binaryData
     * @return array{data: string, extension: string, mime: string, width: int, height: int}
     */
    protected function optimizeForGoogleDiscover(string $binaryData): array
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return [
                'data' => $binaryData,
                'extension' => 'png',
                'mime' => 'image/png',
                'width' => 1024,
                'height' => 1024,
            ];
        }

        try {
            $src = @imagecreatefromstring($binaryData);
            if (!$src) {
                return [
                    'data' => $binaryData,
                    'extension' => 'png',
                    'mime' => 'image/png',
                    'width' => 1024,
                    'height' => 1024,
                ];
            }

            $origW = imagesx($src);
            $origH = imagesy($src);

            $targetW = 1200;
            $targetH = 675; // Exact 16:9 ratio (1200 / (16/9) = 675)
            $targetRatio = 16 / 9;
            $origRatio = $origW / max(1, $origH);

            if ($origRatio > $targetRatio) {
                // Image is wider than 16:9 -> crop horizontal sides (centered)
                $cropH = $origH;
                $cropW = (int) round($origH * $targetRatio);
                $cropX = (int) round(($origW - $cropW) / 2);
                $cropY = 0;
            } else {
                // Image is taller or square -> crop vertical with golden-ratio focal bias (35% from top)
                $cropW = $origW;
                $cropH = (int) round($origW / $targetRatio);
                $cropX = 0;
                $maxOffset = max(0, $origH - $cropH);
                $cropY = min($maxOffset, (int) round($maxOffset * 0.35));
            }

            $dest = imagecreatetruecolor($targetW, $targetH);
            if (function_exists('imagealphablending') && function_exists('imagesavealpha')) {
                imagealphablending($dest, false);
                imagesavealpha($dest, true);
            }

            imagecopyresampled($dest, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

            $outputData = null;
            $extension = 'webp';
            $mime = 'image/webp';

            if (function_exists('imagewebp')) {
                ob_start();
                imagewebp($dest, null, 86);
                $outputData = ob_get_clean();
            } elseif (function_exists('imagejpeg')) {
                ob_start();
                imagejpeg($dest, null, 88);
                $outputData = ob_get_clean();
                $extension = 'jpg';
                $mime = 'image/jpeg';
            }

            imagedestroy($dest);
            imagedestroy($src);

            if (!empty($outputData)) {
                return [
                    'data' => $outputData,
                    'extension' => $extension,
                    'mime' => $mime,
                    'width' => $targetW,
                    'height' => $targetH,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('[FeaturedImageService] Optimization fallback: ' . $e->getMessage());
        }

        return [
            'data' => $binaryData,
            'extension' => 'png',
            'mime' => 'image/png',
            'width' => 1024,
            'height' => 1024,
        ];
    }
}
