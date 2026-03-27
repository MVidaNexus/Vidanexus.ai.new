<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Secondary & SEO Metadata -->
    <title>VidaNexus AI | The Future of Intelligent Automation</title>
    <meta name="description" content="VidaNexus is a high-performance, modular AI ecosystem designed for long-term scalability. Access a growing suite of intelligent tools from article writers to real-time trend monitors.">
    <meta name="keywords" content="AI, Artificial Intelligence, Content Automation, SEO Tools, Article Writer, Keyword Trends, Google Discover, VidaNexus">
    <meta name="author" content="VidaNexus">
    <link rel="canonical" href="https://vidanexus.ai">

    <!-- OpenGraph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://vidanexus.ai/">
    <meta property="og:title" content="VidaNexus AI | The Future of Intelligent Automation">
    <meta property="og:description" content="A modular, high-performance AI ecosystem designed for planetary-scale automation and content intelligence.">
    <meta property="og:image" content="{{ asset('assets/social-preview.png?v=2') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://vidanexus.ai/">
    <meta property="twitter:title" content="VidaNexus AI | The Future of Intelligent Automation">
    <meta property="twitter:description" content="A modular, high-performance AI ecosystem designed for planetary-scale automation and content intelligence.">
    <meta property="twitter:image" content="{{ asset('assets/social-preview.png?v=2') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.v2.css?v=30') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <script>(function(){const t=localStorage.getItem("theme")||"dark";document.documentElement.setAttribute("data-theme",t);})();</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            border-color: rgba(14, 165, 233, 0.3);
        }
        .filter-btn.active {
            background: rgba(14, 165, 233, 0.1);
            color: var(--primary-cyan);
            border-color: rgba(14, 165, 233, 0.5);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.15);
        }
    </style>
