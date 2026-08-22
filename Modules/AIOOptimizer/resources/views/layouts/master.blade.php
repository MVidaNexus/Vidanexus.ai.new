<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | VidaNexus AI</title>
    <meta name="description" content="AIO Optimizer — Maximize your content visibility in Google AI Overviews with NLP-powered gap analysis.">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            cyan: 'var(--primary-cyan, #0ea5e9)',
                            purple: 'var(--neon-purple, #7000ff)',
                        },
                        glass: {
                            bg: 'var(--glass-bg)',
                            border: 'var(--glass-border)',
                        }
                    },
                    fontFamily: {
                        inter: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

        <style>
        body {
            background-color: var(--bg-color, #0d0e12);
            color: var(--text-main, #ffffff);
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
            transition: var(--theme-transition);
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur, blur(10px));
            border: 1px solid var(--glass-border);
            border-radius: 24px;
        }

        [x-cloak] { display: none !important; }

        /* Light mode overrides for Tailwind hardcoded dark classes */
        html[data-theme="light"] .text-white { color: var(--text-main) !important; }
        html[data-theme="light"] .text-gray-200 { color: #64748b !important; }
        html[data-theme="light"] .text-gray-300 { color: #475569 !important; }
        html[data-theme="light"] .text-gray-400 { color: #334155 !important; }
        html[data-theme="light"] .text-gray-500 { color: #1e293b !important; }
        
        html[data-theme="light"] .bg-white\/5,
        html[data-theme="light"] .bg-white\/10,
        html[data-theme="light"] .bg-white\/3,
        html[data-theme="light"] .bg-black\/5 {
            background-color: rgba(15, 23, 42, 0.04) !important;
        }
        
        html[data-theme="light"] .border-white\/5,
        html[data-theme="light"] .border-white\/10,
        html[data-theme="light"] .border-black\/10 {
            border-color: rgba(15, 23, 42, 0.08) !important;
        }

        html[data-theme="light"] .bg-purple-500\/10,
        html[data-theme="light"] .bg-purple-500\/5,
        html[data-theme="light"] .bg-purple-600\/10 {
            background-color: rgba(147, 51, 234, 0.1) !important;
        }
        
        html[data-theme="light"] .text-purple-400 { color: #7e22ce !important; }
        html[data-theme="light"] .border-purple-500\/30 { border-color: rgba(126, 34, 206, 0.2) !important; }

        html[data-theme="light"] .bg-emerald-500\/10 { background-color: rgba(16, 185, 129, 0.1) !important; }
        html[data-theme="light"] .text-emerald-500 { color: #059669 !important; }

        html[data-theme="light"] .bg-red-500\/10 { background-color: rgba(239, 68, 68, 0.1) !important; }
        html[data-theme="light"] .text-red-400, 
        html[data-theme="light"] .text-red-500 { color: #dc2626 !important; }

        html[data-theme="light"] .hover\:text-white:hover { color: var(--text-main) !important; }
        
        html[data-theme="light"] .btn-back-tools {
            background: rgba(15, 23, 42, 0.05);
            border-color: rgba(15, 23, 42, 0.1);
            color: #1e293b;
        }
        html[data-theme="light"] .btn-back-tools span {
            color: #1e293b !important;
        }
    </style>
    @stack('styles')
</head>
<body style="background-color: var(--bg-color); color: var(--text-main);">
    <canvas id="techCanvas" class="fixed inset-0 -z-10 opacity-30 pointer-events-none"></canvas>

    @include('partials.header')

    <main class="pt-32 pb-20 min-h-screen px-4 sm:px-6 lg:px-8">
        @yield('content')

                {{-- Back to all tools button --}}
        <div class="mt-24 flex justify-center pb-12">
            <a href="{{ route('home') }}" class="btn-back-tools group relative px-8 py-3.5 flex items-center gap-3 overflow-hidden rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl transition-all hover:border-primary-cyan/50 hover:shadow-[0_0_30px_rgba(14, 165, 233,0.15)] active:scale-95">
                <i class="fas fa-th-large text-primary-cyan group-hover:scale-110 transition-transform"></i>
                <span class="relative font-black text-sm tracking-tight text-gray-300 group-hover:text-white transition-colors uppercase font-sans">Back to all tools</span>
                <i class="fas fa-arrow-left text-[10px] text-gray-500 group-hover:-translate-x-1 transition-transform"></i>
            </a>
        </div>
    </main>

    @include('partials.footer')

    <script src="{{ asset('script.js?v=15') }}"></script>
    @stack('scripts')
</body>
</html>
