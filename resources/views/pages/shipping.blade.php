@extends('layouts.marketing')

@section('title', 'Digital Delivery Policy | Vida Nexus AI')

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
