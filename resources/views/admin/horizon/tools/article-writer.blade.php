@extends('admin.horizon.layout')

@section('title', "Control: " . $tool['name'])

@section('styles')
<style>
    .ai-provider-row {
        background: var(--horizon-nav-hover) !important;
        border: 1px solid var(--horizon-border) !important;
        backdrop-filter: blur(10px);
    }
    
    .ai-input-base {
        background: rgba(0,0,0,0.2) !important;
        border: 1px solid var(--horizon-border) !important;
        color: var(--text-main) !important;
        transition: all 0.3s ease;
    }
    
    .ai-input-base:focus {
        border-color: var(--primary-admin) !important;
        box-shadow: 0 0 15px rgba(0, 168, 230, 0.1);
    }

    .horizon-tabs { display: flex; gap: 0.25rem; margin-bottom: 2rem; border-bottom: 1px solid var(--horizon-border); padding-bottom: 1rem; overflow-x: auto; scrollbar-width: none; }
    .horizon-tabs::-webkit-scrollbar { display: none; }
    .horizon-tab-btn { background: none; border: 1px solid transparent; color: var(--text-muted); font-size: 0.8rem; font-weight: 600; padding: 0.6rem 0.8rem; cursor: pointer; transition: all 0.3s; border-radius: 8px; font-family: 'Poppins', sans-serif; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
    .horizon-tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }
    .horizon-tab-btn.active { color: var(--primary-admin); background: rgba(0, 168, 230, 0.1); border-color: rgba(0, 168, 230, 0.3); }
    .horizon-tab-pane { display: none; animation: fadeIn 0.3s ease; }
    .horizon-tab-pane.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    .stat-mini-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--horizon-border);
        border-radius: 16px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .rule-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--horizon-border);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .config-section {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--horizon-border);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .config-section h4 {
        color: var(--text-main);
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 800;
    }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">
    <div class="card-admin">
        <!-- Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(20, 184, 166, 0.1); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1.2rem;">
                    <i class="fas {{ $tool['icon'] }}"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">{{ $tool['name'] }} Control Center</h2>
                    <p style="margin: 5px 0 0; font-size: 0.8rem; color: var(--text-muted);">Master administrative override and tool calibration.</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: right;">
                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Status</div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: {{ ($settings['is_active'] ?? true) ? 'var(--horizon-success)' : '#ff4b4b' }}">
                        {{ ($settings['is_active'] ?? true) ? 'OPERATIONAL' : 'MAINTENANCE' }}
                    </div>
                </div>
                <div class="status-toggle" onclick="toggleToolStatus()" style="width: 50px; height: 26px; background: {{ ($settings['is_active'] ?? true) ? 'var(--horizon-success)' : '#444' }}; border-radius: 20px; position: relative; cursor: pointer; transition: all 0.3s ease;">
                    <div style="width: 20px; height: 20px; background: #fff; border-radius: 50%; position: absolute; top: 3px; left: {{ ($settings['is_active'] ?? true) ? '27px' : '3px' }}; transition: all 0.3s ease;"></div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(0, 168, 230, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-admin);">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">Today</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['today_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(168, 85, 247, 0.1); display: flex; align-items: center; justify-content: center; color: #a855f7;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">Month</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['this_month_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #00A58B;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">Lifetime</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['lifetime_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">Sales</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['purchase_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="horizon-tabs">
            <button type="button" class="horizon-tab-btn active" onclick="switchHorizonTab('ai', this)"><i class="fas fa-brain"></i> Intelligence</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('personas', this)"><i class="fas fa-users-gear"></i> Personas & Tones</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('content', this)"><i class="fas fa-sliders-h"></i> Content Options</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('credits', this)"><i class="fas fa-coins"></i> Credit System</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('routing', this)"><i class="fas fa-network-wired"></i> AI Routing</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('analytics', this)"><i class="fas fa-chart-area"></i> Analytics</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('errors', this)"><i class="fas fa-bug"></i> Errors</button>
        </div>

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST" id="config-form" data-ajax-save>
            @csrf
            <input type="hidden" name="is_active" value="{{ ($settings['is_active'] ?? true) ? '1' : '0' }}" id="statusInput">

            <!-- TAB 1: AI Intelligence — Prompt Engineering Lab -->
            <div id="pane-ai" class="horizon-tab-pane active">
                <div style="margin-bottom: 2rem; padding: 1.25rem; background: linear-gradient(135deg, rgba(0, 168, 230, 0.08), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(0, 168, 230, 0.15); border-radius: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-satellite-dish" style="color: var(--primary-admin);"></i>
                            <h4 style="margin: 0; font-size: 0.9rem; color: var(--text-main); font-weight: 800;">Live Research & Grounding</h4>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">GROUNDING:</span>
                            <div class="status-toggle" onclick="toggleSetting('live_search_enabled', this)" style="width: 44px; height: 22px; background: {{ ($settings['live_search_enabled'] ?? true) ? 'var(--horizon-success)' : '#444' }}; border-radius: 20px; position: relative; cursor: pointer; transition: all 0.3s ease;">
                                <div style="width: 16px; height: 16px; background: #fff; border-radius: 50%; position: absolute; top: 3px; left: {{ ($settings['live_search_enabled'] ?? true) ? '25px' : '3px' }}; transition: all 0.3s ease;"></div>
                            </div>
                            <input type="hidden" name="live_search_enabled" value="{{ ($settings['live_search_enabled'] ?? true) ? '1' : '0' }}" id="liveSearchInput">
                        </div>
                    </div>
                    <div style="display: flex; gap: 1.5rem; align-items: center;">
                        <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted); line-height: 1.6; flex: 1;">When enabled, the system performs a live Google News search for the keyword before generation, injecting current facts and [year] context automatically. 5-15 sources recommended.</p>
                        <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.2); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid var(--horizon-border);">
                            <span style="font-size: 0.65rem; color: var(--text-muted);">LIMIT:</span>
                            <input type="number" name="live_search_limit" value="{{ $settings['live_search_limit'] ?? 15 }}" style="background: none; border: none; color: var(--primary-admin); font-weight: 800; font-size: 0.8rem; width: 35px; outline: none; text-align: center;">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 2rem; padding: 1rem 1.25rem; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 12px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-flask" style="color: #a855f7;"></i>
                        <h4 style="margin: 0; font-size: 0.9rem; color: var(--text-main); font-weight: 800;">Prompt Engineering Lab</h4>
                    </div>
                    <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted); line-height: 1.6;">Each section of the generated article has its own dedicated prompt. Use <code style="background: rgba(0,0,0,0.3); color: var(--primary-admin); padding: 0.15rem 0.4rem; border-radius: 4px;">[keyword]</code> <code style="background: rgba(0,0,0,0.3); color: #00A58B; padding: 0.15rem 0.4rem; border-radius: 4px;">[news_context]</code> as placeholders.</p>
                </div>

                <!-- 1. TITLE ENGINEERING PROMPT -->
                <div class="config-section" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.03);">
                    <h4><i class="fas fa-crown" style="color: #f59e0b;"></i> Title Engineering Prompt <span style="font-size: 0.6rem; background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 0.2rem 0.5rem; border-radius: 6px; margin-left: 0.5rem;">CRITICAL — Google Discover Quality</span></h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">This prompt controls how the AI generates the H1 title. It should produce click-magnetic, Google Discover-worthy headlines.</p>
                    <textarea name="prompt_title" rows="10" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_title'] }}</textarea>
                </div>

                <!-- 2. ARTICLE BODY PROMPT -->
                <div class="config-section" style="border-color: rgba(0, 168, 230, 0.3); background: rgba(0, 168, 230, 0.03);">
                    <h4><i class="fas fa-align-left" style="color: var(--primary-admin);"></i> Article Body Prompt <span style="font-size: 0.6rem; background: rgba(0, 168, 230, 0.15); color: var(--primary-admin); padding: 0.2rem 0.5rem; border-radius: 6px; margin-left: 0.5rem;">Core Content Engine</span></h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">Master prompt for the main article body. Controls research depth, comprehensiveness, E-E-A-T compliance, and content structure.</p>
                    <textarea name="prompt_body" rows="18" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_body'] }}</textarea>
                </div>

                <!-- 3. QUICK SUMMARY PROMPT -->
                <div class="config-section">
                    <h4><i class="fas fa-bolt" style="color: #00A58B;"></i> Quick Summary Prompt</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">Controls the executive summary box that appears right after the H1 title.</p>
                    <textarea name="prompt_summary" rows="6" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_summary'] }}</textarea>
                </div>

                <!-- 4. KEY TAKEAWAYS PROMPT -->
                <div class="config-section">
                    <h4><i class="fas fa-list-check" style="color: #a855f7;"></i> Key Takeaways Prompt</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">Controls the bullet-point takeaways section with key insights and data points.</p>
                    <textarea name="prompt_takeaways" rows="6" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_takeaways'] }}</textarea>
                </div>

                <!-- 5. FAQ PROMPT -->
                <div class="config-section">
                    <h4><i class="fas fa-circle-question" style="color: #ec4899;"></i> FAQ Section Prompt</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">Controls the schema-ready FAQ section. Questions should match "People Also Ask" patterns.</p>
                    <textarea name="prompt_faq" rows="6" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_faq'] }}</textarea>
                </div>

                <!-- 6. META DESCRIPTION PROMPT -->
                <div class="config-section">
                    <h4><i class="fas fa-search" style="color: #06b6d4;"></i> SEO Meta & Title Tag Prompt</h4>
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5;">Controls the metadata tags output (SEO Title, Meta Description, Focus Keyword).</p>
                    <textarea name="prompt_meta" rows="6" class="ai-input-base mono" style="width: 100%; border-radius: 12px; padding: 1rem; line-height: 1.6; font-size: 0.78rem; outline: none;">{{ $settings['prompt_meta'] }}</textarea>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-save"></i> Save All Prompt Protocols
                </button>
            </div>

            <!-- TAB: Personas & Tones -->
            <div id="pane-personas" class="horizon-tab-pane">
                <div style="margin-bottom: 2rem; padding: 1.25rem; background: linear-gradient(135deg, rgba(168, 85, 247, 0.08), rgba(16, 185, 129, 0.05)); border: 1px solid rgba(168, 85, 247, 0.15); border-radius: 16px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-users-viewfinder" style="color: #a855f7;"></i>
                        <h4 style="margin: 0; font-size: 0.95rem; color: var(--text-main); font-weight: 800;">Personality Factory</h4>
                    </div>
                    <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted); line-height: 1.6;">Define the specific "AI Directives" for each tone and audience. These instructions are injected into the final prompt to shape the AI's writing style and depth.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- LEFT: EDITORIAL TONES -->
                    <div>
                        <h3 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #a855f7; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-microphone"></i> Editorial Tones
                        </h3>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Professional</label>
                            <textarea name="directive_professional" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_professional'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Informative</label>
                            <textarea name="directive_informative" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_informative'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Casual & Friendly</label>
                            <textarea name="directive_casual" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_casual'] }}</textarea>
                        </div>

                        <div class="rule-card" style="border-color: rgba(168, 85, 247, 0.4); background: rgba(168, 85, 247, 0.03);">
                            <label style="display: block; font-size: 0.7rem; color: #a855f7; text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 800;">Authoritative (Expert)</label>
                            <textarea name="directive_authoritative" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_authoritative'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Creative & Engaging</label>
                            <textarea name="directive_creative" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_creative'] }}</textarea>
                        </div>
                    </div>

                    <!-- RIGHT: TARGET AUDIENCES -->
                    <div>
                        <h3 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; color: #00A58B; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-bullseye"></i> Audience Personas
                        </h3>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">General Audience</label>
                            <textarea name="directive_general" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_general'] }}</textarea>
                        </div>

                        <div class="rule-card" style="border-color: rgba(16, 185, 129, 0.4); background: rgba(16, 185, 129, 0.03);">
                            <label style="display: block; font-size: 0.7rem; color: #00A58B; text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 800;">Industry Professionals</label>
                            <textarea name="directive_professionals" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_professionals'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Beginners & Learners</label>
                            <textarea name="directive_beginners" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_beginners'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Online Shoppers</label>
                            <textarea name="directive_shoppers" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_shoppers'] }}</textarea>
                        </div>

                        <div class="rule-card">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 700;">Digital Marketers</label>
                            <textarea name="directive_marketers" rows="4" class="ai-input-base mono" style="width: 100%; border-radius: 10px; padding: 0.75rem; font-size: 0.75rem; outline: none;">{{ $settings['directive_marketers'] }}</textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-save"></i> Save Persona Protocols
                </button>
            </div>

            <!-- TAB 2: Content Options -->
            <div id="pane-content" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <!-- Available Languages -->
                    <div class="config-section">
                        <h4><i class="fas fa-globe" style="color: var(--primary-admin);"></i> Available Languages</h4>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">One language per line: <code>code:Label</code> (e.g., <code>en:English 🇺🇸</code>)</p>
                        <textarea name="available_languages" rows="6" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $settings['available_languages'] }}</textarea>
                    </div>

                    <!-- Available Tones -->
                    <div class="config-section">
                        <h4><i class="fas fa-palette" style="color: #a855f7;"></i> Editorial Tones</h4>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">One tone per line: <code>value:Label</code> (e.g., <code>professional:Professional</code>)</p>
                        <textarea name="available_tones" rows="6" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $settings['available_tones'] }}</textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <!-- Target Audiences -->
                    <div class="config-section">
                        <h4><i class="fas fa-users" style="color: #00A58B;"></i> Target Audiences</h4>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">One audience per line: <code>value:Label</code></p>
                        <textarea name="available_audiences" rows="6" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $settings['available_audiences'] }}</textarea>
                    </div>

                    <!-- Generation Parameters -->
                    <div class="config-section">
                        <h4><i class="fas fa-cog" style="color: #f59e0b;"></i> Generation Parameters</h4>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Max AI Tokens</label>
                            <input type="number" name="max_tokens" value="{{ $settings['default_tokens'] ?? 4000 }}" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none;">
                            <p style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.4rem;">Controls maximum output length. ~1000 tokens ≈ 750 words.</p>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Default Word Count Target</label>
                            <input type="number" name="default_word_count" value="{{ $settings['default_word_count'] ?? 1500 }}" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none;">
                            <p style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.4rem;">Guides the AI for the "Medium" article length preset.</p>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Available Output Components</label>
                            <textarea name="available_components" rows="4" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $settings['available_components'] ?? "faq:FAQ Section\nsummary:Quick Summary\ntakeaways:Key Takeaways\nmeta:SEO Meta Tags" }}</textarea>
                            <p style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.4rem;">Checkboxes shown to the user. Format: <code>key:Label</code></p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Content Configuration
                </button>
            </div>

            <!-- TAB 3: Credit System -->
            <div id="pane-pricing" class="horizon-tab-pane">
                @php
                    $creditCost = (int) \App\Models\Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 5);
                @endphp
                
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #f59e0b; font-size: 2rem; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-coins"></i>
                    </div>
                    
                    <h2 style="margin: 0 0 0.5rem; font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800;">Financial Unit Calibration</h2>
                    <p style="margin: 0 auto 3rem; color: var(--text-muted); font-size: 0.9rem; max-width: 500px;">Set the operational cost for each article generation request.</p>
                    
                    <div style="max-width: 450px; margin: 0 auto; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2.5rem; position: relative;">
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; font-weight: 800; margin-bottom: 1.5rem; opacity: 0.6;">Cost Per Article</div>
                        
                        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem;">
                            <input type="number" name="credit_cost" value="{{ $creditCost }}" min="0" class="ai-input-base" style="width: 90px; height: 80px; font-size: 2.5rem; font-weight: 800; text-align: center; border-radius: 16px; border: 2px solid var(--primary-admin); background: rgba(0, 168, 230, 0.05) !important; color: var(--primary-admin);">
                            <span style="font-size: 1.25rem; font-weight: 700; color: var(--text-muted); letter-spacing: 1px;">CREDITS</span>
                        </div>

                        <div style="margin-top: 2.5rem; background: rgba(0, 168, 230, 0.05); border: 1px solid rgba(0, 168, 230, 0.1); border-radius: 12px; padding: 1rem; display: flex; gap: 0.75rem; text-align: left;">
                            <div style="color: var(--primary-admin); font-size: 1rem; margin-top: 2px;"><i class="fas fa-info-circle"></i></div>
                            <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted); line-height: 1.5;">This value is deducted from the user's wallet upon successful article generation. Failed generations are cost-neutral.</p>
                        </div>
                    </div>

                    <button type="submit" class="vn-btn vn-btn-primary" style="margin-top: 2rem; padding: 1rem 3rem; font-size: 1rem; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.75rem; background: var(--primary-admin); color: #000; border: none; cursor: pointer;">
                        <i class="fas fa-check-circle"></i> Confirm Credit Settings
                    </button>
                </div>
            </div>

            <!-- TAB 4: AI Routing -->
            <div id="pane-routing" class="horizon-tab-pane">
                <div id="ai-chain-container">
                    @php
                        $aiChain = \App\Models\Setting::get("{$tool['slug']}_ai_chain", []);
                        if (empty($aiChain)) {
                            $aiChain[] = [
                                'provider' => \App\Models\Setting::get("{$tool['slug']}_provider", 'openrouter'),
                                'model' => \App\Models\Setting::get("{$tool['slug']}_model", 'google/gemini-2.0-flash-001'),
                                'api_key' => \App\Models\Setting::get("{$tool['slug']}_api_key", '')
                            ];
                        }
                    @endphp

                    @foreach($aiChain as $index => $chain)
                    <div class="ai-provider-row" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; position: relative; border: 1px solid var(--horizon-border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary-admin); text-transform: uppercase; letter-spacing: 2px;">
                                ENGINE NODE #{{ $index + 1 }}
                            </div>
                            @if($index > 0)
                            <button type="button" onclick="this.closest('.ai-provider-row').remove()" style="background: rgba(255,75,75,0.1); border: 1px solid rgba(255,75,75,0.2); color: #ff4b4b; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.7rem; cursor: pointer;">
                                <i class="fas fa-trash"></i> DELETE NODE
                            </button>
                            @endif
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">AI PROVIDER</label>
                                <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;">
                                    <option value="openrouter" {{ ($chain['provider'] ?? '') == 'openrouter' ? 'selected' : '' }}>OpenRouter (Failover/Cheap)</option>
                                    <option value="openai" {{ ($chain['provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI (Highly Precise)</option>
                                    <option value="google" {{ ($chain['provider'] ?? '') == 'google' ? 'selected' : '' }}>Google Gemini (Multi-modal)</option>
                                    <option value="anthropic" {{ ($chain['provider'] ?? '') == 'anthropic' ? 'selected' : '' }}>Anthropic Claude</option>
                                </select>
                            </div>
                            <div>
                                <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">PRIMARY MODEL ID</label>
                                <input type="text" name="models[]" value="{{ $chain['model'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;" placeholder="google/gemini-2.0-flash-001">
                            </div>
                        </div>
                        
                        <div>
                            <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">SECURE API KEY (OVERRIDE)</label>
                            <input type="password" name="api_keys[]" value="{{ $chain['api_key'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;" placeholder="Leave empty to use global system key">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="addNewAiNode()" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px dashed var(--horizon-border); color: var(--text-main); padding: 1rem; border-radius: 12px; cursor: pointer; transition: all 0.3s; font-size: 0.8rem; font-weight: 600;">
                        <i class="fas fa-plus"></i> ADD FAILOVER NODE
                    </button>
                    <button type="submit" class="btn-save" style="flex: 2;">
                        <i class="fas fa-save"></i> Commit Routing State
                    </button>
                </div>
            </div>

            <!-- TAB 5: Analytics -->
            <div id="pane-analytics" class="horizon-tab-pane">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 12px; border: 1px solid var(--horizon-border);">
                    <div style="font-size: 0.85rem; font-weight: 600;">Usage Timeframe</div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="ai-input-base" style="padding: 0.5rem; border-radius: 8px; font-size: 0.75rem;">
                        <span style="color: var(--text-muted);">to</span>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="ai-input-base" style="padding: 0.5rem; border-radius: 8px; font-size: 0.75rem;">
                        <button type="button" onclick="document.getElementById('config-form').submit()" style="background: var(--primary-admin); border: none; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.75rem; cursor: pointer;">
                            FILTER
                        </button>
                    </div>
                </div>

                <div class="card-admin" style="margin-bottom: 2rem;">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem;"><i class="fas fa-users"></i> Elite Operators</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px;">
                                    <th style="padding: 1rem; border-bottom: 1px solid var(--horizon-border);">User Identity</th>
                                    <th style="padding: 1rem; border-bottom: 1px solid var(--horizon-border);">Wallet Power</th>
                                    <th style="padding: 1rem; border-bottom: 1px solid var(--horizon-border); text-align: right;">Total Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $sub)
                                <tr>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <div style="font-weight: 700; color: var(--text-main);">{{ $sub->name }}</div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $sub->email }}</div>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #00A58B; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800;">
                                            {{ number_format($sub->wallet->balance_credits ?? 0, 1) }} CRS
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02); text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                        {{ number_format($sub->ai_usages_count) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" style="padding: 3rem; text-align: center; color: var(--text-muted);">No subscriber activity detected in this timeframe.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 6: Errors -->
            <div id="pane-errors" class="horizon-tab-pane">
                <div class="card-admin">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem; color: #ff4b4b;"><i class="fas fa-exclamation-circle"></i> Incident Log</h3>
                    @forelse($toolErrors as $error)
                    <div style="background: rgba(255,75,75,0.03); border: 1px solid rgba(255,75,75,0.1); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; position: relative;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div style="font-weight: 700; color: #ff4b4b; font-size: 0.85rem;">{{ $error->error_code ?? 'CRITICAL_FAILURE' }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $error->created_at->diffForHumans() }}</div>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-main); margin-bottom: 0.75rem; line-height: 1.5;">{{ $error->message }}</div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="font-size: 0.7rem; color: var(--text-muted);">Triggered by: <strong>{{ $error->user->name ?? 'System' }}</strong></div>
                            @if($error->payload)
                            <button type="button" class="inspect-error-payload" data-payload="{{ json_encode($error->payload) }}" data-title="Diagnostic Payload" style="background: rgba(0, 168, 230, 0.1); border: 1px solid rgba(0, 168, 230, 0.2); color: var(--primary-admin); padding: 0.3rem 0.7rem; border-radius: 6px; font-size: 0.65rem; font-weight: 800; cursor: pointer;">
                                <i class="fas fa-microscope"></i> INSPECT
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div style="padding: 4rem; text-align: center;">
                        <div style="font-size: 3rem; color: rgba(16, 185, 129, 0.1); margin-bottom: 1rem;"><i class="fas fa-shield-alt"></i></div>
                        <div style="font-weight: 700; color: var(--text-main);">Zero Incidents Detected</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">System integrity is currently at 100%.</div>
                    </div>
                    @endforelse

                    @if($toolErrors->hasPages())
                        <div style="margin-top: 1.5rem;">{{ $toolErrors->appends(request()->query())->links('admin.horizon.partials._pagination') }}</div>
                    @endif
                </div>
            </div>

        </form>
    </div>
</div>

<template id="ai-node-template">
    <div class="ai-provider-row" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; position: relative; border: 1px solid var(--horizon-border); animation: fadeIn 0.3s ease;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary-admin); text-transform: uppercase; letter-spacing: 2px;">
                ENGINE NODE #NEW
            </div>
            <button type="button" onclick="this.closest('.ai-provider-row').remove()" style="background: rgba(255,75,75,0.1); border: 1px solid rgba(255,75,75,0.2); color: #ff4b4b; padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.7rem; cursor: pointer;">
                <i class="fas fa-trash"></i> DELETE NODE
            </button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">AI PROVIDER</label>
                <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;">
                    <option value="openrouter">OpenRouter (Failover/Cheap)</option>
                    <option value="openai">OpenAI (Highly Precise)</option>
                    <option value="google">Google Gemini (Multi-modal)</option>
                    <option value="anthropic">Anthropic Claude</option>
                </select>
            </div>
            <div>
                <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">PRIMARY MODEL ID</label>
                <input type="text" name="models[]" value="" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;" placeholder="gpt-4o-mini">
            </div>
        </div>
        
        <div>
            <label class="ai-chain-label" style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; opacity: 0.7;">SECURE API KEY (OVERRIDE)</label>
            <input type="password" name="api_keys[]" value="" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;" placeholder="Enter specific key...">
        </div>
    </div>
</template>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function switchHorizonTab(tabId, btn) {
        document.querySelectorAll('.horizon-tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.horizon-tab-btn').forEach(b => b.classList.remove('active'));
        
        document.getElementById('pane-' + tabId).classList.add('active');
        btn.classList.add('active');

        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    }

    function toggleToolStatus() {
        const input = document.getElementById('statusInput');
        const toggle = document.querySelector('.status-toggle');
        const orb = toggle.querySelector('div');

        if (input.value == '1') {
            input.value = '0';
            toggle.style.background = '#444';
            orb.style.left = '3px';
        } else {
            input.value = '1';
            toggle.style.background = 'var(--horizon-success)';
            orb.style.left = '27px';
        }
    }

    function toggleSetting(setting, element) {
        const inputId = setting === 'live_search_enabled' ? 'liveSearchInput' : setting;
        const input = document.getElementById(inputId);
        const orb = element.querySelector('div');

        if (input.value == '1') {
            input.value = '0';
            element.style.background = '#444';
            orb.style.left = '3px';
        } else {
            input.value = '1';
            element.style.background = 'var(--horizon-success)';
            orb.style.left = '25px';
        }
    }

    function addNewAiNode() {
        const template = document.getElementById('ai-node-template');
        const container = document.getElementById('ai-chain-container');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }

    // Diagnostics Inspection Handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.inspect-error-payload')) {
            const btn = e.target.closest('.inspect-error-payload');
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
                    confirmButtonColor: '#00A8E6',
                    padding: '1.5rem 1rem'
                });
            } catch (err) {
                Swal.fire('Parsing Error', 'The diagnostic data is corrupted or malformed.', 'error');
            }
        }
    });

    // Handle initial tab from URL
    window.onload = () => {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab) {
            const btn = Array.from(document.querySelectorAll('.horizon-tab-btn')).find(b => b.innerText.toLowerCase().includes(tab.toLowerCase()));
            if (btn) switchHorizonTab(tab, btn);
        }
    };
</script>
@endsection
