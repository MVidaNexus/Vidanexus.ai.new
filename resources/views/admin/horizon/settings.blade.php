@extends('admin.horizon.layout')

@section('title', 'System Settings Matrix')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <form action="{{ route('admin.horizon.settings.update') }}" method="POST">
        @csrf
        
        <!-- Tab Navigation -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 2rem; background: var(--horizon-card); padding: 0.5rem; border-radius: 16px; border: 1px solid var(--horizon-border);">
            <button type="button" onclick="switchTab('availability')" id="tab-availability" class="tab-btn active">
                <i class="fas fa-toggle-on"></i> Tool Availability
            </button>
            <button type="button" onclick="switchTab('welcome')" id="tab-welcome" class="tab-btn">
                <i class="fas fa-store"></i> Marketplace
            </button>
            <button type="button" onclick="switchTab('packages')" id="tab-packages" class="tab-btn">
                <i class="fas fa-box"></i> Credit Packages
            </button>
            <button type="button" onclick="switchTab('smtp')" id="tab-smtp" class="tab-btn">
                <i class="fas fa-envelope-open-text"></i> Email Setup (SMTP)
            </button>
            <button type="button" onclick="switchTab('scripts')" id="tab-scripts" class="tab-btn">
                <i class="fas fa-code"></i> Global Scripts
            </button>
            <button type="button" onclick="switchTab('infrastructure')" id="tab-infrastructure" class="tab-btn">
                <i class="fas fa-server"></i> Infrastructure
            </button>
            <button type="button" onclick="switchTab('ledger')" id="tab-ledger" class="tab-btn">
                <i class="fas fa-coins"></i> Transaction Ledger
            </button>
            <button type="button" onclick="switchTab('command')" id="tab-command" class="tab-btn">
                <i class="fas fa-terminal"></i> Command Center
            </button>
            <button type="button" onclick="switchTab('markdown')" id="tab-markdown" class="tab-btn">
                <i class="fas fa-robot"></i> AI Crawler SEO
            </button>
        </div>


        <!-- 2. Tool Availability -->
        <div id="content-availability" class="tab-panel active">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                    <div>
                        <h3 style="color: #10b981; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-toggle-on"></i> Tool Availability Control
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">
                            Master switch for each tool. <strong style="color: #10b981;">ON</strong> = Live for users. <strong style="color: #ef4444;">OFF</strong> = Coming Soon.
                        </p>
                    </div>
                    <a href="/" target="_blank" class="vn-btn vn-btn-outline" style="padding: 0.6rem 1.25rem; font-size: 0.85rem; border-color: rgba(14, 165, 233, 0.3);">
                        <i class="fas fa-external-link-alt mr-2"></i> View Live Homepage
                    </a>
                </div>
                
                <div style="background: var(--horizon-primary-bg); border: 1px dashed rgba(14, 165, 233, 0.3); padding: 0.75rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.75rem; color: var(--primary-admin);">
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
                                    <div style="font-size: 0.65rem; color: {{ $isAvailable ? '#10b981' : '#ef4444' }}; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
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




        <div id="content-welcome" class="tab-panel">
            <div class="card-admin" style="margin-bottom: 2rem;">
                <h3 style="color: var(--primary-cyan); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-user-plus"></i> Global Registration Welcome Credits
                </h3>
                <div style="background: var(--horizon-nav-hover); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--horizon-border); display: flex; align-items: center; gap: 2rem;">
                    <div style="flex: 1;">
                        <label style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-size: 0.85rem;">Credits for New Users:</label>
                        <p style="color: var(--text-muted); font-size: 0.75rem; margin: 0;">Amount of Credits granted immediately upon registration. Set to 0 to disable free credits.</p>
                    </div>
                    <div style="width: 200px; position: relative;">
                        <input type="number" name="plan_credits_beginner" value="{{ $settings['plan_credits_beginner'] ?? 0 }}" class="modal-input" step="0.01" style="font-size: 1.2rem; font-weight: 700; text-align: center; border-color: var(--primary-cyan); padding-right: 4.5rem;">
                        <span style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.75rem; font-weight: 800; color: var(--primary-cyan); opacity: 0.5; pointer-events: none;">Credits</span>
                    </div>
                </div>
            </div>

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
                            $bonusKey = "tool_bonus_credits_{$tool['slug']}";
                            
                            $currentPrice = $settings[$priceKey] ?? ($tool['unlock_price'] ?? 99);
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
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.4rem; font-weight: 700;">Activation Price / Month</label>
                                    <div style="position: relative;">
                                        <input type="number" name="{{ $priceKey }}" value="{{ $currentPrice }}" class="modal-input" style="padding: 0.5rem 2.8rem 0.5rem 0.75rem; text-align: center;">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.6rem; font-weight: 800; color: var(--text-muted); opacity: 0.4; pointer-events: none;">EGP</span>
                                    </div>
                                </div>
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.6rem; color: #a855f7; text-transform: uppercase; margin-bottom: 0.4rem; font-weight: 700;">Bonus Credits</label>
                                    <div style="position: relative;">
                                        <input type="number" name="{{ $bonusKey }}" value="{{ $currentBonus }}" class="modal-input" style="padding: 0.5rem 3.5rem 0.5rem 0.75rem; text-align: center; border-color: rgba(168, 85, 247, 0.3);">
                                        <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.6rem; font-weight: 800; color: #a855f7; opacity: 0.4; pointer-events: none;">Credits</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- 4. Credit Packages -->
        <div id="content-packages" class="tab-panel">
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
                        'pro' => [ 'name' => 'Agency Pro', 'credits' => '2,500', 'price' => '650', 'desc' => 'High-volume SEO & Content.', 'icon' => 'fa-bolt-lightning', 'color' => 'var(--neon-purple)' ],
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
                                    <input type="number" name="packages[{{ $pkgKey }}][discount]" value="{{ $pkg['discount'] ?? 0 }}" class="modal-input" placeholder="e.g. 20" min="0" max="100" style="border-color: rgba(14, 165, 233, 0.3);">
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
        <div id="content-smtp" class="tab-panel">
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
                                    <i class="fas fa-shield-alt opacity-50"></i> Encryption
                                </label>
                                <select name="MAIL_ENCRYPTION" class="modal-input">
                                    <option value="null" {{ env('MAIL_ENCRYPTION') == null ? 'selected' : '' }}>None</option>
                                    <option value="tls" {{ env('MAIL_ENCRYPTION') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ env('MAIL_ENCRYPTION') == 'ssl' ? 'selected' : '' }}>SSL</option>
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
        <div id="content-scripts" class="tab-panel">
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
        <div id="content-infrastructure" class="tab-panel">
            <div class="card-admin">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-cyan); margin: 0; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-layer-group"></i> Backend Infrastructure
                    </h3>
                    <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">STABLE</span>
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
                                <span style="color: #10b981; font-weight: 700;">Redis (Horizon)</span>
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
                            <span style="font-size: 0.7rem; color: var(--primary-admin); background: rgba(14, 165, 233, 0.1); padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid rgba(14, 165, 233, 0.2); font-weight: 800;">LOAD BALANCING ACTIVE</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Transaction Ledger -->
        <div id="content-ledger" class="tab-panel">
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
                                        <span style="padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: {{ $tx->type == 'deposit' || $tx->type == 'refund' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $tx->type == 'deposit' || $tx->type == 'refund' ? '#10b981' : '#ef4444' }}; border: 1px solid {{ $tx->type == 'deposit' || $tx->type == 'refund' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                                            {{ $tx->type }}
                                        </span>
                                    </td>
                                    <td style="padding: 1.25rem; font-family: 'Space Grotesk'; font-weight: 700; color: var(--text-main); font-size: 1rem;">
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
        <div id="content-command" class="tab-panel">
            <div class="card-admin" style="text-align: center; padding: 4rem 2rem;">
                <div style="width: 80px; height: 80px; background: rgba(14, 165, 233, 0.1); border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; border: 1px solid rgba(14, 165, 233, 0.2);">
                    <i class="fas fa-terminal" style="font-size: 2.5rem; color: var(--primary-admin);"></i>
                </div>
                <h3 style="color: var(--text-main); font-size: 1.75rem; margin-bottom: 1rem; font-family: 'Space Grotesk', sans-serif;">Infrastructure Command Center</h3>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto 3rem; line-height: 1.6;">
                    Direct access to core system operations, health monitoring, and administrative session management.
                </p>

                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center;">
                    <a href="{{ route('admin.horizon.index') }}" class="btn-save" style="text-decoration: none; padding: 1.25rem 3rem; background: linear-gradient(135deg, var(--secondary-admin), var(--primary-admin)); color: #000; display: flex; align-items: center; gap: 0.75rem; border: none; box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2);">
                        <i class="fas fa-brain"></i> AI Intelligence Center
                    </a>
                    <a href="/up" target="_blank" class="btn-save" style="text-decoration: none; padding: 1.25rem 3rem; background: rgba(255,255,255,0.05); color: var(--text-main); border: 1px solid var(--horizon-border); display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-heartbeat" style="color: #10b981;"></i> System Pulse (UP)
                    </a>
                    <button type="button" onclick="let f=document.createElement('form');f.method='POST';f.action='/logout';let t=document.createElement('input');t.type='hidden';t.name='_token';t.value='{{ csrf_token() }}';f.appendChild(t);document.body.appendChild(f);f.submit();" class="btn-save" style="padding: 1.25rem 3rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                        <i class="fas fa-power-off"></i> Logout Session
                    </button>
                </div>
            </div>
        </div>

        <!-- 10. AI Crawler Markdown SEO -->
        <div id="content-markdown" class="tab-panel">
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
                            <i class="fas fa-broom" style="color: #10b981;"></i> Noise Reduction Selectors
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

                <div style="margin-top: 2rem; padding: 1.25rem; background: rgba(14, 165, 233, 0.05); border: 1px dashed rgba(14, 165, 233, 0.2); border-radius: 12px; display: flex; align-items: start; gap: 1rem;">
                    <i class="fas fa-info-circle" style="color: var(--primary-admin); margin-top: 0.25rem;"></i>
                    <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.6;">
                        <strong>SEO Strategy Tip:</strong> AI crawlers like GPTBot prefer clean text data. By serving Markdown, you improve the accuracy of model-based indexing for your tools and content. You can manually verify the output by adding <code>.md</code> to any public URL.
                    </div>
                </div>
            </div>
        </div>

        <div style="position: sticky; bottom: 2rem; z-index: 100; margin-top: 3rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-save" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 4rem; box-shadow: 0 10px 30px rgba(14, 165, 233, 0.2);">
                <i class="fas fa-save"></i> Save All Global Metrics & Matrix
            </button>
        </div>
    </form>
</div>

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
    }
    .tab-btn.active {
        background: var(--horizon-primary-bg);
        color: var(--primary-admin);
        box-shadow: 0 4px 15px rgba(14, 165, 233, 0.1);
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
        box-shadow: 0 0 10px rgba(14, 165, 233, 0.1);
    }
    
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .tool-row:hover { background: var(--horizon-nav-hover); }
</style>

<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        document.getElementById('content-' + tabId).classList.add('active');
        
        // Store tab in localStorage to persist across saves
        localStorage.setItem('active_settings_tab', tabId);
    }

    // Auto-restore tab from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('active_settings_tab');
        if (savedTab && document.getElementById('tab-' + savedTab)) {
            switchTab(savedTab);
        }
    });
</script>
@endsection
