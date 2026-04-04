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
        box-shadow: 0 0 15px rgba(14, 165, 233, 0.1);
    }

    .horizon-tabs { display: flex; gap: 0.25rem; margin-bottom: 2rem; border-bottom: 1px solid var(--horizon-border); padding-bottom: 1rem; overflow-x: auto; scrollbar-width: none; }
    .horizon-tabs::-webkit-scrollbar { display: none; }
    .horizon-tab-btn { background: none; border: 1px solid transparent; color: var(--text-muted); font-size: 0.8rem; font-weight: 600; padding: 0.6rem 0.8rem; cursor: pointer; transition: all 0.3s; border-radius: 8px; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
    .horizon-tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.05); }
    .horizon-tab-btn.active { color: var(--primary-admin); background: rgba(14, 165, 233, 0.1); border-color: rgba(14, 165, 233, 0.3); }
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

    .setting-card { background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.5rem; }
    .label-title { display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; font-weight: 700; }
    .desc-text { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.4; }
    
    .source-toggle-card {
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--horizon-border);
        border-radius: 16px;
        padding: 1.25rem;
        transition: all 0.3s;
    }
    .source-toggle-card:hover { border-color: var(--primary-admin); background: rgba(14, 165, 233, 0.05); }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">
    <div class="card-admin">
        <!-- Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] ?? 'var(--primary-cyan)' }}; font-size: 1.2rem;">
                    <i class="fas {{ $tool['icon'] ?? 'fa-chart-line' }}"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">{{ $tool['name'] }} Control HQ</h2>
                    <p style="margin: 5px 0 0; font-size: 0.8rem; color: var(--text-muted);">Master strategy configuration for Multi-Platform Viral Intelligence.</p>
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
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-admin);">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">TODAY</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['today_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(168, 85, 247, 0.1); display: flex; align-items: center; justify-content: center; color: #a855f7;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">THIS MONTH</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['this_month_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">LIFETIME</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['lifetime_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div class="stat-mini-card">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div>
                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase;">SALES</div>
                    <div style="font-size: 1.1rem; font-weight: 700;">{{ number_format($stats['purchase_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="horizon-tabs">
            <button type="button" class="horizon-tab-btn active" onclick="switchHorizonTab('regional', this)"><i class="fas fa-globe"></i> Regional Control</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('sources', this)"><i class="fas fa-satellite-dish"></i> Sources Mapping</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('ai', this)"><i class="fas fa-brain"></i> AI Intelligence</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('performance', this)"><i class="fas fa-tachometer-alt"></i> Performance</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('routing', this)"><i class="fas fa-network-wired"></i> AI Routing</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('analytics', this)"><i class="fas fa-chart-area"></i> Analytics</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('errors', this)"><i class="fas fa-bug"></i> Errors</button>
        </div>

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST" id="config-form">
            @csrf
            <input type="hidden" name="is_active" value="{{ ($settings['is_active'] ?? true) ? '1' : '0' }}" id="statusInput">

            <!-- TAB 1: Regional Control -->
            <div id="pane-regional" class="horizon-tab-pane active">
                 <div class="setting-card" style="margin-bottom: 2rem;">
                    <label class="label-title">Geographic Availability Whitelist</label>
                    <p class="desc-text" style="margin-bottom: 1.5rem;">Only the checked countries will be visible in the user dashboard.</p>
                    
                    @php
                        $availableCountriesText = $settings['available_countries'] ?? "";
                        $countriesList = [];
                        foreach(explode("\n", $availableCountriesText) as $line) {
                            $parts = explode(':', trim($line));
                            if(count($parts) === 2) $countriesList[trim($parts[0])] = trim($parts[1]);
                        }
                        $activeCountries = json_decode($settings['countries'] ?? '[]', true) ?: array_keys($countriesList);
                    @endphp

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; max-height: 250px; overflow-y: auto; padding-right: 10px;">
                        @foreach($countriesList as $code => $name)
                            <label style="display: flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 12px; padding: 0.75rem 1rem; cursor: pointer;">
                                <input type="checkbox" name="countries[]" value="{{ $code }}" {{ in_array($code, $activeCountries) ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                                <span style="font-size: 0.85rem; font-weight: 600;">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div style="margin-top: 2rem; padding: 1.25rem; background: rgba(0,0,0,0.2); border: 1px dashed var(--horizon-border); border-radius: 12px;">
                        <label class="label-title" style="font-size: 0.65rem;">Regional Database (CODE:Name Flag)</label>
                        <textarea name="available_countries" class="ai-input-base" style="width: 100%; height: 120px; font-family: monospace; font-size: 0.8rem; padding: 1rem; margin-top: 0.5rem; resize: vertical;">{{ $availableCountriesText }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Save Regional State</button>
            </div>

            <!-- TAB 2: Sources Mapping -->
            <div id="pane-sources" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="source-toggle-card">
                         <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(14, 165, 233, 0.1); display: flex; align-items: center; justify-content: center; color: #0ea5e9;">
                                    <i class="fab fa-google"></i>
                                </div>
                                <h4 style="margin: 0; font-size: 0.9rem;">Google Trends</h4>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox" name="source_google_enabled" value="1" {{ ($settings['source_google_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="vn-slider"></span>
                            </label>
                        </div>
                        <label class="label-title" style="font-size: 0.6rem;">RSS Default Mode</label>
                        <select name="feed_type" class="ai-input-base" style="width: 100%; padding: 0.6rem; border-radius: 8px;">
                            <option value="daily" {{ ($settings['feed_type'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily (Global Stable)</option>
                            <option value="realtime" {{ ($settings['feed_type'] ?? 'daily') == 'realtime' ? 'selected' : '' }}>Realtime (Past 24h)</option>
                        </select>
                    </div>

                    <div class="source-toggle-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(168, 85, 247, 0.1); display: flex; align-items: center; justify-content: center; color: #a855f7;">
                                    <i class="fab fa-twitter"></i>
                                </div>
                                <h4 style="margin: 0; font-size: 0.9rem;">X (Twitter)</h4>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox" name="source_x_enabled" value="1" {{ ($settings['source_x_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="vn-slider"></span>
                            </label>
                        </div>
                        <p class="desc-text">Fetching from Trends24 Mirror System.</p>
                    </div>

                    <div class="source-toggle-card">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 0, 0, 0.1); display: flex; align-items: center; justify-content: center; color: #ff0000;">
                                    <i class="fab fa-youtube"></i>
                                </div>
                                <h4 style="margin: 0; font-size: 0.9rem;">YouTube Trends</h4>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox" name="source_youtube_enabled" value="1" {{ ($settings['source_youtube_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="vn-slider"></span>
                            </label>
                        </div>
                        <p class="desc-text">Scraping YouTube Trending page per country.</p>
                    </div>

                    <div class="source-toggle-card" style="grid-column: 1 / -1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(236, 72, 153, 0.1); display: flex; align-items: center; justify-content: center; color: #ec4899;">
                                    <i class="fab fa-tiktok"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 0.9rem;">TikTok Viral</h4>
                                    <p class="desc-text" style="margin: 2px 0 0;">
                                        @if(!empty($settings['tiktok_api_key']))
                                            <span style="color: var(--horizon-success);"><i class="fas fa-check-circle"></i> API Key Configured</span>
                                        @else
                                            <span style="color: #f59e0b;"><i class="fas fa-exclamation-triangle"></i> Requires External API Key</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox" name="source_tiktok_enabled" value="1" {{ ($settings['source_tiktok_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="vn-slider"></span>
                            </label>
                        </div>

                        {{-- TikTok API Configuration Panel --}}
                        <div style="background: rgba(0,0,0,0.2); border: 1px dashed rgba(236, 72, 153, 0.3); border-radius: 12px; padding: 1.25rem; margin-top: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                                <i class="fas fa-key" style="color: #ec4899; font-size: 0.75rem;"></i>
                                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #ec4899;">External API Configuration (RapidAPI)</span>
                            </div>
                            
                            <p class="desc-text" style="margin-bottom: 1rem; line-height: 1.6;">
                                TikTok has no public API for trending data. To enable real-time TikTok trends, subscribe to a TikTok API on 
                                <a href="https://rapidapi.com/search/tiktok%20trending" target="_blank" style="color: var(--primary-admin); text-decoration: underline;">RapidAPI Hub</a>
                                and enter your credentials below.
                            </p>

                            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                                <div>
                                    <label class="label-title" style="font-size: 0.6rem;">RapidAPI Key <span style="color: #ec4899;">*</span></label>
                                    <input type="password" name="tiktok_api_key" 
                                           value="{{ $settings['tiktok_api_key'] ?? '' }}" 
                                           class="ai-input-base" 
                                           placeholder="Enter your x-rapidapi-key..."
                                           style="width: 100%; border-radius: 10px; padding: 0.75rem; outline: none; font-family: monospace; font-size: 0.8rem;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label class="label-title" style="font-size: 0.6rem;">API Host</label>
                                        <input type="text" name="tiktok_api_host" 
                                               value="{{ $settings['tiktok_api_host'] ?? 'tiktok-creative-center-api.p.rapidapi.com' }}" 
                                               class="ai-input-base" 
                                               placeholder="x-rapidapi-host value"
                                               style="width: 100%; border-radius: 10px; padding: 0.75rem; outline: none; font-family: monospace; font-size: 0.75rem;">
                                    </div>
                                    <div>
                                        <label class="label-title" style="font-size: 0.6rem;">Endpoint Path</label>
                                        <input type="text" name="tiktok_api_endpoint" 
                                               value="{{ $settings['tiktok_api_endpoint'] ?? '/api/trending/hashtag' }}" 
                                               class="ai-input-base" 
                                               placeholder="/api/trending/hashtag"
                                               style="width: 100%; border-radius: 10px; padding: 0.75rem; outline: none; font-family: monospace; font-size: 0.75rem;">
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(236, 72, 153, 0.05); border: 1px solid rgba(236, 72, 153, 0.15); border-radius: 8px;">
                                <p style="font-size: 0.65rem; color: var(--text-muted); margin: 0; line-height: 1.6;">
                                    <i class="fas fa-info-circle" style="color: #ec4899;"></i>
                                    <strong>How to setup:</strong> 
                                    1. Go to <a href="https://rapidapi.com" target="_blank" style="color: var(--primary-admin);">rapidapi.com</a> → 
                                    2. Search "TikTok Trending" → 
                                    3. Subscribe (many have free tiers) → 
                                    4. Copy your API Key, Host, and Endpoint here.
                                    Without an API key, TikTok tab will show "No data available".
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Apply Source Mapping</button>
            </div>

            <!-- TAB 3: AI Intelligence -->
            <div id="pane-ai" class="horizon-tab-pane">
                 <div class="setting-card">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <div class="setting-card">
                            <label class="label-title">Analysis Credit Cost</label>
                            <input type="number" name="ai_analysis_credits" value="{{ $settings['ai_analysis_credits'] ?? 2 }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        </div>
                        <div class="setting-card">
                            <label class="label-title">Default AI Model</label>
                            <input type="text" name="ai_model" value="{{ $settings['ai_model'] ?? 'gpt-4o-mini' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        </div>
                    </div>

                    <label class="label-title">Neural Decoding Prompt</label>
                    <p class="desc-text" style="margin-bottom: 1.5rem;">This prompt controls how the AI analyzes the trend, calculates ROI, and suggests strategy.</p>
                    <textarea name="ai_analysis_prompt" class="ai-input-base" style="width: 100%; height: 400px; font-size: 0.9rem; padding: 1.25rem; line-height: 1.6; border-radius: 16px;">{{ $settings['ai_analysis_prompt'] ?? "" }}</textarea>
                    <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                        <span class="badge-vn" style="background: rgba(255,255,255,0.05); color: #888;">Vars: [Trend], [Country], [Lang], [Platform], [Headlines]</span>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1.5rem;"><i class="fas fa-save"></i> Commit Strategy Prompt</button>
            </div>

            <!-- TAB 4: Performance -->
            <div id="pane-performance" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="setting-card">
                        <label class="label-title">Cache Duration (Seconds)</label>
                        <input type="number" name="cache_ttl" value="{{ $settings['cache_ttl'] ?? 3600 }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                    <div class="setting-card">
                        <label class="label-title">Sync Logic Threshold (Mins)</label>
                        <input type="number" name="sync_interval" value="{{ $settings['sync_interval'] ?? 5 }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                </div>
                <div class="setting-card" style="margin-bottom: 2rem;">
                    <label class="label-title">Max Trends per Platform</label>
                    <input type="number" name="max_trends" value="{{ $settings['max_trends'] ?? 20 }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                </div>
                 <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Update Engine Parameters</button>
            </div>

            <!-- TAB 5: AI Routing -->
            <div id="pane-routing" class="horizon-tab-pane">
                 <div id="ai-chain-container">
                    @php
                        $aiChain = \App\Models\Setting::get("{$tool['slug']}_ai_chain", []);
                        if (empty($aiChain)) {
                            $aiChain[] = [
                                'provider' => $settings['provider'] ?? 'openrouter',
                                'model' => $settings['model'] ?? 'google/gemini-2.0-flash-001',
                                'api_key' => $settings['api_key'] ?? ''
                            ];
                        }
                    @endphp

                    @foreach($aiChain as $index => $chain)
                    <div class="ai-provider-row" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; position: relative;">
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
                                <label style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; color: var(--text-muted);">AI PROVIDER</label>
                                <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                                    <option value="openrouter" {{ ($chain['provider'] ?? '') == 'openrouter' ? 'selected' : '' }}>OpenRouter (Fastest)</option>
                                    <option value="openai" {{ ($chain['provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI (Industry Standard)</option>
                                    <option value="google" {{ ($chain['provider'] ?? '') == 'google' ? 'selected' : '' }}>Google Gemini</option>
                                    <option value="anthropic" {{ ($chain['provider'] ?? '') == 'anthropic' ? 'selected' : '' }}>Anthropic Claude</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; color: var(--text-muted);">PRIMARY MODEL ID</label>
                                <input type="text" name="models[]" value="{{ $chain['model'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.7rem; margin-bottom: 0.5rem; color: var(--text-muted);">SECURE API KEY (OVERRIDE)</label>
                            <input type="password" name="api_keys[]" value="{{ $chain['api_key'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" onclick="addNewAiNode()" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px dashed var(--horizon-border); color: var(--text-main); padding: 1.15rem; border-radius: 12px; cursor: pointer; font-size: 0.8rem; font-weight: 600;">
                        <i class="fas fa-plus"></i> ADD FAILOVER NODE
                    </button>
                    <button type="submit" class="btn-save" style="flex: 2;">
                        <i class="fas fa-save"></i> Commit Routing State
                    </button>
                </div>
            </div>

            <!-- TAB 6: Analytics -->
            <div id="pane-analytics" class="horizon-tab-pane">
                 <div class="card-admin">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem;"><i class="fas fa-users-crown text-yellow-400"></i> Top Operators</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--horizon-border);">
                                    <th style="padding: 1rem;">User IDENTITY</th>
                                    <th style="padding: 1rem;">WALLET Power</th>
                                    <th style="padding: 1rem; text-align: right;">Total Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscribers as $sub)
                                <tr>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <div style="font-weight: 700; color: var(--text-main);">{{ optional($sub)->name ?? 'Unknown User' }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ optional($sub)->email ?? '' }}</div>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">
                                            {{ number_format((optional($sub->wallet)->balance_credits ?? 0), 1) }} CRS
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02); text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                        {{ number_format(optional($sub)->ai_usages_count ?? 0) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 7: Errors -->
            <div id="pane-errors" class="horizon-tab-pane">
                 <div class="card-admin">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem; color: #ff4b4b;"><i class="fas fa-exclamation-circle"></i> Incident Log</h3>
                    @forelse($toolErrors ?? [] as $error)
                    <div style="background: rgba(255,75,75,0.03); border: 1px solid rgba(255,75,75,0.1); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div style="font-weight: 700; color: #ff4b4b; font-size: 0.85rem;">{{ optional($error)->error_code ?? 'FAILURE' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ optional($error->created_at)->diffForHumans() ?? 'Just now' }}</div>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-main); line-height: 1.5;">{{ optional($error)->error_message ?? 'Unknown error occurred' }}</div>
                    </div>
                    @empty
                    <div style="padding: 4rem; text-align: center;">
                        <div style="font-size: 3rem; color: rgba(16, 185, 129, 0.1); margin-bottom: 1rem;"><i class="fas fa-shield-check"></i></div>
                        <div style="font-weight: 700; color: var(--text-main);">Zero Incidents Detected</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </form>
    </div>
</div>

<template id="ai-node-template">
    <div class="ai-provider-row" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; position: relative; animation: fadeIn 0.3s ease;">
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
                <select name="providers[]" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    <option value="openrouter">OpenRouter</option>
                    <option value="openai">OpenAI</option>
                    <option value="google">Google Gemini</option>
                    <option value="anthropic">Anthropic Claude</option>
                </select>
            </div>
            <div>
                <input type="text" name="models[]" placeholder="Model ID..." class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
            </div>
        </div>
        <input type="password" name="api_keys[]" placeholder="API Key..." class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
    </div>
</template>
@endsection

@section('scripts')
<script>
    function switchHorizonTab(tabId, btn) {
        document.querySelectorAll('.horizon-tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.horizon-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + tabId).classList.add('active');
        btn.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function toggleToolStatus() {
        const input = document.getElementById('statusInput');
        const isActive = input.value === '1';
        input.value = isActive ? '0' : '1';
        document.getElementById('config-form').submit();
    }

    function addNewAiNode() {
        const template = document.getElementById('ai-node-template');
        const container = document.getElementById('ai-chain-container');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }
</script>
@endsection
