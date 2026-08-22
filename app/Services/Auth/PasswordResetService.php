<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Centralized password recovery service.
 *
 * Responsibilities:
 *  - Issue password-reset links via the standard Laravel broker.
 *  - Persist + verify reset tokens (Laravel default: `password_reset_tokens` table).
 *  - Log every step (request, email dispatch, success, failure) so QA can trace
 *    why a user "didn't receive" or "couldn't use" a reset link.
 *  - Map raw broker statuses to user-facing messages.
 *
 * Notes on email delivery:
 *  - Notifications use the queue (App\Notifications\QueuedResetPassword).
 *  - When QUEUE_CONNECTION=sync (testing) emails fire inline.
 *  - In production, `php artisan queue:work --queue=emails,default` MUST run.
 *    Otherwise the reset email stays in the `jobs` table forever — a common
 *    root cause of "forgot password is broken".
 */
class PasswordResetService
{
    /**
     * Map broker status → user-friendly message.
     *
     * Laravel's translation files normally do this; we centralize it here so
     * the wording is consistent regardless of locale completeness.
     */
    protected const STATUS_MESSAGES = [
        Password::RESET_LINK_SENT => 'A password reset link has been emailed to you. Please check your inbox (and spam folder).',
        Password::PASSWORD_RESET => 'Your password has been reset. You can now sign in with the new password.',
        Password::INVALID_USER => 'We could not find a user with that email address.',
        Password::INVALID_TOKEN => 'This password reset link is invalid or has already been used. Please request a new one.',
        Password::RESET_THROTTLED => 'Please wait a moment before requesting another reset link.',
    ];

    /**
     * Send the password reset link.
     *
     * @return array{status:string, message:string, broker_status:string}
     */
    public function sendResetLink(ForgotPasswordRequest $request): array
    {
        $email = (string) $request->validated('email');

        Log::channel('mail')->info('password_reset.request', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        try {
            $brokerStatus = Password::sendResetLink(['email' => $email]);
        } catch (\Throwable $e) {
            // SMTP / queue / driver errors should never bubble to the user as a 500.
            Log::channel('mail')->error('password_reset.dispatch_failed', [
                'email' => $email,
                'exception' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'We could not send the reset email right now. Please try again in a moment.',
                'broker_status' => 'dispatch_failed',
            ];
        }

        if ($brokerStatus === Password::RESET_LINK_SENT) {
            Log::channel('mail')->info('password_reset.link_dispatched', [
                'email' => $email,
            ]);

            return [
                'status' => 'success',
                'message' => self::STATUS_MESSAGES[$brokerStatus] ?? __($brokerStatus),
                'broker_status' => $brokerStatus,
            ];
        }

        Log::channel('mail')->warning('password_reset.link_not_sent', [
            'email' => $email,
            'broker_status' => $brokerStatus,
        ]);

        return [
            'status' => 'error',
            'message' => self::STATUS_MESSAGES[$brokerStatus] ?? __($brokerStatus),
            'broker_status' => $brokerStatus,
        ];
    }

    /**
     * Reset the password for the user identified by the request.
     *
     * @return array{status:string, message:string, broker_status:string}
     */
    public function resetPassword(Request $request): array
    {
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        Log::channel('mail')->info('password_reset.attempt', [
            'email' => $credentials['email'] ?? null,
            'ip' => $request->ip(),
        ]);

        $brokerStatus = Password::reset(
            $credentials,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Log::channel('mail')->info('password_reset.success', [
                    'user_id' => $user->getKey(),
                    'email' => $user->email,
                ]);
            }
        );

        if ($brokerStatus === Password::PASSWORD_RESET) {
            return [
                'status' => 'success',
                'message' => self::STATUS_MESSAGES[$brokerStatus] ?? __($brokerStatus),
                'broker_status' => $brokerStatus,
            ];
        }

        Log::channel('mail')->warning('password_reset.failed', [
            'email' => $credentials['email'] ?? null,
            'broker_status' => $brokerStatus,
        ]);

        return [
            'status' => 'error',
            'message' => self::STATUS_MESSAGES[$brokerStatus] ?? __($brokerStatus),
            'broker_status' => $brokerStatus,
        ];
    }

    /**
     * Build a redirect for the `sendResetLink` web flow.
     *
     * For security, we deliberately reply with the same success message even
     * when the email isn't on file, to prevent user enumeration. Internally
     * we still record the broker status for analytics.
     */
    public function toRedirect(array $result, bool $hideEnumeration = true): RedirectResponse
    {
        if ($result['status'] === 'success' || ($hideEnumeration && ($result['broker_status'] ?? null) === Password::INVALID_USER)) {
            return back()->with('status', $result['status'] === 'success'
                ? $result['message']
                : self::STATUS_MESSAGES[Password::RESET_LINK_SENT]
            );
        }

        return back()->withErrors(['email' => $result['message']])->withInput(['email' => request('email')]);
    }
}
