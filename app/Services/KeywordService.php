<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KeywordService
{
    /**
     * Fetch Google Trends for a specific region.
     * Uses RSS as a fallback.
     */
    public function fetchGoogleTrends($region = 'EG')
    {
        $cacheKey = "google_trends_strict_{$region}";
        
        return Cache::remember($cacheKey, 3600, function () use ($region) {
            return $this->fetchGoogleTrendsFromRss($region);
        });
    }

    /**
     * Fetch trends from Google Trends RSS feed.
     */
    protected function fetchGoogleTrendsFromRss($region)
    {
        $url = "https://trends.google.com/trending/rss?geo={$region}";
        
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->failed()) return [];

            $xml = simplexml_load_string($response->body());
            if (!$xml) return [];

            $trends = [];
            foreach ($xml->channel->item as $item) {
                // Namespace for ht:approx_traffic
                $ns = $item->getNamespaces(true);
                $traffic = isset($ns['ht']) ? (string)$item->children($ns['ht'])->approx_traffic : '0+';

                $trends[] = [
                    'title' => (string)$item->title,
                    'approx_traffic' => $traffic,
                    'description' => (string)$item->description,
                    'pubDate' => (string)$item->pubDate,
                    'link' => (string)$item->link,
                ];
            }

            return $trends;
        } catch (\Exception $e) {
            Log::error("Google Trends RSS Error ({$region}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Specialized fetch for "Drama Trends" or specifically entertainment keywords.
     * Filters for keywords related to art, movies, and TV shows.
     */
    public function fetchDramaTrends($region = 'EG')
    {
        $trends = $this->fetchGoogleTrends($region);
        
        $dramaKeywords = ['فن', 'مسلسل', 'فيلم', 'دراما', 'اغنية', 'مطرب', 'ممثل', 'مغني', 'سينما', 'تلفزيون', 'رامز', 'رمضان'];

        return array_filter($trends, function ($trend) use ($dramaKeywords) {
            foreach ($dramaKeywords as $keyword) {
                if (mb_stripos($trend['title'], $keyword) !== false || mb_stripos($trend['description'], $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });
    }
}
