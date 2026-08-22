@extends('admin.horizon.layout')

@section('title', "Control: " . $tool['name'])

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Configuration Panel -->
    <div class="card-admin">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}; font-size: 1.2rem;">
                <i class="fas {{ $tool['icon'] }}"></i>
            </div>
            <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">Core Intelligence Center</h2>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--horizon-primary-bg); display: flex; align-items: center; justify-content: center; color: var(--primary-admin);">
                    <i class="fas fa-bolt"></i>
                </div>
                <div>
                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Today's Activity</div>
                    <div style="font-size: 1.25rem; font-weight: 700;">{{ number_format($stats['today_usage'] ?? 0) }}</div>
                </div>
            </div>
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--horizon-border); border-radius: 16px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--horizon-secondary-bg); display: flex; align-items: center; justify-content: center; color: var(--secondary-admin);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Lifetime Usage</div>
                    <div style="font-size: 1.25rem; font-weight: 700;">{{ number_format($stats['lifetime_usage'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST" data-ajax-save>
            @csrf
            
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">System Prompt / Base Instructions</label>
                <textarea name="prompt" rows="15" class="mono" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 12px; color: #fff; padding: 1.5rem; line-height: 1.6; font-size: 0.95rem; outline: none; focus: border-color: var(--primary-admin);">{{ $settings['prompt'] }}</textarea>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                    <i class="fas fa-info-circle"></i> Use keywords like <code>[Keyword]</code> or <code>[Context]</code> if the tool supports dynamic injection.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">AI Provider</label>
                    <select name="provider" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 12px; color: #fff; padding: 1rem; cursor: pointer; outline: none;">
                        <option value="openai" {{ $settings['provider'] == 'openai' ? 'selected' : '' }}>OpenAI (Highly Precise)</option>
                        <option value="google" {{ $settings['provider'] == 'google' ? 'selected' : '' }}>Google Gemini (Multi-modal)</option>
                        <option value="openrouter" {{ $settings['provider'] == 'openrouter' ? 'selected' : '' }}>OpenRouter (Failover/Cheap)</option>
                        <option value="anthropic" {{ $settings['provider'] == 'anthropic' ? 'selected' : '' }}>Anthropic Claude</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">Primary Model (Override)</label>
                    <input type="text" name="model" value="{{ $settings['model'] }}" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 12px; color: #fff; padding: 1rem; outline: none;" placeholder="gpt-4o-mini">
                </div>
            </div>

            @if($tool['slug'] === 'competitor-x-ray')
            <div style="margin-bottom: 2rem; background: rgba(0, 168, 230, 0.05); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(0, 168, 230, 0.2);">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">SerpAPI Key (Secure Storage)</label>
                <div style="display: flex; gap: 1rem;">
                    <input type="password" name="serpapi_key" value="{{ $settings['serpapi_key'] ?? '' }}" style="flex: 1; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 12px; color: #fff; padding: 1rem; outline: none;" placeholder="Enter SerpAPI Key...">
                    <div style="background: rgba(255,255,255,0.05); padding: 0.8rem 1.2rem; border-radius: 12px; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fas fa-lock text-cyan-400"></i> Encrypted
                    </div>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">
                    Required for the <strong>Competitor Gap Scout</strong> functionality to fetch real-time search data.
                </p>
            </div>
            @endif

            <!-- Marketplace Pricing -->
            @php
                $unlockPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$tool['slug']}", $tool['unlock_price'] ?? 99);
                $creditCost = (int) \App\Models\Setting::get("tool_credit_cost_{$tool['slug']}", $tool['credit_cost_per_action'] ?? 1);
                $bonusCredits = (int) \App\Models\Setting::get("tool_bonus_credits_{$tool['slug']}", $tool['initial_bonus_credits'] ?? 10);
            @endphp
            <div style="margin-bottom: 2rem; background: linear-gradient(135deg, rgba(0, 168, 230, 0.05), rgba(191, 0, 255, 0.05)); padding: 1.5rem; border-radius: 16px; border: 1px solid rgba(0, 168, 230, 0.2);">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.25rem;">
                    <i class="fas fa-store" style="color: var(--primary-admin); margin-right: 0.5rem;"></i> Marketplace Pricing
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.5rem;">Monthly Price (EGP)</label>
                        <input type="number" name="unlock_price" value="{{ $unlockPrice }}" min="0" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 10px; color: #fff; padding: 0.75rem; outline: none; font-size: 1rem; font-weight: 700; text-align: center;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.5rem;">Cost / Action (CRS)</label>
                        <input type="number" name="credit_cost" value="{{ $creditCost }}" min="0" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 10px; color: #fff; padding: 0.75rem; outline: none; font-size: 1rem; font-weight: 700; text-align: center;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.5rem;">Bonus on Subscription (CRS)</label>
                        <input type="number" name="bonus_credits" value="{{ $bonusCredits }}" min="0" style="width: 100%; background: #0a0f19; border: 1px solid var(--horizon-border); border-radius: 10px; color: #fff; padding: 0.75rem; outline: none; font-size: 1rem; font-weight: 700; text-align: center;">
                    </div>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem; margin-bottom: 0;">
                    <i class="fas fa-info-circle"></i> Users pay the <strong>Monthly Price</strong> to gain 30 days of access. The system automatically renews the subscription by deducting credits from their wallet. Each action costs <strong>Cost/Action</strong> credits. On each renewal/purchase, <strong>Bonus</strong> credits are added to their tool-specific balance.
                </p>
            </div>

            <div style="margin-bottom: 2.5rem; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 12px; border: 1px dashed var(--horizon-border);">
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-main);">Tool Global Status</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Enable or disable this tool site-wide.</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span id="statusLabel" style="font-size: 0.8rem; font-weight: 700; color: {{ ($settings['is_active'] ?? true) ? 'var(--horizon-success)' : '#ff4b4b' }}">
                            {{ ($settings['is_active'] ?? true) ? 'ACTIVE CONTROL' : 'MAINTENANCE MODE' }}
                        </span>
                        <input type="hidden" name="is_active" value="{{ ($settings['is_active'] ?? true) ? '1' : '0' }}" id="statusInput">
                        <div class="status-toggle" onclick="toggleToolStatus()" style="width: 50px; height: 26px; background: {{ ($settings['is_active'] ?? true) ? 'var(--horizon-success)' : '#444' }}; border-radius: 20px; position: relative; transition: all 0.3s ease;">
                            <div style="width: 20px; height: 20px; background: #fff; border-radius: 50%; position: absolute; top: 3px; left: {{ ($settings['is_active'] ?? true) ? '27px' : '3px' }}; transition: all 0.3s ease;"></div>
                        </div>
                    </div>
                </label>
            </div>

            <script>
                function toggleToolStatus() {
                    const input = document.getElementById('statusInput');
                    const label = document.getElementById('statusLabel');
                    const toggle = document.querySelector('.status-toggle');
                    const orb = toggle.querySelector('div');
                    
                    if (input.value == '1') {
                        input.value = '0';
                        label.innerText = 'MAINTENANCE MODE';
                        label.style.color = '#ff4b4b';
                        toggle.style.background = '#444';
                        orb.style.left = '3px';
                    } else {
                        input.value = '1';
                        label.innerText = 'ACTIVE CONTROL';
                        label.style.color = 'var(--horizon-success)';
                        toggle.style.background = 'var(--horizon-success)';
                        orb.style.left = '27px';
                    }
                }
            </script>

            <button type="submit" class="btn-save" style="width: 100%;">
                <i class="fas fa-save"></i> Synchronize Tool Brain
            </button>
        </form>
    </div>

    <!-- Monitoring Panel -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        <div class="card-admin">
            <h3 style="margin: 0 0 1.5rem; font-size: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-users" style="color: var(--primary-admin);"></i> Top Tool Subscribers
            </h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
                        <th style="padding: 1rem 0; border-bottom: 1px solid var(--horizon-border);">User</th>
                        <th style="padding: 1rem 0; border-bottom: 1px solid var(--horizon-border); text-align: right;">Uses</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.9rem;">
                    @forelse($subscribers as $sub)
                        <tr>
                            <td style="padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.02);">
                                <div style="font-weight: 600;">{{ $sub->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    @if($sub->wallet)
                                        {{ number_format($sub->wallet->balance_credits, 1) }} CRS
                                    @else
                                        Marketplace User
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.02); text-align: right; font-family: monospace;">
                                {{ number_format($sub->ai_usages_count) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="padding: 2rem 0; text-align: center; color: var(--text-muted);">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($subscribers->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $subscribers->appends(request()->query())->links('admin.horizon.partials._pagination') }}
                </div>
            @endif
        </div>

        <div class="card-admin" style="background: linear-gradient(135deg, rgba(191, 0, 255, 0.05), rgba(0, 168, 230, 0.05)); border-color: var(--primary-admin);">
            <h3 style="margin: 0 0 1rem; font-size: 1rem;"><i class="fas fa-shield-halved" style="color: var(--secondary-admin);"></i> Admin Note</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                Modifying the system prompt for <strong>{{ $tool['name'] }}</strong> will impact the behavior of the AI engine immediately for all users. Make sure to test your prompting logic in the site's front-end after saving.
            </p>
        </div>
    </div>
</div>
@endsection
