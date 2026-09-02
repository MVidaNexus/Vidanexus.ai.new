<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ramadan 2026 Trends') | VidaNexus AI</title>
    @include('partials.favicons')
    <meta name="description" content="Ramadan Series Analytics Dashboard - Comparing Series Performance on WATCH IT and Google Trends">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        night: {
                            100: '#f8fafc',
                            400: '#94a3b8',
                            700: '#1e293b',
                            800: '#0f172a',
                            900: '#0b0e14',
                        },
                        accent: {
                            purple: '#8b5cf6',
                            blue: '#0ea5e9',
                            gold: '#fbbf24',
                        }
                    },
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                        tajawal: ['Tajawal', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
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
</head>

<body style="background-color: var(--bg-deep); color: var(--text-main);">
    <canvas id="techCanvas" class="fixed inset-0 -z-10 opacity-30 pointer-events-none"></canvas>

    @include('partials.header')

    <main class="pt-24 lg:pt-32 pb-20 min-h-screen">
        {{-- High-end Sub Navigation --}}
        <header class="sub-nav-header">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between overflow-x-auto whitespace-nowrap scrollbar-hide">
                    <nav class="flex items-center gap-2">
                        <a href="{{ route('dashboard.drama-trends.index') }}" 
                           class="sub-nav-link {{ request()->routeIs('dashboard.drama-trends.index') ? 'active' : '' }}">
                            <i class="fas fa-home"></i>
                            Home
                        </a>
                        <a href="{{ route('dashboard.drama-trends.report') }}" 
                           class="sub-nav-link {{ request()->routeIs('dashboard.drama-trends.report') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            Analytical Report
                        </a>
                        <a href="{{ route('dashboard.drama-trends.management') }}" 
                           class="sub-nav-link {{ request()->routeIs('dashboard.drama-trends.management') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            Management
                        </a>
                    </nav>

                    <div class="hidden md:flex items-center gap-4">
                        <span class="text-xs font-bold text-accent-purple bg-accent-purple/10 px-3 py-1.5 rounded-full border border-accent-purple/20">
                            <i class="fas fa-bolt mr-1"></i> RAMADAN 2026 PRO
                        </span>
                    </div>
                </div>
            </div>
        </header>

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
    <script>
        // Chart default settings
        function getChartColors() {
            const isDark = document.documentElement.getAttribute("data-theme") !== "light";
            return {
                text: isDark ? 'rgba(255,255,255,0.7)' : 'rgba(15,23,42,0.7)',
                grid: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(15,23,42,0.05)',
                label: isDark ? 'rgba(255,255,255,0.5)' : 'rgba(15,23,42,0.5)'
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
