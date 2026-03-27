<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Rules\NotDisposableEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->has('redirect')) {
            session(['url.intended' => $request->redirect]);
        }
        return view('login');
    }

    public function showRegister(Request $request)
    {
        if ($request->has('redirect')) {
            session(['url.intended' => $request->redirect]);
        }
        return view('register');
    }

    public function register(Request $request)
    {
        // ─── Validation: Disposable email blocking + DNS MX check + Phone format ───
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users', new NotDisposableEmail],
            'dial_code'     => 'required|string|regex:/^\+\d{1,4}$/',
            'phone'         => 'required|string|regex:/^[0-9]{7,15}$/',
            'country'       => 'required|string|max:100',
            'password'      => 'required|string|min:8',
            'selected_plan' => 'nullable|string|in:beginner,starter,growth,pro,ultimate',
        ], [
            'email.email'        => 'Please provide a valid email address with a real mail server.',
            'dial_code.regex'    => 'Invalid country dialing code format.',
            'phone.regex'        => 'Phone number must be between 7 and 15 digits.',
        ]);

        // Build the full international phone number
        $dialCode = $request->input('dial_code', '+20');
        $rawPhone = ltrim($request->input('phone'), '0'); // strip leading zero
        $fullPhone = $dialCode . $rawPhone;

        $selectedPlan = $request->input('selected_plan', 'beginner');

        // If a paid plan was selected, store registration data in session
        // and redirect to payment WITHOUT creating the account yet
        if ($selectedPlan !== 'beginner') {
            session([
                'pending_registration' => [
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'phone'    => $fullPhone,
                    'country'  => $request->country,
                    'password' => Hash::make($request->password),
                    'plan'     => $selectedPlan,
                ],
            ]);

            return redirect('/payment?type=plan&id=' . $selectedPlan . '&new_account=1')
                ->with('info', 'Complete your payment to create your account and activate the ' . ucfirst($selectedPlan) . ' plan.');
        }

        // Beginner (free) — create account immediately
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $fullPhone,
            'country'           => $request->country,
            'password'          => Hash::make($request->password),
            'role'              => 'user',
            'subscription_tier' => 'beginner',
        ]);

        $beginnerCredits = (float) \App\Models\Setting::get('plan_credits_beginner', 0.00);

        Wallet::create([
            'id'              => (string) Str::uuid(),
            'user_id'         => $user->id,
            'balance_credits' => $beginnerCredits,
        ]);

        // Handle Verification logic based on global setting
        $isVerificationEnabled = (bool) \App\Models\Setting::get('global_email_verification', true);

        if ($isVerificationEnabled) {
            // Send email verification notification
            $user->sendEmailVerificationNotification();
            
            // Log the user in (but they'll be redirected to verify email)
            Auth::login($user);

            return redirect()->route('verification.notice')
                ->with('success', 'Welcome to VidaNexus! Please verify your email address to activate your account.');
        } else {
            // Automatically verify if global toggle is OFF
            $user->markEmailAsVerified();
            
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome to VidaNexus AI! Your account is active and ready.');
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // If user hasn't verified email, redirect to verification page
            if (!Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        // Check if user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => "We couldn't find a user with that email address."]);
        }

        // For now, since we don't have mail configured, we'll show a message to contact support
        return back()->with('status', "Hello! We've received your password recovery request. Please contact technical support via WhatsApp for now to expedite the process, or check your email later (activation pending).");
    }
}
