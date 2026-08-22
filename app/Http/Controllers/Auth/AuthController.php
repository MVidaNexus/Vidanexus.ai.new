<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function register(RegisterUserRequest $request, UserRegistrationService $registration): \Illuminate\Http\RedirectResponse
    {
        return $registration->register($request);
    }

    public function login(LoginUserRequest $request): \Illuminate\Http\RedirectResponse
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            if (! Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
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

    public function sendResetLink(
        ForgotPasswordRequest $request,
        PasswordResetService $passwordReset
    ): \Illuminate\Http\RedirectResponse {
        $result = $passwordReset->sendResetLink($request);

        return $passwordReset->toRedirect($result);
    }
}
