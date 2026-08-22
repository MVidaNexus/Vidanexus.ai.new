<?php

namespace App\Services\Auth;

use App\Models\FinancialLedgerEntry;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * Drives the OAuth login / linking flow for Google, GitHub, and Microsoft.
 *
 * Behavior:
 *  - First-time login (no SocialAccount, no matching email):
 *      → create new User, attach SocialAccount, sign them in.
 *  - First-time login with email already on file:
 *      → automatically link the SocialAccount to the existing user.
 *  - Repeat login:
 *      → look up by SocialAccount.provider+provider_id, refresh tokens.
 *
 * Anti-abuse:
 *  - Only providers explicitly allow-listed by config('services.socialite.providers')
 *    are honored — guards against typo'd routes.
 *  - Provider credentials are validated up front so we fail-fast with a
 *    user-readable error instead of letting Socialite throw a stack trace.
 */
class SocialAuthService
{
    public const SUPPORTED_PROVIDERS = ['google', 'github', 'microsoft'];

    /**
     * Build an OAuth redirect for the given provider.
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        $this->guardProvider($provider);
        $this->guardConfigured($provider);

        Log::info('social_auth.redirect', ['provider' => $provider]);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback. Returns a redirect to the dashboard on
     * success or back to the login page with an error on failure.
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        $this->guardProvider($provider);
        $this->guardConfigured($provider);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::error('social_auth.callback_failed', [
                'provider' => $provider,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'email' => "We couldn't sign you in with {$provider}. Please try again or use email/password.",
            ]);
        }

        $user = DB::transaction(function () use ($provider, $socialiteUser) {
            return $this->resolveUser($provider, $socialiteUser);
        });

        Auth::login($user, remember: true);

        Log::info('social_auth.success', [
            'provider' => $provider,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        if (! $user->hasVerifiedEmail()) {
            // Trust Google/GitHub/Microsoft email verification: we mark
            // verified at sign-up to skip the email loop. This is industry
            // standard for first-party OAuth providers.
            $user->markEmailAsVerified();
        }

        return redirect()->intended('/dashboard')->with(
            'success',
            'Welcome back! Signed in via '.ucfirst($provider).'.'
        );
    }

    /**
     * Find or create the local user for the given social identity.
     */
    protected function resolveUser(string $provider, SocialiteUser $remote): User
    {
        $providerId = (string) $remote->getId();
        $email = $remote->getEmail();
        $name = $remote->getName() ?: ($remote->getNickname() ?: ($email ? Str::before($email, '@') : 'New User'));

        // 1. Already linked by provider+provider_id?
        $social = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($social) {
            $this->refreshTokens($social, $remote);
            return $social->user;
        }

        // 2. Email exists? Link to existing user.
        $user = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            // 3. First-time registration via social.
            $user = $this->createUserFromSocial($provider, $remote, $email, $name);
        } else {
            // Backfill primary OAuth provider on the user row when missing.
            if (empty($user->oauth_provider)) {
                $user->forceFill([
                    'oauth_provider' => $provider,
                    'oauth_provider_id' => $providerId,
                    'avatar_url' => $remote->getAvatar() ?: $user->avatar_url,
                ])->save();
            }
        }

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $providerId,
            'email' => $email,
            'name' => $name,
            'nickname' => $remote->getNickname(),
            'avatar_url' => $remote->getAvatar(),
            'access_token' => $remote->token ?? null,
            'refresh_token' => $remote->refreshToken ?? null,
            'expires_at' => isset($remote->expiresIn) ? now()->addSeconds((int) $remote->expiresIn) : null,
        ]);

        return $user;
    }

    /**
     * Create a brand new user from social provider data. The user starts on
     * the `beginner` plan and gets the same welcome credits as an email
     * signup, so social-onboarded users have feature parity.
     */
    protected function createUserFromSocial(string $provider, SocialiteUser $remote, ?string $email, string $name): User
    {
        if (! $email) {
            // Some providers (rare GitHub edge case) don't expose email.
            // Mint a deterministic placeholder so the unique constraint passes.
            $email = sprintf('%s_%s@social.local', $provider, $remote->getId());
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            // Placeholder password so legacy NOT NULL doesn't trip. Users
            // can set a real password later from profile settings.
            'password' => Hash::make(Str::random(40)),
            'role' => 'user',
            'subscription_tier' => 'beginner',
            'oauth_provider' => $provider,
            'oauth_provider_id' => (string) $remote->getId(),
            'avatar_url' => $remote->getAvatar(),
            'email_verified_at' => now(),
        ]);

        $beginnerCredits = (float) Setting::get('plan_credits_beginner', 0.00);

        Wallet::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'balance_credits' => $beginnerCredits,
        ]);

        if ($beginnerCredits > 0) {
            FinancialLedgerEntry::create([
                'user_id' => $user->id,
                'event_type' => 'registration_welcome',
                'wallet_delta' => (int) round($beginnerCredits),
                'bonus_delta' => 0,
                'meta' => ['plan' => 'beginner', 'source' => 'social_'.$provider],
            ]);
        }

        Log::info('social_auth.user_created', [
            'user_id' => $user->id,
            'email' => $email,
            'provider' => $provider,
        ]);

        return $user;
    }

    protected function refreshTokens(SocialAccount $social, SocialiteUser $remote): void
    {
        $social->forceFill([
            'access_token' => $remote->token ?? $social->access_token,
            'refresh_token' => $remote->refreshToken ?? $social->refresh_token,
            'expires_at' => isset($remote->expiresIn) ? now()->addSeconds((int) $remote->expiresIn) : $social->expires_at,
            'avatar_url' => $remote->getAvatar() ?: $social->avatar_url,
        ])->save();
    }

    protected function guardProvider(string $provider): void
    {
        $allowed = (array) config('services.socialite.providers', self::SUPPORTED_PROVIDERS);

        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            throw new ModelNotFoundException("Unsupported social provider: {$provider}");
        }

        if (! in_array($provider, $allowed, true)) {
            throw new \RuntimeException("Social provider '{$provider}' is currently disabled.");
        }

        if (! (bool) config('services.socialite.enabled', true)) {
            throw new \RuntimeException('Social authentication is currently disabled.');
        }
    }

    protected function guardConfigured(string $provider): void
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret)) {
            throw new \RuntimeException(
                "Social provider '{$provider}' is not configured. Missing client_id/client_secret."
            );
        }
    }
}
