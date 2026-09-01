        <main class="hero" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: auto; padding: 2rem 1rem 1rem;">
            <p class="hero-pretitle" style="color: var(--primary-cyan); font-weight: 800; letter-spacing: 3px; text-transform: uppercase; font-size: 0.75rem; margin-bottom: 0.75rem; text-shadow: 0 0 15px var(--primary-cyan);">AI Tools For Content & SEO Growth</p>
            <h1 class="hero-title" style="margin: 0 auto 1.25rem;">
                <span class="line-1">The Future is</span>
                <span class="line-2">
                    <span class="word-vida">VIDA</span>
                    <span class="word-nexus">NEXUS</span>
                </span>
            </h1>
            <p class="hero-subtitle" style="margin: 0 auto 2rem; max-width: 780px; font-weight: 400; line-height: 1.6; font-size: clamp(0.88rem, 1.8vw, 1.15rem);">
                Supercharge your publishing workflow with specialized AI tools for keyword discovery, trend tracking, and high-ranking article generation. 
                <span style="color: var(--text-main); font-weight: 600;">Built for SEO growth.</span>
                <span style="color: var(--primary-cyan); font-weight: 700; text-shadow: 0 0 20px var(--primary-cyan);">Fast, accurate, and actionable.</span>
            </p>

            <!-- About The Platform Section: The Vida Nexus Advantage -->
            <div id="ecosystem" class="w-full max-w-7xl mx-auto px-4" style="margin-bottom: 1.5rem;">
                <style>
                    .vna-container {
                        text-align: center;
                        margin-bottom: 1.25rem;
                    }
                    .vna-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.4rem;
                        background: rgba(56, 189, 248, 0.1);
                        border: 1px solid rgba(56, 189, 248, 0.2);
                        padding: 0.25rem 0.65rem;
                        border-radius: 99px;
                        color: #38bdf8;
                        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                        font-size: 0.68rem;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        margin-bottom: 0.6rem;
                    }
                    .vna-title {
                        font-family: var(--font-heading);
                        font-size: clamp(1.5rem, 3.5vw, 2.4rem);
                        font-weight: 800;
                        color: #fff;
                        margin-bottom: 0.35rem;
                        letter-spacing: -0.02em;
                    }
                    .vna-subtitle {
                        color: var(--text-muted);
                        font-size: clamp(0.8rem, 1.5vw, 0.95rem);
                        font-weight: 400;
                        opacity: 0.8;
                    }
                    
                    .vna-grid {
                        display: grid;
                        grid-template-columns: repeat(3, 1fr);
                        gap: 1rem;
                    }
                    .vna-card {
                        background: rgba(255, 255, 255, 0.02);
                        backdrop-filter: blur(30px) saturate(180%);
                        -webkit-backdrop-filter: blur(30px) saturate(180%);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        border-radius: 16px;
                        padding: 1.25rem 1rem;
                        text-align: center;
                        transition: all 0.3s ease;
                        position: relative;
                        overflow: hidden;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }
                    .vna-card:hover {
                        transform: translateY(-4px);
                        background: rgba(255, 255, 255, 0.04);
                        border-color: rgba(255, 255, 255, 0.15);
                    }
                    
                    .vna-icon-main {
                        font-size: 1.5rem;
                        margin-bottom: 0.75rem;
                        filter: drop-shadow(0 0 10px var(--vna-accent));
                    }

                    .vna-dot {
                        width: 5px;
                        height: 5px;
                        border-radius: 50%;
                        background: var(--vna-accent);
                        display: inline-block;
                        box-shadow: 0 0 6px var(--vna-accent);
                    }

                    .vna-card-title {
                        font-family: var(--font-heading);
                        font-size: 1.05rem;
                        font-weight: 800;
                        color: #fff;
                        margin-bottom: 0.4rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                    }
                    .vna-card-desc {
                        font-size: 0.78rem;
                        color: var(--text-muted);
                        line-height: 1.45;
                        font-weight: 400;
                        opacity: 0.85;
                        margin: 0;
                    }

                    @media (max-width: 768px) {
                        .vna-grid {
                            display: grid !important;
                            grid-template-columns: repeat(3, 1fr) !important;
                            gap: 0.4rem !important;
                            padding: 0 !important;
                        }
                        .vna-card {
                            flex: none !important;
                            min-width: 0 !important;
                            padding: 0.75rem 0.35rem !important;
                            border-radius: 12px !important;
                        }
                        .vna-icon-main {
                            font-size: 1.15rem !important;
                            margin-bottom: 0.35rem !important;
                        }
                        .vna-card-title {
                            font-size: 0.72rem !important;
                            margin-bottom: 0.25rem !important;
                            letter-spacing: -0.02em !important;
                            line-height: 1.2 !important;
                            gap: 0.25rem !important;
                        }
                        .vna-dot {
                            display: none !important;
                        }
                        .vna-card-desc {
                            font-size: 0.62rem !important;
                            line-height: 1.3 !important;
                            opacity: 0.8 !important;
                        }
                    }

                    [data-theme="light"] .vna-card {
                        background: rgba(255, 255, 255, 0.95);
                        border-color: rgba(0, 0, 0, 0.08);
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
                    }
                    [data-theme="light"] .vna-title, [data-theme="light"] .vna-card-title { color: #0f172a; }
                </style>

                <div class="vna-container">
                    <div class="vna-badge">
                        <i class="fas fa-laptop-code"></i>
                        SOFTWARE HOUSE
                    </div>
                    <h2 class="vna-title">The Vida Nexus <span style="background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Advantage</span></h2>
                    <p class="vna-subtitle">Tailored AI tools designed to help creators and publishers grow search traffic.</p>
                </div>

                <div class="vna-grid">
                    <!-- Feature 1: Specialized SEO & Content Tools -->
                    <div class="vna-card" style="--vna-accent: #a855f7;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Specialized SEO Tools
                        </h3>
                        <p class="vna-card-desc">Real-time keyword radars, headline analyzers, and article writers built for publishing workflows.</p>
                    </div>

                    <!-- Feature 2: Pay-As-You-Go Credits -->
                    <div class="vna-card" style="--vna-accent: #00A8E6;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Pay-As-You-Go Credits
                        </h3>
                        <p class="vna-card-desc">Only pay for what you use. Transparent wallet credit pricing, zero waste, and complete control.</p>
                    </div>

                    <!-- Feature 3: Fast & Reliable -->
                    <div class="vna-card" style="--vna-accent: #00A58B;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-globe-americas"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Fast & Reliable
                        </h3>
                        <p class="vna-card-desc">High-speed cloud infrastructure delivering real-time scans, competitor insights, and 24/7 AI output.</p>
                    </div>
                </div>
            </div>

            <!-- Precision Stats Bar -->
            <div class="vna-stats-bar-wrapper">
                <style>
                    .vna-stats-bar-wrapper {
                        width: 100%;
                        max-width: 1100px;
                        margin: 0.5rem auto 2rem auto;
                        background: rgba(255, 255, 255, 0.03);
                        backdrop-filter: blur(25px) saturate(200%);
                        -webkit-backdrop-filter: blur(25px) saturate(200%);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        border-radius: 16px;
                        padding: 1rem 1.5rem;
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
                        font-size: 1.8rem;
                        font-weight: 900;
                        display: block;
                        margin-bottom: 0.2rem;
                        letter-spacing: -0.04em;
                    }
                    .vna-stat-label {
                        font-size: 0.65rem;
                        color: var(--text-muted);
                        text-transform: uppercase;
                        letter-spacing: 0.12em;
                        font-weight: 700;
                        opacity: 0.7;
                    }
                    @media (max-width: 768px) {
                        .vna-stats-bar-wrapper { margin: 0.5rem auto 1.5rem auto; padding: 0.75rem 0.5rem; border-radius: 12px; }
                        .vna-stats-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.25rem; }
                        .vna-stat-value { font-size: 1.3rem; margin-bottom: 0.1rem; }
                        .vna-stat-label { font-size: 0.52rem; letter-spacing: 0.04em; }
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
                        <span class="vna-stat-value" style="color: #00A58B; text-shadow: 0 0 15px rgba(16, 185, 129, 0.3);">{{ $activeCount }}</span>
                        <span class="vna-stat-label">Active Tools</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #f59e0b; text-shadow: 0 0 15px rgba(245, 158, 11, 0.3);">{{ $comingSoonCount }}</span>
                        <span class="vna-stat-label">Coming Soon</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #38bdf8; text-shadow: 0 0 15px rgba(56, 189, 248, 0.3);">100+</span>
                        <span class="vna-stat-label">Active Clients</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #a855f7; text-shadow: 0 0 15px rgba(168, 85, 247, 0.3);">20+</span>
                        <span class="vna-stat-label">Partnerships</span>
                    </div>
                </div>
            </div>
            <h2 id="tools" style="font-family: var(--font-heading); font-size: clamp(1.4rem, 3vw, 2rem); margin: 0.5rem 0 1rem; color: #fff;">AI Tools & Solutions</h2>

            <div x-data="{ filter: 'all' }" style="width: 100%;">
                <!-- Filter Bar -->
                <div class="filter-bar" style="margin-bottom: 1.25rem;">
                    <button @click="filter = 'all'" :class="filter === 'all' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-th-large mr-1"></i> All Tools
                    </button>
                    <button @click="filter = 'seo'" :class="filter === 'seo' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-search mr-1"></i> SEO & Search
                    </button>
                    <button @click="filter = 'marketing'" :class="filter === 'marketing' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-bullhorn mr-1"></i> Marketing & Ads
                    </button>
                    <button @click="filter = 'content'" :class="filter === 'content' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-pen-nib mr-1"></i> Content & Writing
                    </button>
                    <button @click="filter = 'intelligence'" :class="filter === 'intelligence' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-brain mr-1"></i> Intelligence
                    </button>
                    <button @click="filter = 'tools'" :class="filter === 'tools' ? 'active' : ''" class="filter-btn">
                        <i class="fas fa-toolbox mr-1"></i> Power Tools
                    </button>
                </div>

<style>
/* Responsive Mobile Optimization for AI Tools & Solutions */
@media (max-width: 768px) {
    .filter-bar {
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 0.35rem !important;
        margin-bottom: 1rem !important;
    }
    .filter-btn {
        padding: 0.32rem 0.65rem !important;
        font-size: 0.72rem !important;
        border-radius: 20px !important;
    }
    .filter-btn i {
        font-size: 0.68rem !important;
        margin-right: 0.2rem !important;
    }
    .tools-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.6rem !important;
    }
    .tool-card {
        padding: 0.9rem 0.65rem !important;
        border-radius: 14px !important;
    }
    .tool-icon {
        width: 36px !important;
        height: 36px !important;
        font-size: 1rem !important;
        border-radius: 10px !important;
        margin-bottom: 0.5rem !important;
    }
    .tool-card h3 {
        font-size: 0.9rem !important;
        margin-bottom: 0.25rem !important;
        line-height: 1.25 !important;
    }
    .tool-card p {
        font-size: 0.7rem !important;
        line-height: 1.35 !important;
        margin-bottom: 0.65rem !important;
        opacity: 0.8 !important;
    }
    .tool-card .vn-btn {
        padding: 0.4rem 0.5rem !important;
        font-size: 0.72rem !important;
        border-radius: 8px !important;
    }
    .coming-soon-pill {
        padding: 0.45rem 0.85rem !important;
        font-size: 0.68rem !important;
        letter-spacing: 1px !important;
    }
    .coming-soon-pill i {
        font-size: 0.75rem !important;
    }
}

