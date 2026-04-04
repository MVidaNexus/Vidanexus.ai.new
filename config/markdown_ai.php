<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI-Friendly Markdown Settings
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for serving Markdown versions of 
    | your public pages to AI agents and crawlers.
    |
    */

    'enabled' => env('MARKDOWN_AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | AI Crawler User-Agents
    |--------------------------------------------------------------------------
    | These identifiers are used to detect bots that should receive Markdown.
    */
    'crawlers' => [
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Strip Tags & Selectors
    |--------------------------------------------------------------------------
    | HTML elements that should be removed before conversion to reduce noise.
    */
    'strip_selectors' => [
        'script',
        'style',
        'canvas',
        'svg',
        'noscript',
        'nav',
        'header',
        'footer',
        '#bg-layer',
        '.whatsapp-btn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
];
