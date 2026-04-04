<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * Serve Markdown versions of HTML pages to AI agents and bots.
 * 
 * Detection methods:
 * 1. URL ends with .md suffix (e.g., /refund.md)
 * 2. Accept: text/markdown header
 * 3. Known AI bot User-Agents (GPTBot, ClaudeBot, PerplexityBot, etc.)
 */
class ServeMarkdownToAI
{
    /**
     * Known AI crawler User-Agent identifiers.
     */
    protected array $aiBots = [
        'GPTBot',
        'ChatGPT-User',
        'ClaudeBot',
        'Claude-Web',
        'PerplexityBot',
        'Bytespider',
        'CCBot',
        'Google-Extended',
        'Googlebot',
        'anthropic-ai',
        'cohere-ai',
        'Applebot-Extended',
        'Meta-ExternalAgent',
        'FacebookBot',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Check if this request should receive Markdown
        $shouldServeMarkdown = $this->shouldServeMarkdown($request);

        // If .md suffix detected, strip it before routing
        $path = $request->getPathInfo();
        if (str_ends_with($path, '.md')) {
            $cleanPath = substr($path, 0, -3) ?: '/';
            $request->server->set('REQUEST_URI', $cleanPath . ($request->getQueryString() ? '?' . $request->getQueryString() : ''));
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }

        $response = $next($request);

        // Only convert HTML responses
        if (!$shouldServeMarkdown) {
            return $response;
        }

        // Only process successful HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if ($response->getStatusCode() !== 200 || !str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (empty($html)) {
            return $response;
        }

        // Cache key based on the clean URL
        $cacheKey = 'markdown_response_' . md5($request->getPathInfo());
        
        $markdown = Cache::remember($cacheKey, 3600, function () use ($html) {
            return $this->convertToMarkdown($html);
        });

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Markdown-Generated' => 'VidaNexus-AI',
        ]);
    }

    /**
     * Determine if the request should receive a Markdown response.
     */
    protected function shouldServeMarkdown(Request $request): bool
    {
        if (!config('markdown_ai.enabled', true)) {
            return false;
        }

        // 1. Check .md suffix
        if (str_ends_with($request->getPathInfo(), '.md')) {
            return true;
        }

        // 2. Check Accept header
        $accept = $request->header('Accept', '');
        if (str_contains($accept, 'text/markdown')) {
            return true;
        }

        // 3. Check User-Agent for AI bots
        $userAgent = $request->header('User-Agent', '');
        $bots = config('markdown_ai.crawlers', []);

        foreach ($bots as $bot) {
            if ($userAgent && stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert HTML to clean, structured Markdown.
     */
    protected function convertToMarkdown(string $html): string
    {
        // Extract the page title from <title> tag
        $pageTitle = '';
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch)) {
            $pageTitle = trim(strip_tags($titleMatch[1]));
        }

        // Extract meta description
        $metaDesc = '';
        if (preg_match('/<meta\s+name="description"\s+content="([^"]+)"/i', $html, $metaMatch)) {
            $metaDesc = $metaMatch[1];
        }

        // Extract canonical URL
        $canonical = '';
        if (preg_match('/<link\s+rel="canonical"\s+href="([^"]+)"/i', $html, $canonMatch)) {
            $canonical = $canonMatch[1];
        }

        // Pre-process: Remove elements that add noise for AI using config selectors
        $selectors = config('markdown_ai.strip_selectors', ['script', 'style', 'canvas', 'svg', 'nav', 'header', 'footer']);
        foreach ($selectors as $selector) {
            // Very basic tag removal for common tags
            if (preg_match('/^[a-z0-9]+$/i', $selector)) {
                $html = preg_replace('/<' . $selector . '\b[^>]*>(.*?)<\/' . $selector . '>/is', '', $html);
            }
        }

        // Explicitly remove VidaNexus noise patterns
        $html = preg_replace('/<div\s+id="bg-layer"[^>]*>(.*?)<\/div>\s*/is', '', $html);
        $html = preg_replace('/<div\s+class="[^"]*whatsapp[^"]*"[^>]*>(.*?)<\/div>/is', '', $html);
        $html = preg_replace('/<a[^>]*class="[^"]*logo-link[^"]*"[^>]*>.*?<\/a>/is', '', $html);
        
        // Remove images with local paths (not useful for AI)
        $html = preg_replace('/<img[^>]*src="http:\/\/(?:127\.0\.0\.1|localhost)[^"]*"[^>]*>/is', '', $html);

        $converter = new HtmlConverter([
            'strip_tags' => true,
            'remove_nodes' => implode(' ', array_filter($selectors, fn($s) => preg_match('/^[a-z0-9]+$/i', $s))),
            'hard_break' => true,
            'header_style' => 'atx',
            'bold_style' => '**',
            'italic_style' => '_',
        ]);

        $markdown = $converter->convert($html);

        // Post-process: Clean up the output
        $markdown = preg_replace('/\n{4,}/', "\n\n", $markdown);
        $markdown = preg_replace('/!\[[^\]]*\]\((?:http:\/\/127\.0\.0\.1|http:\/\/localhost)[^\)]*\)/', '', $markdown);
        $markdown = preg_replace('/\[\s*\]\([^\)]*\)/', '', $markdown);
        
        // Remove common navigation noise lines
        $navTerms = ['Login', 'Get Started', 'Home', 'Tools', 'Pricing', 'Dashboard'];
        foreach ($navTerms as $term) {
            $markdown = preg_replace('/^\s*\[?' . $term . '\]?.*$/m', '', $markdown);
        }

        // Branding noise cleanup
        $markdown = preg_replace('/\[?\s*!\[VidaNexus\].*?VIDA\s*NEXUS.*?\n/is', '', $markdown);
        $markdown = preg_replace('/VIDA\s+NEXUS\s*\n/is', '', $markdown);
        $markdown = preg_replace('/\n{3,}/', "\n\n", $markdown);
        
        $markdown = html_entity_decode($markdown, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Build clean YAML frontmatter
        $header = "---\n";
        $header .= "source: VidaNexus AI\n";
        $header .= "url: https://vidanexus.ai\n";
        if ($pageTitle) $header .= "title: " . str_replace('"', '\\"', $pageTitle) . "\n";
        if ($metaDesc) $header .= "description: " . str_replace('"', '\\"', $metaDesc) . "\n";
        if ($canonical) $header .= "canonical: {$canonical}\n";
        $header .= "format: markdown\n";
        $header .= "generated_at: " . now()->toIso8601String() . "\n";
        $header .= "---\n\n";

        return $header . trim($markdown) . "\n";
    }
}
