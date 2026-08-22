<!DOCTYPE html>
<html lang="@yield('html_lang', 'en')" dir="@yield('html_dir', 'ltr')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'VidaNexus AI')</title>
    @stack('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    @include('partials.theme-init')
    @stack('head')
    @stack('styles')
</head>
<body>
    @hasSection('body_decor')
        @yield('body_decor')
    @else
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    @endif

    <div class="main-container">
        @include('partials.header')
        @yield('content')
        @stack('after_main')
    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
    @stack('scripts')
</body>
</html>
