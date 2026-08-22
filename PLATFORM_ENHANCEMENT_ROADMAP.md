# VidaNexus platform enhancement roadmap

This document maps the requested enterprise enhancements to the codebase and defines phased delivery. Items marked **Done (initial)** reflect work merged in the `2026_04_29` implementation pass; others are planned follow-ups.

## 1. Queue and background jobs

| Topic | Status | Notes |
|--------|--------|--------|
| Laravel Horizon | **Existing** | `config/horizon.php`, `HorizonServiceProvider`, Redis. Use `/horizon` (package) for worker UI where enabled. |
| Supervisor / systemd | **Example** | `deploy/supervisor-laravel-worker.conf.example` — tune `queue:work` or `horizon` for production. |
| Auto-restart workers | **Ops** | Supervisor `autorestart=true` or Horizon’s built-in restart. |
| Retries / failed jobs | **Existing** | `config/queue.php` `failed` table; tune `$tries` / `backoff` per job. |
| Queue logging | **Done (initial)** | `config/logging.php` channels `queue`, `mail`, `audit`; `AppServiceProvider` logs job lifecycle to `queue` channel. |
| Balancing | **Horizon** | Configure `config/horizon.php` `environments` / supervisors for multiple queues (`default`, `mail`, etc.). |

**Follow-up:** Route critical failures to Slack/email via `LOG_SLACK_WEBHOOK_URL` or a custom `Queue::failing` listener that notifies on non-retryable errors.

## 2. Credits system (core)

| Topic | Status | Notes |
|--------|--------|--------|
| Wallet vs tool bonus | **Done (initial)** | `user_tools.allow_bonus_for_ai_usage`: `false` for marketplace-paid unlocks (wallet-only AI usage); `true` for trial grants. Deduction order: **wallet first**, then per-tool bonus when allowed. Service: `App\Services\Credits\ToolCreditConsumptionService`. |
| Welcome CRS | **Done (initial)** | `plan_credits_beginner` + dashboard **Welcome Credits** panel; registration flash mentions welcome CRS. |
| Ledger rows | **Done (initial)** | `financial_ledger_entries` records usage, welcome, coupons, purchases. |

**Follow-up:** Align every module tool path (non-generic) with the same service so all AI routes share one policy.

## 3. Promo / coupon codes

| Topic | Status | Notes |
|--------|--------|--------|
| Credit value, expiry, limits, per-user | **Existing** | `coupons`, `coupon_redemptions`, admin Horizon **Coupons** tab. |
| Scope: all tools vs one tool | **Done (initial)** | `coupons.scope`, `coupons.tool_slug`. All tools → wallet. Specific tool → `user_tools.bonus_credits` (must own tool). |

## 4. Authentication and security

| Topic | Status | Notes |
|--------|--------|--------|
| Email verification | **Existing** | `MustVerifyEmail`, `EnsureEmailIsVerifiedCustom`, `global_email_verification` setting. |
| Queued verification email | **Done (initial)** | `App\Notifications\QueuedVerifyEmail`. |
| Password hashing | **Existing** | `'password' => 'hashed'` cast on `User`. |
| Session cookie | **Done (initial)** | `config/session.php` defaults `secure` to true when not `local` unless `SESSION_SECURE_COOKIE` overrides. |
| RBAC / audit on all endpoints | **Planned** | Today: `role` + `admin` middleware. **Follow-up:** `spatie/laravel-permission` or a slim `permissions` table + policy classes; middleware to append audit lines to `audit` log channel for mutating routes. |

## 5. UI/UX

| Topic | Status | Notes |
|--------|--------|--------|
| Dashboard nav | **Done (initial)** | Sidebar: Welcome Credits, Feedback. |
| Full refactor | **Planned** | Shared layout components, design tokens, mobile audit — incremental. |

## 6. Feedback

| Topic | Status | Notes |
|--------|--------|--------|
| Form + DB + email | **Done (initial)** | `user_feedbacks`, `FeedbackController`, queued `UserFeedbackSubmittedMail`. Admin recipient: `Setting::get('admin_feedback_email', config('mail.from.address'))`. |

**Follow-up:** Add `admin_feedback_email` to Horizon settings matrix if you want it editable without `.env`.

## 7. Logging and monitoring

| Topic | Status | Notes |
|--------|--------|--------|
| Central channels | **Done (initial)** | `queue`, `mail`, `audit` daily logs under `storage/logs/`. |
| Critical error alerts | **Planned** | Wire `LOG_SLACK_WEBHOOK_URL` or `reportable` exceptions + notification. |

## 8. Authentication flow copy

| Topic | Status | Notes |
|--------|--------|--------|
| “Request Access” → “Sign Up” | **Done (initial)** | Login page link. |
| Confirm password | **Done (initial)** | `RegisterUserRequest` + register form. |
| Welcome CRS message | **Done (initial)** | `UserRegistrationService` flash text. |

## 9. Payments and subscriptions

| Topic | Status | Notes |
|--------|--------|--------|
| Fawaterk / packages | **Existing** | `PaymentController`, `PaymentFulfillmentService`. |
| Invoices | **Existing** | `Invoice` model on fulfillment. |
| Reliability hardening | **Planned** | Idempotent webhooks, explicit state machine, dead-letter queue for payment jobs. |

## 10. Financial tracking

| Topic | Status | Notes |
|--------|--------|--------|
| Wallet `transactions` | **Existing** | Deposits / withdrawals tied to `wallets`. |
| Unified ledger | **Done (initial)** | `financial_ledger_entries` + admin **Financial Ledger** (`admin.horizon.ledger.index`). |

## 11. Email debugging

| Topic | Status | Notes |
|--------|--------|--------|
| Forgot password stub | **Fixed** | `AuthController::sendResetLink` uses `Password::sendResetLink`. |
| Queued reset | **Done (initial)** | `App\Notifications\QueuedResetPassword`. |
| Mail log channel | **Done (initial)** | Log mail failures in listeners (**follow-up**). |

## 12. Code quality

| Topic | Status | Notes |
|--------|--------|--------|
| Service layer | **Done (initial)** | `ToolCreditConsumptionService`, standardized `App\Http\Responses\ApiResponse` for JSON APIs. |
| SOLID / repositories | **Planned** | Introduce repositories where query duplication grows (e.g. ledger, coupons). |

---

### Operations checklist (production)

1. `QUEUE_CONNECTION=redis`, run Horizon **or** Supervisor workers.
2. `php artisan queue:failed-table` already migrated; monitor `failed_jobs`.
3. Rotate logs; ship `storage/logs/*.log` to your aggregator if needed.
4. Set `SESSION_SECURE_COOKIE=true`, `APP_URL` with `https`, and trusted proxies (already in `bootstrap/app.php`).
