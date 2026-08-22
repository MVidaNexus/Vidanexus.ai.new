@extends('layouts.marketing')

@section('title', 'Pricing & Subscriptions | VidaNexus AI')

@push('meta')
    <meta name="description" content="Select your VidaNexus AI tier. Access our suite of 12 powerful AI tools with tailored limits for Pro and Ultimate.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Pricing & Subscriptions | VidaNexus AI">
    <meta property="og:description" content="Select your VidaNexus AI tier. Access our suite of 12 powerful AI tools with tailored limits for Pro and Ultimate.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('styles')
    @include('partials.pricing-page-styles')
@endpush

@section('content')
        <main>
            <div class="pricing-hero">
                <h1 class="pricing-title">Fuel Your <span class="gradient-text">Intelligence</span></h1>
                <p class="pricing-subtitle">Purchase credit packages to unlock tools and generate content. Experience institutional-grade performance on demand.</p>
            </div>

            <div style="max-width: 1400px; margin: 0 auto 6rem; padding: 0 1rem;">
                @include('partials.pricing_cards')
            </div>
        </main>
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
