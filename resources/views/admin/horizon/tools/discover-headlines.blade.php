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
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('suggestions', this)"><i class="fas fa-lightbulb"></i> Suggestions</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('rules', this)"><i class="fas fa-gavel"></i> Discovery Rules</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('engine', this)"><i class="fas fa-search-plus"></i> News Engine</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('credits', this)"><i class="fas fa-coins"></i> Credit System</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('routing', this)"><i class="fas fa-network-wired"></i> AI Routing</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('analytics', this)"><i class="fas fa-chart-area"></i> Analytics</button>
            <button type="button" class="horizon-tab-btn" onclick="switchHorizonTab('errors', this)"><i class="fas fa-bug"></i> Errors</button>
        </div>

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST" id="config-form" data-ajax-save>
            @csrf
            <input type="hidden" name="is_active" value="{{ ($settings['is_active'] ?? true) ? '1' : '0' }}" id="statusInput">

            <!-- TAB 1: AI Intelligence -->
            <div id="pane-ai" class="horizon-tab-pane active">
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Core System Prompt (Headline DNA)</label>
                    <textarea name="prompt" rows="12" class="mono ai-input-base" style="width: 100%; border-radius: 12px; padding: 1.5rem; line-height: 1.6; font-size: 0.95rem; outline: none;">{{ $settings['prompt'] }}</textarea>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                        <i class="fas fa-info-circle"></i> This prompt defines the core personality and structural rules for the AI's headline generation.
                    </p>
                </div>
                
                <div class="rule-card" style="background: rgba(0, 168, 230, 0.05); border-color: rgba(0, 168, 230, 0.2);">
                    <h4 style="margin: 0 0 0.5rem; font-size: 0.9rem; color: var(--primary-admin);">Pro Tip</h4>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">Use <code>[Keyword]</code> and <code>[NewsContext]</code> as placeholders. The system will automatically inject live search data at runtime.</p>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-save"></i> Save Intelligence Sync
                </button>
            </div>

            <!-- TAB 2: Suggestions Prompt -->
            <div id="pane-suggestions" class="horizon-tab-pane">
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Suggestions Logic Prompt</label>
                    <textarea name="suggestions_prompt" rows="12" class="mono ai-input-base" style="width: 100%; border-radius: 12px; padding: 1.5rem; line-height: 1.6; font-size: 0.95rem; outline: none;" placeholder="Enter instructions for generating keyword/topic suggestions...">{{ $settings['suggestions_prompt'] ?? '' }}</textarea>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                        <i class="fas fa-info-circle"></i> Controls how the AI suggests new topics or viral angles for the user to explore.
                    </p>
                </div>

                <button type="submit" class="btn-save" style="width: 100%;">
                    <i class="fas fa-save"></i> Synchronize Suggestion Brain
                </button>
            </div>

            <!-- TAB 3: Discovery Rules -->
            <div id="pane-rules" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Minimum Headline Length</label>
                        <input type="number" name="min_chars" value="{{ $settings['min_chars'] ?? 55 }}" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Maximum Headline Length</label>
                        <input type="number" name="max_chars" value="{{ $settings['max_chars'] ?? 85 }}" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 1rem;">High-Engagement Power Words (Comma Separated)</label>
                    <textarea name="power_words" rows="4" class="ai-input-base" style="width: 100%; border-radius: 12px; padding: 1rem; outline: none;">{{ $settings['power_words'] ?? '' }}</textarea>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">The scoring engine awards bonus points when headlines include these specific verbs or hooks.</p>
                </div>

                <div class="rule-card">
                    <h4 style="margin: 0 0 1rem; font-size: 0.85rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-shield-alt text-cyan-400"></i> Auto-Scoring Parameters
                    </h4>
                    <div style="display: grid; gap: 0.75rem; font-size: 0.75rem; color: var(--text-muted);">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Keyword Match Bonus</span>
                            <span style="color: var(--horizon-success);">+30 PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Ideal Length ({{ ($settings['min_chars'] ?? 55) }}-{{ ($settings['max_chars'] ?? 85) }})</span>
                            <span style="color: var(--horizon-success);">+20 PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Entity Authority Injection</span>
                            <span style="color: var(--horizon-success);">+10 PTS</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--horizon-border); padding-top: 0.75rem;">
                            <span>Irrelevant Content Penalty</span>
                            <span style="color: #ff4b4b;">-50 PTS</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-save"></i> Update Discovery Rules
                </button>
            </div>

            <!-- TAB 4: News Engine -->
            <div id="pane-engine" class="horizon-tab-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Default RSS Region</label>
                        <select name="rss_region" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none; cursor: pointer;">
                            <option value="EG" {{ ($settings['rss_region'] ?? '') == 'EG' ? 'selected' : '' }}>Egypt (Default) 🇪🇬</option>
                            <option value="SA" {{ ($settings['rss_region'] ?? '') == 'SA' ? 'selected' : '' }}>Saudi Arabia 🇸🇦</option>
                            <option value="US" {{ ($settings['rss_region'] ?? '') == 'US' ? 'selected' : '' }}>United States 🇺🇸</option>
                            <option value="GB" {{ ($settings['rss_region'] ?? '') == 'GB' ? 'selected' : '' }}>United Kingdom 🇬🇧</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">News Window (Staleness)</label>
                        <select name="rss_time_window" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none; cursor: pointer;">
                            <option value="12h" {{ ($settings['rss_time_window'] ?? '') == '12h' ? 'selected' : '' }}>Last 12 Hours</option>
                            <option value="24h" {{ ($settings['rss_time_window'] ?? '') == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="7d" {{ ($settings['rss_time_window'] ?? '') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.75rem;">Context Cache TTL (Seconds)</label>
                    <input type="number" name="cache_ttl" value="{{ $settings['cache_ttl'] ?? 1800 }}" class="ai-input-base" style="width: 100%; padding: 0.75rem; border-radius: 10px; outline: none;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Reduces API calls by caching RSS context for repeated queries. Recommended: 1800 (30 mins).</p>
                </div>

                <div style="padding: 1.5rem; background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 16px;">
                    <p style="margin: 0; font-size: 0.8rem; color: #f59e0b; line-height: 1.5;">
                        <i class="fas fa-exclamation-triangle"></i> Changing the RSS Region or Window will clear existing discovery caches to ensure fresh data flow.
                    </p>
                </div>

                <button type="submit" class="btn-save" style="width: 100%; margin-top: 1.5rem;">
                    <i class="fas fa-save"></i> Apply Engine Parameters
                </button>
            </div>

            <!-- TAB 5: Credit System -->
            <div id="pane-pricing" class="horizon-tab-pane">
                @php
                    $creditCost = (int) \App\Models\Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 1);
                @endphp
                
                <div style="text-align: center; padding: 2rem 0;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #f59e0b; font-size: 2rem; border: 1px solid rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-coins"></i>
                    </div>
                    
                    <h2 style="margin: 0 0 0.5rem; font-family: 'Poppins', sans-serif; font-size: 1.5rem; font-weight: 800;">Financial Unit Calibration</h2>
                    <p style="margin: 0 auto 3rem; color: var(--text-muted); font-size: 0.9rem; max-width: 500px;">Set the operational cost for each manual tool synchronization.</p>
                    
                    <div style="max-width: 450px; margin: 0 auto; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 24px; padding: 2.5rem; position: relative;">
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; font-weight: 800; margin-bottom: 1.5rem; opacity: 0.6;">Sync Multiplier</div>
                        
                        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem;">
                            <input type="number" name="credit_cost" value="{{ $creditCost }}" min="0" class="ai-input-base" style="width: 90px; height: 80px; font-size: 2.5rem; font-weight: 800; text-align: center; border-radius: 16px; border: 2px solid var(--primary-admin); background: rgba(0, 168, 230, 0.05) !important; color: var(--primary-admin);">
                            <span style="font-size: 1.25rem; font-weight: 700; color: var(--text-muted); letter-spacing: 1px;">CREDITS</span>
                        </div>

                        <div style="margin-top: 2.5rem; background: rgba(0, 168, 230, 0.05); border: 1px solid rgba(0, 168, 230, 0.1); border-radius: 12px; padding: 1rem; display: flex; gap: 0.75rem; text-align: left;">
                            <div style="color: var(--primary-admin); font-size: 1rem; margin-top: 2px;"><i class="fas fa-info-circle"></i></div>
                            <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted); line-height: 1.5;">This value represents the total credits deducted from a user's wallet upon initiating a manual "Sync". automated tasks are cost-neutral.</p>
                        </div>
                    </div>

                    <button type="submit" class="vn-btn vn-btn-primary" style="margin-top: 2rem; padding: 1rem 3rem; font-size: 1rem; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-check-circle"></i> Confirm Credit Settings
                    </button>
                </div>
            </div>

            <!-- TAB 6: AI Routing -->
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
                                <input type="text" name="models[]" value="{{ $chain['model'] ?? '' }}" class="ai-input-base" style="width: 100%; border-radius: 10px; padding: 0.8rem; outline: none;" placeholder="google/gemini-2.0-flash-lite-001">
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

            <!-- TAB 7: Analytics -->
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
                    <h3 style="margin: 0 0 1.5rem; font-size: 1rem;"><i class="fas fa-users-crown text-yellow-400"></i> Elite Operators</h3>
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

            <!-- TAB 8: Errors -->
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
                        <div style="font-size: 3rem; color: rgba(16, 185, 129, 0.1); margin-bottom: 1rem;"><i class="fas fa-shield-check"></i></div>
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

        // Persist tab state in URL if needed
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    }

    function toggleToolStatus() {
        const input = document.getElementById('statusInput');
        const toggle = document.querySelector('.status-toggle');
        const orb = toggle.querySelector('div');
        const label = document.querySelector('.operational-status-text');

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
                console.error("Payload parsing error:", err);
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
