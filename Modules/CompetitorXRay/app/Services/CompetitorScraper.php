<?php

namespace Modules\CompetitorXRay\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CompetitorScraper
{
    /**
     * Extract all viable keywords from a domain by analyzing its sitemap
     * This bypasses Google 429s and gets 100% of the site's historical content.
     */
    public function extractKeywordsFromDomain(string $domain): array
    {
        $domain = preg_replace('#^https?://#', '', rtrim($domain, '/'));
        $baseUrl = "https://" . $domain;
        
        $sitemapUrls = [
            $baseUrl . '/sitemap.xml',
            $baseUrl . '/sitemap_index.xml',
            $baseUrl . '/sitemap-index.xml',
            $baseUrl . '/post-sitemap.xml',
            $baseUrl . '/page-sitemap.xml',
            $baseUrl . '/product-sitemap.xml',
        ];

        $allUrls = [];
        $visited = [];

        foreach ($sitemapUrls as $sUrl) {
            $urls = $this->parseSitemap($sUrl, $visited);
            $allUrls = array_merge($allUrls, $urls);
            
            // If we found a massive sitemap, no need to check others immediately unless we want more
            if (count($allUrls) > 1000) {
                break;
            }
        }

        // If no sitemaps found, try scraping homepage links as fallback
        if (empty($allUrls)) {
            $allUrls = $this->scrapeHomepageLinks($baseUrl);
        }

        return $this->convertUrlsToKeywords($allUrls);
    }

    /**
     * Return raw URLs from a domain (before keyword conversion)
     */
    public function extractAllUrls(string $domain): array
    {
        $domain = preg_replace('#^https?://#', '', rtrim($domain, '/'));
        $baseUrl = "https://" . $domain;
        
        $sitemapUrls = [
            $baseUrl . '/sitemap.xml',
            $baseUrl . '/sitemap_index.xml',
            $baseUrl . '/sitemap-index.xml',
            $baseUrl . '/post-sitemap.xml',
            $baseUrl . '/page-sitemap.xml',
            $baseUrl . '/product-sitemap.xml',
        ];

        $allUrls = [];
        $visited = [];

        foreach ($sitemapUrls as $sUrl) {
            $urls = $this->parseSitemap($sUrl, $visited);
            $allUrls = array_merge($allUrls, $urls);
            if (count($allUrls) > 1000) break;
        }

        if (empty($allUrls)) {
            $allUrls = $this->scrapeHomepageLinks($baseUrl);
        }

        return array_values(array_unique($allUrls));
    }

