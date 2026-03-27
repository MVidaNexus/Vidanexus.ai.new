<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidaNexus AI — Secure Login</title>
    <meta name="description" content="Access your VidaNexus AI dashboard. Secure, planetary-scale intelligent automation at your fingertips.">
    
    <!-- OpenGraph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="VidaNexus AI — Secure Login">
    <meta property="og:description" content="Access your VidaNexus AI dashboard. Secure, planetary-scale intelligent automation at your fingertips.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>
    <div class="glow-orb orb-3"></div>

    <div class="main-container">
        @include('partials.header')

        <main class="hero">

            <div class="glass-panel" style="max-width: 450px; padding: 3rem;">
                <h2 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 2rem; color: var(--text-main);">Authorized Access</h2>
                
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

                    <button type="submit" class="notify-btn" style="width: 100%; justify-content: center;">
                        <span>Authenticate Access</span>
                        <i class="fas fa-shield-alt"></i>
                    </button>
                </form>

                <div style="margin-top: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                    Don't have an account? <a href="/register{{ request()->has('redirect') ? '?redirect=' . request('redirect') : '' }}" style="color: var(--primary-cyan); text-decoration: none;">Request Access</a>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
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
</body>
</html>
