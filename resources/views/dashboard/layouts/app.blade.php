<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VidaNexus Tool')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap for tool-specific styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <style>
        body {
            background-color: var(--bg-color, #0d0e12);
            color: var(--text-main, #ffffff);
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
            transition: var(--theme-transition);
        }
        .main-content {
            padding-top: 120px;
            padding-bottom: 60px;
            min-height: 100vh;
            position: relative;
            z-index: 10;
        }
        [x-cloak] { display: none !important; }
        
        /* Fix Bootstrap colors for dark mode */
        .card { 
            background: var(--glass-bg); 
            border: 1px solid var(--glass-border); 
            color: var(--text-main);
            backdrop-filter: var(--glass-blur);
            border-radius: 16px;
        }
        .text-muted { color: var(--text-muted) !important; }

        .btn-back-tools {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-main);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: var(--glass-blur);
        }

        .btn-back-tools:hover {
            border-color: var(--primary-cyan);
            background: rgba(var(--primary-cyan), 0.1);
            color: var(--primary-cyan);
            transform: translateY(-2px);
        }

        #techCanvas {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }

        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.1;
            z-index: 1;
            pointer-events: none;
        }
        .orb-1 { width: 400px; height: 400px; background: var(--accent-cyan); top: -100px; right: -100px; }
        .orb-2 { width: 500px; height: 500px; background: var(--primary); bottom: -150px; left: -150px; }
    </style>
    @stack('styles')
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    @include('partials.header')

    <main class="main-content container mt-5">
        @yield('content')
        
        {{-- Back to all tools button --}}
        <div class="mt-5 text-center">
            <a href="{{ route('home') }}" class="btn-back-tools">
                <i class="fas fa-th-large" style="color: var(--primary-cyan);"></i> 
                <span>Back to all tools</span>
            </a>
        </div>
    </main>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('script.js?v=15') }}"></script>
    @stack('scripts')
</body>
</html>