/* 100% Image-Style Heavy Blur on Coming Soon Tool Cards */
.tool-card.is-coming-soon,
.dash-tool-card.is-coming-soon {
    position: relative !important;
    overflow: hidden !important;
    cursor: not-allowed !important;
    user-select: none !important;
    background: rgba(13, 18, 33, 0.7) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
}

.tool-card.is-coming-soon:hover,
.dash-tool-card.is-coming-soon:hover {
    transform: none !important;
    box-shadow: none !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

.tool-card.is-coming-soon .blur-content,
.dash-tool-card.is-coming-soon .blur-content {
    filter: blur(14px) !important;
    -webkit-filter: blur(14px) !important;
    opacity: 0.25 !important;
    pointer-events: none !important;
    user-select: none !important;
    transform: scale(0.96) !important;
}

.coming-soon-glass-cover {
    position: absolute !important;
    inset: 0 !important;
    z-index: 30 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: rgba(5, 8, 16, 0.4) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    pointer-events: all !important;
}

.coming-soon-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.6rem !important;
    padding: 0.7rem 1.4rem;
    background: rgba(15, 23, 42, 0.92) !important;
    border: 1px solid rgba(0, 168, 230, 0.5) !important;
    border-radius: 50px !important;
    box-shadow: 0 0 30px rgba(0, 168, 230, 0.35), inset 0 0 12px rgba(0, 168, 230, 0.15) !important;
    color: #ffffff !important;
    font-size: 0.82rem;
    font-weight: 900 !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
}

