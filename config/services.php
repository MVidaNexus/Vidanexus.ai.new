<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Fawaterak: sandbox (staging API) vs live (production API).
    | When FAWATERK_SANDBOX is unset, empty, or "auto", APP_ENV drives the default:
    | local, development, dev, testing, staging → sandbox; production → live.
    | Set FAWATERK_SANDBOX=true / false to force either mode on any APP_ENV.
    */
    /*
    |--------------------------------------------------------------------------
    | Social Authentication (Laravel Socialite)
    |--------------------------------------------------------------------------
    |
    | Each provider needs a Client ID, Client Secret, and a Redirect URL
    | registered with the upstream OAuth application. The redirect URL must
    | EXACTLY match the route registered under `routes/web/auth.php`
    | (`/auth/{provider}/callback`).
    |
    | Microsoft uses the SocialiteProviders/Microsoft community package; it
    | follows the same Socialite contract.
    |
    | Set `socialite.enabled` to false to hard-disable all social buttons —
    | this is the operational kill switch when a provider goes down.
    */
    'socialite' => [
        'enabled' => filter_var(env('SOCIALITE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'providers' => array_values(array_filter(explode(
            ',',
            (string) env('SOCIALITE_PROVIDERS', 'google,github,microsoft')
        ))),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI', env('APP_URL').'/auth/github/callback'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI', env('APP_URL').'/auth/microsoft/callback'),
        'tenant' => env('MICROSOFT_TENANT', 'common'),
    ],

    'fawaterk' => array_merge(
        (static function (): array {
            $raw = env('FAWATERK_SANDBOX');
            $appEnv = (string) env('APP_ENV', 'production');
            $autoSandboxEnvs = ['local', 'development', 'dev', 'testing', 'staging'];
            $isAuto = $raw === null || $raw === ''
                || strtolower(trim((string) $raw)) === 'auto';
            $sandbox = $isAuto
                ? in_array($appEnv, $autoSandboxEnvs, true)
                : filter_var($raw, FILTER_VALIDATE_BOOLEAN);
            $sandboxMode = $isAuto ? 'auto' : ($sandbox ? 'sandbox' : 'live');

            return [
                'sandbox' => $sandbox,
                'sandbox_mode' => $sandboxMode,
            ];
        })(),
        [
            'api_key' => env('FAWATERK_API_KEY'),
            'vendor_key' => env('FAWATERK_VENDOR_KEY'),
            'webhook_secret' => env('FAWATERK_WEBHOOK_SECRET'),
            'api_base_url' => env('FAWATERK_API_BASE_URL'),
        ],
    ),

];
