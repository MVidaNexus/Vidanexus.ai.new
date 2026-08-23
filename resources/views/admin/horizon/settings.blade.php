@extends('admin.horizon.layout')

@php
    $settingsTitles = [
        'availability' => 'System Settings - Tool Availability',
        'welcome' => 'System Settings - Marketplace',
        'credit-system' => 'System Settings - Credit System',
        'trial' => 'System Settings - Trial Package',
        'coupons' => 'System Settings - Coupons',
        'packages' => 'System Settings - Credit Packages',
        'smtp' => 'System Settings - Email Setup (SMTP)',
        'scripts' => 'System Settings - Global Scripts',
        'infrastructure' => 'System Settings - Infrastructure',
        'ledger' => 'System Settings - Transaction Ledger',
        'command' => 'System Settings - Command Center',
        'markdown' => 'System Settings - AI Crawler SEO',
        'countries' => 'System Settings - Country Registry',
    ];
@endphp
@section('title', $settingsTitles[$activeTab ?? 'availability'] ?? 'System Settings Matrix')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    @php
        $activeTab = $activeTab ?? (request()->route('tab') ?: 'availability');
    @endphp
    <form action="{{ route('admin.horizon.settings.update', ['tab' => $activeTab]) }}" method="POST" data-ajax-save>
        @csrf


        <!-- 2. Tool Availability -->
        <div id="content-availability" class="tab-panel {{ $activeTab === 'availability' ? 'active' : '' }}">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div>
                        <h3 style="color: #00A58B; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-toggle-on"></i> Tool Availability Control
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">
                            Master switch for each tool. <strong style="color: #00A58B;">ON</strong> = Live for users. <strong style="color: #ef4444;">OFF</strong> = Coming Soon.
                        </p>
                    </div>
                    <a href="/" target="_blank" class="vn-btn vn-btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.85rem; border-color: rgba(0, 168, 230, 0.3);">
                        <i class="fas fa-external-link-alt mr-2"></i> View Live Homepage
                    </a>
                </div>
                
                <div style="background: var(--horizon-primary-bg); border: 1px dashed rgba(0, 168, 230, 0.3); padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.75rem; color: var(--primary-admin);">
                    <i class="fas fa-info-circle mr-2"></i> <strong>Note:</strong> Since you are an <strong>Admin</strong>, all tools will appear active to you on the homepage. Use an <strong>Incognito Window</strong> or <strong>Logout</strong> to see how regular users see the "Coming Soon" states.
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
                    @foreach($tools as $tool)
                        @php
                            $availKey = "tool_available_{$tool['slug']}";
                            $isAvailable = $settings[$availKey] ?? false;
                        @endphp
                        <div style="background: var(--horizon-nav-hover); padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid {{ $isAvailable ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.2)' }}; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1rem;">
                                    <i class="fas {{ $tool['icon'] }}"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $tool['name'] }}</div>
                                    <div style="font-size: 0.65rem; color: {{ $isAvailable ? '#00A58B' : '#ef4444' }}; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        {{ $isAvailable ? '● LIVE' : '● COMING SOON' }}
                                    </div>
                                </div>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox" name="{{ $availKey }}" {{ $isAvailable ? 'checked' : '' }}>
                                <span class="vn-slider"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>




        <div id="content-welcome" class="tab-panel {{ $activeTab === 'welcome' ? 'active' : '' }}">
            <div class="card-admin">
                <h3 style="color: #a855f7; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-unlock-alt"></i> Marketplace
                </h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">
                    Centralized control for tool activation costs (Credits) and the strategic marketplace bonuses granted upon first acquisition.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.25rem;">
                    @foreach($tools as $tool)
                        @php
                            $priceKey = "tool_unlock_price_{$tool['slug']}";
                            $creditKey = "tool_credit_cost_{$tool['slug']}";
                            $bonusKey = "tool_bonus_credits_{$tool['slug']}";
                            
                            $currentPrice = $settings[$priceKey] ?? ($tool['unlock_price'] ?? 99);
                            $currentCredit = $settings[$creditKey] ?? ($tool['credit_cost_per_action'] ?? 1);
                            $currentBonus = $settings[$bonusKey] ?? ($tool['initial_bonus_credits'] ?? 10);
                        @endphp
                        <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }};">
                                        <i class="fas {{ $tool['icon'] }}"></i>
                                    </div>
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">{{ $tool['name'] }}</span>
                                </div>
                                <span style="font-size: 0.6rem; color: var(--text-muted); font-family: monospace;">{{ $tool['slug'] }}</span>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; font-weight: 700;">Activation Price / Month</label>
                                    <div style="position: relative;">
                                        <input type="number" name="{{ $priceKey }}" value="{{ $currentPrice }}" class="modal-input" min="0" style="padding: 0.5rem 2.8rem 0.5rem 0.75rem; text-align: center;">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.6rem; font-weight: 800; color: var(--text-muted); opacity: 0.4; pointer-events: none;">EGP</span>
                                    </div>
                                </div>
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.6rem; color: var(--primary-admin); text-transform: uppercase; margin-bottom: 0.4rem; font-weight: 700;">System Credit / Action</label>
                                    <div style="position: relative;">
                                        <input type="number" name="{{ $creditKey }}" value="{{ $currentCredit }}" class="modal-input" min="0" style="padding: 0.5rem 3.5rem 0.5rem 0.75rem; text-align: center; border-color: rgba(0, 168, 230, 0.3);">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.6rem; font-weight: 800; color: var(--primary-admin); opacity: 0.45; pointer-events: none;">Credits</span>
                                    </div>
                                </div>
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.6rem; color: #a855f7; text-transform: uppercase; margin-bottom: 0.4rem; font-weight: 700;">Bonus Credits</label>
                                    <div style="position: relative;">
                                        <input type="number" name="{{ $bonusKey }}" value="{{ $currentBonus }}" class="modal-input" min="0" style="padding: 0.5rem 3.5rem 0.5rem 0.75rem; text-align: center; border-color: rgba(168, 85, 247, 0.3);">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.6rem; font-weight: 800; color: #a855f7; opacity: 0.4; pointer-events: none;">Credits</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- Credit System Tab -->
        <div id="content-credit-system" class="tab-panel {{ $activeTab === 'credit-system' ? 'active' : '' }}">
            <div class="card-admin" style="margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                    <div>
                        <h3 style="color: #f59e0b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-coins"></i> Credit System
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; max-width: 720px; line-height: 1.55;">
                            Single source of truth for what each tool charges per action. The canonical setting key is
                            <code style="color: var(--primary-admin);">tool_credit_cost_&lt;slug&gt;</code> — every tool deduction
                            (generic flow and modules) reads this value through <code style="color: var(--primary-admin);">ToolCreditConsumptionService</code>.
                            Editing here also updates the legacy <code>{slug}_sync_credits</code> key so the per-tool admin pages stay aligned.
                        </p>
                    </div>
                    <div style="background: var(--horizon-primary-bg); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 12px; padding: 0.75rem 1rem; min-width: 220px;">
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px;">Wallet Pool (All Users)</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary-admin); font-family: 'Space Grotesk', sans-serif;">
                            {{ number_format($stats['total_credits'] ?? 0) }} CRS
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1rem;">
                    @foreach($tools as $tool)
                        @php
                            $creditKey = "tool_credit_cost_{$tool['slug']}";
                            $currentCredit = $settings[$creditKey] ?? ($tool['credit_cost_per_action'] ?? 1);
                            $configDefault = (int) ($tool['credit_cost_per_action'] ?? 1);
                            $legacySync = $settings["{$tool['slug']}_sync_credits"] ?? null;
                            $legacyAi = $settings["{$tool['slug']}_ai_analysis_credits"] ?? null;
                            $availKey = "tool_available_{$tool['slug']}";
                            $isAvailable = $settings[$availKey] ?? false;
                            $mismatch = $legacySync !== null && (int) $legacySync !== (int) $currentCredit;
                        @endphp
                        <div style="background: var(--horizon-nav-hover); border: 1px solid {{ $mismatch ? 'rgba(245, 158, 11, 0.4)' : 'var(--horizon-border)' }}; border-radius: 16px; padding: 1.1rem 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                                    <div style="width: 32px; height: 32px; border-radius: 9px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] ?? 'var(--primary-admin)' }}; flex-shrink: 0;">
                                        <i class="fas {{ $tool['icon'] ?? 'fa-cube' }}"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $tool['name'] }}</div>
                                        <div style="font-size: 0.6rem; color: var(--text-muted); font-family: monospace;">{{ $tool['slug'] }}</div>
                                    </div>
                                </div>
                                <span style="font-size: 0.55rem; padding: 0.2rem 0.5rem; border-radius: 6px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; background: {{ $isAvailable ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.12)' }}; color: {{ $isAvailable ? '#10b981' : '#ef4444' }};">
                                    {{ $isAvailable ? 'Live' : 'Hidden' }}
                                </span>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.65rem; color: var(--text-muted); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 1.2px;">Action Cost (CRS)</label>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="number" name="{{ $creditKey }}" value="{{ $currentCredit }}" min="0" step="1"
                                        style="flex: 1; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 10px; color: var(--text-main); padding: 0.7rem 0.85rem; outline: none; font-size: 1.05rem; font-weight: 800; text-align: center; font-family: 'Space Grotesk', sans-serif;">
                                    <span style="font-size: 0.7rem; color: var(--text-muted); white-space: nowrap;">/ action</span>
                                </div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.4rem; line-height: 1.4;">
                                    Config default: <strong style="color: var(--text-main);">{{ $configDefault }}</strong>
                                    @if($legacySync !== null)
                                        · Legacy <code>{{ $tool['slug'] }}_sync_credits</code>: <strong style="color: {{ $mismatch ? '#f59e0b' : 'var(--horizon-success)' }};">{{ (int) $legacySync }}</strong>
                                    @endif
                                    @if($legacyAi !== null)
                                        · AI deep-analyze: <strong style="color: var(--text-main);">{{ (int) $legacyAi }}</strong>
                                    @endif
                                </div>
                                @if($mismatch)
                                    <div style="margin-top: 0.5rem; font-size: 0.65rem; color: #f59e0b; display: flex; align-items: center; gap: 0.35rem;">
                                        <i class="fas fa-exclamation-triangle"></i> Legacy key out of sync — saving will fix it.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" class="btn-save" style="padding: 0.95rem 2.5rem;">
                        <i class="fas fa-save"></i> Save Credit Matrix
                    </button>
                    <a href="{{ route('admin.horizon.ledger.index') }}" class="vn-btn vn-btn-outline" style="padding: 0.7rem 1.25rem; font-size: 0.85rem;">
                        <i class="fas fa-book mr-2"></i> Open Financial Ledger
                    </a>
                </div>
            </div>

            <div class="card-admin">
                <h3 style="color: var(--primary-admin); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-info-circle"></i> How the credit system works
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; font-size: 0.85rem; color: var(--text-muted); line-height: 1.55;">
                    <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 12px; padding: 1rem;">
                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">1. Wallet first</div>
                        Every tool action deducts from <code>wallets.balance_credits</code> first.
                    </div>
                    <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 12px; padding: 1rem;">
                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">2. Bonus next</div>
                        If the user owns the tool with <code>allow_bonus_for_ai_usage = true</code>, leftover cost is taken from the per-tool bonus pool.
                    </div>
                    <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 12px; padding: 1rem;">
                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">3. Ledger + Audit</div>
                        A row is written to <code>transactions</code>, <code>financial_ledger_entries</code> and the audit log on every deduction.
                    </div>
                    <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 12px; padding: 1rem;">
                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem;">4. Live UI</div>
                        Each tool response returns the new balance, and <code>credits-live.js</code> animates every <code>.js-credit-balance</code> chip without a refresh.
                    </div>
                </div>
            </div>
        </div>


        <!-- Trial Package Tab -->
        <div id="content-trial" class="tab-panel {{ $activeTab === 'trial' ? 'active' : '' }}">

            {{-- Card 1: Welcome Credits --}}
            <div class="card-admin" style="margin-bottom: 2rem;">
                <h3 style="color: #f59e0b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-coins"></i> Welcome Credits for New Users
                </h3>
                <div style="background: var(--horizon-nav-hover); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(245,158,11,0.25); display: flex; align-items: center; gap: 2rem;">
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-size: 0.85rem;">Credits Granted on Registration:</label>
                        <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">
                            Amount of Credits automatically added to a new user's wallet upon sign-up. These credits serve as the trial budget for accessing any unlocked trial tools below.
                            Set to <strong style="color:#ef4444;">0</strong> to disable free credits.
                        </p>
                    </div>
                    <div style="width: 200px; position: relative;">
                        <input type="number" name="plan_credits_beginner" value="{{ $settings['plan_credits_beginner'] ?? 0 }}" class="modal-input" step="1" min="0" style="font-size: 1.2rem; font-weight: 700; text-align: center; border-color: rgba(245,158,11,0.5); padding-right: 4.5rem;">
                        <span style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 800; color: #f59e0b; opacity: 0.6; pointer-events: none;">Credits</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Trial Tool Pre-Unlock --}}
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h3 style="color: #f59e0b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-unlock"></i> Trial Tool Pre-Unlock
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">
                            Tools toggled <strong style="color:#00A58B;">ON</strong> here are automatically granted to every new user upon registration — no payment required.
                            They can use these tools immediately using their welcome credits above.
                        </p>
                    </div>
                    <div style="display: flex; gap: 0.75rem; flex-shrink: 0;">
                        <button type="button" onclick="setAllTrialTools(true)" style="padding: 0.5rem 1.2rem; border-radius: 10px; border: 1px solid rgba(16,185,129,0.4); background: rgba(16,185,129,0.08); color: #00A58B; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(16,185,129,0.18)'" onmouseout="this.style.background='rgba(16,185,129,0.08)'">
                            <i class="fas fa-toggle-on mr-1"></i> Enable All
                        </button>
                        <button type="button" onclick="setAllTrialTools(false)" style="padding: 0.5rem 1.2rem; border-radius: 10px; border: 1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.06); color: #ef4444; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.14)'" onmouseout="this.style.background='rgba(239,68,68,0.06)'">
                            <i class="fas fa-toggle-off mr-1"></i> Disable All
                        </button>
                    </div>
                </div>

                <div style="background: rgba(245,158,11,0.05); border: 1px dashed rgba(245,158,11,0.25); padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.75rem; color: #d97706;">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>How it works:</strong> Each trial tool uses the user's wallet credits per action (as configured in Marketplace). Trial access is unlimited in time — it ends naturally when the welcome credits are exhausted.
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                    @foreach($tools as $tool)
                        @php
                            $trialKey   = "trial_tool_{$tool['slug']}";
                            $isTrialOn  = $settings[$trialKey] ?? false;
                            $creditCost = $settings["tool_credit_cost_{$tool['slug']}"] ?? ($tool['credit_cost_per_action'] ?? 1);
                        @endphp
                        <div id="trial-card-{{ $tool['slug'] }}"
                             style="background: var(--horizon-nav-hover); padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid {{ $isTrialOn ? 'rgba(245,158,11,0.45)' : 'rgba(255,255,255,0.06)' }}; display: flex; justify-content: space-between; align-items: center; transition: border-color 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1rem; flex-shrink: 0;">
                                    <i class="fas {{ $tool['icon'] }}"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $tool['name'] }}</div>
                                    <div style="font-size: 0.65rem; margin-top: 2px;">
                                        @if($isTrialOn)
                                            <span style="color: #f59e0b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">● TRIAL UNLOCKED</span>
                                        @else
                                            <span style="color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">● LOCKED</span>
                                        @endif
                                        <span style="color: var(--text-muted); margin-left: 6px;">{{ $creditCost }} CRS/action</span>
                                    </div>
                                </div>
                            </div>
                            <label class="vn-switch">
                                <input type="checkbox"
                                       name="{{ $trialKey }}"
                                       class="trial-tool-checkbox"
                                       data-slug="{{ $tool['slug'] }}"
                                       {{ $isTrialOn ? 'checked' : '' }}
                                       onchange="updateTrialCard(this)">
                                <span class="vn-slider"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- 4. Credit Packages -->
        <div id="content-packages" class="tab-panel {{ $activeTab === 'packages' ? 'active' : '' }}">
            <div class="card-admin">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div>
                        <h3 style="color: var(--primary-admin); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-box"></i> Marketplace Credit Packages
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">
                            Configure the packages shown on the public pricing page.
                        </p>
                    </div>
                </div>
                
                @php
                    $defaultPackages = [
                        'lite' => [ 'name' => 'Lite Dash', 'credits' => '100', 'price' => '35', 'desc' => 'Perfect for testing a single tool.', 'icon' => 'fa-seedling', 'color' => '#00ffaa' ],
                        'standard' => [ 'name' => 'Creator Pack', 'credits' => '500', 'price' => '150', 'desc' => 'Best for social media managers.', 'icon' => 'fa-rocket', 'color' => 'var(--primary-cyan)', 'popular' => true ],
                        'pro' => [ 'name' => 'Agency Pro', 'credits' => '2,500', 'price' => '650', 'desc' => 'High-volume SEO & Content.', 'icon' => 'fa-bolt-lightning', 'color' => 'var(--accent)' ],
                        'enterprise' => [ 'name' => 'Power Node', 'credits' => '10,000', 'price' => '2,250', 'desc' => 'Infrastructure level usage.', 'icon' => 'fa-crown', 'color' => '#ffcc00' ]
                    ];
                    $savedPackagesJson = collect($settings)->get('marketplace_packages', null);
                    $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);
                @endphp

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                    @foreach(['lite', 'standard', 'pro', 'enterprise'] as $pkgKey)
                        @php $pkg = $packages[$pkgKey] ?? $defaultPackages[$pkgKey]; @endphp
                        <div style="background: var(--horizon-nav-hover); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.5rem;">
                            <h4 style="margin: 0 0 1rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem; text-transform: capitalize;">
                                <i class="fas {{ $pkg['icon'] }}" style="color: {{ $pkg['color'] }};"></i> 
                                {{ $pkgKey }} Package
                                <input type="hidden" name="packages[{{ $pkgKey }}][icon]" value="{{ $pkg['icon'] }}">
                                <input type="hidden" name="packages[{{ $pkgKey }}][color]" value="{{ $pkg['color'] }}">
                            </h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Name</label>
                                    <input type="text" name="packages[{{ $pkgKey }}][name]" value="{{ $pkg['name'] }}" class="modal-input">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Credits Amount</label>
                                    <div style="position: relative;">
                                        <input type="text" name="packages[{{ $pkgKey }}][credits]" value="{{ $pkg['credits'] }}" class="modal-input" style="padding-right: 4rem;">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.7rem; font-weight: 800; color: var(--text-muted); opacity: 0.4; pointer-events: none;">Credits</span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Price (EGP)</label>
                                    <div style="position: relative;">
                                        <input type="text" name="packages[{{ $pkgKey }}][price]" value="{{ $pkg['price'] }}" class="modal-input" style="padding-right: 3rem;">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.7rem; font-weight: 800; color: var(--text-muted); opacity: 0.4; pointer-events: none;">EGP</span>
                                    </div>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.75rem; color: var(--primary-admin); margin-bottom: 0.5rem; font-weight: 700;">Discount (%)</label>
                                    <input type="number" name="packages[{{ $pkgKey }}][discount]" value="{{ $pkg['discount'] ?? 0 }}" class="modal-input" placeholder="e.g. 20" min="0" max="100" style="border-color: rgba(0, 168, 230, 0.3);">
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; font-weight: 600;">Description</label>
                                <input type="text" name="packages[{{ $pkgKey }}][desc]" value="{{ $pkg['desc'] }}" class="modal-input">
                            </div>
                            <div style="display: flex; flex-direction: column; justify-content: flex-end; background: rgba(0,0,0,0.05); padding: 0.75rem; border-radius: 10px; border: 1px dashed var(--horizon-border);">
                                <label style="font-size: 0.85rem; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: 600;">
                                    <input type="checkbox" name="packages[{{ $pkgKey }}][popular]" value="1" {{ isset($pkg['popular']) && $pkg['popular'] ? 'checked' : '' }} style="width: 16px; height: 16px; cursor: pointer;">
                                    Mark as "Best Value" Ribbon
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 4. Email Setup (SMTP) -->
        <div id="content-smtp" class="tab-panel {{ $activeTab === 'smtp' ? 'active' : '' }}">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--horizon-border); padding-bottom: 1rem;">
                    <div>
                        <h3 style="color: var(--primary-cyan); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.75rem; font-size: 1.25rem;">
                            <i class="fas fa-envelope-open-text"></i> Email Infrastructure
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Configure your outgoing SMTP server for system communications.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
                    <!-- Connection Settings -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 1.5rem;">
                        <h4 style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem;">
                            <i class="fas fa-plug" style="color: var(--primary-cyan);"></i> Server Connectivity
                        </h4>
                        
                        <div style="margin-bottom: 1.25rem;">
                            <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                <i class="fas fa-paper-plane opacity-50"></i> Mail driver
                            </label>
                            <select name="MAIL_MAILER" class="modal-input">
                                @php $mailer = env('MAIL_MAILER', 'log'); @endphp
                                <option value="log" {{ $mailer === 'log' ? 'selected' : '' }}>log (dev only — no real email)</option>
                                <option value="smtp" {{ $mailer === 'smtp' ? 'selected' : '' }}>smtp (send via SMTP)</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 1.25rem;">
                            <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                <i class="fas fa-server opacity-50"></i> SMTP Host
                            </label>
                            <input type="text" name="MAIL_HOST" value="{{ env('MAIL_HOST') }}" class="modal-input" placeholder="e.g. smtp.gmail.com">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                            <div>
                                <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-microchip opacity-50"></i> Port
                                </label>
                                <input type="text" name="MAIL_PORT" value="{{ env('MAIL_PORT') }}" class="modal-input" placeholder="587">
                            </div>
                            <div>
                                <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-shield-alt opacity-50"></i> Transport scheme (Laravel 11)
                                </label>
                                <select name="MAIL_SCHEME" class="modal-input">
                                    @php $scheme = env('MAIL_SCHEME'); @endphp
                                    <option value="" {{ $scheme === null || $scheme === '' ? 'selected' : '' }}>Default</option>
                                    <option value="smtp" {{ $scheme === 'smtp' ? 'selected' : '' }}>smtp (STARTTLS, e.g. port 587)</option>
                                    <option value="smtps" {{ $scheme === 'smtps' ? 'selected' : '' }}>smtps (SSL, e.g. port 465)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                <i class="fas fa-certificate opacity-50"></i> Peer Verification
                            </label>
                            <select name="MAIL_VERIFY_PEER" class="modal-input" style="border-color: {{ env('MAIL_VERIFY_PEER', true) ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                                <option value="true" {{ env('MAIL_VERIFY_PEER', true) === true || env('MAIL_VERIFY_PEER') === 'true' ? 'selected' : '' }}>Enabled (Secure)</option>
                                <option value="false" {{ env('MAIL_VERIFY_PEER') === false || env('MAIL_VERIFY_PEER') === 'false' || env('MAIL_VERIFY_PEER') === '' ? 'selected' : '' }}>Disabled (Bypass)</option>
                            </select>
                            <p style="font-size: 0.65rem; color: #f59e0b; margin-top: 0.5rem; line-height: 1.4;">
                                <i class="fas fa-info-circle"></i> Use "Bypass" if you get SSL certificate errors.
                            </p>
                        </div>
                    </div>

                    <!-- Authentication & Sender -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 1.5rem;">
                        <h4 style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 0.75rem;">
                            <i class="fas fa-user-shield" style="color: #a855f7;"></i> Authentication & Sender
                        </h4>

                        <div style="margin-bottom: 1.25rem;">
                            <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                <i class="fas fa-at opacity-50"></i> Username
                            </label>
                            <input type="text" name="MAIL_USERNAME" value="{{ env('MAIL_USERNAME') }}" class="modal-input" placeholder="Your email address">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                <i class="fas fa-key opacity-50"></i> Password
                            </label>
                            <input type="password" name="MAIL_PASSWORD" value="{{ env('MAIL_PASSWORD') }}" class="modal-input" placeholder="••••••••••••">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-paper-plane opacity-50"></i> From Email
                                </label>
                                <input type="email" name="MAIL_FROM_ADDRESS" value="{{ env('MAIL_FROM_ADDRESS') }}" class="modal-input" placeholder="no-reply@domain.com">
                            </div>
                            <div>
                                <label class="setting-label" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                                    <i class="fas fa-signature opacity-50"></i> From Name
                                </label>
                                <input type="text" name="MAIL_FROM_NAME" value="{{ env('MAIL_FROM_NAME') }}" class="modal-input" placeholder="VidaNexus AI">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1.25rem; background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.2); border-radius: 12px; display: flex; align-items: flex-start; gap: 1rem;">
                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-top: 0.25rem;"></i>
                    <div style="font-size: 0.75rem; color: #d97706; line-height: 1.6;">
                        <strong>Direct Environment Update:</strong> Saving these settings will immediately modify your <code>.env</code> file. Ensure your mail provider credentials are correct to avoid system notification failures.
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Global Scripts -->
        <div id="content-scripts" class="tab-panel {{ $activeTab === 'scripts' ? 'active' : '' }}">
            <div class="card-admin">
                <h3 style="color: var(--primary-cyan); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-code"></i> Site Scripts & Global Injection
                </h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 2rem;">
                    Inject custom HTML/Javascript headers or tracking codes to all pages. Included before the closing <code>&lt;/body&gt;</code> tag.
                </p>
                
                <div style="background: var(--horizon-nav-hover); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--horizon-border);">
                    <label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; font-size: 0.85rem;">Footer Injection Script:</label>
                    <textarea name="footer_script" class="modal-input" style="height: 300px; font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; line-height: 1.5; resize: vertical;" placeholder="Paste your script here...">{{ $settings['footer_script'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- 7. Backend Infrastructure -->
        <div id="content-infrastructure" class="tab-panel {{ $activeTab === 'infrastructure' ? 'active' : '' }}">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-cyan); margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-layer-group"></i> Backend Infrastructure
                    </h3>
                    <span style="background: rgba(16, 185, 129, 0.1); color: #00A58B; border: 1px solid rgba(16, 185, 129, 0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">STABLE</span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 2rem;">
                        <h4 style="margin-bottom: 1.5rem; color: var(--primary-cyan); font-size: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-microchip"></i> System Environment
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid var(--horizon-border);">
                                <span style="color: var(--text-muted); font-size: 0.85rem;">PHP Version</span>
                                <span style="color: var(--text-main); font-weight: 700; font-family: 'JetBrains Mono';">{{ $stats['php_version'] }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid var(--horizon-border);">
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Laravel Core</span>
                                <span style="color: var(--text-main); font-weight: 700; font-family: 'JetBrains Mono';">{{ $stats['laravel_version'] }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid var(--horizon-border);">
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Queue Driver</span>
                                <span style="color: #00A58B; font-weight: 700;">Redis (Horizon)</span>
                            </div>
                        </div>
                    </div>

                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 2rem;">
                        <h4 style="margin-bottom: 1.5rem; color: var(--secondary-admin); font-size: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-brain"></i> AI Gateway Sync
                        </h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; margin-bottom: 1.5rem;">
                            High-availability proxy coordinating between multiple AI provider APIs including OpenAI, Gemini, and Claude (via OpenRouter).
                        </p>
                        <div style="display: flex; gap: 0.75rem;">
                            <span style="font-size: 0.7rem; color: var(--primary-admin); background: rgba(0, 168, 230, 0.1); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid rgba(0, 168, 230, 0.2); font-weight: 800;">LOAD BALANCING ACTIVE</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Transaction Ledger -->
        <div id="content-ledger" class="tab-panel {{ $activeTab === 'ledger' ? 'active' : '' }}">
            <div class="card-admin" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
                <a href="{{ route('admin.horizon.ledger.index') }}" style="color: var(--primary-admin); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-book"></i> Open full financial ledger (wallet + bonus events)
                </a>
            </div>
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-admin); margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-list-ul"></i> Transaction Ledger
                    </h3>
                    <div style="display: flex; gap: 1rem;">
                        <div style="text-align: right;">
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Global Supply</div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: var(--primary-admin);">{{ number_format($stats['total_credits'], 0) }} Credits</div>
                        </div>
                    </div>
                </div>

                <div style="overflow-x: auto; background: rgba(0,0,0,0.2); border: 1px solid var(--horizon-border); border-radius: 16px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--horizon-border);">
                                <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Type</th>
                                <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Amount</th>
                                <th style="padding: 1.25rem; text-align: left; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">User / Tool</th>
                                <th style="padding: 1.25rem; text-align: right; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['latest_transactions'] as $tx)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: 1.25rem;">
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: {{ $tx->type == 'deposit' || $tx->type == 'refund' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $tx->type == 'deposit' || $tx->type == 'refund' ? '#00A58B' : '#ef4444' }}; border: 1px solid {{ $tx->type == 'deposit' || $tx->type == 'refund' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                                            {{ $tx->type }}
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem; font-family: 'Poppins'; font-weight: 700; color: var(--text-main); font-size: 1rem;">
                                        {{ $tx->type == 'deposit' || $tx->type == 'refund' ? '+' : '-' }}{{ number_format($tx->amount, 2) }}
                                    </td>
                                    <td style="padding: 1.25rem;">
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $tx->user_name ?? 'System' }}</div>
                                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $tx->tool_name ?: 'System Admin' }}</div>
                                    </td>
                                    <td style="padding: 1.25rem; text-align: right; font-size: 0.8rem; color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 9. Infrastructure Command Center -->
        <div id="content-command" class="tab-panel {{ $activeTab === 'command' ? 'active' : '' }}">
            <div class="card-admin" style="text-align: center; padding: 4rem 2rem;">
                <div style="width: 80px; height: 80px; background: rgba(0, 168, 230, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 1px solid rgba(0, 168, 230, 0.2);">
                    <i class="fas fa-terminal" style="font-size: 2.5rem; color: var(--primary-admin);"></i>
                </div>
                <h3 style="color: var(--text-main); font-size: 1.75rem; margin-bottom: 1rem; font-family: 'Poppins', sans-serif;">Infrastructure Command Center</h3>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 3rem; line-height: 1.6;">
                    Direct access to core system operations, health monitoring, and administrative session management.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center;">
                    <a href="{{ route('admin.horizon.index') }}" class="btn-save" style="text-decoration: none; padding: 1.25rem 3rem; background: linear-gradient(135deg, var(--secondary-admin), var(--primary-admin)); color: #000; display: flex; align-items: center; gap: 0.75rem; border: none; box-shadow: 0 10px 20px rgba(0, 168, 230, 0.2);">
                        <i class="fas fa-brain"></i> AI Intelligence Center
                    </a>
                    <a href="/up" target="_blank" class="btn-save" style="text-decoration: none; padding: 1.25rem 3rem; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--horizon-border); display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-heartbeat" style="color: #00A58B;"></i> System Pulse (UP)
                    </a>
                    <button type="button" onclick="let f=document.createElement('form');f.method='POST';f.action='/logout';let t=document.createElement('input');t.type='hidden';t.name='_token';t.value='{{ csrf_token() }}';f.appendChild(t);document.body.appendChild(f);f.submit();" class="btn-save" style="padding: 1.25rem 3rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <i class="fas fa-power-off"></i> Logout Session
                    </button>
                </div>
            </div>
        </div>

        <!-- 10. AI Crawler Markdown SEO -->
        <div id="content-markdown" class="tab-panel {{ $activeTab === 'markdown' ? 'active' : '' }}">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
                    <div>
                        <h3 style="color: #f59e0b; margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-robot"></i> AI Crawler Markdown Adaptation
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">
                            Control how your site appears to AI agents (GPTBot, ClaudeBot, etc.) by serving noise-free Markdown.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem; background: rgba(245, 158, 11, 0.05); padding: 0.75rem 1.25rem; border-radius: 12px; border: 1px solid rgba(245, 158, 11, 0.2);">
                        <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">Global AI Protocol</span>
                        <label class="vn-switch">
                            <input type="checkbox" name="markdown_ai_enabled" {{ ($settings['markdown_ai_enabled'] ?? config('markdown_ai.enabled')) ? 'checked' : '' }}>
                            <span class="vn-slider"></span>
                        </label>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
                    <!-- Bot Identification -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 1.5rem;">
                        <h4 style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 800;">
                            <i class="fas fa-user-secret" style="color: #f59e0b;"></i> Crawler Identification
                        </h4>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                            List of User-Agent substrings that trigger Markdown delivery. Separate by commas (e.g., GPTBot, ClaudeBot).
                        </p>
                        <textarea name="markdown_ai_crawlers" class="modal-input" style="height: 120px; font-family: monospace; font-size: 0.8rem; line-height: 1.6;" placeholder="GPTBot, ClaudeBot, PerplexityBot...">{{ $settings['markdown_ai_crawlers'] ?? implode(', ', config('markdown_ai.crawlers')) }}</textarea>
                    </div>

                    <!-- Layout Cleaning -->
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 1.5rem;">
                        <h4 style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; font-weight: 800;">
                            <i class="fas fa-broom" style="color: #00A58B;"></i> Noise Reduction Selectors
                        </h4>
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                            HTML tags or CSS selectors to strip before conversion to minimize indexable bloat (e.g., nav, header, footer).
                        </p>
                        <textarea name="markdown_ai_selectors" class="modal-input" style="height: 120px; font-family: monospace; font-size: 0.8rem; line-height: 1.6;" placeholder="nav, header, footer, .whatsapp-btn...">{{ $settings['markdown_ai_selectors'] ?? implode(', ', config('markdown_ai.strip_selectors')) }}</textarea>
                    </div>
                </div>

                <div style="margin-top: 2rem; background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 20px; padding: 1.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="flex: 1;">
                            <h4 style="color: var(--text-main); font-size: 0.9rem; margin-bottom: 0.5rem; font-weight: 800;">Markdown Generation Cache</h4>
                            <p style="font-size: 0.7rem; color: var(--text-muted);">How long the generated Markdown remains in memory before re-rendering (seconds).</p>
                        </div>
                        <div style="width: 200px; position: relative;">
                            <input type="number" name="markdown_ai_cache_ttl" value="{{ $settings['markdown_ai_cache_ttl'] ?? config('markdown_ai.cache.ttl') }}" class="modal-input" style="text-align: center; padding-right: 4rem; font-weight: 700; color: #f59e0b;">
                            <span style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.65rem; color: var(--text-muted); font-weight: 800;">SEC</span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding: 1.25rem; background: rgba(0, 168, 230, 0.05); border: 1px dashed rgba(0, 168, 230, 0.2); border-radius: 12px; display: flex; align-items: start; gap: 1rem;">
                    <i class="fas fa-info-circle" style="color: var(--primary-admin); margin-top: 0.25rem;"></i>
                    <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.6;">
                        <strong>SEO Strategy Tip:</strong> AI crawlers like GPTBot prefer clean text data. By serving Markdown, you improve the accuracy of model-based indexing for your tools and content. You can manually verify the output by adding <code>.md</code> to any public URL.
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────────────────────────────────────────────────── --}}
        {{-- COUNTRY REGISTRY (centralized country visibility for all tools) --}}
        {{-- ──────────────────────────────────────────────────────────── --}}
        <div id="content-countries" class="tab-panel {{ $activeTab === 'countries' ? 'active' : '' }}">
            @php
                $registryText = \App\Support\CountryRegistry::globalLines();
                $globalActive = \App\Support\CountryRegistry::globalActiveCodes();
                $allFromRegistry = \App\Support\CountryRegistry::parseAdminLines($registryText);
                $defaultMap = \App\Support\CountryRegistry::baseMap();
                $merged = $allFromRegistry + array_combine(
                    array_keys($defaultMap),
                    array_map(function ($c, $code) {
                        return [
                            'name' => $c['name'] ?? $code,
                            'flag' => $c['flag'] ?? '🌐',
                            'lang' => $c['lang'] ?? 'en',
                        ];
                    }, $defaultMap, array_keys($defaultMap))
                );
                $isVisible = function ($code) use ($globalActive) {
                    return $globalActive === null ? true : in_array($code, $globalActive, true);
                };
            @endphp

            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div>
                        <h3 style="color: #0ea5e9; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-globe"></i> Country Registry
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 720px; line-height: 1.6;">
                            Single source of truth for the country dropdowns across <strong>Global News Monitor</strong>, the <strong>Viral Search Monitor</strong>, <strong>Discover Headlines</strong>, and any future country-aware tool. Per-tool overrides still work, but they are intersected with the visibility list below — countries you uncheck here disappear from every tool immediately.
                        </p>
                    </div>
                </div>

                <div style="background: rgba(14, 165, 233, 0.05); border: 1px dashed rgba(14, 165, 233, 0.3); padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 1.75rem; font-size: 0.78rem; color: var(--primary-admin); line-height: 1.6;">
                    <i class="fas fa-info-circle mr-2"></i>
                    Countries are matched by ISO-2 codes (e.g. <code>EG</code>, <code>SA</code>). Use the master list textarea below to add new countries (one per line, format <code>CODE:Name flag</code>), then tick which ones should be globally visible.
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
                    <div>
                        <label class="label-title" style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.75rem; font-weight: 800;">
                            Visible in tool dropdowns
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem;">
                            @foreach($merged as $code => $meta)
                                @php $checked = $isVisible($code); @endphp
                                <label style="display: flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.03); border: 1px solid {{ $checked ? 'rgba(14,165,233,0.35)' : 'var(--horizon-border)' }}; border-radius: 12px; padding: 0.7rem 1rem; cursor: pointer; transition: all 0.2s;">
                                    <input type="checkbox" name="global_country_visibility[]" value="{{ $code }}" {{ $checked ? 'checked' : '' }} style="accent-color: var(--primary-admin); transform: scale(1.15);">
                                    <span style="font-size: 1.05rem;">{{ $meta['flag'] ?? '🌐' }}</span>
                                    <div style="display: flex; flex-direction: column; min-width: 0;">
                                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $meta['name'] ?? $code }}</span>
                                        <span style="font-size: 0.6rem; color: var(--text-muted); font-family: monospace; letter-spacing: 1px;">{{ $code }} · {{ $meta['lang'] ?? 'en' }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="label-title" style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.75rem; font-weight: 800;">
                            Master country list (CODE:Name flag)
                        </label>
                        <textarea name="global_country_registry" class="modal-input" style="width: 100%; height: 220px; font-family: monospace; font-size: 0.82rem; padding: 1rem; resize: vertical; background: rgba(0,0,0,0.25); border: 1px solid var(--horizon-border); border-radius: 12px; color: var(--text-main); line-height: 1.6;">{{ $registryText }}</textarea>
                        <p style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.6rem; line-height: 1.5;">
                            One country per line, format <code>CODE:Name flag</code> (e.g. <code>EG:Egypt 🇪🇬</code>). Leave blank to fall back to <code>config/keywords.php</code>. The visibility checkboxes above only affect codes that exist in this list.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div id="settings-save-bar" style="position: sticky; bottom: 2rem; z-index: 100; margin-top: 3rem; display: {{ $activeTab === 'coupons' ? 'none' : 'flex' }}; justify-content: flex-end;">
            <button type="submit" class="btn-save" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 4rem; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.2);">
                <i class="fas fa-save"></i> {{ $activeTab === 'countries' ? 'Save Country Registry' : 'Save All Global Metrics & Matrix' }}
            </button>
        </div>
    </form>

    {{-- COUPONS PANEL — outside the main settings form, uses its own forms for CRUD --}}
    <div id="content-coupons" class="tab-panel {{ $activeTab === 'coupons' ? 'active' : '' }}">

        @if(session('coupon_success'))
            <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#00A58B;padding:1rem 1.5rem;border-radius:12px;margin-bottom:1.5rem;font-weight:600;display:flex;align-items:center;gap:0.75rem;">
                <i class="fas fa-check-circle"></i> {{ session('coupon_success') }}
            </div>
        @endif

        {{-- Create --}}
        <div class="card-admin" style="margin-bottom:2rem;">
            <h3 style="color:#f59e0b;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                <i class="fas fa-plus-circle"></i> Create New Coupon
            </h3>
            <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf
                @if($errors->any())
                    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:1rem 1.5rem;border-radius:12px;margin-bottom:1.5rem;font-size:0.85rem;">
                        <ul style="margin:0;padding-left:1.25rem;">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block;font-size:0.75rem;color:#f59e0b;text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Coupon Code *</label>
                        <input type="text" name="code" value="{{ strtoupper(old('code')) }}" class="modal-input"
                               placeholder="e.g. WELCOME50" style="text-transform:uppercase;font-family:monospace;letter-spacing:1px;border-color:rgba(245,158,11,0.4);" required>
                    </div>
                    <div style="position:relative;">
                        <label style="display:block;font-size:0.75rem;color:var(--primary-admin);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Credits to Grant *</label>
                        <input type="number" name="credits" value="{{ old('credits', 50) }}" class="modal-input"
                               min="1" style="padding-right:4.5rem;border-color:rgba(0, 168, 230,0.3);" required>
                        <span style="position:absolute;right:1rem;top:calc(50% + 10px);transform:translateY(-50%);font-size:0.65rem;font-weight:800;color:var(--text-muted);opacity:0.5;pointer-events:none;">CRS</span>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Max Uses <span style="opacity:0.5">(blank = unlimited)</span></label>
                        <input type="number" name="max_uses" value="{{ old('max_uses') }}" class="modal-input" min="1" placeholder="e.g. 100">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Expires At <span style="opacity:0.5">(optional)</span></label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="modal-input">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;margin-bottom:1.25rem;">
                    <div>
                        <label style="display:block;font-size:0.75rem;color:#f59e0b;text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Scope *</label>
                        <select name="scope" id="couponScope" class="modal-input" required>
                            <option value="all_tools" {{ old('scope', 'all_tools') === 'all_tools' ? 'selected' : '' }}>All tools — credit wallet (CRS)</option>
                            <option value="specific_tool" {{ old('scope') === 'specific_tool' ? 'selected' : '' }}>Single tool — add to tool bonus pool only</option>
                        </select>
                    </div>
                    <div id="couponToolSlugWrap" style="{{ old('scope') === 'specific_tool' ? '' : 'display:none;' }}">
                        <label style="display:block;font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Tool slug *</label>
                        <input type="text" name="tool_slug" value="{{ old('tool_slug') }}" class="modal-input" placeholder="e.g. article_writer" style="font-family:monospace;">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
                    <div>
                        <label style="display:block;font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Description / Note</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="modal-input" placeholder="e.g. Summer promo 2026">
                    </div>
                    <div>
                        <label style="display:block;font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:0.4rem;">Assign to User <span style="opacity:0.5">(blank = public)</span></label>
                        <select name="assigned_user_id" class="modal-input">
                            <option value="">— Public Coupon —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('assigned_user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var s = document.getElementById('couponScope');
                        var w = document.getElementById('couponToolSlugWrap');
                        if (s && w) {
                            s.addEventListener('change', function() {
                                w.style.display = s.value === 'specific_tool' ? 'block' : 'none';
                            });
                        }
                    });
                </script>
                <button type="submit" class="btn-save" style="padding:0.85rem 2.5rem;display:inline-flex;align-items:center;gap:0.75rem;">
                    <i class="fas fa-tag"></i> Create Coupon
                </button>
            </form>
        </div>

        {{-- Coupon List --}}
        <div class="card-admin">
            <h3 style="color:#f59e0b;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;">
                <i class="fas fa-list"></i> All Coupons
                <span style="font-size:0.7rem;background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);border-radius:20px;padding:2px 10px;font-weight:700;">{{ $coupons->count() }}</span>
            </h3>
            @if($coupons->isEmpty())
                <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                    <i class="fas fa-tag" style="font-size:2.5rem;opacity:0.2;margin-bottom:1rem;display:block;"></i>
                    No coupons yet. Use the form above to create your first one.
                </div>
            @else
                <div style="overflow-x:auto;background:rgba(0,0,0,0.2);border:1px solid var(--horizon-border);border-radius:16px;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--horizon-border);">
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Code</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Scope</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Credits</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Used / Max</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Assigned To</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Expires</th>
                                <th style="padding:1rem 1.25rem;text-align:left;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Status</th>
                                <th style="padding:1rem 1.25rem;text-align:right;color:var(--text-muted);font-size:0.7rem;text-transform:uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                                @php
                                    $statusLabel = $coupon->status_label;
                                    $statusColor = match($statusLabel) {
                                        'Active'    => '#00A58B',
                                        'Inactive'  => '#6b7280',
                                        'Expired'   => '#ef4444',
                                        'Exhausted' => '#f59e0b',
                                        default     => '#6b7280',
                                    };
                                @endphp
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.03);">
                                    <td style="padding:1rem 1.25rem;">
                                        <div style="font-family:monospace;font-weight:800;color:var(--text-main);font-size:0.9rem;letter-spacing:1px;">{{ $coupon->code }}</div>
                                        @if($coupon->description)<div style="font-size:0.7rem;color:var(--text-muted);margin-top:2px;">{{ $coupon->description }}</div>@endif
                                    </td>
                                    <td style="padding:1rem 1.25rem;font-size:0.75rem;">
                                        @if(($coupon->scope ?? 'all_tools') === 'specific_tool')
                                            <span style="color:#a855f7;font-weight:700;">Tool</span>
                                            <div style="font-family:monospace;font-size:0.65rem;color:var(--text-muted);">{{ $coupon->tool_slug }}</div>
                                        @else
                                            <span style="color:var(--primary-admin);font-weight:700;">Wallet</span>
                                        @endif
                                    </td>
                                    <td style="padding:1rem 1.25rem;">
                                        <span style="font-weight:800;color:var(--primary-admin);font-size:1rem;">+{{ number_format($coupon->credits) }}</span>
                                        <span style="font-size:0.7rem;color:var(--text-muted);"> CRS</span>
                                    </td>
                                    <td style="padding:1rem 1.25rem;">
                                        <span style="font-weight:700;">{{ $coupon->used_count }}</span>
                                        <span style="color:var(--text-muted);"> / {{ $coupon->max_uses ?? '∞' }}</span>
                                    </td>
                                    <td style="padding:1rem 1.25rem;">
                                        @if($coupon->assignedUser)
                                            <div style="font-size:0.78rem;background:rgba(168,85,247,0.1);color:#a855f7;border:1px solid rgba(168,85,247,0.2);border-radius:8px;padding:3px 8px;display:inline-block;">
                                                <i class="fas fa-user" style="font-size:0.65rem;"></i> {{ $coupon->assignedUser->name }}
                                            </div>
                                        @else
                                            <span style="font-size:0.75rem;color:var(--text-muted);">Public</span>
                                        @endif
                                    </td>
                                    <td style="padding:1rem 1.25rem;font-size:0.8rem;">
                                        @if($coupon->expires_at)
                                            <span style="color:{{ $coupon->expires_at->isPast() ? '#ef4444' : 'var(--text-main)' }};">
                                                {{ $coupon->expires_at->format('M j, Y') }}
                                            </span>
                                        @else
                                            <span style="color:var(--text-muted);">Never</span>
                                        @endif
                                    </td>
                                    <td style="padding:1rem 1.25rem;">
                                        <span style="font-size:0.7rem;font-weight:800;text-transform:uppercase;padding:3px 10px;border-radius:6px;background:{{ $statusColor }}22;color:{{ $statusColor }};border:1px solid {{ $statusColor }}44;">{{ $statusLabel }}</span>
                                    </td>
                                    <td style="padding:1rem 1.25rem;text-align:right;">
                                        <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                                            <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST" style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}"
                                                    style="padding:0.4rem 0.7rem;border-radius:8px;border:1px solid {{ $coupon->is_active ? 'rgba(245,158,11,0.4)' : 'rgba(16,185,129,0.4)' }};background:{{ $coupon->is_active ? 'rgba(245,158,11,0.08)' : 'rgba(16,185,129,0.08)' }};color:{{ $coupon->is_active ? '#f59e0b' : '#00A58B' }};cursor:pointer;">
                                                    <i class="fas {{ $coupon->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" style="display:inline;"
                                                  onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete"
                                                    style="padding:0.4rem 0.7rem;border-radius:8px;border:1px solid rgba(239,68,68,0.3);background:rgba(239,68,68,0.06);color:#ef4444;cursor:pointer;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>{{-- /content-coupons --}}

