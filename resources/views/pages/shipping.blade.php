@extends('layouts.marketing')

@section('title', 'Digital Delivery Policy | VidaNexus AI')

@push('meta')
    <meta name="description" content="Information on instant digital fulfillment, access delivery, and account credit activation on VidaNexus AI.">
    <link rel="canonical" href="https://vidanexus.ai/shipping">
    <meta property="og:type" content="article">
    <meta property="og:url" content="https://vidanexus.ai/shipping">
    <meta property="og:title" content="Digital Delivery Policy | VidaNexus AI">
    <meta property="og:description" content="Information on instant digital fulfillment, access delivery, and account credit activation on VidaNexus AI.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="article:published_time" content="2026-01-01T00:00:00+00:00">
    <meta property="article:modified_time" content="{{ date('c') }}">
    <meta property="og:updated_time" content="{{ date('c') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@vidanexus_ai">
    <meta name="twitter:creator" content="@vidanexus_ai">
    <meta name="twitter:url" content="https://vidanexus.ai/shipping">
    <meta name="twitter:title" content="Digital Delivery Policy | VidaNexus AI">
    <meta name="twitter:description" content="Information on instant digital fulfillment, access delivery, and account credit activation on VidaNexus AI.">
    <meta name="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebPage",
          "name": "Digital Delivery Policy | VidaNexus AI",
          "url": "https://vidanexus.ai/shipping",
          "datePublished": "2026-01-01T00:00:00+00:00",
          "dateModified": "{{ date('c') }}",
          "description": "Information on instant digital fulfillment, access delivery, and account credit activation on VidaNexus AI."
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
              "name": "Digital Delivery Policy",
              "item": "https://vidanexus.ai/shipping"
            }
          ]
        }
      ]
    }
    </script>
@endpush

@push('styles')
    @include('partials.legal-page-styles')
@endpush

@section('content')
        <div class="page-header">
            <h1 class="page-title">Digital Delivery Policy</h1>
            <p class="page-subtitle">How we deliver our AI services and automation assets.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Instant Digital Fulfillment</h2>
                <p>VidaNexus AI exclusively provides digital services and assets. There are no physical goods shipped as part of any plan or package. Upon successful payment, your account features or credits are typically activated <strong>instantly</strong>.</p>

                <h2>2. Delivery Mechanism</h2>
                <p>All tool outputs (articles, images, data reports) are delivered directly within your user dashboard or via the specified API endpoints. You will receive an email confirmation for all subscription upgrades and credit purchases.</p>

                <h2>3. Access Issues</h2>
                <p>If your account does not reflect your purchase within 30 minutes, please refresh your dashboard or log out and log back in. If the issue persists, contact our technical support team for manual verification.</p>

                <h2>4. Geographical Availability</h2>
                <p>As a cloud-based platform, our services are available globally. However, execution speed and latency may vary based on your local network conditions and proximity to our server nodes.</p>

                <h2>5. Contact Support</h2>
                <p>For any questions regarding your access or digital delivery, please contact us at:</p>
                <p><strong>Email:</strong> <a href="mailto:info@vidanexus.net" style="color: var(--primary-cyan); text-decoration: none;">info@vidanexus.net</a></p>
            </div>
        </div>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
