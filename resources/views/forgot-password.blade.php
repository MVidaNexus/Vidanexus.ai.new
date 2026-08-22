@extends('layouts.marketing')

@section('title', 'VidaNexus AI — Password Recovery')

@push('styles')
    <style>
        .glass-panel { max-width: 450px; padding: 2.5rem; text-align: center; }
        .success-msg { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #00A58B; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.6; }
        .error-msg { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem; }
    </style>
@endpush

@section('content')
        <main class="hero">
            <div class="glass-panel">
                <div style="margin-bottom: 2rem;">
                    <i class="fas fa-key-skeleton" style="font-size: 3rem; color: var(--primary-cyan); margin-bottom: 1rem;"></i>
                    <h2 style="font-size: 1.8rem; color: var(--text-main); margin-bottom: 0.5rem;">Account Recovery</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Enter your email address and we'll help you get back into your account.</p>
                </div>

                @if(session('status'))
                    <div class="success-msg">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="error-msg">
                        @foreach($errors->all() as $error)
                            <p style="margin: 0;">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" style="text-align: left;">
                    @csrf
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.9rem;">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="notify-btn" style="width: 100%; justify-content: center;">
                        <span>Send Recovery Link</span>
                        <i class="fas fa-paper-plane" style="margin-left: 0.5rem;"></i>
                    </button>
                </form>

                <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                    Remember your password? <a href="/login" style="color: var(--primary-cyan); text-decoration: none;">Secure Login</a>
                </div>
            </div>
        </main>
@endsection
