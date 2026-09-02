<!-- VidaNexus Official AI Emblem Favicons & PWA Standards -->
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon-512x512.png?v=' . (file_exists(public_path('favicon-512x512.png')) ? filemtime(public_path('favicon-512x512.png')) : '2026_fav')) }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png?v=' . (file_exists(public_path('favicon-192x192.png')) ? filemtime(public_path('favicon-192x192.png')) : '2026_fav')) }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon-64x64.png?v=' . (file_exists(public_path('favicon-64x64.png')) ? filemtime(public_path('favicon-64x64.png')) : '2026_fav')) }}">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png?v=' . (file_exists(public_path('favicon-48x48.png')) ? filemtime(public_path('favicon-48x48.png')) : '2026_fav')) }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=' . (file_exists(public_path('favicon-32x32.png')) ? filemtime(public_path('favicon-32x32.png')) : '2026_fav')) }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png?v=' . (file_exists(public_path('favicon-16x16.png')) ? filemtime(public_path('favicon-16x16.png')) : '2026_fav')) }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png?v=' . (file_exists(public_path('apple-touch-icon.png')) ? filemtime(public_path('apple-touch-icon.png')) : '2026_fav')) }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico?v=' . (file_exists(public_path('favicon.ico')) ? filemtime(public_path('favicon.ico')) : '2026_fav')) }}">
<link rel="icon" type="image/png" href="{{ asset('favicon.png?v=' . (file_exists(public_path('favicon.png')) ? filemtime(public_path('favicon.png')) : '2026_fav')) }}">

<!-- PWA Web Manifest & App Capabilities -->
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#030712">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="VidaNexus">
<meta name="application-name" content="VidaNexus AI">
<meta name="msapplication-TileColor" content="#030712">
<meta name="msapplication-TileImage" content="{{ asset('favicon-512x512.png?v=2026_fav') }}">

<!-- PWA Service Worker & Install Script -->
<script defer src="{{ asset('js/pwa.js') }}?v=1.0.0"></script>
