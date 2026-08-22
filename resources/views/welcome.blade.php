@extends('layouts.marketing')

@section('title', 'VidaNexus AI | The Future of Intelligent Automation')

@push('meta')
    <meta name="description" content="VidaNexus is a high-performance, modular AI ecosystem designed for long-term scalability. Access a growing suite of intelligent tools from article writers to real-time trend monitors.">
    <meta name="keywords" content="AI, Artificial Intelligence, Content Automation, SEO Tools, Article Writer, Keyword Trends, Google Discover, VidaNexus">
    <meta name="author" content="VidaNexus">
    <link rel="canonical" href="https://vidanexus.ai">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/">
    <meta property="og:title" content="VidaNexus AI | The Future of Intelligent Automation">
    <meta property="og:description" content="A modular, high-performance AI ecosystem designed for planetary-scale automation and content intelligence.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://vidanexus.ai/">
    <meta property="twitter:title" content="VidaNexus AI | The Future of Intelligent Automation">
    <meta property="twitter:description" content="A modular, high-performance AI ecosystem designed for planetary-scale automation and content intelligence.">
    <meta property="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
@endpush

@push('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@push('styles')
    <style>
        .filter-bar {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 4rem;
            flex-wrap: wrap;
            padding: 0 1rem;
        }
        .filter-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-muted);
            padding: 0.7rem 1.4rem;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
            text-transform: uppercase;
        }
        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            transform: translateY(-2px);
            border-color: rgba(0, 168, 230, 0.3);
        }
        .filter-btn.active {
            background: rgba(0, 168, 230, 0.1);
            color: var(--primary-cyan);
            border-color: rgba(0, 168, 230, 0.5);
            box-shadow: 0 0 20px rgba(0, 168, 230, 0.15);
        }

        /* Light Mode Overrides for Filter Bar */
        html[data-theme="light"] .filter-btn {
            background: rgba(255, 255, 255, 0.6);
            border-color: rgba(15, 23, 42, 0.1);
            color: var(--text-muted);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }
        html[data-theme="light"] .filter-btn:hover {
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-main);
            border-color: rgba(0, 168, 230, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        html[data-theme="light"] .filter-btn.active {
            background: rgba(0, 168, 230, 0.05);
            color: var(--primary-cyan);
            border-color: var(--primary-cyan);
            box-shadow: 0 4px 15px rgba(0, 168, 230, 0.1);
        }
    </style>
@endpush

@section('body_decor')
    <div id="bg-layer">
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    </div>
@endsection

@section('content')
    @include('partials.welcome-main')
@endsection

@push('after_main')
    @include('partials.footer')
@endpush
