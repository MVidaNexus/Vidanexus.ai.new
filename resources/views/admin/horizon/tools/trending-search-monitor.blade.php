@extends('admin.horizon.layout')

@section('title', "Control: " . $tool['name'])

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Configuration Panel -->
    <div class="card-admin">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--horizon-border);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] ?? 'var(--primary-cyan)' }}; font-size: 1.2rem;">
                <i class="fas {{ $tool['icon'] ?? 'fa-chart-line' }}"></i>
            </div>
            <div>
                <h2 style="margin: 0; font-family: 'Space+Grotesk', sans-serif;">Viral Search Monitor Control</h2>
                <p style="margin: 0; font-size: 0.75rem; color: var(--text-muted);">Configure available countries and the default search trend feed (Daily vs Realtime).</p>
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

            {{-- Feed Type --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-satellite-dish" style="color: var(--primary-admin);"></i> Trend Feed Type
                </label>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @php $feedType = $settings['feed_type'] ?? 'daily'; @endphp
                    @php
                        $feedOptions = [
                            'daily' => 'Daily Trends (All Countries)',
                            'realtime' => 'Realtime Trends (Supported Countries Only)'
                        ];
                    @endphp
                    @foreach($feedOptions as $val => $label)
                        <label style="display: flex; align-items: center; gap: 0.5rem; background: {{ $feedType === $val ? 'var(--horizon-primary-bg)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ $feedType === $val ? 'var(--primary-admin)' : 'var(--horizon-border)' }}; border-radius: 12px; padding: 0.75rem 1.25rem; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="feed_type" value="{{ $val }}" {{ $feedType === $val ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                            <span style="font-size: 0.85rem; font-weight: 600; color: {{ $feedType === $val ? 'var(--primary-admin)' : 'var(--text-main)' }};">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p style="font-size: 0.75rem; color: var(--horizon-warning); margin-top: 0.5rem;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Realtime Trends API does not natively support all regions (e.g., Egypt). If a user selects an unsupported region while Realtime is forced, Google will return zero results. 
                </p>
            </div>

            {{-- Realtime Category --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-filter" style="color: var(--primary-admin);"></i> Realtime Category (Applies only if Realtime is active)
                </label>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    @php $category = $settings['category'] ?? 'all'; @endphp
                    @foreach([
                        'all' => 'All Categories', 
                        'b' => 'Business', 
                        'e' => 'Entertainment', 
                        'm' => 'Health / Medical', 
                        't' => 'Sci/Tech', 
                        's' => 'Sports'
                        ] as $val => $label)
                        <label style="display: flex; align-items: center; gap: 0.5rem; background: {{ $category === $val ? 'var(--horizon-primary-bg)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ $category === $val ? 'var(--primary-admin)' : 'var(--horizon-border)' }}; border-radius: 12px; padding: 0.75rem 1.25rem; cursor: pointer; transition: all 0.2s;">
                            <input type="radio" name="category" value="{{ $val }}" {{ $category === $val ? 'checked' : '' }} style="accent-color: var(--primary-admin);">
                            <span style="font-size: 0.85rem; font-weight: 600; color: {{ $category === $val ? 'var(--primary-admin)' : 'var(--text-main)' }};">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Active Countries --}}
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1rem;">
                    <i class="fas fa-globe" style="color: var(--primary-admin);"></i> Available Countries Dropdown
                </label>
                @php
                    // Dynamically build the default list from config, plus US and Poland if not present
                    $defaultFallbackMap = config('keywords.countries', []);
                    $defaultFallbackMap['US'] = ['name' => 'United States', 'flag' => '🇺🇸'];
                    $defaultFallbackMap['PL'] = ['name' => 'Poland', 'flag' => '🇵🇱'];
                    
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
                        @php
                            $isActive = in_array($code, $activeCountries);
                            $bg = $isActive ? 'rgba(14, 165, 233,0.1)' : 'rgba(255,255,255,0.02)';
                            $border = $isActive ? 'rgba(14, 165, 233,0.3)' : 'var(--horizon-border)';
                        @endphp
                        <label style="display: flex; align-items: center; gap: 0.4rem; background: {{ $bg }}; border: 1px solid {{ $border }}; border-radius: 10px; padding: 0.5rem 0.85rem; cursor: pointer; transition: all 0.2s;">
                            <input type="checkbox" name="countries[]" value="{{ $code }}" {{ $isActive ? 'checked' : '' }} style="accent-color: var(--primary-admin); width: 14px; height: 14px;">
                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $name }}</span>
                        </label>
                    @endforeach
                </div>
                
                {{-- Manage Available Countries --}}
                <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.01); border: 1px dashed var(--horizon-border); border-radius: 12px;">
                    <label style="display: block; font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Manage Database (One per line: CODE:Name)</label>
                    <textarea name="available_countries" style="width: 100%; height: 120px; background: var(--vn-input-bg); border: 1px solid var(--vn-input-border); border-radius: 8px; color: var(--text-main); font-family: monospace; font-size: 0.8rem; padding: 0.75rem; resize: vertical; outline: none;">{{ $availableCountriesText }}</textarea>
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                    <i class="fas fa-info-circle"></i> Only checked countries will be shown in the tool's frontend dropdown for users to select.
                </p>
            </div>

            {{-- Hidden fields for the generic form handler --}}
            <input type="hidden" name="prompt" value="">
            <input type="hidden" name="provider" value="{{ $settings['provider'] ?? 'rss' }}">
            <input type="hidden" name="model" value="{{ $settings['model'] ?? 'google-trends' }}">

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
                <i class="fas fa-save"></i> Save Viral Search Monitor Configuration
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

            @if(isset($subscribers) && $subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator && $subscribers->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $subscribers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <div class="card-admin" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.05), rgba(139, 92, 246, 0.05)); border-color: var(--primary-admin);">
            <h3 style="margin: 0 0 1rem; font-size: 1rem;"><i class="fas fa-rss" style="color: var(--primary-cyan);"></i> Data Source Details</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                <strong>Viral Search Monitor</strong> extracts data directly from the official Google Trends feeds.<br><br>
                • <strong>Daily Trends:</strong> Available for almost all countries globally. Best for Egypt, KSA, UAE, etc.<br>
                • <strong>Realtime Trends:</strong> Filters by the past 24 hours and allows categorization, but is strictly limited to supported regions (like US, UK, Australia, India).
            </p>
        </div>
    </div>
</div>
@endsection
