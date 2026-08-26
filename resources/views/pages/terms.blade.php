@extends('layouts.marketing')

@section('title', 'Terms of Service | VidaNexus AI')

@push('meta')
    <meta name="description" content="Review the Terms of Service for using the VidaNexus AI platform, services, APIs, and credit-based tools.">
    <link rel="canonical" href="https://vidanexus.ai/terms">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/terms">
    <meta property="og:title" content="Terms of Service | VidaNexus AI">
    <meta property="og:description" content="Review the Terms of Service for using the VidaNexus AI platform, services, APIs, and credit-based tools.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://vidanexus.ai/terms">
    <meta property="twitter:title" content="Terms of Service | VidaNexus AI">
    <meta property="twitter:description" content="Review the Terms of Service for using the VidaNexus AI platform, services, APIs, and credit-based tools.">
    <meta property="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('schema')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
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
          "name": "Terms of Service",
          "item": "https://vidanexus.ai/terms"
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
            <h1 class="page-title">Terms of Service</h1>
            <p class="page-subtitle">Standard terms and conditions governing the use of VidaNexus APIs and platform tools.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using the VidaNexus AI platform and services, you agree to be bound by these Terms of Service. If you do not agree to these terms, you must cease using our services.</p>

                <h2>2. Description of Service</h2>
                <p>VidaNexus provides artificial intelligence tools for creators, including SEO analysis, article generation, viral trend monitoring, and headline discovery. These services are delivered on a credit and subscription basis.</p>

                <h2>3. Credits, Marketplace, and Billing</h2>
                <p>Our services operate on a flexible Credit model:</p>
                <ul>
                    <li>Credits are purchased as packages to use across our tools.</li>
                    <li>Individual tools can be unlocked or accessed according to your plan and credit balance.</li>
                    <li>Credits are consumed per tool execution according to the specific action cost listed for each tool.</li>
                    <li>Payments are processed securely via verified third-party payment gateways (e.g., Fawaterk).</li>
                    <li>Account sharing or unauthorized credential distribution is strictly prohibited.</li>
                </ul>

                <h2>4. Acceptable Use Policy</h2>
                <p>You agree not to misuse our platform. Prohibited activities include:</p>
                <ul>
                    <li>Attempting to reverse-engineer, decompile, or bypass the security of our platform or APIs.</li>
                    <li>Using our AI generation tools to produce illegal, abusive, or explicitly harmful content.</li>
                    <li>Automating interactions outside of authorized API endpoints resulting in Denial of Service (DoS) conditions.</li>
                    <li>Reselling platform access without an explicit enterprise partnership agreement.</li>
                </ul>

                <h2>5. Intellectual Property</h2>
                <p>VidaNexus retains all rights, title, and interest in and to the platform, including its proprietary algorithms, design, and branding. You retain ownership of any distinct inputs provided to the service and the standard usage rights for the specific outputs generated, subject to any open-source licensing constraints applicable to our underlying foundation models.</p>

                <h2>6. Limitation of Liability</h2>
                <p>VidaNexus shall not be liable for any indirect, incidental, or consequential damages resulting from the use or inability to use our services, including data loss or business interruption. In no event shall our total liability exceed the aggregate amount paid by you for the service in the preceding 12 months.</p>

                <h2>7. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms at any time. Significant changes will be communicated via your registered email or through a prominent notice on the platform dashboard.</p>
            </div>
        </div>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
