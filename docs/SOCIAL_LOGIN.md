# Social Login

Supported providers: **Google**, **GitHub**, **Microsoft**.

## Architecture

```
GET  /auth/{provider}/redirect    → SocialAuthController@redirect    → Socialite::driver($p)->redirect()
GET  /auth/{provider}/callback    → SocialAuthController@callback    → SocialAuthService::handleProviderCallback()
                                                                     → resolveUser() (lookup by social_account → email → create)
                                                                     → Auth::login($user)
```

Three database side-effects per first-time social login:

1. `users` row gets `oauth_provider`, `oauth_provider_id`, and `avatar_url` populated.
2. `social_accounts` row records the provider/provider_id with refresh tokens.
3. `wallets` + welcome `financial_ledger_entries` row created (matches the email-signup onboarding).

Repeat logins find the user via `social_accounts.provider + provider_id` and only refresh the access/refresh token.

Account linking by email is automatic: if a user already exists with the same email, the social identity is linked to that user instead of creating a duplicate. Once linked, both login methods (email/password and social) work.

## Configuration

Set these in `.env` (see `.env.example` for the full list):

| Variable | Required | Notes |
|----------|----------|-------|
| `SOCIALITE_ENABLED` | optional | `false` hides all social buttons. Default `true`. |
| `SOCIALITE_PROVIDERS` | optional | Comma-separated allow-list. Default `google,github,microsoft`. |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | yes (for Google) | Get from [Google Cloud Console](https://console.cloud.google.com/apis/credentials). |
| `GITHUB_CLIENT_ID` / `GITHUB_CLIENT_SECRET` | yes (for GitHub) | Get from [GitHub Developer Settings](https://github.com/settings/applications/new). |
| `MICROSOFT_CLIENT_ID` / `MICROSOFT_CLIENT_SECRET` | yes (for Microsoft) | Get from [Microsoft Entra Admin Center](https://entra.microsoft.com/) → App registrations. |
| `MICROSOFT_TENANT` | optional | `common` (default), `consumers`, or your tenant ID. |
| `*_REDIRECT_URI` | optional | Defaults to `{APP_URL}/auth/{provider}/callback`. Must EXACTLY match the redirect registered with the provider. |

## Setup steps per provider

### Google

1. Visit [Google Cloud Console → APIs & Services → Credentials](https://console.cloud.google.com/apis/credentials).
2. Create an OAuth 2.0 Client ID, type **Web application**.
3. Add `https://your-domain/auth/google/callback` to **Authorized redirect URIs**.
4. Copy Client ID + Secret into `.env`.

### GitHub

1. [Register a new OAuth app](https://github.com/settings/applications/new).
2. **Authorization callback URL** = `https://your-domain/auth/github/callback`.
3. Generate a client secret, copy into `.env`.

### Microsoft

1. [Microsoft Entra → App registrations → New registration](https://entra.microsoft.com/).
2. **Redirect URI** (Web) = `https://your-domain/auth/microsoft/callback`.
3. Under **Certificates & secrets**, create a Client Secret.
4. Set `MICROSOFT_TENANT=common` for any work/personal account, or your tenant GUID for single-tenant apps.

## Composer dependencies

```bash
composer require laravel/socialite socialiteproviders/microsoft
```

The Microsoft provider is registered via `App\Providers\EventServiceProvider` (already wired in `bootstrap/providers.php`).

## Database migration

Migration `2026_06_04_220000_add_oauth_columns_and_social_accounts_table.php` adds:

- `users.oauth_provider`, `users.oauth_provider_id`, `users.avatar_url` (all nullable).
- New `social_accounts` table with one row per linked external identity.

Run `php artisan migrate` after pulling this branch.

## Security notes

- The `{provider}` URL parameter is matched against `App\Services\Auth\SocialAuthService::SUPPORTED_PROVIDERS` AND `config('services.socialite.providers')`. Unknown / disabled providers return a 404-style error (no Socialite call is made).
- Provider credentials are checked **before** any redirect. If `client_id`/`client_secret` are empty, the user gets a friendly "this provider is not configured" message instead of a Socialite stack trace.
- Email is trusted: if Google/GitHub/Microsoft assert an email, we mark `email_verified_at` immediately so users skip the verification loop. (This matches industry-standard SSO behaviour.)
- Access/refresh tokens are stored encrypted-at-rest only if the underlying database column is encrypted; consider enabling SQL TDE in production.
