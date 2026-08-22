<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    public function __construct(protected SocialAuthService $socialAuth) {}

    /**
     * GET /auth/{provider}/redirect
     */
    public function redirect(string $provider): RedirectResponse
    {
        try {
            return $this->socialAuth->redirectToProvider($provider);
        } catch (\Throwable $e) {
            Log::warning('social_auth.redirect_failed', [
                'provider' => $provider,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => $e->getMessage(),
            ]);
        }
    }

    /**
     * GET /auth/{provider}/callback
     */
    public function callback(string $provider): RedirectResponse
    {
        try {
            return $this->socialAuth->handleProviderCallback($provider);
        } catch (\Throwable $e) {
            Log::error('social_auth.controller_callback_failed', [
                'provider' => $provider,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => "We couldn't complete the {$provider} sign-in. Please try again.",
            ]);
        }
    }
}
