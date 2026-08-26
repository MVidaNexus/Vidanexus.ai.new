<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://vidanexus.ai');
        $tools = config('tools.all_tools', []);

        $staticPages = [
            [
                'loc' => $baseUrl,
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => "{$baseUrl}/pricing",
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => "{$baseUrl}/help-center",
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ],
            [
                'loc' => "{$baseUrl}/terms",
                'lastmod' => '2026-03-01',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => "{$baseUrl}/privacy",
                'lastmod' => '2026-03-01',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => "{$baseUrl}/refund",
                'lastmod' => '2026-03-01',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => "{$baseUrl}/shipping",
                'lastmod' => '2026-03-01',
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
        ];

        $toolPages = [];
        foreach ($tools as $tool) {
            if (!empty($tool['slug'])) {
                $toolPages[] = [
                    'loc' => "{$baseUrl}/tools/{$tool['slug']}",
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                ];
            }
        }

        $allUrls = array_merge($staticPages, $toolPages);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 ' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($allUrls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $baseUrl = config('app.url', 'https://vidanexus.ai');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Allow: /tools/\n";
        $content .= "Allow: /pricing\n";
        $content .= "Allow: /help-center\n";
        $content .= "Allow: /terms\n";
        $content .= "Allow: /privacy\n";
        $content .= "Allow: /refund\n";
        $content .= "Allow: /shipping\n";
        $content .= "Allow: /llms.txt\n\n";

        $content .= "# Sensitive and Authenticated Areas\n";
        $content .= "Disallow: /horizon-admin/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password/\n";
        $content .= "Disallow: /payment\n";
        $content .= "Disallow: /media/image-proxy\n";
        $content .= "Disallow: /logout\n\n";

        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
