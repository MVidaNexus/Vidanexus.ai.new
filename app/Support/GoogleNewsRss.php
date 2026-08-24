<?php

namespace App\Support;

/**
 * Google News RSS URL building and article link normalization.
 */
class GoogleNewsRss
{
    /**
     * Build a Google News RSS URL with correct gl/hl/ceid for any supported country.
     */
    public static function feedUrl(string $country, ?string $lang = null, array $query = [], ?string $path = null): string
    {
        $country = CountryRegistry::normalizeCode($country) ?: 'EG';
        $lang = $lang ?: CountryRegistry::langFor($country);
        $ceid = CountryRegistry::googleNewsCeid($country);

        $base = 'https://news.google.com/rss';
        if ($path !== null && $path !== '') {
            $base .= '/'.ltrim($path, '/');
        }

        $params = array_merge([
            'hl' => $lang,
            'gl' => $country,
            'ceid' => $ceid,
        ], $query);

        return $base.'?'.http_build_query($params);
    }

    public static function topicUrl(string $encodedTopicId, string $country, ?string $lang = null): string
    {
        return self::feedUrl($country, $lang, [], "topics/{$encodedTopicId}");
    }

    public static function sectionUrl(string $topic, string $country, ?string $lang = null): string
    {
        $topic = strtoupper($topic);

        return self::feedUrl($country, $lang, [], "headlines/section/topic/{$topic}");
    }

    public static function searchUrl(string $query, string $country, ?string $lang = null): string
    {
        return self::feedUrl($country, $lang, ['q' => $query], 'search');
    }

    /**
     * Prefer a direct publisher URL over Google's RSS redirect wrapper.
     */
    public static function resolveArticleLink(string $rssLink, string $description = '', string $sourceUrl = ''): string
    {
        $candidates = [];

        if ($description !== '') {
            if (preg_match_all('/href=["\']([^"\']+)["\']/i', $description, $matches)) {
                foreach ($matches[1] as $href) {
                    $candidates[] = html_entity_decode($href, ENT_QUOTES, 'UTF-8');
                }
            }
        }

        if ($sourceUrl !== '' && self::isValidOutboundUrl($sourceUrl)) {
            $candidates[] = $sourceUrl;
        }

        if ($rssLink !== '') {
            $candidates[] = $rssLink;
        }

        foreach ($candidates as $url) {
            $url = trim($url);
            if (! self::isValidOutboundUrl($url)) {
                continue;
            }
            if (self::isGoogleNewsWrapper($url) && count($candidates) > 1) {
                continue;
            }

            return $url;
        }

        return $rssLink;
    }

    public static function isGoogleNewsWrapper(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return str_contains($host, 'news.google.com')
            || str_contains($host, 'google.com/news');
    }

    public static function isValidOutboundUrl(string $url): bool
    {
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Normalize a parsed RSS item into a consistent article array.
     *
     * @return array<string, mixed>|null
     */
    public static function mapRssItem(\SimpleXMLElement $item, ?string $scrapedAt = null): ?array
    {
        $rssLink = trim((string) $item->link);
        $title = html_entity_decode(trim((string) $item->title), ENT_QUOTES, 'UTF-8');
        $title = trim((string) preg_replace('/\s*[-|–—]\s*[^-|–—]*$/u', '', $title));
        $description = (string) ($item->description ?? '');
        $sourceName = trim((string) ($item->source ?? ''));
        $sourceUrl = '';
        if (isset($item->source) && isset($item->source['url'])) {
            $sourceUrl = trim((string) $item->source['url']);
        }

        if ($title === '') {
            return null;
        }

        $link = self::resolveArticleLink($rssLink, $description, $sourceUrl);
        if (! self::isValidOutboundUrl($link)) {
            return null;
        }

        $snippet = mb_substr(trim(strip_tags($description)), 0, 220);
        $image = null;
        if (preg_match('/src=["\']([^"\']+)["\']/i', $description, $matches)) {
            $image = $matches[1];
        }

        return [
            'title' => $title,
            'link' => $link,
            'url' => $link,
            'pubDate' => trim((string) ($item->pubDate ?? '')),
            'source' => $sourceName,
            'source_url' => $sourceUrl,
            'description' => $description,
            'snippet' => $snippet,
            'image' => $image,
            'scraped_at' => $scrapedAt ?? now()->toIso8601String(),
            'google_rss_link' => $rssLink !== $link ? $rssLink : null,
        ];
    }
}
