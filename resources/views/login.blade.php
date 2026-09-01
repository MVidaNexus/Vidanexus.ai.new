@extends('layouts.marketing')

@section('title', 'Sign In | VidaNexus AI')

@push('meta')
    <meta name="description" content="Sign in to your VidaNexus AI account to access your tools, credits, and live radars.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Sign In | VidaNexus AI">
    <meta property="og:description" content="Sign in to your VidaNexus AI account to access your tools, credits, and live radars.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@section('content')
        <main class="hero">

            <div class="glass-panel" style="max-width: 450px; padding: 3rem;">
                <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 2rem; color: var(--text-main);">Welcome Back</h2>

                @if(session('status'))
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #00A58B; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.9rem;">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="/login" method="POST" style="text-align: left;">
                    @csrf
                    @if(request()->has('redirect'))
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                    @endif
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
                        </div>
                        @error('email')
                            <div style="color: #ff4b4b; font-size: 0.8rem; margin-top: 0.5rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label style="color: var(--text-muted); font-size: 0.9rem;">Security Key</label>
                            <a href="{{ route('password.request') }}" style="color: var(--primary-cyan); font-size: 0.8rem; text-decoration: none; opacity: 0.8;">Forgot Password?</a>
                        </div>
                        <div class="input-wrapper" style="position: relative;">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="loginPassword" name="password" placeholder="••••••••" required style="padding-right: 2.5rem;">
                            <i class="fas fa-eye" id="toggleLoginPassword" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'" onclick="togglePassword('loginPassword', 'toggleLoginPassword')"></i>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-muted); font-size: 0.85rem; user-select: none;">
                            <input type="checkbox" name="remember" value="1" checked style="accent-color: var(--primary-cyan); width: 16px; height: 16px; border-radius: 4px; cursor: pointer;">
                            <span>Keep me signed in</span>
                        </label>
                    </div>

                    <button type="submit" class="notify-btn" style="width: 100%; justify-content: center;">
                        <span>Authenticate Access</span>
                        <i class="fas fa-shield-alt"></i>
                    </button>
                </form>

                <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                    Don't have an account? <a href="/register{{ request()->has('redirect') ? '?redirect=' . request('redirect') : '' }}" style="color: var(--primary-cyan); text-decoration: none;">Sign Up</a>
                </div>
            </div>
        </main>
@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endpush
