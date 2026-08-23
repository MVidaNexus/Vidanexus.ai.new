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
            background-color: var(--bg-color, #0d0e12) !important;
            color: var(--text-main, #ffffff) !important;
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
        
        /* High Contrast Dark Mode Typography */
        h1, h2, h3, h4, h5, h6 {
            color: var(--text-main, #ffffff) !important;
        }

        p, span, label, div {
            color: inherit;
        }

        .text-muted { color: var(--text-muted, #94a3b8) !important; }

        /* Bootstrap Card & Components Overrides */
        .card { 
            background: var(--glass-bg, rgba(17, 24, 39, 0.75)) !important; 
            border: 1px solid var(--glass-border, rgba(255, 255, 255, 0.1)) !important; 
            color: var(--text-main, #ffffff) !important;
            backdrop-filter: var(--glass-blur, blur(20px)) !important;
            border-radius: 20px !important;
        }

        /* Generic Tool Inputs & Textareas */
        .premium-generic-input,
        .form-control,
        .form-select,
        textarea,
        input[type="text"],
        input[type="search"],
        input[type="number"],
        input[type="email"] {
            width: 100% !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 1rem !important;
            padding: 1rem 1.5rem !important;
            color: #ffffff !important;
            transition: all 0.3s ease !important;
            font-size: 1rem !important;
        }

        .premium-generic-input::placeholder,
        .form-control::placeholder,
        textarea::placeholder,
        input::placeholder {
            color: rgba(255, 255, 255, 0.45) !important;
        }

        .premium-generic-input:focus,
        .form-control:focus,
        .form-select:focus,
        textarea:focus,
        input:focus {
            outline: none !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(0, 168, 230, 0.7) !important;
            box-shadow: 0 0 25px rgba(0, 168, 230, 0.25) !important;
            color: #ffffff !important;
        }

        .premium-generic-output {
            background: rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 1rem !important;
            padding: 1.75rem !important;
            color: #f8fafc !important;
            font-weight: 500 !important;
            white-space: pre-wrap !important;
            line-height: 1.7 !important;
            font-size: 1.05rem !important;
        }

        .premium-generic-feature {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 1rem !important;
            transition: all 0.3s ease !important;
        }

        .premium-generic-feature:hover {
            border-color: rgba(0, 168, 230, 0.4) !important;
            background: rgba(255, 255, 255, 0.07) !important;
            transform: translateY(-3px) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        }

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
