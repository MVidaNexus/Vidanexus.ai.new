@extends('layouts.marketing')

@section('title', $tool['meta_title'])

@push('meta')
    <meta name="description" content="{{ $tool['meta_desc'] }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $tool['meta_title'] }}">
    <meta property="og:description" content="{{ $tool['meta_desc'] }}">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
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
