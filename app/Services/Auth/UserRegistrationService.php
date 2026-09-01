<?php

namespace App\Services\Auth;

use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\FinancialLedgerEntry;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserTool;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserRegistrationService
{
    public function register(RegisterUserRequest $request): RedirectResponse
    {
        // `phone_e164` is normalized + validated in RegisterUserRequest::prepareForValidation()
        // (see App\Support\PhoneNumber). Falls back to manual concatenation
        // for backward compatibility with any legacy callers that bypass the
        // FormRequest's prepareForValidation hook.
        $fullPhone = $request->input('phone_e164')
            ?? ($request->input('dial_code', '+20').ltrim((string) $request->input('phone'), '0'));

        $selectedPlan = $request->input('selected_plan', 'beginner');

        if ($selectedPlan !== 'beginner') {
            $packageId = $this->subscriptionPlanToPackageId($selectedPlan);
            session([
                'pending_registration' => [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $fullPhone,
                    'country' => $request->input('country'),
                    'password' => Hash::make($request->input('password')),
                    'plan' => $selectedPlan,
                ],
            ]);

            return redirect('/payment?type=package&id='.$packageId.'&new_account=1')
                ->with('info', 'Complete your payment to create your account and activate the '.ucfirst($selectedPlan).' plan.');
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $fullPhone,
            'country' => $request->input('country'),
            'password' => Hash::make($request->input('password')),
            'role' => 'user',
            'subscription_tier' => 'beginner',
        ]);

        $beginnerCredits = (float) Setting::get('plan_credits_beginner', 0.00);

        Wallet::create([
            'id'              => (string) Str::uuid(),
            'user_id'         => $user->id,
            'balance_credits' => $beginnerCredits,
        ]);

        if ($beginnerCredits > 0) {
            FinancialLedgerEntry::create([
                'user_id' => $user->id,
                'event_type' => 'registration_welcome',
                'wallet_delta' => (int) round($beginnerCredits),
                'bonus_delta' => 0,
                'meta' => ['plan' => 'beginner', 'source' => 'plan_credits_beginner'],
            ]);
        }

        // Auto-grant any trial tools configured by the admin
        $this->grantTrialTools($user);

        // Send automated transactional Welcome Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeNewUserMail($user, $beginnerCredits));
        } catch (\Throwable $e) {
            Log::warning('Failed sending welcome email: ' . $e->getMessage(), ['user_id' => $user->id]);
        }

        $isVerificationEnabled = (bool) Setting::get('global_email_verification', true);

        if ($isVerificationEnabled) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::error('Failed sending verification email on registration: ' . $e->getMessage(), ['user_id' => $user->id]);
            }

            Auth::login($user);

            $crsMsg = $beginnerCredits > 0
                ? ' You have received '.number_format($beginnerCredits).' welcome CRS in your wallet.'
                : '';

            return redirect()->route('verification.notice')
                ->with('success', 'Welcome to VidaNexus! Please verify your email address to activate your account.'.$crsMsg);
        }

        $user->markEmailAsVerified();
        Auth::login($user);

        $crsMsg = $beginnerCredits > 0
            ? ' You have received '.number_format($beginnerCredits).' welcome CRS in your wallet.'
            : '';

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to VidaNexus AI! Your account is active and ready.'.$crsMsg);
    }

    /**
     * Map signup "subscription tier" labels to credit package keys (see PaymentCatalogService).
     */
    private function subscriptionPlanToPackageId(string $plan): string
    {
        return match ($plan) {
            'starter'  => 'lite',
            'growth'   => 'standard',
            'pro'      => 'pro',
            'ultimate' => 'enterprise',
            default    => 'lite',
        };
    }

    /**
     * Auto-grant pre-configured trial tools to a newly registered user.
     * Tools are determined by the admin via Setting('trial_tool_{slug}').
     * Access expires naturally when welcome credits run out (no hard expiry date).
     */
    private function grantTrialTools(User $user): void
    {
        $tools = config('tools.all_tools', []);

        // Reload fresh wallet reference for transaction linking
        $user->load('wallet');

        foreach ($tools as $tool) {
            $slug = $tool['slug'] ?? null;
            if (!$slug) {
                continue;
            }

            if (!(bool) Setting::get("trial_tool_{$slug}", false)) {
                continue;
            }

            // Create the UserTool record (grants ownership, price = 0, no expiry)
            UserTool::create([
                'user_id' => $user->id,
                'tool_slug' => $slug,
                'price_paid' => 0.00,
                'bonus_credits' => 0,
                'allow_bonus_for_ai_usage' => true,
                'expires_at' => null,
                'renews_at' => null,
                'auto_renew' => false,
            ]);

            FinancialLedgerEntry::create([
                'user_id' => $user->id,
                'event_type' => 'trial_tool_grant',
                'wallet_delta' => 0,
                'bonus_delta' => 0,
                'tool_slug' => $slug,
                'reference' => 'TRIAL_'.$slug.'_'.$user->id,
                'meta' => ['tool_name' => $tool['name'] ?? $slug],
            ]);

            Log::info('Trial tool granted', [
                'user_id' => $user->id,
                'tool'    => $slug,
            ]);
        }
    }
}
