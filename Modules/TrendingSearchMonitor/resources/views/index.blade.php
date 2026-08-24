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

    .trend-news-section .news-featured:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    .trend-img-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.03);
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
    <div class="flex items-center gap-2 bg-white/3 p-1.5 rounded-2xl border border-white/5 w-full max-w-full overflow-x-auto flex-nowrap sm:flex-wrap" style="scrollbar-width:none;-webkit-overflow-scrolling:touch;">
        @if($enabledPlatforms['google'] ?? true)
            <button onclick="switchPlatform('google', this)" class="platform-tab whitespace-nowrap flex-shrink-0 {{ ($platform ?? 'google') === 'google' ? 'active' : '' }}"><i class="fab fa-google mr-2 text-xs"></i> Google Search</button>
        @endif
        @if($enabledPlatforms['x'] ?? true)
            <button onclick="switchPlatform('x', this)" class="platform-tab whitespace-nowrap flex-shrink-0 {{ ($platform ?? '') === 'x' ? 'active' : '' }}"><i class="fab fa-twitter mr-2 text-xs"></i> X (Twitter)</button>
        @endif
        @if($enabledPlatforms['tiktok'] ?? true)
            <button onclick="switchPlatform('tiktok', this)" class="platform-tab whitespace-nowrap flex-shrink-0 {{ ($platform ?? '') === 'tiktok' ? 'active' : '' }}"><i class="fab fa-tiktok mr-2 text-xs"></i> TikTok Trends</button>
        @endif
        @if($enabledPlatforms['youtube'] ?? true)
            <button onclick="switchPlatform('youtube', this)" class="platform-tab whitespace-nowrap flex-shrink-0 {{ ($platform ?? '') === 'youtube' ? 'active' : '' }}"><i class="fab fa-youtube mr-2 text-xs"></i> YouTube Trends</button>
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
        <div class="flex items-center justify-between px-2 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <span class="text-3xl" id="current-flag">{{ $currentCountry['flag'] }}</span>
                <div>
                    <h3 class="text-xl font-black text-white" id="current-country-name">Trending in {{ $currentCountry['name'] }}</h3>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.2em]" id="trends-count">{{ count($trends) }} Trends Detected</p>
                </div>
            </div>

            {{-- Multi-select toolbar (Select All + Copy Selected) --}}
            <div id="bulk-toolbar" class="flex items-center gap-2 bg-white/3 px-2 py-1.5 rounded-xl border border-white/5">
                <label class="flex items-center gap-2 px-2 py-1 text-[11px] font-bold text-gray-400 hover:text-white cursor-pointer transition-colors">
                    <input type="checkbox" id="select-all-trends" style="accent-color:#0ea5e9;cursor:pointer;">
                    <span>Select All</span>
                </label>
                <span class="text-[10px] text-gray-600 font-bold px-1" id="selected-count">0 selected</span>
                <button onclick="copySelectedTrends()" id="copy-selected-btn" disabled
                        class="vn-btn vn-btn-primary px-3 py-1.5 rounded-lg text-[11px] flex items-center gap-1.5 disabled:opacity-30 disabled:cursor-not-allowed">
                    <i class="fas fa-clipboard-list text-[10px]"></i> Copy Selected
                </button>
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
        if (window.VidaCredits) window.VidaCredits.apply(data);
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

