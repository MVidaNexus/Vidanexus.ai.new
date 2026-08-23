<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | VidaNexus AI</title>
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
                            cyan: '#00A8E6',
                            purple: '#7000ff',
                        },
                        glass: {
                            bg: 'rgba(255, 255, 255, 0.03)',
                            border: 'rgba(255, 255, 255, 0.1)',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }
        .text-neon-cyan { color: #00A8E6; text-shadow: 0 0 10px rgba(0, 168, 230, 0.5); }
        .bg-neon-cyan { background-color: #00A8E6; box-shadow: 0 0 20px rgba(0, 168, 230, 0.4); }
    </style>
    @stack('styles')
</head>
<body class="bg-[#0d0e12] text-white font-sans selection:bg-primary-cyan/30 selection:text-white">
    
    @include('partials.header')

    <main class="pt-32 pb-20 min-h-screen px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        @yield('content')

        <div class="mt-24 flex justify-center pb-12">
            <a href="{{ route('home') }}" class="group relative px-8 py-3.5 flex items-center gap-3 overflow-hidden rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl transition-all hover:border-primary-cyan/50 hover:shadow-[0_0_30px_rgba(0, 168, 230,0.15)] active:scale-95">
                <i class="fas fa-th-large text-primary-cyan group-hover:scale-110 transition-transform"></i>
                <span class="relative font-bold text-sm tracking-tight text-gray-300 group-hover:text-white transition-colors uppercase">Back to all tools</span>
                <i class="fas fa-arrow-left text-[10px] text-gray-500 group-hover:-translate-x-1 transition-transform"></i>
            </a>
        </div>
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