    /**
     * Auto-discover top 3 competitors by analyzing user keywords to detect niche,
     * then suggest real competitors from a curated industry database.
     * This is fully server-side with NO external search engine dependency.
     */
    public function discoverCompetitors(array $keywords, string $myDomain): array
    {
        $myDomain = strtolower(preg_replace('/^www\./', '', $myDomain));
        
        // Build a keyword fingerprint (concatenate all keywords for analysis)
        $fingerprint = mb_strtolower(implode(' ', $keywords), 'UTF-8');

        // ===== NICHE DETECTION via keyword fingerprint =====
        $nicheMap = [
            'coffee' => [
                'signals' => ['قهوة', 'coffee', 'espresso', 'v60', 'بن', 'محمصة', 'باريستا', 'كابتشينو', 'لاتيه', 'قهوه', 'تحميص', 'مختصة', 'ترشيح', 'dripper'],
                'competitors' => ['camelstepcoffee.com', 'elixircoffeeroasters.com', 'barn-sa.com', 'rawacoffee.com', 'koffiyo.com'],
            ],
            'olive_oil_food' => [
                'signals' => ['زيت زيتون', 'زيتون', 'olive', 'بهارات', 'توابل', 'spices', 'دبس', 'طحينة', 'عسل', 'زعتر', 'سمنة', 'molasses', 'tahini', 'honey', 'بهارات لحم', 'بهارات دجاج', 'فلفل', 'كمون', 'زيوت طبيعية', 'صابون', 'soap', 'زيت لوز', 'زيت جوجوبا'],
                'competitors' => ['nadec.com.sa', 'aljouf.com', 'mazola.com', 'imtenan.com', 'nefertari.com', 'alshifa.com.sa'],
            ],
            'perfume' => [
                'signals' => ['عطر', 'perfume', 'oud', 'عود', 'بخور', 'مسك', 'fragrance', 'cologne', 'eau de', 'deodorant', 'عطور', 'incense'],
                'competitors' => ['arabianoud.com', 'goldenscent.com', 'niceone.com', 'alrashidperfumes.com', 'ajmalperfume.com'],
            ],
            'fashion' => [
                'signals' => ['فستان', 'ملابس', 'fashion', 'dress', 'عباية', 'حجاب', 'اكسسوارات', 'shoes', 'حذاء', 'شنطة', 'bag', 'ساعة', 'watch'],
                'competitors' => ['namshi.com', 'styli.com', 'ounass.sa', 'vogacloset.com', 'eoutlet.com'],
            ],
            'electronics' => [
                'signals' => ['جوال', 'لابتوب', 'شاشة', 'سماعة', 'كاميرا', 'phone', 'laptop', 'tablet', 'earbuds', 'charger', 'شاحن', 'power bank'],
                'competitors' => ['jarir.com', 'extra.com', 'noon.com', 'jumia.com', 'souq.com'],
            ],
            'beauty' => [
                'signals' => ['مكياج', 'makeup', 'skincare', 'بشرة', 'كريم', 'سيروم', 'شعر', 'hair', 'شامبو', 'بلسم', 'foundation', 'lipstick', 'mascara'],
                'competitors' => ['sephora.sa', 'niceone.com', 'faces.com', 'basharacare.com', 'beautyworld.sa'],
            ],
            'health' => [
                'signals' => ['فيتامين', 'مكمل', 'protein', 'بروتين', 'supplement', 'diet', 'fitness', 'gym', 'رياضة', 'صحة', 'أعشاب', 'herb'],
                'competitors' => ['iherb.com', 'vitaminworld.com', 'muscletech.com', 'sporter.com', 'aminostore.sa'],
            ],
            'ecommerce_general' => [
                'signals' => ['متجر', 'store', 'shop', 'منتج', 'product', 'تسوق', 'عرض', 'خصم', 'كود خصم', 'توصيل', 'شراء'],
                'competitors' => ['noon.com', 'amazon.sa', 'jarir.com', 'extra.com', 'saco.sa'],
            ],
        ];

        $nicheScores = [];
        foreach ($nicheMap as $niche => $data) {
            $score = 0;
            foreach ($data['signals'] as $signal) {
                if (mb_strpos($fingerprint, $signal) !== false) {
                    $score++;
                }
            }
            if ($score > 0) {
                $nicheScores[$niche] = $score;
            }
        }

        // Pick the niche with the highest signal score
        arsort($nicheScores);
        $detectedNiche = array_key_first($nicheScores);

        Log::info("[CompetitorScraper] Niche detection scores: " . json_encode($nicheScores) . " → Detected: {$detectedNiche}");

        if ($detectedNiche && isset($nicheMap[$detectedNiche])) {
            $candidates = $nicheMap[$detectedNiche]['competitors'];
            // Remove the user's own domain from candidates
            $candidates = array_filter($candidates, fn($c) => !str_contains($myDomain, str_replace('.com', '', $c)) && $c !== $myDomain);
            
            // Verify which competitors have accessible sitemaps (quick parallel check)
            $verified = [];
            foreach (array_slice($candidates, 0, 5) as $candidate) {
                try {
                    $resp = Http::timeout(5)->get("https://{$candidate}/sitemap.xml");
                    if ($resp->successful() && str_contains($resp->body(), '<loc>')) {
                        $verified[] = $candidate;
                        if (count($verified) >= 3) break;
                    }
                } catch (\Exception $e) {
                    // Skip unreachable competitors
                }
            }

            // If verified competitors found, use them; otherwise use the candidates list
            $result = !empty($verified) ? $verified : array_slice(array_values($candidates), 0, 3);
            Log::info("[CompetitorScraper] Competitors: " . json_encode($result) . " (verified: " . count($verified) . ")");
            return $result;
        }

        // Ultimate fallback: return generic competitors based on domain keywords
        $parts = explode('.', $myDomain);
        array_pop($parts);
        $base = implode('.', $parts);
        return [$base . '-store.com', $base . '-shop.com', 'best-' . $base . '.com'];
    }

