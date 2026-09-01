@extends('layouts.marketing')

@section('title', 'VidaNexus AI | Next-Gen AI Solutions for Business Growth & Intelligence')

@push('meta')
    <meta name="description" content="VidaNexus empowers modern businesses, enterprises, and creators with autonomous AI engines for market discovery, competitive intelligence, and scalable growth.">
    <meta name="keywords" content="AI Business Solutions, Market Intelligence, AI Automation, Enterprise AI, Business Growth Suite, Competitive Intelligence, Search Intelligence, VidaNexus">
    <meta name="author" content="VidaNexus">
    <link rel="canonical" href="https://vidanexus.ai/">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/">
    <meta property="og:title" content="VidaNexus AI | Next-Gen AI Solutions for Business Growth & Intelligence">
    <meta property="og:description" content="Autonomous AI engines for market discovery, competitive intelligence, and scalable business growth.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="article:published_time" content="2026-01-01T00:00:00+00:00">
    <meta property="article:modified_time" content="{{ date('c') }}">
    <meta property="og:updated_time" content="{{ date('c') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@vidanexus_ai">
    <meta name="twitter:creator" content="@vidanexus_ai">
    <meta name="twitter:url" content="https://vidanexus.ai/">
    <meta name="twitter:title" content="VidaNexus AI | Next-Gen AI Solutions for Business Growth & Intelligence">
    <meta name="twitter:description" content="Autonomous AI engines for market discovery, competitive intelligence, and scalable business growth.">
    <meta name="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://vidanexus.ai/#organization",
          "name": "VidaNexus AI",
          "url": "https://vidanexus.ai/",
          "logo": {
            "@type": "ImageObject",
            "url": "https://vidanexus.ai/brand-icon.png",
            "caption": "VidaNexus AI Logo"
          },
          "description": "VidaNexus is an advanced AI workspace engineered for businesses, enterprises, and growth teams.",
          "contactPoint": {
            "@type": "ContactPoint",
            "email": "info@vidanexus.net",
            "telephone": "+201019944589",
            "contactType": "customer service",
            "areaServed": "Global",
            "availableLanguage": ["English", "Arabic"]
          },
          "sameAs": [
            "https://www.facebook.com/VidaNexusAi/",
            "https://www.linkedin.com/company/vida-nexus-ai/"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "https://vidanexus.ai/#website",
          "url": "https://vidanexus.ai/",
          "name": "VidaNexus AI",
          "datePublished": "2026-01-01T00:00:00+00:00",
          "dateModified": "{{ date('c') }}",
          "publisher": {
            "@id": "https://vidanexus.ai/#organization"
          },
          "inLanguage": "en-US"
        },
        {
          "@type": "SoftwareApplication",
          "name": "VidaNexus AI Workspace",
          "applicationCategory": "BusinessApplication",
          "operatingSystem": "Web Browser",
          "url": "https://vidanexus.ai/",
          "datePublished": "2026-01-01T00:00:00+00:00",
          "dateModified": "{{ date('c') }}",
          "provider": {
            "@id": "https://vidanexus.ai/#organization"
          },
          "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "EGP",
            "lowPrice": "35",
            "highPrice": "2250",
            "offerCount": "4"
          }
        }
      ]
    }
    </script>
@endpush

@push('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@push('styles')
    <style>
        .filter-bar {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 4rem;
            flex-wrap: wrap;
            padding: 0 1rem;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            padding: 0.7rem 1.4rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
            text-transform: uppercase;
        }
        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            transform: translateY(-2px);
            border-color: rgba(0, 168, 230, 0.3);
        }
        .filter-btn.active {
            background: rgba(0, 168, 230, 0.1);
            color: var(--primary-cyan);
            border-color: rgba(0, 168, 230, 0.5);
            box-shadow: 0 0 20px rgba(0, 168, 230, 0.15);
        }

        /* Light Mode Overrides for Filter Bar */
        html[data-theme="light"] .filter-btn {
            background: rgba(255, 255, 255, 0.6);
            border-color: rgba(15, 23, 42, 0.1);
            color: var(--text-muted);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }
        html[data-theme="light"] .filter-btn:hover {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-main);
            border-color: rgba(0, 168, 230, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        html[data-theme="light"] .filter-btn.active {
            background: rgba(0, 168, 230, 0.05);
            color: var(--primary-cyan);
            border-color: var(--primary-cyan);
            box-shadow: 0 4px 15px rgba(0, 168, 230, 0.1);
        }
    </style>
@endpush

@section('body_decor')
    <div id="bg-layer">
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    </div>
@endsection

@section('content')
    @include('partials.welcome-main')
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
