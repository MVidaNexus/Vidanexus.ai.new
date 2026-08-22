# Authentication

VidaNexus AI exposes three authentication entry points:

| Flow                | Route(s)                                                | Notes |
|---------------------|---------------------------------------------------------|-------|
| Email + password    | `GET /login`, `POST /login`, `GET /register`, `POST /register` | Default flow. |
| Forgot password     | `GET /forgot-password`, `POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password` | Token-based, expires in 60 minutes. |
| Social login        | `GET /auth/{provider}/redirect`, `GET /auth/{provider}/callback` | OAuth2 via Laravel Socialite. |

## Forgot password

Request flow:

```
POST /forgot-password
  └─ App\Http\Requests\Auth\ForgotPasswordRequest
        └─ App\Services\Auth\PasswordResetService::sendResetLink()
              └─ Password::sendResetLink()  (Laravel default broker)
                    └─ User::sendPasswordResetNotification($token)
                          └─ App\Notifications\QueuedResetPassword (queue: emails)
```

Reset flow:

```
POST /reset-password
  └─ App\Http\Requests\Auth\ResetPasswordRequest
        └─ App\Services\Auth\PasswordResetService::resetPassword()
              └─ Password::reset()  →  rehash password, fire PasswordReset event
```

### Operational checklist

The single most common reason "forgot password is broken" in production is that **the email queue worker isn't running**. Reset notifications use `App\Notifications\QueuedResetPassword` which implements `ShouldQueue` on the `emails` queue. Make sure:

```bash
php artisan queue:work --queue=emails,default
```

is running (Horizon or systemd). Without it, the email row sits in the `jobs` table forever.

Other things to verify:

- `MAIL_*` credentials in `.env` are valid SMTP credentials (or a transactional provider).
- `APP_URL` is correct — the reset link is built from `route('password.reset', ...)`.
- The `password_reset_tokens` table exists (created by the default `0001_01_01_000000_create_users_table` migration).

### Anti-enumeration

`PasswordResetService::toRedirect()` deliberately replies with the SAME success message even when the email isn't on file. The actual broker status is logged for support, but never surfaced to the user. This prevents attackers from enumerating valid accounts via the reset form.

### Logging

Every step writes to the `mail` channel (`storage/logs/mail.log`):

| Event | Meaning |
|-------|---------|
| `password_reset.request` | User hit the form. |
| `password_reset.link_dispatched` | Link successfully queued. |
| `password_reset.link_not_sent` | Broker returned non-success status (throttled / unknown user). |
| `password_reset.dispatch_failed` | SMTP / queue error — investigate. |
| `password_reset.attempt` | User submitted the new password. |
| `password_reset.success` | Password updated. |
| `password_reset.failed` | Token invalid / mismatched. |

## Email verification

Standard Laravel signed-route flow. Notifications use `App\Notifications\QueuedVerifyEmail` on the `emails` queue. Verification can be globally disabled via `Setting::set('global_email_verification', false)`.
