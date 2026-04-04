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
    
    .rule-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--horizon-border);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .setting-card { background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.5rem; }
    .label-title { display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem; font-weight: 700; }
    .desc-text { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; line-height: 1.4; }
</style>
@endsection

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">
    <div class="card-admin">
        <!-- Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1.2rem;">
                    <i class="fas {{ $tool['icon'] }}"></i>
                </div>
                <div>
                    <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">{{ $tool['name'] }} Control Center</h2>
                    <p style="margin: 5px 0 0; font-size: 0.8rem; color: var(--text-muted);">Configure regional data, ranking engine weights, and AI intelligence patterns.</p>
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
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: #10b981;">
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
            <button type="button" class="horizon-tab-btn active" onclick="switchHorizonTab('regional', this)"><i class="fas fa-globe"></i> Regional Control</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('ranking', this)"><i class="fas fa-microchip"></i> Ranking Engine</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('ai', this)"><i class="fas fa-brain"></i> Intelligence</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('authority', this)"><i class="fas fa-shield-alt"></i> Authority Map</button>
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
                <div style="margin-bottom: 2.5rem;">
                    <label class="label-title"><i class="fas fa-clock" style="color: var(--primary-admin);"></i> News Time Window</label>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        @php $timeWindow = $settings['time_window'] ?? '12h'; @endphp
                        @foreach(['1h' => 'Last Hour', '3h' => 'Last 3 Hours', '6h' => 'Last 6 Hours', '12h' => 'Last 12 Hours', '24h' => 'Last 24 Hours', '48h' => 'Last 48 Hours'] as $val => $label)
                            <label style="display: flex; align-items: center; gap: 0.5rem; background: {{ $timeWindow === $val ? 'rgba(14, 165, 233, 0.1)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ $timeWindow === $val ? 'var(--primary-admin)' : 'var(--horizon-border)' }}; border-radius: 12px; padding: 0.75rem 1.25rem; cursor: pointer; transition: all 0.2s;">
                                <input type="radio" name="time_window" value="{{ $val }}" {{ $timeWindow === $val ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                                <span style="font-size: 0.85rem; font-weight: 600; color: {{ $timeWindow === $val ? 'var(--primary-admin)' : 'var(--text-main)' }};">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="setting-card">
                        <label class="label-title">Active Countries</label>
                        @php
                            $availableCountriesText = $settings['available_countries'] ?? "";
                            $defaultCountries = [];
                            foreach(explode("\n", $availableCountriesText) as $line) {
                                $parts = explode(':', trim($line));
                                if(count($parts) === 2) $defaultCountries[trim($parts[0])] = trim($parts[1]);
                            }
                            $activeCountries = json_decode($settings['countries'] ?? '[]', true) ?: array_keys($defaultCountries);
                        @endphp
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 200px; overflow-y: auto; padding-right: 5px;">
                            @foreach($defaultCountries as $code => $name)
                                <label style="display: flex; align-items: center; gap: 0.4rem; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 10px; padding: 0.5rem 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="countries[]" value="{{ $code }}" {{ in_array($code, $activeCountries) ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                                    <span style="font-size: 0.8rem; font-weight: 600;">{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="setting-card">
                        <label class="label-title">Active Topics</label>
                        @php
                            $availableTopicsText = $settings['available_topics'] ?? "";
                            $defaultTopics = [];
                            foreach(explode("\n", $availableTopicsText) as $line) {
                                $parts = explode(':', trim($line));
                                if(count($parts) === 2) $defaultTopics[trim($parts[0])] = trim($parts[1]);
                            }
                            $activeTopics = json_decode($settings['topics'] ?? '[]', true) ?: array_keys($defaultTopics);
                        @endphp
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($defaultTopics as $tKey => $tName)
                                <label style="display: flex; align-items: center; gap: 0.4rem; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 10px; padding: 0.5rem 0.85rem; cursor: pointer;">
                                    <input type="checkbox" name="topics[]" value="{{ $tKey }}" {{ in_array($tKey, $activeTopics) ? 'checked' : '' }} style="accent-color: var(--secondary-admin);">
                                    <span style="font-size: 0.8rem; font-weight: 600;">{{ $tName }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label class="label-title">Definitions: Countries (CODE:Name)</label>
                        <textarea name="available_countries" class="ai-input-base" style="width: 100%; height: 120px; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $availableCountriesText }}</textarea>
                    </div>
                    <div>
                        <label class="label-title">Definitions: Topics (CODE:Name)</label>
                        <textarea name="available_topics" class="ai-input-base" style="width: 100%; height: 120px; border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.8rem; outline: none;">{{ $availableTopicsText }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Save Regional Intelligence</button>
            </div>

            <!-- TAB 2: Ranking Engine -->
            <div id="pane-ranking" class="horizon-tab-pane">
                <div style="background: rgba(14, 165, 233, 0.05); border: 1px solid rgba(14, 165, 233, 0.2); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="margin: 0 0 0.5rem; font-size: 1rem; color: var(--primary-admin);"><i class="fas fa-microchip"></i> Weighted Opportunity Scoring (V2.0)</h3>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted); line-height: 1.5;">Configure how the system calculates "Ranking Potential". Total weight should ideally equal 100%.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="setting-card">
                        <label class="label-title">🔥 Virality Weight (%)</label>
                        <input type="number" name="weight_virality" value="{{ $settings['weight_virality'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        <p class="desc-text">Sentiment velocity and viral buzz factor.</p>
                    </div>
                    <div class="setting-card">
                        <label class="label-title">⚡ Freshness Weight (%)</label>
                        <input type="number" name="weight_freshness" value="{{ $settings['weight_freshness'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        <p class="desc-text">Emphasis on items published in the last 1-3 hours.</p>
                    </div>
                    <div class="setting-card">
                        <label class="label-title">🔍 SERP Saturation Weight (%)</label>
                        <input type="number" name="weight_serp" value="{{ $settings['weight_serp'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        <p class="desc-text">Bonus for unique titles with low search results output.</p>
                    </div>
                    <div class="setting-card">
                        <label class="label-title">🛡️ Authority Gap Weight (%)</label>
                        <input type="number" name="weight_authority" value="{{ $settings['weight_authority'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                        <p class="desc-text">Bonus for stories currently dominated by low-authority sites.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="setting-card" style="border-color: var(--horizon-success);">
                        <label class="label-title" style="color: var(--horizon-success);">🟢 High Opportunity Threshold (%)</label>
                        <input type="number" name="threshold_high" value="{{ $settings['threshold_high'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                    <div class="setting-card" style="border-color: #f59e0b;">
                        <label class="label-title" style="color: #f59e0b;">🟡 Moderate Opportunity Threshold (%)</label>
                        <input type="number" name="threshold_moderate" value="{{ $settings['threshold_moderate'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                </div>

                <div class="rule-card">
                    <h4 style="margin: 0 0 1rem; font-size: 0.85rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-shield-alt text-cyan-400"></i> Opportunity Logic Visualization
                    </h4>
                    <div style="display: grid; gap: 0.75rem; font-size: 0.75rem; color: var(--text-muted);">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Viral Pulse (Sentiment + Speed)</span>
                            <span style="color: var(--horizon-success);">MAX {{ $settings['weight_virality'] }} PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Breaking Edge (Recency Penalty/Bonus)</span>
                            <span style="color: var(--horizon-success);">MAX {{ $settings['weight_freshness'] }} PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Authority Gap (Source Strength Penalty)</span>
                            <span style="color: var(--horizon-success);">MAX {{ $settings['weight_authority'] }} PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--horizon-border); padding-top: 0.75rem;">
                            <span>Total Normalized Score</span>
                            <span style="color: var(--primary-admin);">100% SIGNAL</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Synchronize Engine Weights</button>
            </div>

            <!-- TAB 3: Intelligence -->
            <div id="pane-ai" class="horizon-tab-pane">
                <div style="margin-bottom: 2rem;">
                    <label class="label-title">Deep Analysis AI Prompt</label>
                    <textarea name="ai_analysis_prompt" rows="12" class="ai-input-base mono" style="width: 100%; border-radius: 16px; padding: 1.5rem; font-size: 0.85rem; line-height: 1.6; outline: none;" placeholder="Instructions for article analysis...">{{ $settings['ai_analysis_prompt'] }}</textarea>
                    
                    {{-- Prompt Placeholders Guide --}}
                    <div class="rule-card" style="margin-top: 1rem; background: rgba(14, 165, 233, 0.05); border-color: rgba(14, 165, 233, 0.2);">
                        <h4 style="margin: 0 0 0.75rem; font-size: 0.85rem; color: var(--primary-admin);"><i class="fas fa-magic"></i> Dynamic Prompt Placeholders</h4>
                        <p style="margin: 0 0 0.75rem; font-size: 0.8rem; color: var(--text-main); font-weight: 600;">You can use the following placeholders in your prompt. The system will automatically inject live news context at runtime:</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.75rem; font-family: monospace;">
                            <div style="color: var(--text-muted);"><span style="color: var(--primary-admin);">[Title]</span>: News Headline</div>
                            <div style="color: var(--text-muted);"><span style="color: var(--primary-admin);">[Description]</span>: News Content/Snippet</div>
                            <div style="color: var(--text-muted);"><span style="color: var(--primary-admin);">[Country]</span>: News Region</div>
                            <div style="color: var(--text-muted);"><span style="color: var(--primary-admin);">[Topic]</span>: News Category</div>
                        </div>
                    </div>

                    <p class="desc-text"><i class="fas fa-info-circle"></i> Controls how the AI extracts entities, sentiment, and writing suggestions.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="setting-card">
                        <label class="label-title">Sync Credit Cost</label>
                        <input type="number" name="sync_credits" value="{{ $settings['sync_credits'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                    <div class="setting-card">
                        <label class="label-title">Deep AI Analysis Cost</label>
                        <input type="number" name="ai_analysis_credits" value="{{ $settings['ai_analysis_credits'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Save Intelligence Parameters</button>
            </div>

            <!-- TAB 4: Authority Map -->
            <div id="pane-authority" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label class="label-title">Major Authority Outlets (List)</label>
                        <textarea name="major_authority_sources" class="ai-input-base" style="width: 100%; height: 250px; border-radius: 16px; padding: 1rem; line-height: 1.5; outline: none;">{{ $settings['major_authority_sources'] }}</textarea>
                    </div>
                    <div>
                        <label class="label-title">Mid-Tier Authority Outlets (List)</label>
                        <textarea name="mid_authority_sources" class="ai-input-base" style="width: 100%; height: 250px; border-radius: 16px; padding: 1rem; line-height: 1.5; outline: none;">{{ $settings['mid_authority_sources'] }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Commit Source Mapping</button>
            </div>

            <!-- TAB 5: Performance -->
            <div id="pane-performance" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="setting-card">
                        <label class="label-title">Cache Duration (Seconds)</label>
                        <input type="number" name="cache_ttl" value="{{ $settings['cache_ttl'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                    <div class="setting-card">
                        <label class="label-title">Auto-Refresh Interval (Seconds)</label>
                        <input type="number" name="auto_refresh_seconds" value="{{ $settings['auto_refresh_seconds'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                    </div>
                </div>

                <div class="setting-card" style="margin-bottom: 2rem;">
                    <label class="label-title">Max Articles per Request</label>
                    <input type="number" name="max_articles_per_fetch" value="{{ $settings['max_articles_per_fetch'] }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.85rem; outline: none;">
                </div>

                <button type="submit" class="btn-save" style="width: 100%;"><i class="fas fa-save"></i> Save API Config</button>
            </div>

            <!-- TAB 6: AI Routing -->
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
                                    <option value="openrouter" {{ ($chain['provider'] ?? '') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                                    <option value="openai" {{ ($chain['provider'] ?? '') == 'openai' ? 'selected' : '' }}>OpenAI</option>
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

            <!-- TAB 7: Analytics -->
            <div id="pane-analytics" class="horizon-tab-pane">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; background: rgba(0,0,0,0.2); padding: 1.25rem; border-radius: 16px; border: 1px solid var(--horizon-border);">
                    <div style="font-size: 0.85rem; font-weight: 600;">Usage Timeframe</div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="ai-input-base" style="padding: 0.6rem; border-radius: 10px; font-size: 0.8rem; outline: none;">
                        <span style="color: var(--text-muted);">to</span>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="ai-input-base" style="padding: 0.6rem; border-radius: 10px; font-size: 0.8rem; outline: none;">
                        <button type="button" onclick="document.getElementById('config-form').submit()" style="background: var(--primary-admin); border: none; color: white; padding: 0.6rem 1.5rem; border-radius: 10px; font-size: 0.8rem; font-weight: 700; cursor: pointer;">
                            FILTER
                        </button>
                    </div>
                </div>

                <div class="card-admin">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem;"><i class="fas fa-users-crown text-yellow-400"></i> Elite Operators</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--horizon-border);">
                                    <th style="padding: 1rem;">User Identity</th>
                                    <th style="padding: 1rem;">Wallet Power</th>
                                    <th style="padding: 1rem; text-align: right;">Total Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subscribers as $sub)
                                <tr>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <div style="font-weight: 700; color: var(--text-main);">{{ $sub->name }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $sub->email }}</div>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.3rem 0.6rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">
                                            {{ number_format($sub->wallet->balance_credits ?? 0, 1) }} CRS
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02); text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                        {{ number_format($sub->ai_usages_count) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 1.5rem;">{{ $subscribers->links() }}</div>
                </div>
            </div>

            <!-- TAB 8: Errors -->
            <div id="pane-errors" class="horizon-tab-pane">
                <div class="card-admin">
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem; color: #ff4b4b;"><i class="fas fa-exclamation-circle"></i> Incident Log</h3>
                    @forelse($toolErrors as $error)
                    <div style="background: rgba(255,75,75,0.03); border: 1px solid rgba(255,75,75,0.1); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; position: relative;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div style="font-weight: 700; color: #ff4b4b; font-size: 0.85rem;">{{ $error->error_code ?? 'FAILURE' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $error->created_at->diffForHumans() }}</div>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-main); margin-bottom: 0.75rem; line-height: 1.5;">{{ $error->error_message }}</div>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Triggered by: <strong>{{ $error->user->name ?? 'SYSTEM' }}</strong></div>
                            <button type="button" class="inspect-error-payload" data-payload="{{ json_encode(['error' => $error->error_message, 'context' => $error->tool_slug]) }}" data-title="Diagnostic Metadata" style="background: rgba(14, 165, 233, 0.1); border: 1px solid rgba(14, 165, 233, 0.2); color: var(--primary-admin); padding: 0.35rem 0.8rem; border-radius: 8px; font-size: 0.7rem; font-weight: 800; cursor: pointer;">
                                <i class="fas fa-microscope"></i> INSPECT
                            </button>
                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function switchHorizonTab(tabId, btn) {
        document.querySelectorAll('.horizon-tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.horizon-tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + tabId).classList.add('active');
        btn.classList.add('active');
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

    function addNewAiNode() {
        const template = document.getElementById('ai-node-template');
        const container = document.getElementById('ai-chain-container');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.inspect-error-payload')) {
            const btn = e.target.closest('.inspect-error-payload');
            const payload = JSON.parse(btn.dataset.payload);
            Swal.fire({
                title: btn.dataset.title,
                html: `<pre style="text-align:left; font-size:0.75rem; background:#000; padding:20px; border-radius:12px; color:#0f0; direction:ltr; max-height:450px; overflow:auto;">${JSON.stringify(payload, null, 2)}</pre>`,
                background: '#111', color: '#fff', width: '800px'
            });
        }
    });
</script>
@endsection
