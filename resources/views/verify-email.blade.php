<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidaNexus AI — Verify Your Email</title>
    <meta name="description" content="Verify your email address to activate your VidaNexus AI account.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    
    <style>
        .verify-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .verify-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .verify-icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(14,165,233,0.15), rgba(6,182,212,0.15));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse-glow 2s ease-in-out infinite;
        }
        .verify-icon-wrapper i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(14,165,233,0.15); }
            50% { box-shadow: 0 0 40px rgba(14,165,233,0.3); }
        }
        .verify-title {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            color: var(--text-main);
            margin-bottom: 0.75rem;
        }
        .verify-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .verify-email-highlight {
            color: var(--primary-cyan);
            font-weight: 600;
        }
        .verify-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 12px;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
        }
        .verify-btn-primary {
            background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal));
            color: #fff;
        }
        .verify-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14,165,233,0.3);
        }
        .verify-btn-secondary {
            background: rgba(255,255,255,0.05);
            color: var(--text-muted);
            border: 1px solid var(--glass-border);
            margin-top: 0.75rem;
        }
        .verify-btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text-main);
        }
        .verify-success {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #22c55e;
            font-size: 0.95rem;
        }
        .verify-divider {
            border: none;
            border-top: 1px solid var(--glass-border);
            margin: 1.5rem 0;
        }
        .verify-footer {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.6;
        }
    </style>
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="main-container">
        @include('partials.header')

        <div class="verify-container">
            <div class="verify-card">
                <div class="verify-icon-wrapper">
                    <i class="fas fa-envelope-circle-check"></i>
                </div>

                <h1 class="verify-title">Verify Your Email</h1>
                
                <p class="verify-subtitle">
                    We've sent a verification link to
                    <br>
                    <span class="verify-email-highlight">{{ Auth::user()->email }}</span>
                    <br><br>
                    Click the link in your inbox to activate your VidaNexus AI account and unlock your free credits.
                </p>

                @if (session('status') === 'verification-link-sent' || session('resent'))
                    <div class="verify-success">
                        <i class="fas fa-check-circle"></i>
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="verify-btn verify-btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Resend Verification Email
                    </button>
                </form>

                <a href="/" class="verify-btn verify-btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>

                <hr class="verify-divider">

                <div class="verify-footer">
                    <i class="fas fa-info-circle"></i>
                    Didn't receive the email? Check your spam folder or contact support at
                    <a href="mailto:support@vidanexus.ai" style="color: var(--primary-cyan);">support@vidanexus.ai</a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