</head>
<body>
    <div id="bg-layer">
        <canvas id="techCanvas"></canvas>
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    </div>

    <div class="main-container">
        @include('partials.header')

        <main class="hero" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh;">
            <p class="hero-pretitle" style="color: var(--primary-cyan); font-weight: 800; letter-spacing: 4px; text-transform: uppercase; font-size: 0.8rem; margin-bottom: 1rem; text-shadow: 0 0 15px var(--primary-cyan);">The Era of Intelligence</p>
            <h1 class="hero-title" style="margin: 0 auto 2rem;">
                <span class="line-1">The Future is</span>
                <span class="line-2">
                    <span class="word-vida">VIDA</span>
                    <span class="word-nexus">NEXUS</span>
                </span>
            </h1>
            <p class="hero-subtitle" style="margin: 0 auto 3rem; max-width: 800px; font-weight: 400; line-height: 1.8; font-size: clamp(0.95rem, 2vw, 1.25rem);">
                Experience the pinnacle of AI-driven automation. 
                <span style="color: var(--text-main); font-weight: 600;">One Subscription.</span>
                A singular ecosystem for absolute scalability. 
                <span style="color: var(--primary-cyan); font-weight: 700; text-shadow: 0 0 20px var(--primary-cyan);">Unlimited Potential.</span>
            </p>

            <!-- About The Platform Section: The Vida Nexus Advantage (3D High-Fidelity) -->
            <!-- About The Platform Section: The Vida Nexus Advantage (Precision Minimalist) -->
            <div id="ecosystem" class="mb-16 w-full max-w-7xl mx-auto px-6">
                <style>
                    .vna-container {
                        text-align: center;
                        margin-bottom: 4rem;
                    }
                    .vna-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        background: rgba(56, 189, 248, 0.1);
                        border: 1px solid rgba(56, 189, 248, 0.2);
                        padding: 0.4rem 0.8rem;
                        border-radius: 99px;
                        color: #38bdf8;
                        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                        font-size: 0.75rem;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        margin-bottom: 1.5rem;
                    }
                    .vna-title {
                        font-family: var(--font-heading);
                        font-size: clamp(2rem, 5vw, 3.5rem);
                        font-weight: 800;
                        color: #fff;
                        margin-bottom: 0.75rem;
                        letter-spacing: -0.03em;
                    }
                    .vna-subtitle {
                        color: var(--text-muted);
                        font-size: clamp(0.9rem, 2vw, 1.1rem);
                        font-weight: 400;
                        opacity: 0.8;
                    }
                    
                    .vna-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 1.5rem;
                    }
                    .vna-card {
                        background: rgba(255, 255, 255, 0.02);
                        backdrop-filter: blur(40px) saturate(180%);
                        -webkit-backdrop-filter: blur(40px) saturate(180%);
                        border: 1.2px solid rgba(255, 255, 255, 0.08);
                        border-radius: 24px;
                        padding: 2.25rem 1.5rem;
                        text-align: center;
                        transition: all 0.5s cubic-bezier(0.2, 1, 0.3, 1);
                        position: relative;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }
                    .vna-card:hover {
                        transform: translateY(-8px);
                        background: rgba(255, 255, 255, 0.04);
                        border-color: rgba(255, 255, 255, 0.15);
                        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.6);
                    }
                    
                    .vna-icon-main {
                        font-size: 2.25rem;
                        margin-bottom: 1.5rem;
                        filter: drop-shadow(0 0 15px var(--vna-accent));
                        transition: transform 0.5s ease;
                    }
                    .vna-card:hover .vna-icon-main { transform: scale(1.1); }

                    .vna-dot {
                        width: 6px;
                        height: 6px;
                        border-radius: 50%;
                        background: var(--vna-accent);
                        position: relative;
                        display: inline-block;
                        box-shadow: 0 0 8px var(--vna-accent);
                    }
                    .vna-dot::after {
                        content: '';
                        position: absolute;
                        inset: -2px;
                        border-radius: 50%;
                        border: 1px solid var(--vna-accent);
                        animation: vna-pulse 2s infinite;
                        opacity: 0;
                    }
                    @keyframes vna-pulse {
                        0% { transform: scale(0.8); opacity: 0.8; }
                        100% { transform: scale(2.5); opacity: 0; }
                    }

                    .vna-card-title {
                        font-family: var(--font-heading);
                        font-size: 1.25rem;
                        font-weight: 800;
                        color: #fff;
                        margin-bottom: 0.75rem;
                        letter-spacing: -0.01em;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.6rem;
                    }
                    .vna-card-desc {
                        font-size: 0.85rem;
                        color: var(--text-muted);
                        line-height: 1.6;
                        font-weight: 400;
                        opacity: 0.8;
                    }

                    @media (max-width: 1024px) {
                        .vna-grid { grid-template-columns: 1fr; max-width: 450px; margin: 0 auto; gap: 2rem; }
                    }

                    [data-theme="light"] .vna-card {
                        background: rgba(255, 255, 255, 0.95);
                        border-color: rgba(0, 0, 0, 0.08);
                        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
                    }
                    [data-theme="light"] .vna-title, [data-theme="light"] .vna-card-title { color: #0f172a; }
                </style>

                <div class="vna-container">
                    <div class="vna-badge">
                        <i class="fas fa-laptop-code" style="margin-right: 0.4rem;"></i>
                        SOFTWARE HOUSE
                    </div>
                    <h2 class="vna-title">The Vida Nexus <span style="background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Advantage</span></h2>
                    <p class="vna-subtitle">The Digital Backbone of Tomorrow's AI Ecosystem</p>
                </div>

                <div class="vna-grid">
                    <!-- Feature 1: Infinite AI Arsenal -->
                    <div class="vna-card" style="--vna-accent: #a855f7;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Infinite AI Arsenal
                        </h3>
                        <p class="vna-card-desc">Access a precision-engineered marketplace of specialized AI tools designed to conquer digital complexity with modular efficiency.</p>
                    </div>

                    <!-- Feature 2: Smart Credits -->
                    <div class="vna-card" style="--vna-accent: #0ea5e9;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Transparent Credits
                        </h3>
                        <p class="vna-card-desc">High-precision credit logic that aligns computing investment directly with operational value, ensuring zero resource waste.</p>
                    </div>

                    <!-- Feature 3: Planetary Reliability -->
                    <div class="vna-card" style="--vna-accent: #10b981;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-globe-americas"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Core Availability
                        </h3>
                        <p class="vna-card-desc">Redundant global infrastructure guaranteeing 99.99% uptime for mission-critical automation at any planetary scale.</p>
                    </div>
                </div>
            </div>

            <!-- Precision Stats Bar -->
            <div class="vna-stats-bar-wrapper">
                <style>
                    .vna-stats-bar-wrapper {
                        width: 100%;
                        max-width: 1100px;
                        margin: 2rem auto 6rem auto;
                        background: rgba(255, 255, 255, 0.03);
                        backdrop-filter: blur(25px) saturate(200%);
                        -webkit-backdrop-filter: blur(25px) saturate(200%);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        border-radius: 20px;
                        padding: 2.5rem 1rem;
                        z-index: 10;
                    }
                    .vna-stats-container {
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                        gap: 1rem;
                    }
                    .vna-stat-item {
                        text-align: center;
                        flex: 1;
                    }
                    .vna-stat-value {
                        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                        font-size: 2.25rem;
                        font-weight: 900;
                        display: block;
                        margin-bottom: 0.5rem;
                        letter-spacing: -0.05em;
                        text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
                    }
                    .vna-stat-label {
                        font-size: 0.7rem;
                        color: var(--text-muted);
                        text-transform: uppercase;
                        letter-spacing: 0.2em;
                        font-weight: 700;
                        opacity: 0.6;
                    }
                    @media (max-width: 900px) {
                        .vna-stat-value { font-size: 1.75rem; }
                        .vna-stat-label { font-size: 0.6rem; }
                    }
                    @media (max-width: 768px) {
                        .vna-stats-container { flex-direction: column; gap: 3rem; }
                        .vna-stats-bar-wrapper { margin-bottom: 4rem; padding: 3rem 1rem; }
                    }
                </style>
                <div class="vna-stats-container">
                    @php 
                        $activeCount = 0;
                        foreach($tools as $t) {
                            if (\App\Models\Setting::get("tool_available_{$t['slug']}", false)) {
                                $activeCount++;
                            }
                        }
                        $comingSoonCount = count($tools) - $activeCount;
                    @endphp
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #10b981; text-shadow: 0 0 20px rgba(16, 185, 129, 0.3);">{{ $activeCount }}</span>
                        <span class="vna-stat-label">Active Tools</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #f59e0b; text-shadow: 0 0 20px rgba(245, 158, 11, 0.3);">{{ $comingSoonCount }}</span>
                        <span class="vna-stat-label">Coming Soon</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #38bdf8; text-shadow: 0 0 20px rgba(56, 189, 248, 0.3);">100+</span>
                        <span class="vna-stat-label">Active Clients</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #a855f7; text-shadow: 0 0 20px rgba(168, 85, 247, 0.3);">20+</span>
                        <span class="vna-stat-label">Partnerships</span>
                    </div>
                </div>
            </div>
            <h2 id="tools" style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 3.5rem;">Exploration Deck</h2>

            <div x-data="{ filter: 'all' }" style="width: 100%;">
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-th-large mr-2"></i> All Tools
                    </button>
                    <button @click="filter = 'seo'" :class="filter === 'seo' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-search mr-2"></i> SEO & Search
                    </button>
                    <button @click="filter = 'marketing'" :class="filter === 'marketing' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-bullhorn mr-2"></i> Marketing & Ads
                    </button>
                    <button @click="filter = 'content'" :class="filter === 'content' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-pen-nib mr-2"></i> Content & Writing
                    </button>
                    <button @click="filter = 'intelligence'" :class="filter === 'intelligence' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-brain mr-2"></i> Intelligence
                    </button>
                    <button @click="filter = 'tools'" :class="filter === 'tools' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-toolbox mr-2"></i> Power Tools
                    </button>
                </div>

                <div class="tools-grid">
                    @foreach($tools as $tool)
                    <div class="tool-card {{ !$tool['is_available'] ? 'is-coming-soon' : '' }}" x-show="filter === 'all' || filter === '{{ $tool['category'] ?? '' }}'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="{{ !$tool['is_available'] ? 'position: relative; overflow: hidden; opacity: 0.8; filter: grayscale(0.6);' : '' }}">
                        @if(!$tool['is_available'])
                            <div style="position: absolute; top: 1rem; right: 1rem; background: var(--card-bg); backdrop-filter: blur(5px); color: var(--text-muted); font-size: 0.65rem; font-weight: 800; padding: 0.4rem 0.8rem; border-radius: 20px; border: 1px solid var(--glass-border); box-shadow: 0 4px 10px rgba(0,0,0,0.05); z-index: 10; letter-spacing: 1px;">
                                <i class="fas fa-clock mr-1"></i> COMING SOON
                            </div>
                        @elseif(!$tool['is_owned'])
                             <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(168, 85, 247, 0.15); backdrop-filter: blur(5px); color: #a855f7; font-size: 0.65rem; font-weight: 800; padding: 0.4rem 0.8rem; border-radius: 20px; border: 1px solid rgba(168, 85, 247, 0.3); z-index: 10; letter-spacing: 1px;">
                                <i class="fas fa-shopping-cart mr-1"></i> MARKETPLACE
                            </div>
                        @endif
                        <div class="tool-icon" style="color: {{ $tool['color'] }};">
                            <i class="fas {{ $tool['icon'] }}"></i>
                        </div>
                        <h3>{{ $tool['name'] }}</h3>
                        <p>{{ $tool['tagline'] ?? $tool['name'] }}</p>
                        
                        @if($tool['is_available'])
                            <a href="/tools/{{ $tool['slug'] }}" class="vn-btn vn-btn-primary" style="width: 100%;">
                                <span>Explore Tool</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @else
                            <button class="vn-btn btn-outline" style="width: 100%; cursor: not-allowed; opacity: 0.5;" disabled>
                                <span>Coming Soon</span>
                                <i class="fas fa-lock"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
                </div>
            </div>
            
        </main>
        @include('partials.footer')

    </div>

    <script src="{{ asset('script.js?v=14') }}"></script>
</body>
</html>
