<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pre-Launch Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, all public routes will redirect to the Coming Soon page.
    | Admins and authenticated users with specific roles can bypass this.
    |
    */
    'prelaunch' => env('VIDANEXUS_PRELAUNCH', true),

    /*
    |--------------------------------------------------------------------------
    | Pre-Launch Bypass Token
    |--------------------------------------------------------------------------
    |
    | Secret token to bypass the prelaunch shield. 
    | Access via: https://vidanexus.ai/?preview=YOUR_TOKEN
    |
    */
    'prelaunch_bypass_token' => env('VIDANEXUS_BYPASS_TOKEN', 'vn_secret_2026'),

    /*
    |--------------------------------------------------------------------------
    | Public stylesheet cache buster (style.v2.css)
    |--------------------------------------------------------------------------
    |
    | Bump VIDANEXUS_STYLE_VERSION when you deploy CSS changes so browsers
    | refetch; all Blade layouts read this single value.
    |
    */
    'style_css_version' => env('VIDANEXUS_STYLE_VERSION', '34'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers & Rates
    |--------------------------------------------------------------------------
    |
    | Configuration for AI providers and their respective token rates.
    |
    */
    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'openai'),
        // Only providers actually registered by App\Providers\AIServiceProvider
        // belong here. `anthropic` was removed because no AnthropicProvider class
        // exists yet — leaving it in produced noisy "Provider is not registered."
        // entries in every attempts[] array.
        'failover_order' => ['openai', 'google', 'openrouter'],
        'markup' => 1.4, // 40% profit buffer by default
        'rates' => [
            'openai' => [
                'gpt-4o' => [
                    'input' => 0.000005, // Credits per token
                    'output' => 0.000015,
                ],
                'gpt-4o-mini' => [
                    'input' => 0.00000015,
                    'output' => 0.0000006,
                ],
            ],
            'google' => [
                'gemini-1.5-pro' => [
                    'input' => 0.0000035,
                    'output' => 0.0000105,
                ],
                'gemini-1.5-flash' => [
                    'input' => 0.000000075,
                    'output' => 0.0000003,
                ],
            ],
            'anthropic' => [
                'claude-3-5-sonnet' => [
                    'input' => 0.000003,
                    'output' => 0.000015,
                ],
            ],
            'openrouter' => [
                'google/gemini-2.0-flash-001' => [
                    'input' => 0.0000001,
                    'output' => 0.0000004,
                ],
            ],
        ],
    ],
];
