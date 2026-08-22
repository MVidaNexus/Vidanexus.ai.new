@extends('layouts.marketing')

@section('title', 'VidaNexus AI — Set new password')

@push('styles')
    <style>
        .glass-panel { max-width: 450px; padding: 2.5rem; text-align: center; }
        .error-msg { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; }
    </style>
@endpush

@section('content')
    <main class="hero">
        <div class="glass-panel">
            <h2 style="font-size: 1.6rem; color: var(--text-main); margin-bottom: 0.5rem;">Choose a new password</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Use at least 8 characters.</p>

            @if($errors->any())
                <div class="error-msg">
                    @foreach($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" style="text-align: left;">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username">
                    </div>
                </div>
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">New password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Confirm password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="notify-btn" style="width: 100%; justify-content: center;">
                    <span>Update password</span>
                </button>
            </form>

            <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                <a href="/login" style="color: var(--primary-cyan); text-decoration: none;">Back to login</a>
            </div>
        </div>
    </main>
@endsection
