<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect('/dashboard')->with('success', 'Email verified successfully! Welcome to VidaNexus AI.');
    }

    public function send(Request $request): RedirectResponse
    {
        try {
            $request->user()->sendEmailVerificationNotification();

            return back()->with('resent', true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resend verification email: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
            ]);

            return back()->with('error', 'Unable to deliver verification email at the moment. Please try again shortly or contact support.');
        }
    }
}
