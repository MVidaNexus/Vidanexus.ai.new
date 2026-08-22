<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    @include('partials.theme-init')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Dashboard — Vida Nexus</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Core Styles -->
    <link rel="stylesheet" href="{{ asset('style.v2.css') }}?v={{ config('vidanexus.style_css_version') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    @include('dashboard.partials.styles')
</head>
<body>
    <canvas id="techCanvas"></canvas>
    <div class="glow-orb orb-1"></div>
    <div class="glow-orb orb-2"></div>

    <div class="main-container">
        @include('partials.header')

        <div class="dashboard-container">
            @include('dashboard.partials.sidebar')

            <div class="dash-content">
                @include('dashboard.partials.flash-alerts')
                @include('dashboard.partials.stats-row')
                @include('dashboard.partials.panel-overview')
                @include('dashboard.partials.panel-tools')
                @include('dashboard.partials.panel-billing')
                @include('dashboard.partials.panel-welcome-credits')
                @include('dashboard.partials.panel-feedback')
                @include('dashboard.partials.panel-settings')
            </div>
        </div>

        @include('partials.footer')
    </div>
    <script src="{{ asset('script.js?v=14') }}"></script>
    @include('dashboard.partials.scripts')
</body>
</html>
