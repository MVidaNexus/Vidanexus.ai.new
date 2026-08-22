@extends('admin.horizon.layout')

@section('title', 'VidaNexus Control Center')

@section('styles')
<style>
    .horizon-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.25rem;
    }

    .horizon-tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    .metric-card {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        text-decoration: none;
        color: inherit;
        transition: transform 0.25s ease, border-color 0.25s ease;
    }

    .metric-card:hover {
        transform: translateY(-4px);
        border-color: var(--primary-admin);
    }

    .tool-card-link {
        text-decoration: none;
        color: inherit;
    }

    .tool-card {
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, border-color 0.25s ease;
        height: 100%;
    }

    .tool-card:hover {
        transform: translateY(-3px);
        border-color: var(--primary-admin);
    }
</style>
@endsection

@section('content')
<div class="horizon-stats-grid">
    <a href="{{ route('admin.users.index') }}" class="card-admin metric-card">
        <div style="width: 50px; height: 50px; background: var(--horizon-primary-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-admin); font-size: 1.5rem;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Total Users</div>
            <div style="font-size: 1.8rem; font-weight: 700;">{{ number_format($stats['total_users']) }}</div>
        </div>
    </a>
    <div class="card-admin" style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 50px; height: 50px; background: var(--horizon-secondary-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--secondary-admin); font-size: 1.5rem;">
            <i class="fas fa-bolt"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Total Requests</div>
            <div style="font-size: 1.8rem; font-weight: 700;">{{ number_format($stats['total_requests']) }}</div>
        </div>
    </div>
    <div class="card-admin" style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 50px; height: 50px; background: var(--horizon-success-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--horizon-success); font-size: 1.5rem;">
            <i class="fas fa-crown"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Marketplace Buyers</div>
            <div style="font-size: 1.8rem; font-weight: 700;">{{ number_format($stats['paid_users'] ?? 0) }}</div>
        </div>
    </div>
    <div class="card-admin" style="display: flex; align-items: center; gap: 1.5rem;">
        <div style="width: 50px; height: 50px; background: rgba(255, 87, 34, 0.12); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ff7043; font-size: 1.5rem;">
            <i class="fas fa-heartbeat"></i>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Failed Jobs</div>
            <div style="font-size: 1.4rem; font-weight: 700;">{{ number_format($stats['failed_jobs'] ?? 0) }}</div>
            <div style="font-size: 0.75rem; color: #ff9d80;">Payment failures (24h): {{ number_format($stats['payment_failures_24h'] ?? 0) }}</div>
        </div>
    </div>
</div>

<h2 style="font-family: 'Space+Grotesk', sans-serif; font-size: 1.4rem; margin-bottom: 1.5rem;">Platform AI Tools</h2>
<div class="horizon-tools-grid">
    @foreach($tools as $tool)
        <a href="{{ route('admin.horizon.show', $tool['slug']) }}" class="tool-card-link">
            <div class="card-admin tool-card">
                <div style="position: absolute; top: 0; right: 0; padding: 1rem; opacity: 0.1; font-size: 4rem;">
                    <i class="fas {{ $tool['icon'] }}"></i>
                </div>
                @php $isActive = App\Models\Setting::get($tool['slug'] . '_active', true); @endphp
                <div style="position: absolute; top: 1rem; right: 1rem; font-size: 0.6rem; font-weight: 800; padding: 2px 8px; border-radius: 4px; background: {{ $isActive ? 'rgba(0, 255, 170, 0.1)' : 'rgba(255, 75, 75, 0.1)' }}; color: {{ $isActive ? 'var(--horizon-success)' : '#ff4b4b' }}; border: 1px solid {{ $isActive ? 'rgba(0, 255, 170, 0.2)' : 'rgba(255, 75, 75, 0.2)' }};">
                    {{ $isActive ? 'ACTIVE' : 'MAINTENANCE' }}
                </div>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--horizon-icon-bg); display: flex; align-items: center; justify-content: center; color: {{ $tool['color'] }}">
                        <i class="fas {{ $tool['icon'] }}"></i>
                    </div>
                    <h3 style="margin: 0; font-size: 1.1rem;">{{ $tool['name'] }}</h3>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div>
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Today</div>
                        <div style="font-size: 1rem; font-weight: 700; color: var(--primary-admin);">{{ number_format($tool['today_usage']) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Lifetime</div>
                        <div style="font-size: 1rem; font-weight: 700;">{{ number_format($tool['usage_count']) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; color: #ffaa00;">Sold</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #ffaa00;">{{ number_format($tool['purchase_count']) }}</div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-cog"></i> Configure Tool</span>
                    <i class="fas fa-chevron-right" style="font-size: 0.8rem; color: var(--primary-admin);"></i>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
