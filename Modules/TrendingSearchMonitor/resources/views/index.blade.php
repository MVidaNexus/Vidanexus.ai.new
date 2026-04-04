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

    /* V2.0 Metrics */
    .metric-chip {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        padding: 0.4rem 0.75rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .metric-value { font-weight: 800; font-size: 0.8rem; color: #fff; }
    .metric-label { font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

    .platform-tab {
        padding: 0.6rem 1.25rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: all 0.3s;
        border: 1px solid transparent;
        color: var(--text-muted);
    }
    .platform-tab.active {
        background: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
        border-color: rgba(14, 165, 233, 0.2);
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

    {{-- V2.0 Platform Switcher (only show enabled platforms) --}}
    <div class="flex items-center gap-2 bg-white/3 p-1.5 rounded-2xl border border-white/5 w-fit">
        @if($enabledPlatforms['google'] ?? true)
            <button onclick="switchPlatform('google', this)" class="platform-tab {{ ($platform ?? 'google') === 'google' ? 'active' : '' }}"><i class="fab fa-google mr-2 text-xs"></i> Google Search</button>
        @endif
        @if($enabledPlatforms['x'] ?? true)
            <button onclick="switchPlatform('x', this)" class="platform-tab {{ ($platform ?? '') === 'x' ? 'active' : '' }}"><i class="fab fa-twitter mr-2 text-xs"></i> X (Twitter)</button>
        @endif
        @if($enabledPlatforms['tiktok'] ?? true)
            <button onclick="switchPlatform('tiktok', this)" class="platform-tab {{ ($platform ?? '') === 'tiktok' ? 'active' : '' }}"><i class="fab fa-tiktok mr-2 text-xs"></i> TikTok Trends</button>
        @endif
        @if($enabledPlatforms['youtube'] ?? true)
            <button onclick="switchPlatform('youtube', this)" class="platform-tab {{ ($platform ?? '') === 'youtube' ? 'active' : '' }}"><i class="fab fa-youtube mr-2 text-xs"></i> YouTube Trends</button>
        @endif
    </div>

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
            @if(count($trends) > 0)
                @include('trendingsearchmonitor::partials.trends-list', ['trends' => $trends, 'region' => $region, 'currentCountry' => $currentCountry])
            @else
                <div class="glass-card p-12 text-center border-dashed border-white/5 bg-white/2">
                    <div class="w-20 h-20 rounded-full bg-primary-cyan/5 flex items-center justify-center mx-auto mb-6 border border-primary-cyan/10">
                        <i class="fas fa-satellite-dish text-3xl text-primary-cyan animate-pulse"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-2">Ready to Sync</h3>
                    <p class="text-gray-400 text-sm max-w-sm mx-auto mb-8">Click the button below to fetch the latest trending topics for <b>{{ $currentCountry['name'] }}</b>. (1 Credit will be deducted)</p>
                    
                    <a href="{{ route('dashboard.trending-searches.index') }}?fetch=1&country={{ $region }}" class="vn-btn vn-btn-primary px-8 py-4 rounded-xl font-black tracking-widest text-sm flex items-center gap-3 mx-auto w-fit shadow-xl shadow-primary-cyan/20">
                        <i class="fas fa-sync-alt"></i>
                        GET TRENDING NOW
                    </a>
                </div>
            @endif
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
let currentPlatform = '{{ $platform ?? 'google' }}';
let isLoading = false;

function switchPlatform(platform, btn) {
    if (isLoading || currentPlatform === platform) return;
    
    currentPlatform = platform;
    document.querySelectorAll('.platform-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    navigateToCountry(currentRegion, true);
}

function navigateToCountry(countryCode, force = false) {
    if (isLoading) return;
    
    showLoadingState();
    
    // Update active button state
    document.querySelectorAll('.country-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.country === countryCode);
    });

    const url = new URL(window.location.href);
    url.searchParams.set('country', countryCode);
    url.searchParams.set('platform', currentPlatform);
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
        
        // --- V2.5 PLATFORM BRANDING ---
        let brandColor = '#0ea5e9'; // Default Cyan
        let brandIcon = 'fab fa-google';
        let cardBg = 'rgba(255, 255, 255, 0.04)';

        if (currentPlatform === 'x' || currentPlatform === 'twitter') {
            brandColor = '#ffffff'; // X White
            brandIcon = 'fab fa-twitter';
            cardBg = 'rgba(255, 255, 255, 0.02)';
        } else if (currentPlatform === 'tiktok') {
            brandColor = '#ec4899'; // TikTok Pink
            brandIcon = 'fab fa-tiktok';
            cardBg = 'rgba(236, 72, 153, 0.02)';
        } else if (currentPlatform === 'youtube') {
            brandColor = '#ff0000'; // YouTube Red
            brandIcon = 'fab fa-youtube';
            cardBg = 'rgba(255, 0, 0, 0.02)';
        }

        // --- V2.0 OFFLINE SCORING LOGIC ---
        let oppScore = 55; // Base
        const highValue = ['سعر', 'موعد', 'شراء', 'تطبيق', 'فرصة', 'price', 'buy', 'how to', 'best', 'sale'];
        if (highValue.some(w => trend.title.toLowerCase().includes(w))) oppScore += 20;
        if (index < 3) oppScore += 10;
        if (currentPlatform !== 'google') oppScore += 5; // Social trends have higher velocity
        oppScore = Math.min(100, oppScore);
        
        // News items HTML
        let newsHtml = '';
        if (trend.news && trend.news.length > 0) {
            newsHtml = `
                <div class="mt-6 pt-6 border-t border-white/5">
                    <div class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-newspaper"></i> Contextual News Flow
                    </div>
                    <ul class="space-y-4">
                        ${trend.news.map(n => `
                            <li class="group/news">
                                <a href="${n.url}" target="_blank" class="news-link-item flex items-start gap-3 group">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white/10 mt-1.5 group-hover/news:bg-primary-cyan transition-colors"></div>
                                    <div class="flex-1">
                                        <span class="news-title-text text-[11px] text-gray-400 font-medium group-hover/news:text-primary-cyan transition-colors duration-300 line-clamp-1 mb-1">${n.title}</span>
                                        <div class="text-[10px] text-gray-600 flex items-center gap-2">
                                            <span class="font-black uppercase">${n.source}</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-external-link-alt text-[9px] text-gray-700 group-hover/news:text-primary-cyan opacity-0 group-hover/news:opacity-100 transition-all"></i>
                                </a>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        } else {
            newsHtml = `<p class="text-xs text-gray-500 font-medium break-words mt-1">${trend.subtitle || ''}</p>`;
        }

        return `
        <div class="trend-card glass-card p-5 group/card" style="background: ${cardBg}">
            <div class="flex items-center gap-5">
                <div class="rank-badge ${rankClass} flex-shrink-0">
                    ${index + 1}
                </div>

                ${trend.image ? 
                    `<img src="${trend.image}" class="w-14 h-14 rounded-2xl object-cover border border-white/5 shadow-2xl" alt="">` :
                    `<div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center text-gray-600 border border-white/5">
                        <i class="${brandIcon}" style="color: ${brandColor}"></i>
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
                                ${trend.traffic}
                            </span>
                        ` : ''}

                        <!-- V2.0 Opportunity Meter -->
                        <div class="flex items-center gap-2 ml-auto md:ml-0">
                             <div class="w-16 h-1.5 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                 <div class="h-full bg-gradient-to-r from-primary-cyan to-primary-purple" style="width: ${oppScore}%"></div>
                             </div>
                             <span class="text-[9px] font-black ${oppScore > 60 ? 'text-primary-cyan' : 'text-gray-500'} uppercase tracking-tighter">${oppScore}% ROI</span>
                        </div>
                    </div>
                    
                    ${newsHtml}
                </div>

                <div class="flex items-center gap-2 transition-all duration-300">
                    <!-- V2.0 AI Deep Intel Button -->
                    <button onclick="analyzeTrend('${trend.title.replace(/'/g, "\\'")}', '${country.code}', '${country.lang || 'ar'}', this)" 
                            class="w-10 h-10 rounded-xl bg-primary-cyan/10 border border-primary-cyan/20 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-primary-cyan"
                            title="AI Deep Intelligence">
                        <i class="fas fa-brain text-sm"></i>
                    </button>

                    <button onclick="copyTrend('${trend.title.replace(/'/g, "\\'")}', this)" 
                            class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all text-gray-400"
                            title="Copy Keyword">
                        <i class="fas fa-copy text-sm"></i>
                    </button>

                    <a href="{{ route('headlines.index') }}?keyword=${encodeURIComponent(trend.title)}" 
                       class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-gray-400"
                       title="Generate Discover Headlines">
                        <i class="fas fa-bolt text-sm"></i>
                    </a>

                    <a href="{{ route('articlewriter.index') }}?keyword=${encodeURIComponent(trend.title)}" 
                       class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-purple hover:text-white flex items-center justify-center transition-all text-gray-400"
                       title="One-Click Article Writer">
                        <i class="fas fa-pen-fancy text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

async function analyzeTrend(trend, country, lang, btn) {
    if (isLoading) return;
    
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>';
    btn.disabled = true;

    // V2.8: Robust Context Extraction (Targeting news headlines)
    const card = btn.closest('.glass-card');
    const headlineElements = card.querySelectorAll('.news-title-text');
    const headlines = Array.from(headlineElements).map(el => el.innerText.trim()).filter(t => t !== '');
    
    if (headlines.length === 0) {
        console.warn('No context headlines found for:', trend);
    }

    try {
        const response = await fetch('{{ route("dashboard.trending-searches.analyze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                trend: trend,
                country: country,
                lang: lang,
                platform: currentPlatform,
                headlines: headlines
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Intelligence failure');
        }

        const intel = data.analysis;

        Swal.fire({
            title: `<div class="flex items-center gap-3 justify-center mb-2">
                        <div class="w-10 h-10 rounded-xl bg-primary-cyan/10 flex items-center justify-center text-primary-cyan">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-[10px] text-primary-cyan font-black uppercase tracking-[0.2em]">Viral Intelligence</div>
                            <div class="text-xl font-black text-white">${trend}</div>
                        </div>
                    </div>`,
            html: `
                <div class="text-left space-y-6 mt-4 pb-6 border-b border-white/5">
                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Trend Decoding</div>
                        <p class="text-sm text-gray-300 leading-relaxed">${intel.why_trending}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="metric-chip">
                             <i class="fas fa-rocket text-primary-cyan text-xs"></i>
                             <div>
                                 <div class="metric-label">Opportunity</div>
                                 <div class="metric-value">${intel.opportunity_score}%</div>
                             </div>
                        </div>
                        <div class="metric-chip">
                             <i class="fas fa-shield-alt text-yellow-500 text-xs"></i>
                             <div>
                                 <div class="metric-label">Difficulty</div>
                                 <div class="metric-value">${intel.difficulty_score}/100</div>
                             </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Suggested Writing Angle</div>
                        <p class="text-sm text-white font-black italic">"${intel.content_strategy}"</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <a href="{{ route('articlewriter.index') }}?keyword=${encodeURIComponent(trend)}" class="vn-btn vn-btn-primary py-3 rounded-xl flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-pen-fancy"></i> Write Article
                    </a>
                    <button onclick="Swal.close()" class="vn-btn border border-white/10 hover:bg-white/5 py-3 rounded-xl text-gray-400 text-sm">
                        Close Intel
                    </button>
                </div>
            `,
            background: '#0d0e12',
            color: '#fff',
            showConfirmButton: false,
            width: '500px',
            padding: '2rem'
        });

    } catch (err) {
        Swal.fire({
            title: 'Intel Failed',
            text: err.message,
            icon: 'error',
            background: '#1a1b1e',
            color: '#fff'
        });
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
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
