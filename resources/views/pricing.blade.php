@extends('layouts.marketing')

@section('title', 'Simple, Transparent Pricing | VidaNexus AI')

@push('meta')
    <meta name="description" content="Flexible credit packages for content creators and SEO professionals. Top up your wallet anytime to unlock tools and generate articles.">
    <link rel="canonical" href="https://vidanexus.ai/pricing">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/pricing">
    <meta property="og:title" content="Simple, Transparent Pricing | VidaNexus AI">
    <meta property="og:description" content="Flexible credit packages for content creators and SEO professionals. Top up your wallet anytime to unlock tools and generate articles.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="article:published_time" content="2026-01-01T00:00:00+00:00">
    <meta property="article:modified_time" content="{{ date('c') }}">
    <meta property="og:updated_time" content="{{ date('c') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@vidanexus_ai">
    <meta name="twitter:creator" content="@vidanexus_ai">
    <meta name="twitter:url" content="https://vidanexus.ai/pricing">
    <meta name="twitter:title" content="Simple, Transparent Pricing | VidaNexus AI">
    <meta name="twitter:description" content="Flexible credit packages for content creators and SEO professionals. Top up your wallet anytime to unlock tools and generate articles.">
    <meta name="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Product",
          "name": "VidaNexus AI Credit Packages",
          "description": "Flexible credit packages for content creators and SEO professionals.",
          "datePublished": "2026-01-01T00:00:00+00:00",
          "dateModified": "{{ date('c') }}",
          "brand": {
            "@type": "Brand",
            "name": "VidaNexus AI"
          },
          "offers": {
            "@type": "AggregateOffer",
            "priceCurrency": "EGP",
            "lowPrice": "35",
            "highPrice": "2250",
            "offerCount": "4"
          }
        },
        {
          "@type": "BreadcrumbList",
          "itemListElement": [
            {
              "@type": "ListItem",
              "position": 1,
              "name": "Home",
              "item": "https://vidanexus.ai/"
            },
            {
              "@type": "ListItem",
              "position": 2,
              "name": "Pricing",
              "item": "https://vidanexus.ai/pricing"
            }
          ]
        }
      ]
    }
    </script>
@endpush

@push('styles')
    @include('partials.pricing-page-styles')
@endpush

@section('content')
        <main>
            <div class="pricing-hero">
                <h1 class="pricing-title">Simple, Transparent <span class="gradient-text">Pricing</span></h1>
                <p class="pricing-subtitle">Purchase flexible credit packages to unlock specialized tools and generate content. Pay only for what you need with zero subscription traps.</p>
            </div>

            <div style="max-width: 1400px; margin: 0 auto 6rem; padding: 0 1rem;">
                @include('partials.pricing_cards')
            </div>
        </main>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