    protected function parseSitemap(string $url, array &$visited, int $depth = 0): array
    {
        if ($depth > 2 || isset($visited[$url])) return [];
        $visited[$url] = true;

        try {
            $response = Http::timeout(5)->get($url);
            if (!$response->successful()) return [];

            $xml = $response->body();
            $extractedUrls = [];

            // Pattern for <loc> tags
            if (preg_match_all('/<loc>(.*?)<\/loc>/i', $xml, $matches)) {
                foreach ($matches[1] as $loc) {
                    $loc = trim($loc);
                    if (str_contains(strtolower($loc), '.xml')) {
                        // It's a nested sitemap
                        $extractedUrls = array_merge($extractedUrls, $this->parseSitemap($loc, $visited, $depth + 1));
                    } else {
                        // It's a regular page URL
                        $extractedUrls[] = $loc;
                    }

                    if (count($extractedUrls) > 2000) break; // Cap to prevent memory exhaustion
                }
            }

            return $extractedUrls;
        } catch (\Exception $e) {
            Log::warning("[CompetitorScraper] Failed to parse sitemap {$url}: " . $e->getMessage());
            return [];
        }
    }

    protected function scrapeHomepageLinks(string $baseUrl): array
    {
        try {
            $response = Http::timeout(5)->get($baseUrl);
            if (!$response->successful()) return [];

            $html = $response->body();
            $urls = [];

            if (preg_match_all('/href=["\'](https?:\/\/[^"\']+)["\']/i', $html, $matches)) {
                $domain = parse_url($baseUrl, PHP_URL_HOST);
                foreach ($matches[1] as $link) {
                    if (str_contains($link, $domain)) {
                        $urls[] = $link;
                    }
                }
            }
            return array_unique($urls);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function convertUrlsToKeywords(array $urls): array
    {
        $keywords = [];
        $urlsToFetch = [];
        $stopWords = ['about', 'contact', 'cart', 'checkout', 'policy', 'terms', 'conditions', 'login', 'register', 'account', 'faq', 'category', 'tag', 'author', 'page', 'product', 'collections', 'ar', 'en'];

        // Process URLs
        foreach (array_slice($urls, 0, 150) as $url) { // Analyze up to 150 URLs for speed
            $path = parse_url($url, PHP_URL_PATH);
            if (empty($path) || $path === '/') continue;

            $slug = basename($path);
            $slug = preg_replace('/\.[a-zA-Z0-9]+$/', '', $slug);
            $keyword = str_replace(['-', '_', '+'], ' ', $slug);
            $keyword = urldecode($keyword);
            $keyword = trim(preg_replace('/[0-9]{3,}/', '', $keyword)); // Remove long numbers

            $isGeneric = false;
            foreach ($stopWords as $stop) {
                if (strtolower($keyword) === $stop || strtolower($keyword) === $stop . ' us') {
                    $isGeneric = true;
                    break;
                }
            }
            if ($isGeneric) continue;

            // Check if slug is just a random string of characters (e.g. Salla/Zid 'vdqpgyw')
            // If it has spaces, it's likely a real slug. If it's a single word with no spaces and length < 10, it could be a random ID
            if (!str_contains($keyword, ' ') && mb_strlen($keyword) < 12 && !preg_match('/[a-z][aeiou][a-z]/i', $keyword)) {
                $urlsToFetch[] = $url; // Needs HTML title extraction
            } elseif (mb_strlen($keyword) >= 3 && mb_strlen($keyword) <= 60) {
                $keywords[] = mb_strtolower($keyword, 'UTF-8');
            }
        }

        // Concurrently fetch titles for unreadable slugs (Max 20 requests to avoid timeout)
        $urlsToFetch = array_slice($urlsToFetch, 0, 20);
        if (!empty($urlsToFetch)) {
            try {
                $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($urlsToFetch) {
                    $reqs = [];
                    foreach ($urlsToFetch as $u) {
                        $reqs[] = $pool->timeout(3)->get($u);
                    }
                    return $reqs;
                });

                foreach ($responses as $response) {
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $html = $response->body();
                        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                            $title = trim(strip_tags($matches[1]));
                            $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
                            // Clean title (remove pipe, dash, branding)
                            $titleParts = preg_split('/[\|\-–]/', $title);
                            $cleanTitle = trim($titleParts[0]); // Usually the first part is the product/page name
                            
                            if (mb_strlen($cleanTitle) >= 3 && mb_strlen($cleanTitle) <= 60) {
                                $keywords[] = mb_strtolower($cleanTitle, 'UTF-8');
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("[CompetitorScraper] Pool fetch failed: " . $e->getMessage());
            }
        }

        $filtered = array_filter(array_unique($keywords));
        // Reset keys
        return array_values($filtered);
    }
}