.coming-soon-pill i {
    color: var(--primary-cyan, #00A8E6) !important;
    font-size: 0.9rem;
    filter: drop-shadow(0 0 8px rgba(0, 168, 230, 0.9)) !important;
}

html[data-theme="light"] .tool-card.is-coming-soon,
html[data-theme="light"] .dash-tool-card.is-coming-soon {
    background: rgba(241, 245, 249, 0.8) !important;
    border-color: rgba(0, 0, 0, 0.08) !important;
}
html[data-theme="light"] .coming-soon-glass-cover {
    background: rgba(255, 255, 255, 0.35) !important;
}
html[data-theme="light"] .coming-soon-pill {
    background: rgba(255, 255, 255, 0.95) !important;
    color: #0f172a !important;
    border-color: rgba(0, 168, 230, 0.5) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}
</style>

                <div class="tools-grid">
                    @php
                        $activeToolsOnly = collect($tools)->filter(fn($t) => (bool)$t['is_available']);
                        $comingSoonTools = collect($tools)->filter(fn($t) => !(bool)$t['is_available']);
                    @endphp

                    {{-- Active Tools Cards --}}
                    @foreach($activeToolsOnly as $tool)
                        <div class="tool-card" x-show="filter === 'all' || filter === '{{ $tool['category'] ?? '' }}'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="tool-card-body" style="display: flex; flex-direction: column; height: 100%;">
                                @if(!$tool['is_owned'])
                                     <div style="position: absolute; top: 0.85rem; right: 0.85rem; background: rgba(168, 85, 247, 0.15); backdrop-filter: blur(5px); color: #a855f7; font-size: 0.62rem; font-weight: 800; padding: 0.3rem 0.65rem; border-radius: 20px; border: 1px solid rgba(168, 85, 247, 0.3); z-index: 10; letter-spacing: 1px;">
                                        <i class="fas fa-shopping-cart mr-1"></i> MARKETPLACE
                                    </div>
                                @endif
                                <div class="tool-icon" style="color: {{ $tool['color'] }};">
                                    <i class="fas {{ $tool['icon'] }}"></i>
                                </div>
                                <h3>{{ $tool['name'] }}</h3>
                                <p style="flex-grow: 1;">{{ $tool['tagline'] ?? $tool['name'] }}</p>
                                
                                <a href="/tools/{{ $tool['slug'] }}" class="vn-btn vn-btn-primary" style="width: 100%;">
                                    <span>Explore Tool</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach

                    {{-- Single Unified Coming Soon Teaser Card --}}
                    @if($comingSoonTools->isNotEmpty())
                        <div class="tool-card coming-soon-teaser-card" x-show="filter === 'all' || ['seo', 'marketing', 'content', 'intelligence', 'tools'].includes(filter)" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="background: radial-gradient(circle at top left, rgba(0, 168, 230, 0.09), rgba(15, 23, 42, 0.7)); border: 1.5px dashed rgba(0, 168, 230, 0.35); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; text-align: left;">
                            <div style="position: absolute; top: 0.85rem; right: 0.85rem; background: rgba(0, 168, 230, 0.15); border: 1px solid rgba(0, 168, 230, 0.35); color: var(--primary-cyan, #00A8E6); font-size: 0.62rem; font-weight: 800; padding: 0.3rem 0.65rem; border-radius: 20px; letter-spacing: 0.5px;">
                                <i class="fas fa-sparkles mr-1"></i> PIPELINE
                            </div>
                            
                            <div>
                                <div class="tool-icon" style="color: #00A8E6; background: rgba(0, 168, 230, 0.12); border: 1px solid rgba(0, 168, 230, 0.25);">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <h3 style="color: #fff; font-size: clamp(1rem, 2vw, 1.25rem); margin-bottom: 0.4rem;">
                                    +{{ $comingSoonTools->count() }} More AI Tools
                                </h3>
                                <p style="color: var(--text-muted); font-size: 0.78rem; line-height: 1.45; margin-bottom: 1rem;">
                                    New specialized solutions for Article Writing, Competitor X-Ray, Velocity Auditing, and Folio OCR are currently in active engineering.
                                </p>
                            </div>

                            <div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.6rem 0.9rem; background: rgba(0, 168, 230, 0.08); border: 1px solid rgba(0, 168, 230, 0.25); border-radius: 10px; color: var(--primary-cyan); font-size: 0.78rem; font-weight: 700;">
                                    <i class="fas fa-clock"></i>
                                    <span>Coming Soon to VidaNexus</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
        </main>
