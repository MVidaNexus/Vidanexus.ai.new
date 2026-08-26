@extends('layouts.marketing')

@section('title', 'Privacy Policy | Vida Nexus AI')

@push('styles')
    @include('partials.legal-page-styles')
@endpush

@section('content')
        <div class="page-header">
            <h1 class="page-title">Privacy Policy</h1>
            <p class="page-subtitle">How we collect, use, and protect your data across the VidaNexus platform.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. Introduction</h2>
                <p>Welcome to VidaNexus AI. We are committed to protecting your personal data and respecting your privacy. This policy outlines our data processing practices when you use our tools and associated services.</p>

                <h2>2. Data We Collect</h2>
                <p>To provide you with our high-performance AI services, we collect the following types of information:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, and secure password hashes required for platform access.</li>
                    <li><strong>Usage Data:</strong> Metrics on tool execution, API calls, and credit consumption.</li>
                    <li><strong>Payment Data:</strong> Handled securely via our PCI-compliant partners (Fawaterk); we do not store raw credit card information.</li>
                    <li><strong>Generated Content:</strong> Temporary storage of inputs and outputs for AI processing, which are routinely purged according to our retention schedules.</li>
                </ul>

                <h2>3. How We Use Your Data</h2>
                <p>Your data is strictly utilized to:</p>
                <ul>
                    <li>Provide, maintain, and improve the VidaNexus tools and APIs.</li>
                    <li>Process transactions and manage your subscription state.</li>
                    <li>Analyze usage to scale infrastructure and optimize AI model performance.</li>
                    <li>Send critical service updates, security alerts, and support messages.</li>
                </ul>

                <h2>4. Data Security</h2>
                <p>We implement enterprise-grade security measures including SSL/TLS encryption for data in transit and AES-256 encryption for sensitive data at rest. Access to production environments is strictly gated and monitored.</p>

                <h2>5. Third-Party Services</h2>
                <p>VidaNexus integrates with specialized APIs (e.g., AI Engine Providers, Google Trends, Fawaterk) to deliver our services. Data shared with these sub-processors is limited to what is strictly necessary for the operation requested.</p>

                <h2>6. Your Rights</h2>
                <p>You have the right to access, correct, or request deletion of your personal data. You may also export your usage data or withdraw consent where processing is consent-based. Contact us at <a href="mailto:info@vidanexus.net" style="color: var(--primary-cyan); text-decoration: none;">info@vidanexus.net</a> for assistance.</p>

                <h2>7. Contact Us</h2>
                <p>For any privacy-related questions or concerns, please contact:</p>
                <p><strong>Email:</strong> <a href="mailto:info@vidanexus.net" style="color: var(--primary-cyan); text-decoration: none;">info@vidanexus.net</a></p>
            </div>
        </div>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