</div>{{-- /outer wrapper --}}

<style>
    .tab-btn {
        flex: 1;
        min-width: 180px;
        padding: 0.85rem 1rem;
        border: none;
        background: none;
        color: var(--text-muted);
        font-weight: 600;
        cursor: pointer;
        border-radius: 12px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        font-family: 'Space+Grotesk', sans-serif;
        font-size: 0.9rem;
        text-decoration: none;
    }
    .tab-btn.active {
        background: var(--horizon-primary-bg);
        color: var(--primary-admin);
        box-shadow: 0 4px 15px rgba(0, 168, 230, 0.1);
    }
    .tab-btn:hover:not(.active) {
        background: var(--horizon-nav-hover);
        color: var(--text-main);
    }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; animation: fadeInSettings 0.4s ease; }
    
    @keyframes fadeInSettings {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-input {
        background: var(--vn-input-bg);
        border: 1px solid var(--vn-input-border);
        color: var(--text-main);
        padding: 0.8rem 1rem;
        border-radius: 10px;
        width: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.9rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .modal-input:focus {
        outline: none;
        border-color: var(--primary-admin);
        box-shadow: 0 0 10px rgba(0, 168, 230, 0.1);
    }
    
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .tool-row:hover { background: var(--horizon-nav-hover); }
</style>

<script>
    // ── Trial Package helpers ──────────────────────────────────────
    function setAllTrialTools(enable) {
        document.querySelectorAll('.trial-tool-checkbox').forEach(cb => {
            cb.checked = enable;
            updateTrialCard(cb);
        });
    }

    function updateTrialCard(checkbox) {
        const slug    = checkbox.dataset.slug;
        const card    = document.getElementById('trial-card-' + slug);
        const label   = card.querySelector('[data-status]') || card.querySelector('span[style*="TRIAL"], span[style*="LOCKED"]');
        const enabled = checkbox.checked;

        // Update border
        card.style.borderColor = enabled ? 'rgba(245,158,11,0.45)' : 'rgba(255,255,255,0.06)';

        // Update status label text + colour
        const statusEl = card.querySelector('.trial-status-label');
        if (statusEl) {
            statusEl.textContent  = enabled ? '● TRIAL UNLOCKED' : '● LOCKED';
            statusEl.style.color  = enabled ? '#f59e0b' : 'var(--text-muted)';
        }
    }

    // Tag status labels on load so updateTrialCard can target them
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.trial-tool-checkbox').forEach(cb => {
            const card = document.getElementById('trial-card-' + cb.dataset.slug);
            const spans = card.querySelectorAll('div[style*="font-size: 0.65rem"] span');
            if (spans.length > 0) spans[0].classList.add('trial-status-label');
        });

        // Pre-encode textareas to Base64 to bypass server ModSecurity filters seamlessly
        const form = document.querySelector('form[data-ajax-save]');
        if (form) {
            form.addEventListener('submit', (e) => {
                form.querySelectorAll('textarea').forEach(ta => {
                    if (ta.name && !ta.name.startsWith('_b64_')) {
                        let hidden = form.querySelector('input[type="hidden"][name="_b64_' + ta.name + '"]');
                        if (!hidden) {
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = '_b64_' + ta.name;
                            form.appendChild(hidden);
                        }
                        try {
                            hidden.value = btoa(unescape(encodeURIComponent(ta.value || '')));
                        } catch (err) {
                            hidden.value = ta.value;
                        }
                    }
                });
            }, true);
        }
    });
</script>
@endsection
