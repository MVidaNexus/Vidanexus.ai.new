@extends('layouts.marketing')

@section('title', 'Refund Policy | Vida Nexus AI')

@push('styles')
    @include('partials.legal-page-styles')
@endpush

@section('content')
        <div class="page-header">
            <h1 class="page-title">Refund Policy</h1>
            <p class="page-subtitle">Our transparent approach to billing and satisfaction.</p>
        </div>

        <div class="content-container">
            <div class="legal-content">
                <span class="last-updated">Last Updated: March 2026</span>

                <h2>1. General Terms</h2>
                <p>VidaNexus AI provides digital SaaS tools and AI generation services. Due to the high computational costs associated with AI generation (processing power, API fees), our refund policy is designed to be fair to both the user and the platform.</p>

                <h2>2. Credit Package Refunds</h2>
                <p>One-time credit packages (Lite, Creator, Agency, etc.) are refundable within <strong>24 hours</strong> of purchase, provided that you have consumed less than <strong>2% of the package's credits</strong>. Once the 24-hour window has passed or more than 2% of credits have been used (even for a single tool execution), the package becomes non-refundable due to the immediate computational costs incurred.</p>

                <h2>3. Service Malfunctions</h2>
                <p>If a technical error on our platform results in the failure of a generation or tool execution, the credits used for that specific action will be automatically or manually restored to your wallet. If a persistent system-wide issue prevents you from using the service for more than 48 hours, we may issue bonus credits or a partial refund at our discretion.</p>

                <h2>5. Processing Refunds</h2>
                <p>Refunds are processed back to the original payment method used via Fawaterk. Please note that it may take 5-10 business days for the funds to appear in your account depending on your bank's policies.</p>

                <h2>6. Contact For Refunds</h2>
                <p>To request a refund, please contact us at:</p>
                <p><strong>Email:</strong> <a href="mailto:info@vidanexus.net" style="color: var(--primary-cyan); text-decoration: none;">info@vidanexus.net</a></p>
                <p>Please include your order reference number and the email associated with your account.</p>
            </div>
        </div>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
