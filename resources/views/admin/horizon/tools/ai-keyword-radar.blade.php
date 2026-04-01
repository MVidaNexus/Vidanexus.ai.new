@extends('admin.horizon.layout')

@section('title', "Control: " . $tool['name'])

@section('styles')
<style>
    .ai-provider-row {
        background: var(--horizon-nav-hover) !important;
        border: 1px solid var(--horizon-border) !important;
    }
    
    .ai-input-base {
        background: var(--vn-input-bg) !important;
        border: 1px solid var(--vn-input-border) !important;
        color: var(--text-main) !important;
        transition: all 0.3s ease;
    }
    
    .ai-input-base:focus-within {
        border-color: var(--vn-input-focus) !important;
        box-shadow: 0 0 10px rgba(14, 165, 233, 0.1);
    }
    
    .ai-chain-label {
        color: var(--text-main) !important;
    }

    .ai-provider-row {
        background: var(--horizon-nav-hover) !important;
        border: 1px solid var(--horizon-border) !important;
        backdrop-filter: blur(10px);
    }
</style>
@endsection

@section('content')
<style>
    .horizon-tabs { display: flex; gap: 0.25rem; margin-bottom: 2rem; border-bottom: 1px solid var(--horizon-border); padding-bottom: 1rem; overflow-x: auto; scrollbar-width: none; }
    .horizon-tabs::-webkit-scrollbar { display: none; }
    .horizon-tab-btn { background: none; border: 1px solid transparent; color: var(--text-muted); font-size: 0.8rem; font-weight: 600; padding: 0.6rem 0.8rem; cursor: pointer; transition: all 0.3s; border-radius: 8px; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
    .horizon-tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }
    .horizon-tab-btn.active { color: var(--primary-admin); background: rgba(14, 165, 233, 0.1); border-color: rgba(14, 165, 233, 0.3); }
    .horizon-tab-pane { display: none; animation: fadeIn 0.3s ease; }
    .horizon-tab-pane.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div style="max-width: 1100px; margin: 0 auto;">
    <div class="card-admin">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1.2rem;">
                <i class="fas {{ $tool['icon'] }}"></i>
            </div>
            <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">Core Intelligence Center</h2>
        </div>

        <div class="horizon-tabs" style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
            <button type="button" class="horizon-tab-btn active" onclick="switchHorizonTab('ai', this)"><i class="fas fa-brain"></i> Intelligence</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('sources', this)"><i class="fas fa-satellite-dish"></i> Sources</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('filters', this)"><i class="fas fa-filter"></i> Filters</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('strategy', this)"><i class="fas fa-layer-group"></i> Strategy Engine</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('credits', this)"><i class="fas fa-coins"></i> Credit System</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('apis', this)"><i class="fas fa-network-wired"></i> AI Routing</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('stats', this)"><i class="fas fa-chart-line"></i> Analytics</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('errors', this)"><i class="fas fa-bug"></i> Errors</button>
        </div>

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST" id="config-form">
            @csrf

            <!-- TAB 1: AI Intelligence -->
            <div id="pane-ai" class="horizon-tab-pane active">
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">System Prompt / AI Extraction Rules</label>
                    <textarea name="prompt" rows="10" class="mono ai-input-base" style="width: 100%; border-radius: 12px; padding: 1.5rem; line-height: 1.6; font-size: 0.95rem; outline: none; border: 1px solid var(--horizon-border); background: rgba(0,0,0,0.2);">{{ $settings['prompt'] }}</textarea>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                        <i class="fas fa-info-circle"></i> This prompt controls how the AI transforms raw headlines into "Target Search Queries".
                    </p>
                </div>

                <div class="card-admin" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(191, 0, 255, 0.05)); border: 1px solid var(--primary-admin); margin-bottom: 2rem; padding: 1.5rem; border-radius: 16px;">
                    <h3 style="margin: 0 0 1rem; font-size: 1rem;"><i class="fas fa-microchip" style="color: var(--secondary-admin);"></i> Core Intelligence Rules</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        Modifying the system prompt for <strong>{{ $tool['name'] }}</strong> will impact all future keyword extraction cycles. Use precise constraints for optimal SEO discovery.
                    </p>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Intelligence Configuration
                </button>
            </div>

            <!-- TAB 2: Radar Sources -->
            <div id="pane-sources" class="horizon-tab-pane">
                <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem;">
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-main); line-height: 1.5;">
                        <i class="fas fa-satellite-dish" style="color: var(--primary-cyan); margin-right: 0.5rem;"></i>
                        These global sources provide the foundational data for the Radar. User-added URLs will be intelligently merged at runtime.
                    </p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Global Competitors (One URL per line)</label>
                    <textarea name="competitors" rows="8" class="mono ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.85rem; outline: none; border: 1px solid var(--horizon-border); background: rgba(0,0,0,0.2);" placeholder="https://competitor.com/news">{{ $settings['competitors'] }}</textarea>
                </div>

                <div style="margin-bottom: 2.5rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Global RSS Feeds</label>
                    <textarea name="rss_feeds" rows="6" class="mono ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.85rem; outline: none; border: 1px solid var(--horizon-border); background: rgba(0,0,0,0.2);" placeholder="https://example.com/feed.xml">{{ $settings['rss_feeds'] }}</textarea>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Global Sources
                </button>
            </div>

            <div id="pane-filters" class="horizon-tab-pane">
                <div style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem;">
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-main); line-height: 1.5;">
                        <i class="fas fa-sliders-h" style="color: var(--secondary-admin); margin-right: 0.5rem;"></i>
                        Define the global quality rules for Keyword Extraction. These boundaries filter out poor results (like single letters or full paragraphs) before saving them to the database.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; padding: 2rem; background: rgba(0,0,0,0.15); border: 1px solid var(--horizon-border); border-radius: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Minimum Characters</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-text-width" style="color: var(--primary-admin); font-size: 0.9rem;"></i>
                            <input type="number" min="1" max="50" name="min_chars" value="{{ App\Models\Setting::get('ai-keyword-radar_min_chars', 8) }}" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-size: 1rem; font-weight: 600;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">Any keyword with fewer characters than this number will be rejected and not saved.</p>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Minimum Words</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-grip-lines" style="color: var(--primary-admin); font-size: 0.9rem;"></i>
                            <input type="number" min="1" max="20" name="min_words" value="{{ App\Models\Setting::get('ai-keyword-radar_min_words', 2) }}" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-size: 1rem; font-weight: 600;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">Avoid catching generic 1-word tags like "The". Set to 2 or more to capture precise phrases.</p>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Maximum Words</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-align-justify" style="color: var(--primary-admin); font-size: 0.9rem;"></i>
                            <input type="number" min="3" max="50" name="max_words" value="{{ App\Models\Setting::get('ai-keyword-radar_max_words', 12) }}" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-size: 1rem; font-weight: 600;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">Stop the AI from accidentally capturing entire sentences. 12 is recommended.</p>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Duplicate Sensitivity (%)</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-clone" style="color: var(--primary-admin); font-size: 0.9rem;"></i>
                            <input type="number" min="50" max="100" name="similarity_threshold" value="{{ App\Models\Setting::get('ai-keyword-radar_similarity_threshold', 96) }}" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-size: 1rem; font-weight: 600;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">How strict the system is matching similar words. 100% means exact match; 90% merges singular/plural terms.</p>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Global Filters
                </button>
            </div>

            <!-- TAB 3: Strategy Engine -->
            <div id="pane-strategy" class="horizon-tab-pane">
                <style>
                    .draggable-item {
                        background: rgba(255,255,255,0.03);
                        border: 1px solid var(--horizon-border);
                        border-radius: 12px;
                        padding: 1.25rem 1.75rem;
                        margin-bottom: 1rem;
                        cursor: grab;
                        display: flex;
                        align-items: center;
                        gap: 1.5rem;
                        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                        position: relative;
                        overflow: hidden;
                    }
                    .draggable-item:hover {
                        border-color: var(--primary-admin);
                        background: rgba(14, 165, 233, 0.05);
                        transform: translateX(4px);
                    }
                    .draggable-item.dragging {
                        opacity: 0.4;
                        background: var(--horizon-primary-bg);
                        border: 1px dashed var(--primary-admin);
                        transform: scale(0.98);
                    }
                    .drag-handle {
                        color: var(--text-muted);
                        cursor: grab;
                        font-size: 1rem;
                        opacity: 0.5;
                    }
                    .strategy-label {
                        flex: 1;
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--text-main);
                        font-family: 'Space Grotesk', sans-serif;
                    }
                    .strategy-tag {
                        font-size: 0.6rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 1.5px;
                        padding: 4px 10px;
                        border-radius: 6px;
                        background: rgba(14, 165, 233, 0.1);
                        color: var(--primary-admin);
                        border: 1px solid rgba(14, 165, 233, 0.2);
                    }
                    /* Custom Pagination Styles */
                    .horizon-pagination nav {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        gap: 0.5rem;
                    }
                    .horizon-pagination .pagination {
                        display: flex;
                        list-style: none;
                        padding: 0;
                        margin: 0;
                        gap: 5px;
                    }
                    .horizon-pagination .page-item {
                        display: inline-block;
                    }
                    .horizon-pagination .page-link {
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid var(--horizon-border);
                        color: var(--text-muted);
                        padding: 8px 14px;
                        border-radius: 10px;
                        text-decoration: none;
                        font-family: 'Space Grotesk', sans-serif;
                        font-weight: 600;
                        font-size: 0.9rem;
                        transition: all 0.2s;
                    }
                    .horizon-pagination .page-link:hover {
                        background: rgba(255, 255, 255, 0.1);
                        color: var(--text-main);
                        border-color: var(--primary-admin);
                    }
                    .horizon-pagination .page-item.active .page-link {
                        background: var(--primary-admin);
                        color: #fff;
                        border-color: var(--primary-admin);
                        box-shadow: 0 0 15px rgba(14, 165, 233, 0.3);
                    }
                    .horizon-pagination .page-item.disabled .page-link {
                        opacity: 0.3;
                        cursor: not-allowed;
                    }
                    .horizon-pagination svg {
                        width: 20px;
                        height: 20px;
                    }
                    .horizon-pagination .hidden {
                        display: none !important;
                    }
                    .horizon-pagination div:first-child {
                        margin-bottom: 1rem;
                        color: var(--text-muted);
                        font-size: 0.8rem;
                        text-align: center;
                    }
                </style>

                <div style="margin-bottom: 3rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-microchip" style="color: var(--primary-admin);"></i>
                        <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-main); font-family: 'Space Grotesk', sans-serif;">Fallback Scrutiny Order</h3>
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem; line-height: 1.6;">Define the priority of discovery methods. The system will cycle through these in order until results are found.</p>
                    
                    <div id="strategy-sortable-list">
                        @php
                            $savedStrats = App\Models\Setting::get('ai-keyword-radar_strategies', 'sitemap,google_html,google_news,rss,html_scrape');
                            $stratList = explode(',', $savedStrats);
                            $stratNames = [
                                'sitemap' => ['name' => 'Sitemap Index', 'desc' => 'Instant detection via structured site maps', 'icon' => 'fas fa-sitemap'],
                                'google_html' => ['name' => 'Google Search', 'desc' => 'Deep search via localized Google results', 'icon' => 'fab fa-google'],
                                'google_news' => ['name' => 'Google News', 'desc' => 'High-velocity discovery via News RSS', 'icon' => 'fas fa-newspaper'],
                                'rss' => ['name' => 'RSS Feed', 'desc' => 'Direct monitoring of website feed endpoints', 'icon' => 'fas fa-rss'],
                                'html_scrape' => ['name' => 'Site Scraping', 'desc' => 'Heuristic analysis of homepage headlines', 'icon' => 'fas fa-code']
                            ];
                        @endphp

                        @foreach($stratList as $strat)
                            @php $strat = trim($strat); @endphp
                            @if(isset($stratNames[$strat]))
                                <div class="draggable-item" draggable="true" data-id="{{ $strat }}">
                                    <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--primary-admin); border: 1px solid rgba(255,255,255,0.1);">
                                        <i class="{{ $stratNames[$strat]['icon'] }}"></i>
                                    </div>
                                    <div class="strategy-label">
                                        {{ $stratNames[$strat]['name'] }}
                                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400; margin-top: 3px;">{{ $stratNames[$strat]['desc'] }}</div>
                                    </div>
                                    <div class="strategy-tag">Priority {{ $loop->iteration }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <input type="hidden" name="strategies" id="strategies-hidden-input" value="{{ $savedStrats }}">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; padding: 2rem; background: rgba(0,0,0,0.15); border: 1px solid var(--horizon-border); border-radius: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Scraping Limit (Depth)</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-layer-group" style="color: var(--primary-admin); font-size: 0.9rem;"></i>
                            <input type="number" name="scraping_depth" value="{{ App\Models\Setting::get('ai-keyword-radar_scraping_depth', 20) }}" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-size: 1rem; font-weight: 600;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">Headlines per site per cycle</p>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">SerpApi Fallback Token</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 12px; padding: 0 1rem; background: rgba(0,0,0,0.2) !important;">
                            <i class="fas fa-shield-alt" style="color: var(--primary-cyan); font-size: 0.9rem;"></i>
                            <input type="password" name="serpapi_key" value="{{ $settings['serpapi_key'] ?? '' }}" placeholder="Enter key (Optional)" style="flex: 1; background: transparent; border: none; color: #fff; padding: 1.25rem 0; outline: none; font-family: 'JetBrains Mono'; font-size: 0.9rem;">
                        </div>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.75rem;">Used for reliable Google News indexing</p>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-rocket"></i> Apply Engine Configurations
                </button>
            </div>

            <!-- TAB 4: Credit System -->
            <div id="pane-credits" class="horizon-tab-pane">
                <div style="max-width: 650px; margin: 0 auto; padding: 1rem 0;">
                    <div style="text-align: center; margin-bottom: 3.5rem;">
                        <div style="width: 90px; height: 90px; border-radius: 24px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border: 1px solid rgba(245, 158, 11, 0.2); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 3rem; margin: 0 auto 1.5rem; transform: rotate(-5deg);">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.75rem; margin: 0 0 0.5rem; color: var(--text-main);">Financial Unit Calibration</h3>
                        <p style="color: var(--text-muted); font-size: 1rem;">Set the operational cost for each manual tool synchronization.</p>
                    </div>

                    <div style="background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%); border: 1px solid var(--horizon-border); border-radius: 28px; padding: 3rem; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
                        <label style="display: block; font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1.5rem;">Sync Multiplier</label>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-bottom: 2.5rem;">
                            <input type="number" name="sync_credits" value="{{ App\Models\Setting::get('ai-keyword-radar_sync_credits', 1) }}" style="width: 160px; background: #000; border: 2px solid var(--primary-admin); color: var(--primary-admin); padding: 1.25rem; border-radius: 20px; font-size: 3rem; font-weight: 800; text-align: center; outline: none; font-family: 'Space Grotesk'; box-shadow: 0 0 30px rgba(14, 165, 233, 0.2);">
                            <span style="font-size: 1.5rem; font-weight: 600; color: var(--text-muted); opacity: 0.6;">CREDITS</span>
                        </div>
                        
                        <div style="background: rgba(14, 165, 233, 0.05); border-radius: 16px; padding: 1.25rem; border: 1px solid rgba(14, 165, 233, 0.1); display: flex; align-items: flex-start; gap: 1rem; text-align: left;">
                            <i class="fas fa-info-circle" style="color: var(--primary-admin); margin-top: 3px;"></i>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">
                                This value represents the total credits deducted from a user's wallet upon initiating a manual "Sync". automated tasks are cost-neutral.
                            </p>
                        </div>
                    </div>

                    <button type="submit" class="btn-save" style="width: 100%; margin-top: 2.5rem; height: 60px; font-size: 1.1rem;">
                        <i class="fas fa-check-circle"></i> Confirm Credit Settings
                    </button>
                </div>
            </div>

            <!-- TAB 5: AI Routing -->
            <div id="pane-apis" class="horizon-tab-pane">
                @php
                    $aiChainJson = App\Models\Setting::get($tool['slug'] . '_ai_chain');
                    $aiChain = is_array($aiChainJson) ? $aiChainJson : ($aiChainJson ? json_decode($aiChainJson, true) : null);
                    if (!$aiChain || count($aiChain) === 0) {
                        $aiChain = [['provider' => $settings['provider'] ?? 'openrouter', 'model' => $settings['model'] ?? '', 'api_key' => $settings['api_key'] ?? '']];
                    }
                @endphp

                <div style="margin-bottom: 2.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
                        <label class="ai-chain-label" style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; color: var(--text-main);">
                            <i class="fas fa-network-wired" style="color: var(--primary-admin); margin-right: 0.75rem;"></i> Intelligence Routing Chain
                        </label>
                        <button type="button" onclick="addAiProviderRow()" class="vn-btn vn-btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 10px;">
                            <i class="fas fa-plus mr-1"></i> Add Provider
                        </button>
                    </div>
                    
                    <div id="ai-chain-container" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @foreach($aiChain as $index => $link)
                            <div class="ai-provider-row" style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 1.25rem; align-items: center; padding: 1.5rem; border-radius: 20px; position: relative; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border);">
                                @if($index === 0)
                                    <div style="position: absolute; top: -12px; left: 1.5rem; background: var(--primary-admin); color: #000; font-size: 0.65rem; font-weight: 900; padding: 3px 12px; border-radius: 6px; text-transform: uppercase; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);">Primary Engine</div>
                                @else
                                    <div class="fallback-label-v2" style="position: absolute; top: -12px; left: 1.5rem; background: #333; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 12px; border-radius: 6px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.1);">Fallback Node #{{ $index }}</div>
                                @endif
                                
                                <div style="min-width: 0;">
                                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Provider</label>
                                    <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; cursor: pointer; outline: none; font-size: 0.9rem; background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1);">
                                        <option value="openai" {{ ($link['provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI</option>
                                        <option value="google" {{ ($link['provider'] ?? '') == 'google' ? 'selected' : '' }}>Google Gemini</option>
                                        <option value="openrouter" {{ ($link['provider'] ?? '') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                                        <option value="anthropic" {{ ($link['provider'] ?? '') == 'anthropic' ? 'selected' : '' }}>Anthropic</option>
                                    </select>
                                </div>
                                <div style="min-width: 0;">
                                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Model Overwrite</label>
                                    <input type="text" name="models[]" value="{{ $link['model'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none; font-size: 0.9rem; background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. gpt-4o">
                                </div>
                                <div style="min-width: 0;">
                                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Dedicated API Key</label>
                                    <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 10px; padding: 0 1rem; background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1);">
                                        <i class="fas fa-lock" style="color: var(--text-muted); font-size: 0.8rem; opacity: 0.5;"></i>
                                        <input type="password" name="api_keys[]" value="{{ $link['api_key'] ?? '' }}" placeholder="Global key fallback..." style="flex: 1; background: transparent; border: none; color: #fff; padding: 0.85rem 0; outline: none; font-family: 'JetBrains Mono'; font-size: 0.85rem;">
                                    </div>
                                </div>
                                <div style="padding-top: 1.5rem;">
                                    <button type="button" onclick="this.closest('.ai-provider-row').remove(); updateFallbackLabels();" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; width: 42px; height: 42px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="background: rgba(14, 165, 233, 0.05); border-radius: 16px; padding: 1.5rem; border: 1px solid rgba(14, 165, 233, 0.1); display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 2rem;">
                    <i class="fas fa-info-circle" style="color: var(--primary-admin); margin-top: 3px;"></i>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">
                        High-availability routing ensures that if the Primary node fails (rate limited or offline), the system will automatically attempt extraction using fallback nodes in sequence.
                    </p>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; height: 55px; font-size: 1rem;">
                    <i class="fas fa-save"></i> Save Global Routing Parameters
                </button>
            </div>

            <!-- Template for new provider row -->
            <template id="ai-provider-template">
                <div class="ai-provider-row" style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 1.25rem; align-items: center; padding: 1.5rem; border-radius: 20px; position: relative; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border);">
                    <div class="fallback-label-v2" style="position: absolute; top: -12px; left: 1.5rem; background: #333; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 12px; border-radius: 6px; text-transform: uppercase;">Fallback Node</div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Provider</label>
                        <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; cursor: pointer; outline: none; font-size: 0.9rem;">
                            <option value="openai">OpenAI</option>
                            <option value="google">Google Gemini</option>
                            <option value="openrouter">OpenRouter</option>
                            <option value="anthropic">Anthropic</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Model Override</label>
                        <input type="text" name="models[]" value="" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none; font-size: 0.9rem;" placeholder="e.g. gpt-4o">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.6rem; text-transform: uppercase; font-weight: 600;">Dedicated API Key</label>
                        <div class="ai-input-base" style="display: flex; align-items: center; gap: 0.75rem; border-radius: 10px; padding: 0 1rem;">
                            <i class="fas fa-lock" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                            <input type="password" name="api_keys[]" value="" placeholder="Global key fallback..." style="flex: 1; background: transparent; border: none; color: #fff; padding: 0.85rem 0; outline: none; font-family: 'JetBrains Mono'; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div style="padding-top: 1.5rem;">
                        <button type="button" onclick="this.closest('.ai-provider-row').remove(); updateFallbackLabels();" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; width: 42px; height: 42px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </template>

            <script>
                function addAiProviderRow() {
                    const container = document.getElementById('ai-chain-container');
                    const template = document.getElementById('ai-provider-template');
                    const clone = template.content.cloneNode(true);
                    container.appendChild(clone);
                    updateFallbackLabels();
                }

                function updateFallbackLabels() {
                    const rows = document.querySelectorAll('#ai-chain-container .ai-provider-row');
                    rows.forEach((row, index) => {
                        const label = row.querySelector('.fallback-label-v2') || row.querySelector('div[style*="position: absolute"]');
                        if (label) {
                            if (index === 0) {
                                label.innerHTML = 'Primary Engine';
                                label.style.background = 'var(--primary-admin)';
                                label.style.color = '#000';
                            } else {
                                label.innerHTML = 'Fallback Node #' + index;
                                label.style.background = '#333';
                                label.style.color = '#fff';
                            }
                        }
                    });
                    
                    const trashButtons = document.querySelectorAll('#ai-chain-container .ai-provider-row button');
                    if(rows.length <= 1 && trashButtons[0]) {
                        trashButtons[0].style.display = 'none';
                    } else {
                        trashButtons.forEach(btn => btn.style.display = 'flex');
                    }
                }
                document.addEventListener('DOMContentLoaded', updateFallbackLabels);
            </script>
        </form>

        <!-- TAB 6: Analytics & Performance -->
        <div id="pane-analytics" class="horizon-tab-pane">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                <!-- Today -->
                <div style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(14, 165, 233, 0.02)); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-admin); font-size: 1.25rem;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 2px;">Daily Velocity</div>
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: 'Space Grotesk';">{{ number_format($stats['today_usage'] ?? 0) }}</div>
                    </div>
                </div>

                <!-- This Month -->
                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.02)); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 1.25rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 2px;">Monthly Volume</div>
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: 'Space Grotesk';">{{ number_format($stats['this_month_usage'] ?? 0) }}</div>
                    </div>
                </div>

                <!-- Lifetime -->
                <div style="background: linear-gradient(135deg, rgba(191, 0, 255, 0.1), rgba(191, 0, 255, 0.02)); border: 1px solid rgba(191, 0, 255, 0.2); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
                    <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(191, 0, 255, 0.1); display: flex; align-items: center; justify-content: center; color: #bf00ff; font-size: 1.25rem;">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 2px;">Lifetime Pulse</div>
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: 'Space Grotesk';">{{ number_format($stats['lifetime_usage'] ?? 0) }}</div>
                    </div>
                </div>

                @if($stats['filtered_usage'] !== null)
                    <!-- Filtered -->
                    <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.02)); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem;">
                        <div style="width: 50px; height: 50px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 1.25rem;">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; color: #f59e0b; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 700; margin-bottom: 2px;">Filtered Output</div>
                            <div style="font-size: 1.5rem; font-weight: 800; font-family: 'Space Grotesk';">{{ number_format($stats['filtered_usage']) }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Enhanced Filter -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 1.75rem; margin-bottom: 2.5rem;">
                <form action="{{ url()->current() }}" method="GET" style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
                    <input type="hidden" name="tab" value="analytics">
                    
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 1px; font-weight: 600;">Analysis Start Date</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 0.85rem; font-size: 0.9rem; background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1);">
                    </div>

                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem; letter-spacing: 1px; font-weight: 600;">Analysis End Date</label>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 0.85rem; font-size: 0.9rem; background: rgba(0,0,0,0.3) !important; border: 1px solid rgba(255,255,255,0.1);">
                    </div>

                    <div style="display: flex; gap: 0.75rem;">
                        <button type="submit" class="vn-btn vn-btn-primary" style="padding: 0.85rem 2rem; font-size: 0.9rem; border-radius: 12px;">
                            <i class="fas fa-search mr-2"></i> Update Analytics
                        </button>
                    </div>
                </form>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="margin: 0; font-size: 1.1rem; font-family: 'Space Grotesk', sans-serif; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-users-crown" style="color: var(--primary-admin);"></i> 
                        {{ (request('from_date') || request('to_date')) ? 'Activity Breakdown (Filtered)' : 'Core User Utilization' }}
                    </h3>
                </div>
                
                <table style="width: 100%; border-collapse: separate; border-spacing: 0 0.5rem;">
                    <thead>
                        <tr style="text-align: left; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800;">
                            <th style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--horizon-border);">User Profile</th>
                            <th style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--horizon-border); text-align: right;">AI Interactions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.95rem;">
                        @forelse($subscribers as $sub)
                            <tr style="background: rgba(255,255,255,0.01); transition: all 0.2s; border-radius: 12px;">
                                <td style="padding: 1.25rem 1.5rem; border-radius: 12px 0 0 12px; border: 1px solid transparent;">
                                    <div style="font-weight: 700; color: var(--text-main);">{{ $sub->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        @if($sub->wallet)
                                            {{ number_format($sub->wallet->balance_credits, 1) }} CRS
                                        @else
                                            Marketplace User
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1.5rem; border-radius: 0 12px 12px 0; text-align: right; border: 1px solid transparent; font-family: 'JetBrains Mono'; color: var(--primary-admin); font-weight: 700;">
                                    {{ number_format($sub->ai_usages_count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="padding: 4rem 0; text-align: center; color: var(--text-muted); font-style: italic;">
                                    <i class="fas fa-database" style="display: block; font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                                    No subscriber activity matching current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($subscribers->hasPages())
                    <div class="horizon-pagination" style="margin-top: 2rem;">
                        {{ $subscribers->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 7: Execution Errors -->
        <div id="pane-errors" class="horizon-tab-pane">
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.25rem; font-family: 'Space Grotesk', sans-serif; display: flex; align-items: center; gap: 0.75rem; color: #ef4444;">
                            <i class="fas fa-microchip-ai" style="animation: pulse 2s infinite;"></i> 
                            System Anomaly Logs
                        </h3>
                        <p style="margin: 5px 0 0; font-size: 0.85rem; color: var(--text-muted);">Real-time monitoring of AI processing failures</p>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 0 0.75rem; min-width: 900px;">
                        <thead>
                            <tr style="text-align: left; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; font-weight: 800;">
                                <th style="padding: 0 1.5rem;">Actor</th>
                                <th style="padding: 0 1.5rem;">Timestamp</th>
                                <th style="padding: 0 1.5rem;">Domain</th>
                                <th style="padding: 0 1.5rem;">Critical Intelligence</th>
                                <th style="padding: 0 1.5rem; text-align: right;">Diagnostics</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            @forelse($toolErrors as $error)
                                <tr style="background: rgba(0,0,0,0.2); border-radius: 16px; border: 1px solid var(--horizon-border);">
                                    <td style="padding: 1.5rem; border-radius: 16px 0 0 16px;">
                                        @if($error->user)
                                            <div style="font-weight: 700; color: var(--text-main);">{{ $error->user->name }}</div>
                                            <div style="font-size: 0.7rem; color: var(--text-muted); opacity: 0.6;">UID: {{ $error->user_id }}</div>
                                        @else
                                            <div style="color: var(--text-muted); font-weight: 700;">SYSTEM DAEMON</div>
                                        @endif
                                    </td>
                                    <td style="padding: 1.5rem; white-space: nowrap;">
                                        <div style="color: var(--text-main); font-weight: 600;">{{ $error->created_at->format('M d, H:i:s') }}</div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted); opacity: 0.6;">{{ $error->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td style="padding: 1.5rem;">
                                        <span style="display: inline-block; padding: 4px 10px; background: rgba(14, 165, 233, 0.1); border-radius: 8px; font-size: 0.75rem; color: var(--primary-admin); border: 1px solid rgba(14, 165, 233, 0.2); font-weight: 700; text-transform: uppercase;">
                                            {{ $error->component ?? 'Kernel' }}
                                        </span>
                                    </td>
                                    <td style="padding: 1.5rem; max-width: 350px;">
                                        <div style="color: #ef4444; font-weight: 600; line-height: 1.4; font-family: 'JetBrains Mono'; font-size: 0.8rem;">{{ $error->error_message }}</div>
                                    </td>
                                    <td style="padding: 1.5rem; text-align: right; border-radius: 0 16px 16px 0;">
                                        @if($error->payload)
                                            <button type="button" 
                                                    class="inspect-error-payload" 
                                                    data-payload="{{ json_encode($error->payload) }}"
                                                    data-title="Error Context: {{ $error->component ?? 'Kernel' }}"
                                                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-muted); padding: 8px 16px; border-radius: 10px; font-size: 0.75rem; cursor: pointer; transition: all 0.25s; font-weight: 700;">
                                                <i class="fas fa-terminal mr-2"></i> Inspect
                                            </button>
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.75rem; opacity: 0.4;">Log-Only</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 5rem 0; text-align: center; color: var(--text-muted);">
                                        <div style="width: 100px; height: 100px; background: rgba(16, 185, 129, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 1px solid rgba(16, 185, 129, 0.1);">
                                            <i class="fas fa-shield-check" style="font-size: 3rem; color: #10b981; opacity: 0.5;"></i>
                                        </div>
                                        <div style="font-family: 'Space Grotesk', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text-main);">Zero Anomalies Detected</div>
                                        <div style="font-size: 0.9rem; margin-top: 0.5rem; opacity: 0.6;">The radar system is operating within optimal parameters.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($toolErrors->hasPages())
                    <div class="horizon-pagination" style="margin-top: 2rem;">
                        {{ $toolErrors->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

    </div>

<script>
    function switchHorizonTab(tabId, element = null) {
        // Handle mapped tab IDs for backward compatibility if needed
        const mappedId = (tabId === 'stats' || tabId === 'analytics') ? 'analytics' : tabId;

        // Remove active from all buttons
        document.querySelectorAll('.horizon-tab-btn').forEach(b => b.classList.remove('active'));
        // Hide all panes
        document.querySelectorAll('.horizon-tab-pane').forEach(p => p.classList.remove('active'));
        
        // Activate target button
        const btn = element || document.querySelector(`.horizon-tab-btn[onclick*="'${tabId}'"]`);
        if (btn) btn.classList.add('active');
        
        // Show target pane
        const pane = document.getElementById('pane-' + mappedId);
        if (pane) pane.classList.add('active');

        // No longer persisting to localStorage to ensure first tab remains default
        // localStorage.setItem('horizon_active_tab_{{ $tool['slug'] }}', tabId);
    }

    // Handle tab persistence from URL
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            switchHorizonTab(tab);
        } else if (urlParams.has('errors_page')) {
            switchHorizonTab('errors');
        } else if (urlParams.has('from_date') || urlParams.has('to_date') || urlParams.has('stats_page')) {
            switchHorizonTab('analytics');
        } else {
            // Default to 'ai' (Intelligence) tab explicitly if no other indicators are present
            switchHorizonTab('ai');
        }

        // Initialize Strategy Drag and Drop
        initStrategySortable();
    });

    function initStrategySortable() {
        const list = document.getElementById('strategy-sortable-list');
        const input = document.getElementById('strategies-hidden-input');
        if (!list || !input) return;

        let dragItem = null;

        list.addEventListener('dragstart', (e) => {
            dragItem = e.target.closest('.draggable-item');
            if (dragItem) {
                setTimeout(() => dragItem.classList.add('dragging'), 0);
            }
        });

        list.addEventListener('dragend', () => {
            if (dragItem) {
                dragItem.classList.remove('dragging');
                dragItem = null;
                updateStrategyInput();
            }
        });

        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(list, e.clientY);
            if (afterElement == null) {
                list.appendChild(dragItem);
            } else {
                list.insertBefore(dragItem, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.draggable-item:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function updateStrategyInput() {
            const items = [...list.querySelectorAll('.draggable-item')];
            const ids = items.map(item => item.getAttribute('data-id'));
            input.value = ids.join(',');
            
            // Update "Priority #" tags
            items.forEach((item, idx) => {
                const tag = item.querySelector('.strategy-tag');
                if (tag) tag.innerHTML = 'Priority ' + (idx + 1);
            });
        }
    }
</script>
@endsection

@section('scripts')
    <!-- SweetAlert2 for Diagnostics -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Diagnostics Inspection Handler
        document.addEventListener('click', function(e) {
            if (e.target.closest('.inspect-error-payload')) {
                const btn = e.target.closest('.inspect-error-payload');
                if (typeof Swal === 'undefined') {
                    console.error("SweetAlert2 not loaded.");
                    alert("System error: Diagnostics library not loaded.");
                    return;
                }
                
                const rawPayload = btn.dataset.payload;
                const title = btn.dataset.title;
                
                try {
                    const payload = JSON.parse(rawPayload);
                    Swal.fire({
                        title: title,
                        html: `<pre style="text-align:left; font-size:0.75rem; background:#000; padding:20px; border-radius:12px; color:#0f0; direction:ltr; max-height:450px; overflow:auto; font-family: 'JetBrains Mono', monospace; margin:0;">${JSON.stringify(payload, null, 2)}</pre>`,
                        background: '#111',
                        color: '#fff',
                        width: '850px',
                        showConfirmButton: true,
                        confirmButtonText: 'Acknowledged',
                        confirmButtonColor: '#0ea5e9',
                        padding: '1.5rem 1rem'
                    });
                } catch (err) {
                    console.error("Payload parsing error:", err);
                    Swal.fire('Parsing Error', 'The diagnostic data is corrupted or malformed.', 'error');
                }
            }
        });
    </script>
@endsection
