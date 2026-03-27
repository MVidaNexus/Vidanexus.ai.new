<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tool['meta_title'] }}</title>
    <meta name="description" content="{{ $tool['meta_desc'] }}">
    <!-- OpenGraph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $tool['meta_title'] }}">
    <meta property="og:description" content="{{ $tool['meta_desc'] }}">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <style>
        .tool-hero {
            position: relative;
            padding: 4rem 1.5rem 2rem;
            text-align: center;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .tool-icon-large {
            font-size: 5rem;
            color: {{ $tool['color'] }};
            margin-bottom: 2rem;
            filter: drop-shadow(0 0 30px {{ $tool['color'] }}66);
            animation: float 6s ease-in-out infinite;
        }
        .tool-title {
            font-family: var(--font-heading);
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            background: linear-gradient(to right, var(--title-color), {{ $tool['color'] }});
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px var(--title-glow);
            color: var(--title-color); /* Fallback for browsers that don't support text-fill-color */
            display: block;
        }
        .tool-tagline {
            font-size: clamp(1.1rem, 3vw, 1.5rem);
            color: var(--text-muted);
            max-width: 800px;
            margin: 0 auto 3rem;
            font-weight: 400;
            line-height: 1.6;
            letter-spacing: 0.05em;
        }
        .tool-description {
            font-size: 1rem;
            color: var(--text-main);
            max-width: 900px;
            width: 100%;
            margin: 0 auto 3rem;
            line-height: 1.7;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 2rem 1.5rem;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            border-left: 4px solid {{ $tool['color'] }};
            text-align: left;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 6rem;
            padding: 0 2rem;
        }
        .feature-item {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: left;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        .feature-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, {{ $tool['color'] }}15, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .feature-item:hover {
            transform: translateY(-10px);
            border-color: {{ $tool['color'] }}44;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 20px {{ $tool['color'] }}22;
        }
        .feature-item:hover::before {
            opacity: 1;
        }
        .feature-icon {
            font-size: 2rem;
            color: {{ $tool['color'] }};
            margin-bottom: 1.5rem;
        }
        .feature-title {
            font-size: 1.2rem;
            color: var(--text-main);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .feature-desc {
            color: var(--text-muted); /* brighter than text-muted for dark backgrounds */
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .cta-section {
            text-align: center;
            padding: 4rem 2rem 8rem;
        }
        /* Removed legacy .cta-button styles to use global .vn-btn system */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .orb-special {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, {{ $tool['color'] }} 0%, transparent 70%);
            opacity: 0.15;
            filter: blur(80px);
            z-index: -1;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            pointer-events: none;
        }

        .coming-soon-btn {
            background: var(--card-bg) !important;
            border: 1px solid var(--card-border) !important;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.1), 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            cursor: not-allowed !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            width: 100%;
            border-radius: 16px;
            transition: all 0.3s ease;
            backdrop-filter: var(--glass-blur);
        }
        .coming-soon-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, var(--glass-bg), transparent);
            transform: skewX(-20deg);
            animation: shine 4s ease-in-out infinite;
        }
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }
        .coming-soon-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        .coming-soon-title {
            font-size: 1.3rem;
            color: var(--text-main);
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .coming-soon-sub {
            font-size: 0.8rem;
            color: var(--primary-cyan);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 600;
            opacity: 0.8;
        }
        
        /* Light Theme Overrides for Coming Soon */
        [data-theme="light"] .coming-soon-btn {
            background: rgba(240, 245, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.03), 0 10px 20px rgba(0,0,0,0.03);
        }
        [data-theme="light"] .coming-soon-btn::before {
            background: linear-gradient(to right, transparent, rgba(0,0,0,0.02), transparent);
        }
        [data-theme="light"] .coming-soon-btn .fa-lock {
            color: rgba(0,0,0,0.25) !important;
        }

        .tool-cta-btn {
            font-size: 1.5rem; 
            padding: 1.2rem 4rem;
            min-width: 320px;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .tool-cta-btn {
                font-size: 1.1rem !important;
                padding: 1rem 2rem !important;
                min-width: 100% !important;
            }
            /* CTA buttons now use global responsive classes */
            .tool-icon-large {
                font-size: 3rem;
                margin-bottom: 1rem;
            }
            .tool-hero {
                padding-top: 2rem;
            }
            .feature-item {
                padding: 1.5rem;
            }
            .tool-description {
                padding: 1.5rem 1rem;
            }
        }
    </style>
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
</head>
<body>
    <div id="bg-layer">
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
        <div class="orb-special"></div> <!-- Dynamic Tool Color Orb -->
    </div>

    <div class="main-container">
        @include('partials.header')

        <main>
            <div class="tool-hero">
                <i class="fas {{ $tool['icon'] }} tool-icon-large"></i>
                <h1 class="tool-title">{{ $tool['name'] }}</h1>
                <p class="tool-tagline">{{ $tool['tagline'] }}</p>
                <div class="tool-description">
                    {!! $tool['marketing_content'] ?? nl2br(e($tool['description'])) !!}
                </div>
            </div>

            <div class="features-grid">
                @foreach($tool['features'] as $feature)
                <div class="feature-item">
                    <i class="fas {{ $feature['icon'] }} feature-icon"></i>
                    <h3 class="feature-title">{{ $feature['title'] }}</h3>
                    <p class="feature-desc">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>

            <div class="cta-section">
                @auth
                    @if($isOwned)
                        @php
                            $targetUrl = isset($tool['route']) ? route($tool['route']) : '/dashboard';
                        @endphp
                        <a href="{{ $targetUrl }}" class="vn-btn vn-btn-primary tool-cta-btn">
                            <i class="fas fa-play"></i>
                            <span>Start Using {{ $tool['name'] }}</span>
                        </a>
                    @elseif($isAvailable)
                        <a href="/payment?type=tool&id={{ $tool['slug'] }}" class="vn-btn tool-cta-btn" style="background: linear-gradient(135deg, #a855f7, #6366f1); color: white; border: none;">
                            <i class="fas fa-unlock-alt"></i>
                            <span>Get Full Access for {{ number_format($tool['unlock_price']) }} EGP</span>
                        </a>
                    @else
                        <div class="coming-soon-btn tool-cta-btn">
                            <i class="fas fa-lock" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                            <div class="coming-soon-text">
                                <span class="coming-soon-title">Module Locked</span>
                                <span class="coming-soon-sub">In Active Development</span>
                            </div>
                        </div>
                    @endif
                @else
                    <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="vn-btn vn-btn-primary tool-cta-btn">
                        <span>Login to Access</span>
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                @endauth
            </div>
        </main>

        @include('partials.footer')

    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
