<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', 'Article Writer') | VidaNexus AI</title>
    @include('partials.favicons')

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-articlewriter', 'resources/assets/sass/app.scss', storage_path('vite.hot')) }} --}}
</head>

<body>
    @yield('content')

            {{-- Back to all tools button --}}
        <div class="mt-24 flex justify-center pb-12">
            <a href="{{ route('home') }}" class="btn-back-tools group relative px-8 py-3.5 flex items-center gap-3 overflow-hidden rounded-2xl bg-white/5 border border-white/10 backdrop-blur-xl transition-all hover:border-primary-cyan/50 hover:shadow-[0_0_30px_rgba(14, 165, 233,0.15)] active:scale-95">
                <i class="fas fa-th-large text-primary-cyan group-hover:scale-110 transition-transform"></i>
                <span class="relative font-black text-sm tracking-tight text-gray-300 group-hover:text-white transition-colors uppercase font-sans">Back to all tools</span>
                <i class="fas fa-arrow-left text-[10px] text-gray-500 group-hover:-translate-x-1 transition-transform"></i>
            </a>
        </div>

    {{-- Vite JS --}}
    {{-- {{ module_vite('build-articlewriter', 'resources/assets/js/app.js', storage_path('vite.hot')) }} --}}
</body>
