<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(ResetPasswordRequest $request, PasswordResetService $passwordReset): RedirectResponse
    {
        $result = $passwordReset->resetPassword($request);

        if ($result['status'] === 'success') {
            return redirect()->route('login')->with('status', $result['message']);
        }

        return back()
            ->withErrors(['email' => $result['message']])
            ->withInput($request->only('email'));
    }
}
