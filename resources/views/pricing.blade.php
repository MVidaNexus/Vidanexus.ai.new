@extends('layouts.marketing')

@section('title', 'Simple, Transparent Pricing | VidaNexus AI')

@push('meta')
    <meta name="description" content="Flexible credit packages for content creators and SEO professionals. Top up your wallet anytime to unlock tools and generate articles.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Simple, Transparent Pricing | VidaNexus AI">
    <meta property="og:description" content="Flexible credit packages for content creators and SEO professionals. Top up your wallet anytime to unlock tools and generate articles.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
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
