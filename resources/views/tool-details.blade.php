@extends('layouts.marketing')

@section('title', $tool['meta_title'])

@push('meta')
    <meta name="description" content="{{ $tool['meta_desc'] }}">
    <link rel="canonical" href="{{ url('/tools/' . $tool['slug']) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/tools/' . $tool['slug']) }}">
    <meta property="og:title" content="{{ $tool['meta_title'] }}">
    <meta property="og:description" content="{{ $tool['meta_desc'] }}">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/tools/' . $tool['slug']) }}">
    <meta property="twitter:title" content="{{ $tool['meta_title'] }}">
    <meta property="twitter:description" content="{{ $tool['meta_desc'] }}">
    <meta property="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "SoftwareApplication",
          "name": "{{ addslashes($tool['name']) }}",
          "applicationCategory": "BusinessApplication",
          "operatingSystem": "Web Browser",
          "url": "{{ url('/tools/' . $tool['slug']) }}",
          "description": "{{ addslashes($tool['meta_desc'] ?? $tool['description'] ?? '') }}",
          "offers": {
            "@type": "Offer",
            "price": "{{ (int) ($tool['unlock_price'] ?? 99) }}",
            "priceCurrency": "EGP",
            "availability": "https://schema.org/InStock"
          },
          "provider": {
            "@type": "Organization",
            "name": "VidaNexus AI",
            "url": "https://vidanexus.ai/"
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
              "name": "Tools",
              "item": "https://vidanexus.ai/#tools"
            },
            {
              "@type": "ListItem",
              "position": 3,
              "name": "{{ addslashes($tool['name']) }}",
              "item": "{{ url('/tools/' . $tool['slug']) }}"
            }
          ]
        }
      ]
    }
    </script>
@endpush

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
@endpush

@push('styles')
    @include('partials.tool-details-styles')
@endpush

@section('body_decor')
    <div id="bg-layer">
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
        <div class="orb-special"></div>
    </div>
@endsection

@section('content')
    @include('partials.tool-details-main')
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
