        <main class="hero" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: auto; padding: 2rem 1rem 1rem;">
            <p class="hero-pretitle" style="color: var(--primary-cyan); font-weight: 800; letter-spacing: 3px; text-transform: uppercase; font-size: 0.75rem; margin-bottom: 0.75rem; text-shadow: 0 0 15px var(--primary-cyan);">Next-Gen AI Solutions For Business Growth</p>
            <h1 class="hero-title" style="margin: 0 auto 1.25rem;">
                <span class="line-1">The Future is</span>
                <span class="line-2">
                    <span class="word-vida">VIDA</span>
                    <span class="word-nexus">NEXUS</span>
                </span>
            </h1>
            <p class="hero-subtitle" style="margin: 0 auto 2rem; max-width: 820px; font-weight: 400; line-height: 1.6; font-size: clamp(0.88rem, 1.8vw, 1.15rem);">
                Supercharge your business growth and digital presence with specialized AI engines for market discovery, competitive intelligence, and automated high-impact execution. 
                <span style="color: var(--text-main); font-weight: 600;">Built to scale your operations.</span>
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
                        <i class="fas fa-cubes"></i>
                        AI GROWTH SUITE
                    </div>
                    <h2 class="vna-title">The Vida Nexus <span style="background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Advantage</span></h2>
                    <p class="vna-subtitle">Enterprise-grade AI solutions engineered to accelerate business growth, insights, and scaling.</p>
                </div>

                <div class="vna-grid">
                    <!-- Feature 1: Autonomous AI Solutions -->
                    <div class="vna-card" style="--vna-accent: #a855f7;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            AI Intelligence
                        </h3>
                        <p class="vna-card-desc">Real-time market radars, automated analyzers, and actionable intelligence engines.</p>
                    </div>

                    <!-- Feature 2: Pay-As-You-Go Credits -->
                    <div class="vna-card" style="--vna-accent: #00A8E6;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Scalable Growth
                        </h3>
                        <p class="vna-card-desc">Pay-as-you-grow wallet pricing with complete budget control and zero waste.</p>
                    </div>

                    <!-- Feature 3: Fast & Reliable -->
                    <div class="vna-card" style="--vna-accent: #00A58B;">
                        <div class="vna-icon-main" style="color: var(--vna-accent);">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="vna-card-title">
                            <span class="vna-dot"></span>
                            Enterprise Speed
                        </h3>
                        <p class="vna-card-desc">High-speed cloud infrastructure delivering 24/7 autonomous scans and instant output.</p>
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
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #00f3ff; text-shadow: 0 0 15px rgba(0, 243, 255, 0.35);">100K+</span>
                        <span class="vna-stat-label">Keywords Scanned</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #10b981; text-shadow: 0 0 15px rgba(16, 185, 129, 0.35);">50K+</span>
                        <span class="vna-stat-label">Articles Generated</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #38bdf8; text-shadow: 0 0 15px rgba(56, 189, 248, 0.35);">100+</span>
                        <span class="vna-stat-label">Active Publishers</span>
                    </div>
                    <div class="vna-stat-item">
                        <span class="vna-stat-value" style="color: #a855f7; text-shadow: 0 0 15px rgba(168, 85, 247, 0.35);">24/7</span>
                        <span class="vna-stat-label">Autonomous Scans</span>
                    </div>
                </div>
            </div>
            <h2 id="tools" style="font-family: var(--font-heading); font-size: clamp(1.4rem, 3vw, 2rem); margin: 0.5rem 0 1.5rem; color: #fff;">AI Tools & Solutions</h2>

<style>
/* Responsive Grid and Mobile Optimization for AI Tools & Solutions */
.tools-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 1.5rem !important;
    width: 100% !important;
    max-width: 1200px !important;
    margin: 0 auto !important;
    justify-content: center !important;
}

.tool-card {
    width: 100% !important;
    max-width: 380px !important;
    margin: 0 auto !important;
    box-sizing: border-box !important;
}

@media (max-width: 768px) {
    .tools-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.6rem !important;
    }
    .tool-card {
        padding: 0.9rem 0.65rem !important;
        border-radius: 14px !important;
        max-width: 100% !important;
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
}
</style>

            <div style="width: 100%;">
                <div class="tools-grid">
                    @php
                        $activeToolsOnly = collect($tools)->filter(fn($t) => (bool)$t['is_available']);
                        $comingSoonCount = count($tools) - $activeToolsOnly->count();
                    @endphp

                    {{-- Render ONLY Live, Working Tools --}}
                    @foreach($activeToolsOnly as $tool)
                        <div class="tool-card">
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
                </div>

                {{-- Clean & Prestigious Pipeline Status Indicator --}}
                @if($comingSoonCount > 0)
                    <div style="margin: 2.5rem auto 1rem auto; text-align: center;">
                        <div style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.55rem 1.25rem; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 99px; backdrop-filter: blur(20px); max-width: 90%; flex-wrap: wrap; justify-content: center;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: rgba(0, 168, 230, 0.18); border: 1px solid rgba(0, 168, 230, 0.4); color: var(--primary-cyan, #00A8E6); font-size: 0.72rem; box-shadow: 0 0 10px rgba(0, 168, 230, 0.3);">
                                <i class="fas fa-wand-magic-sparkles"></i>
                            </span>
                            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                                <strong style="color: #fff; font-weight: 700;">+{{ $comingSoonCount }} More AI Tools</strong> in active pipeline for Content, Marketing & Market Intelligence.
                            </span>
                        </div>
                    </div>
                @endif
            </div>
            
        </main>
