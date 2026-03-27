@extends('trendingsearchmonitor::layouts.master')

@section('title', 'Viral Search Monitor')

@push('styles')
<style>
    .pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #0ea5e9;
        border-radius: 50%;
        display: inline-block;
        position: relative;
    }
    .pulse-dot::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background-color: inherit;
        border-radius: inherit;
        animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(.33); opacity: 0.8; }
        80%, 100% { transform: scale(3); opacity: 0; }
    }

    .trend-card {
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .trend-card:hover {
        transform: translateY(-5px) scale(1.01);
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(14, 165, 233, 0.3);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }

    .rank-badge {
        width: 32px;
        height: 32px;
        display: flex;
        items-center: center;
        justify-content: center;
        border-radius: 10px;
        font-weight: 900;
        font-size: 14px;
    }
    .rank-1 { background: linear-gradient(135deg, #ffd700, #ff8c00); color: #000; box-shadow: 0 0 15px rgba(255, 215, 0, 0.3); }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0, #808080); color: #000; }
    .rank-3 { background: linear-gradient(135deg, #cd7f32, #a0522d); color: #000; }
    .rank-default { background: rgba(255, 255, 255, 0.05); color: #a0a0a0; }

    .country-btn {
        transition: all 0.3s ease;
    }
    .country-btn.active {
        background: rgba(14, 165, 233, 0.1);
        border-color: rgba(14, 165, 233, 0.4);
        color: #0ea5e9;
    }

    .loading-skeleton {
        background: linear-gradient(90deg, rgba(255,255,255,0.03) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.03) 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
    }
    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    {{-- Header Card --}}
    <div class="glass-card p-8 border-primary-cyan/10 relative overflow-hidden group">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-cyan/5 blur-3xl rounded-full -mr-32 -mt-32"></div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-cyan to-primary-purple p-[1px]">
                        <div class="w-full h-full rounded-2xl bg-[#0d0e12] flex items-center justify-center">
                            <i class="fas fa-chart-line text-xl text-primary-cyan"></i>
                        </div>
                    </div>
                    <h2 class="text-3xl font-black text-white tracking-tight">Viral <span class="text-primary-cyan">Search Monitor</span></h2>
                </div>
                <p class="text-gray-400 font-medium">Explore what millions are searching for in real-time.</p>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                    <span class="pulse-dot"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-primary-cyan">Live Sync</span>
                </div>
                <button onclick="refreshTrends(true)" class="vn-btn vn-btn-primary px-6 py-2.5 rounded-xl text-sm flex items-center gap-2 shadow-lg shadow-primary-cyan/10">
                    <i class="fas fa-sync-alt" id="refresh-icon"></i>
                    Refresh Now
                </button>
            </div>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'trending-search-monitor'])

    {{-- Country Selector --}}
    <div class="glass-card p-6">
        <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-globe-africa text-gray-500"></i>
            <h3 class="font-bold text-white uppercase tracking-wider text-sm">Select Geographic Region</h3>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($countryMap as $code => $country)
                <button onclick="navigateToCountry('{{ $code }}')" 
                        class="country-btn flex items-center gap-2.5 px-4 py-2 rounded-xl border border-white/5 bg-white/3 hover:bg-white/10 text-sm font-bold transition-all {{ $region === $code ? 'active' : '' }}"
                        data-country="{{ $code }}">
                    <span class="text-lg">{{ $country['flag'] }}</span>
                    <span>{{ $country['name'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Trends Section --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-3">
                <span class="text-3xl" id="current-flag">{{ $currentCountry['flag'] }}</span>
                <div>
                    <h3 class="text-xl font-black text-white" id="current-country-name">Trending in {{ $currentCountry['name'] }}</h3>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em]" id="trends-count">{{ count($trends) }} Trends Detected</p>
                </div>
            </div>
        </div>

        <div id="trends-container" class="grid gap-3 transition-opacity duration-300">
            @include('trendingsearchmonitor::partials.trends-list', ['trends' => $trends, 'region' => $region, 'currentCountry' => $currentCountry])
        </div>
    </div>

    <div class="text-center py-8">
        <p class="text-xs text-gray-600 font-medium">Last updated at <span id="update-time" class="text-gray-400 tracking-widest">{{ now()->format('h:i A') }}</span> — Auto-syncs every 5 minutes</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentRegion = '{{ $region }}';
let isLoading = false;

function navigateToCountry(countryCode, force = false) {
    if (isLoading) return;
    
    showLoadingState();
    
    // Update active button state
    document.querySelectorAll('.country-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.country === countryCode);
    });

    const url = new URL(window.location.href);
    url.searchParams.set('country', countryCode);
    if (force) url.searchParams.set('refresh', '1');
    
    // Smooth transition
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
    .then(({ ok, data }) => {
        if (!ok) {
            if (data.message && data.message.includes('Insufficient balance')) {
                showInsufficientBalanceAlert(data.message);
            }
            return;
        }
        currentRegion = countryCode;
        window.history.pushState({}, '', url);
        updateUI(data);
    })
    .catch(err => {
        console.error(err);
    })
    .finally(() => {
        isLoading = false;
    });
}

function updateUI(data) {
    const container = document.getElementById('trends-container');
    const flag = document.getElementById('current-flag');
    const name = document.getElementById('current-country-name');
    const count = document.getElementById('trends-count');
    const time = document.getElementById('update-time');

    flag.textContent = data.country.flag;
    name.textContent = `Trending in ${data.country.name}`;
    count.textContent = `${data.count} Trends Detected`;
    time.textContent = data.cached_at;

    container.style.opacity = '0';
    setTimeout(() => {
        container.innerHTML = renderTrends(data.trends, data.country);
        container.style.opacity = '1';
    }, 300);
}

function renderTrends(trends, country) {
    if (!trends || trends.length === 0) {
        return `
            <div class="glass-card p-16 text-center border-dashed border-white/5">
                <i class="fas fa-search-minus text-4xl text-gray-700 mb-4 block"></i>
                <h3 class="text-xl font-bold text-gray-500">No data available currently</h3>
                <p class="text-gray-600 text-sm mt-2">Try selecting another region or refreshing later</p>
            </div>
        `;
    }

    return trends.map((trend, index) => {
        const rankClass = index === 0 ? 'rank-1' : (index === 1 ? 'rank-2' : (index === 2 ? 'rank-3' : 'rank-default'));
        
        // News items HTML
        let newsHtml = '';
        if (trend.news && trend.news.length > 0) {
            newsHtml = `
                <div class="mt-2 space-y-1.5">
                    ${trend.news.map(ni => `
                        <div class="flex items-start gap-2 group/news">
                            <i class="fas fa-newspaper text-[9px] mt-1 text-gray-600 group-hover/news:text-primary-cyan transition-colors"></i>
                            <a href="${ni.url}" target="_blank" class="text-[11px] text-gray-400 hover:text-white transition-colors line-clamp-1 no-underline">
                                <span class="font-bold text-gray-500">[${ni.source}]</span> ${ni.title}
                            </a>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            newsHtml = `<p class="text-xs text-gray-500 font-medium break-words mt-1">${trend.subtitle || ''}</p>`;
        }

        return `
        <div class="trend-card glass-card p-5 group/card">
            <div class="flex items-center gap-5">
                <div class="rank-badge ${rankClass} flex-shrink-0">
                    ${index + 1}
                </div>

                ${trend.image ? 
                    `<img src="${trend.image}" class="w-14 h-14 rounded-2xl object-cover border border-white/5 shadow-2xl" alt="">` :
                    `<div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center text-gray-600 border border-white/5">
                        <i class="fas fa-trending-up"></i>
                    </div>`
                }

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <a href="https://www.google.com/search?q=${encodeURIComponent(trend.title)}&gl=${country.code}" 
                           target="_blank" 
                           class="text-lg font-black text-white hover:text-primary-cyan transition-colors block break-words no-underline">
                            ${trend.title}
                        </a>
                        ${trend.traffic ? `
                            <span class="px-2 py-0.5 bg-primary-cyan/10 text-primary-cyan text-[10px] border border-primary-cyan/20 rounded-lg font-bold">
                                ${trend.traffic} search
                            </span>
                        ` : ''}
                    </div>
                    
                    ${newsHtml}
                </div>

                <div class="flex items-center gap-2 opacity-0 group-hover/card:opacity-100 transition-opacity duration-300">
                    <button onclick="copyTrend('${trend.title.replace(/'/g, "\\'")}', this)" 
                            class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-gray-400"
                            title="Copy Keyword">
                        <i class="fas fa-copy text-sm"></i>
                    </button>
                    <a href="{{ route('headlines.index') }}?keyword=${encodeURIComponent(trend.title)}" 
                       class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-gray-400"
                       title="Generate Discover Headlines">
                        <i class="fas fa-bolt text-sm"></i>
                    </a>
                    <a href="https://www.google.com/search?q=${encodeURIComponent(trend.title)}&gl=${country.code}" 
                       target="_blank"
                       class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-gray-400"
                       title="Search Google">
                        <i class="fas fa-external-link-alt text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

function showLoadingState() {
    document.getElementById('trends-container').innerHTML = Array(8).fill(0).map(() => `
        <div class="glass-card p-5">
            <div class="flex items-center gap-5">
                <div class="w-8 h-8 rounded-lg loading-skeleton flex-shrink-0"></div>
                <div class="w-14 h-14 rounded-2xl loading-skeleton flex-shrink-0"></div>
                <div class="flex-1 space-y-3">
                    <div class="h-4 w-1/3 rounded-lg loading-skeleton"></div>
                    <div class="h-3 w-1/2 rounded-lg loading-skeleton"></div>
                </div>
                <div class="flex gap-2">
                    <div class="w-10 h-10 rounded-xl loading-skeleton"></div>
                    <div class="w-10 h-10 rounded-xl loading-skeleton"></div>
                </div>
            </div>
        </div>
    `).join('');
}

function refreshTrends(force = false) {
    const icon = document.getElementById('refresh-icon');
    icon.classList.add('animate-spin');
    navigateToCountry(currentRegion, force);
    setTimeout(() => icon.classList.remove('animate-spin'), 1000);
}

function copyTrend(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        const originalClass = icon.className;
        icon.className = 'fas fa-check text-black';
        btn.classList.add('bg-primary-cyan', 'text-black');
        btn.classList.remove('bg-white/5', 'text-gray-400');
        
        setTimeout(() => {
            icon.className = originalClass;
            btn.classList.remove('bg-primary-cyan', 'text-black');
            btn.classList.add('bg-white/5', 'text-gray-400');
        }, 2000);

        Swal.fire({
            title: 'Copied!',
            text: `"${text}" copied to clipboard`,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1a1b1e',
            color: '#fff'
        });
    });
}
</script>
@endpush
