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
            <div>
                <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">News Monitor Control Center</h2>
                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">This tool uses RSS feeds, not AI. Configure countries, topics, and time window below.</p>
            </div>
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

        <form action="{{ route('admin.horizon.update', $tool['slug']) }}" method="POST">
            @csrf

            {{-- Time Duration --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-clock" style="color: var(--primary-admin);"></i> News Time Window
                </label>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @php $timeWindow = $settings['time_window'] ?? '12h'; @endphp
                    @foreach(['1h' => 'Last Hour', '3h' => 'Last 3 Hours', '6h' => 'Last 6 Hours', '12h' => 'Last 12 Hours', '24h' => 'Last 24 Hours', '48h' => 'Last 48 Hours'] as $val => $label)
                        <label style="display: flex; align-items: center; gap: 0.5rem; background: {{ $timeWindow === $val ? 'var(--horizon-primary-bg)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ $timeWindow === $val ? 'var(--primary-admin)' : 'var(--horizon-border)' }}; border-radius: 12px; padding: 0.75rem 1.25rem; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="time_window" value="{{ $val }}" {{ $timeWindow === $val ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                            <span style="font-size: 0.85rem; font-weight: 600; color: {{ $timeWindow === $val ? 'var(--primary-admin)' : 'var(--text-main)' }};">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <i class="fas fa-info-circle"></i> Controls the <code>when:Xh</code> parameter in Google News search queries. Shorter = fresher results, longer = more results.
                </p>
            </div>

            {{-- Active Countries --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-globe" style="color: var(--primary-admin);"></i> Active Countries
                </label>
                @php
                    // Dynamically build the default list from config
                    $defaultFallbackMap = config('keywords.countries', []);
                    
                    $fallbackText = [];
                    foreach($defaultFallbackMap as $code => $data) {
                        $fallbackText[] = $code . ':' . $data['name'] . ' ' . $data['flag'];
                    }
                    
                    $availableCountriesText = $settings['available_countries'] ?? implode("\n", $fallbackText);
                    $defaultCountries = [];
                    foreach(explode("\n", $availableCountriesText) as $line) {
                        $parts = explode(':', trim($line));
                        if(count($parts) === 2) {
                            $code = trim($parts[0]);
                            $name = trim($parts[1]);
                            $defaultCountries[$code] = $name;
                        }
                    }
                    $activeCountries = json_decode($settings['countries'] ?? 'null', true) ?: array_keys($defaultCountries);
                @endphp
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    @foreach($defaultCountries as $code => $name)
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: {{ in_array($code, $activeCountries) ? 'rgba(14, 165, 233,0.1)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ in_array($code, $activeCountries) ? 'rgba(14, 165, 233,0.3)' : 'var(--horizon-border)' }}; border-radius: 10px; padding: 0.5rem 0.85rem; cursor: pointer; transition: all 0.2s;">
                            <input type="checkbox" name="countries[]" value="{{ $code }}" {{ in_array($code, $activeCountries) ? 'checked' : '' }} style="accent-color: var(--primary-admin); width: 14px; height: 14px;">
                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
                
                {{-- Manage Available Countries --}}
                <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.01); border: 1px dashed var(--horizon-border); border-radius: 12px;">
                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Manage Available Countries (One per line: CODE:Name)</label>
                    <textarea name="available_countries" style="width: 100%; height: 100px; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 8px; color: var(--text-main); font-family: monospace; font-size: 0.8rem; padding: 0.75rem; resize: vertical; outline: none;">{{ $availableCountriesText }}</textarea>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <i class="fas fa-info-circle"></i> Only checked countries will be visible to users in the country selector dropdown.
                </p>
            </div>

            {{-- Active Topics --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-tags" style="color: var(--primary-admin);"></i> Active Topics / Sections
                </label>
                @php
                    $availableTopicsText = $settings['available_topics'] ?? "GENERAL:Top Stories\nWORLD:World";
                    $defaultTopics = [];
                    foreach(explode("\n", $availableTopicsText) as $line) {
                        $parts = explode(':', trim($line));
                        if(count($parts) === 2) $defaultTopics[trim($parts[0])] = trim($parts[1]);
                    }
                    $activeTopics = json_decode($settings['topics'] ?? '[]', true) ?: array_keys($defaultTopics);
                @endphp
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    @foreach($defaultTopics as $tKey => $tName)
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: {{ in_array($tKey, $activeTopics) ? 'rgba(191,0,255,0.1)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ in_array($tKey, $activeTopics) ? 'rgba(191,0,255,0.3)' : 'var(--horizon-border)' }}; border-radius: 10px; padding: 0.5rem 0.85rem; cursor: pointer; transition: all 0.2s;">
                            <input type="checkbox" name="topics[]" value="{{ $tKey }}" {{ in_array($tKey, $activeTopics) ? 'checked' : '' }} style="accent-color: #bf00ff; width: 14px; height: 14px;">
                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $tName }}</span>
                        </label>
                    @endforeach
                </div>
                
                {{-- Manage Available Topics --}}
                <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.01); border: 1px dashed var(--horizon-border); border-radius: 12px;">
                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Manage Available Topics (One per line: CODE:Name)</label>
                    <textarea name="available_topics" style="width: 100%; height: 100px; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 8px; color: var(--text-main); font-family: monospace; font-size: 0.8rem; padding: 0.75rem; resize: vertical; outline: none;">{{ $availableTopicsText }}</textarea>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <i class="fas fa-info-circle"></i> Only checked topics will appear in the topics bar for users.
                </p>
            </div>

            {{-- Hidden fields for the generic form handler --}}
            <input type="hidden" name="prompt" value="">
            <input type="hidden" name="provider" value="{{ $settings['provider'] }}">
            <input type="hidden" name="model" value="{{ $settings['model'] }}">

            {{-- Tool Status --}}
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
                        <input type="hidden" name="is_active" value="0">
                        <label class="vn-switch">
                            <input type="checkbox" name="is_active" value="1" {{ ($settings['is_active'] ?? true) ? 'checked' : '' }} onchange="updateStatusLabel(this)">
                            <span class="vn-slider"></span>
                        </label>
                    </div>
                </label>
            </div>

            <script>
                function updateStatusLabel(checkbox) {
                    const label = document.getElementById('statusLabel');
                    if (checkbox.checked) {
                        label.innerText = 'ACTIVE CONTROL';
                        label.style.color = 'var(--horizon-success)';
                    } else {
                        label.innerText = 'MAINTENANCE MODE';
                        label.style.color = '#ff4b4b';
                    }
                }
            </script>

            <button type="submit" class="btn-save" style="width: 100%;">
                <i class="fas fa-save"></i> Save News Monitor Configuration
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
                    @forelse($subscribers ?? [] as $sub)
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
                    {{ $subscribers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <div class="card-admin" style="background: linear-gradient(135deg, rgba(0, 200, 150, 0.05), rgba(14, 165, 233, 0.05)); border-color: var(--primary-admin);">
            <h3 style="margin: 0 0 1rem; font-size: 1rem;"><i class="fas fa-rss" style="color: #00c896;"></i> How This Tool Works</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                <strong>{{ $tool['name'] }}</strong> uses Google News RSS feeds — not AI. It fetches live, real-time news from Google by combining the <code>gl=</code> (country) and <code>ceid=</code> (locale) parameters with a time filter (<code>when:Xh</code>). No AI credits are consumed for content generation — only 1 credit per fetch operation for rate limiting.
            </p>
        </div>
    </div>
</div>
@endsection
