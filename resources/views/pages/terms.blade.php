@extends('layouts.marketing')

@section('title', 'Terms of Service | Vida Nexus AI')

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
                <p>By accessing or using the VidaNexus AI ecosystem, APIs, and associated infrastructure, you agree to be bound by these Terms of Service. If you do not agree to these terms, you must disconnect from our services immediately.</p>

                <h2>2. Description of Service</h2>
                <p>VidaNexus provides modular artificial intelligence tools, including but not limited to, SEO analysis, article generation, viral trend monitoring, and data visualization. These services are delivered "as-is", and availability may vary based on your active subscription plan.</p>

                <h2>3. Credits, Marketplace, and Billing</h2>
                <p>Our services operate on a Marketplace Credit model (CRS):</p>
                <ul>
                    <li>Credits (CRS) are purchased as one-time packages and have no expiration date unless the account is terminated.</li>
                    <li>Individual tools are unlocked via credits or granted as part of specific marketplace bundles.</li>
                    <li>Credits are consumed per tool execution according to the specific "Action Cost" documented for each tool.</li>
                    <li>Payments are processed securely via third-party providers (e.g., Fawaterk) and are non-refundable once consumption begins.</li>
                    <li>Account sharing or API key sharing is strictly prohibited and will result in immediate termination.</li>
                </ul>

                <h2>4. Acceptable Use Policy</h2>
                <p>You agree not to misuse our ecosystem. Prohibited activities include:</p>
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