function renderTrendNewsHtml(trend) {
    const news = (trend.news || []).slice(0, 3);
    if (!news.length) {
        return `<p class="text-xs text-slate-300 font-medium break-words mt-1">${escapeHtml(trend.subtitle || '')}</p>`;
    }

    const featured = news[0];
    const rest = news.slice(1);
    const featuredImg = featured.image || trend.image || '';

    const imgFallback = `<div class="trend-img-fallback absolute inset-0 items-center justify-center" style="display:flex;"><i class="fas fa-newspaper text-2xl text-slate-400"></i></div>`;
    const featuredImgHtml = featuredImg
        ? `<img src="${escapeAttr(featuredImg)}" alt="" class="trend-news-img w-full h-full object-cover min-h-[7rem]" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">${imgFallback}`
        : imgFallback;

    const featuredBlock = `
        <a href="${escapeAttr(featured.url)}" target="_blank" rel="noopener"
           class="news-featured block mb-3 rounded-xl overflow-hidden border border-white/10 bg-white/[0.04] hover:border-primary-cyan/40 transition-all group/feat no-underline">
            <div class="flex flex-col sm:flex-row">
                <div class="sm:w-36 h-28 sm:h-auto flex-shrink-0 relative bg-white/10">${featuredImgHtml}</div>
                <div class="p-3.5 flex-1 min-w-0">
                    <span class="news-title-text text-sm font-bold text-white group-hover/feat:text-primary-cyan transition-colors line-clamp-2 block leading-snug">${escapeHtml(featured.title)}</span>
                    ${featured.snippet ? `<p class="text-[12px] text-slate-300 mt-1.5 line-clamp-2 leading-relaxed">${escapeHtml(featured.snippet)}</p>` : ''}
                    <div class="text-[11px] text-primary-cyan font-bold mt-2 flex flex-wrap items-center gap-2">
                        ${featured.source ? `<span class="uppercase px-2 py-0.5 rounded bg-primary-cyan/10 border border-primary-cyan/20">${escapeHtml(featured.source)}</span>` : ''}
                        ${featured.date ? `<span class="text-slate-400 font-normal">${escapeHtml(featured.date)}</span>` : ''}
                    </div>
                </div>
            </div>
        </a>`;

    const restHtml = rest.map(n => {
        const thumbImg = n.image
            ? `<img src="${escapeAttr(n.image)}" alt="" class="trend-news-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="trend-img-fallback absolute inset-0 items-center justify-center" style="display:none;"><i class="fas fa-file-alt text-xs text-slate-400"></i></div>`
            : `<div class="trend-img-fallback absolute inset-0 items-center justify-center" style="display:flex;"><i class="fas fa-file-alt text-xs text-slate-400"></i></div>`;
        return `
            <li>
                <a href="${escapeAttr(n.url)}" target="_blank" rel="noopener" class="news-link-item flex items-start gap-3 p-2 rounded-xl bg-white/[0.02] hover:bg-white/[0.06] border border-white/5 hover:border-white/15 transition-all group/news no-underline">
                    <div class="w-11 h-11 rounded-lg overflow-hidden flex-shrink-0 bg-white/10 relative">${thumbImg}</div>
                    <div class="flex-1 min-w-0">
                        <span class="news-title-text text-[13px] text-slate-100 font-medium group-hover/news:text-primary-cyan transition-colors line-clamp-2 block leading-snug">${escapeHtml(n.title)}</span>
                        <div class="text-[11px] text-primary-cyan font-bold flex flex-wrap items-center gap-2 mt-1">
                            ${n.source ? `<span class="uppercase text-[10px] px-1.5 py-0.5 rounded bg-primary-cyan/10 border border-primary-cyan/20">${escapeHtml(n.source)}</span>` : ''}
                        </div>
                    </div>
                    <i class="fas fa-external-link-alt text-[10px] text-slate-400 group-hover/news:text-primary-cyan opacity-0 group-hover/news:opacity-100 transition-all mt-1"></i>
                </a>
            </li>`;
    }).join('');

    return `
        <div class="trend-news-section mt-4 pt-4 border-t border-white/10" data-news-articles='${escapeAttr(JSON.stringify(news))}'>
            <div class="text-[11px] font-black text-primary-cyan uppercase tracking-widest mb-3 flex items-center gap-2">
                <i class="fas fa-newspaper"></i> Related News (${news.length})
            </div>
            ${featuredBlock}
            ${rest.length ? `<ul class="space-y-2.5">${restHtml}</ul>` : ''}
        </div>`;
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
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
        
        oppScore = Math.min(100, oppScore);

        const newsItems = (trend.news || []).slice(0, 3);
        const featuredImg = trend.image || (newsItems[0] && newsItems[0].image) || '';
        const newsHtml = renderTrendNewsHtml(trend);

        const thumbImgHtml = featuredImg
            ? `<img src="${escapeAttr(featuredImg)}" alt="${escapeAttr(trend.title)}" class="trend-thumb-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="trend-img-fallback absolute inset-0 items-center justify-center" style="display:none;"><i class="fas fa-trending-up text-gray-600"></i></div>`
            : `<div class="trend-img-fallback absolute inset-0 items-center justify-center" style="display:flex;"><i class="fas fa-trending-up text-gray-600"></i></div>`;

        return `
            <div class="trend-card glass-card p-5 group/card" data-trend-title="${escapeAttr(trend.title)}" style="background: ${cardBg}; border-color: rgba(255,255,255,0.08);">
                <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <label class="trend-select-label flex-shrink-0" style="display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);cursor:pointer;margin-top:4px;">
                            <input type="checkbox" class="trend-select-checkbox" value="${escapeAttr(trend.title)}" style="accent-color:#0ea5e9;cursor:pointer;">
                        </label>
                        <div class="rank-badge ${rankClass} flex-shrink-0">
                            ${index + 1}
                        </div>

                        <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0 border border-white/10 shadow-2xl relative bg-white/10">
                            ${thumbImgHtml}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <a href="https://www.google.com/search?q=${encodeURIComponent(trend.title)}&gl=${country.code || 'US'}"
                                   target="_blank"
                                   class="text-lg font-black text-white hover:text-primary-cyan transition-colors block break-words no-underline">
                                    ${escapeHtml(trend.title)}
                                </a>
                                ${trend.traffic ? `
                                    <span class="px-2 py-0.5 bg-primary-cyan/10 text-primary-cyan text-[10px] border border-primary-cyan/20 rounded-lg font-bold">
                                        ${escapeHtml(trend.traffic)} search
                                    </span>
                                ` : ''}
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-white/10 rounded-full overflow-hidden border border-white/10">
                                        <div class="h-full bg-gradient-to-r from-primary-cyan to-primary-purple" style="width: ${oppScore}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-primary-cyan uppercase tracking-tighter">${oppScore}% ROI</span>
                                </div>
                            </div>

                            ${newsHtml}
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0 self-start lg:self-center pl-9 lg:pl-0">
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
                        <a href="{{ route('dashboard.article-writer.index') }}?keyword=${encodeURIComponent(trend.title)}" target="_blank"
                           class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#a855f7] to-[#6366f1] hover:scale-110 flex items-center justify-center transition-all text-white shadow-lg"
                           title="Write with AI">
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
    isLoading = true;

    const card = btn.closest('.trend-card') || btn.closest('.glass-card');
    let articles = [];

    const newsSection = card?.querySelector('[data-news-articles]');
    if (newsSection) {
        try {
            articles = JSON.parse(newsSection.getAttribute('data-news-articles') || '[]');
        } catch (e) {
            console.warn('Could not parse news articles JSON', e);
        }
    }

    if (!articles.length) {
        const headlineElements = card?.querySelectorAll('.news-title-text') || [];
        articles = Array.from(headlineElements).map(el => ({
            title: el.innerText.trim(),
            summary: '',
            source: '',
            date: ''
        })).filter(a => a.title !== '');
    }

    articles = articles.slice(0, 3).map(a => ({
        title: a.title || '',
        summary: a.summary || a.snippet || '',
        source: a.source || '',
        date: a.date || ''
    }));

    try {
        const response = await fetch('{{ route("dashboard.trending-searches.analyze") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                trend: trend,
                country: country,
                lang: lang,
                platform: currentPlatform,
                articles: articles
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || data.message || 'Intelligence analysis failed. Please try again.');
        }

        if (window.VidaCredits) window.VidaCredits.apply(data);

        const intel = data.analysis;
        const sentimentColor = {
            positive: 'text-emerald-400',
            negative: 'text-red-400',
            neutral: 'text-gray-400'
        }[intel.sentiment] || 'text-gray-400';

        const listHtml = (items, emptyMsg) => {
            if (!items || !items.length) return `<p class="text-xs text-gray-600 italic">${emptyMsg}</p>`;
            return `<ul class="space-y-1.5">${items.map(i => `<li class="text-sm text-gray-300 flex items-start gap-2"><i class="fas fa-circle text-[5px] mt-2 text-primary-cyan"></i><span>${escapeHtml(i)}</span></li>`).join('')}</ul>`;
        };

        Swal.fire({
            title: `<div class="flex items-center gap-3 justify-center mb-2">
                        <div class="w-10 h-10 rounded-xl bg-primary-cyan/10 flex items-center justify-center text-primary-cyan">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-[10px] text-primary-cyan font-black uppercase tracking-[0.2em]">Viral Intelligence</div>
                            <div class="text-xl font-black text-white">${escapeHtml(trend)}</div>
                        </div>
                    </div>`,
            html: `
                <div class="text-left space-y-5 mt-4 pb-6 border-b border-white/5 max-h-[60vh] overflow-y-auto">
                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Why It's Trending</div>
                        <p class="text-sm text-gray-300 leading-relaxed">${escapeHtml(intel.why_trending || '')}</p>
                    </div>

                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Key Reasons</div>
                        ${listHtml(intel.key_reasons, 'No specific reasons extracted.')}
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="metric-chip">
                            <i class="fas fa-rocket text-primary-cyan text-xs"></i>
                            <div>
                                <div class="metric-label">Opportunity</div>
                                <div class="metric-value">${intel.opportunity_score ?? 0}%</div>
                            </div>
                        </div>
                        <div class="metric-chip">
                            <i class="fas fa-shield-alt text-yellow-500 text-xs"></i>
                            <div>
                                <div class="metric-label">Difficulty</div>
                                <div class="metric-value">${intel.difficulty_score ?? 0}/100</div>
                            </div>
                        </div>
                        <div class="metric-chip col-span-2 sm:col-span-1">
                            <i class="fas fa-heart text-pink-400 text-xs"></i>
                            <div>
                                <div class="metric-label">Sentiment</div>
                                <div class="metric-value ${sentimentColor}">${escapeHtml(intel.sentiment || 'neutral')}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Related Topics & Entities</div>
                        ${intel.related_topics && intel.related_topics.length
                            ? `<div class="flex flex-wrap gap-1.5">${intel.related_topics.map(t => `<span class="text-[10px] px-2 py-0.5 rounded-md bg-primary-cyan/10 text-primary-cyan border border-primary-cyan/20">${escapeHtml(t)}</span>`).join('')}</div>`
                            : '<p class="text-xs text-gray-600 italic">None identified.</p>'}
                    </div>

                    <div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Suggested Content Angles</div>
                        ${listHtml(intel.content_angles || (intel.content_strategy ? [intel.content_strategy] : []), 'No angles suggested.')}
                    </div>

                    ${articles.length ? `<div class="text-[9px] text-gray-600">Analyzed ${articles.length} news article${articles.length > 1 ? 's' : ''}</div>` : ''}
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <a href="{{ route('dashboard.article-writer.index') }}?keyword=${encodeURIComponent(trend)}" target="_blank"
                       class="vn-btn vn-btn-primary py-3 rounded-xl flex items-center justify-center gap-2 text-sm shadow-lg shadow-primary-cyan/20">
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
            width: '560px',
            padding: '2rem'
        });

    } catch (err) {
        Swal.fire({
            title: 'Analysis Failed',
            text: err.message || 'Could not complete AI analysis. Please try again.',
            icon: 'error',
            background: '#1a1b1e',
            color: '#fff'
        });
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        isLoading = false;
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

// ─── Multi-select + bulk copy ───────────────────────────────────────
function escapeAttr(str) {
    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function getSelectedTrendTitles() {
    return Array.from(document.querySelectorAll('.trend-select-checkbox:checked')).map(cb => cb.value);
}

function refreshBulkToolbar() {
    const selected = getSelectedTrendTitles();
    const countEl = document.getElementById('selected-count');
    const btn = document.getElementById('copy-selected-btn');
    if (countEl) countEl.textContent = `${selected.length} selected`;
    if (btn) btn.disabled = selected.length === 0;

    const allBoxes = document.querySelectorAll('.trend-select-checkbox');
    const selectAll = document.getElementById('select-all-trends');
    if (selectAll && allBoxes.length > 0) {
        selectAll.checked = selected.length === allBoxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < allBoxes.length;
    }
}

document.addEventListener('change', (e) => {
    if (e.target.matches('.trend-select-checkbox')) {
        refreshBulkToolbar();
    } else if (e.target.id === 'select-all-trends') {
        const checked = e.target.checked;
        document.querySelectorAll('.trend-select-checkbox').forEach(cb => { cb.checked = checked; });
        refreshBulkToolbar();
    }
});

function copySelectedTrends() {
    const selected = getSelectedTrendTitles();
    if (!selected.length) return;

    const text = selected.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            title: `Copied ${selected.length} trend${selected.length > 1 ? 's' : ''}`,
            text: 'Each on its own line — paste anywhere.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#1a1b1e',
            color: '#fff'
        });
    }).catch(() => {
        Swal.fire({ title: 'Copy failed', text: 'Clipboard permission denied.', icon: 'error', background: '#1a1b1e', color: '#fff' });
    });
}

// Reset selection state whenever the trends grid is replaced.
document.addEventListener('DOMContentLoaded', refreshBulkToolbar);
const trendsContainer = document.getElementById('trends-container');
if (trendsContainer) {
    new MutationObserver(refreshBulkToolbar).observe(trendsContainer, { childList: true, subtree: true });
}
</script>
@endpush
