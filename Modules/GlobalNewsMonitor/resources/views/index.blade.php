@extends('globalnewsmonitor::layouts.master')

@section('title', 'Global News Monitor')

@section('content')
<div x-data="newsMonitor()" x-init="init()" class="max-w-7xl mx-auto pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <h1 class="text-4xl md:text-5xl font-black mb-2 flex items-center gap-4" style="color: var(--text-main);">
                <i class="fas fa-satellite-dish text-primary-cyan"></i>
                <span>News Intelligence</span>
            </h1>
            <p class="text-lg font-medium" style="color: var(--text-muted);">Real-Time Ranking Opportunity Scanner</p>
        </div>
        
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Breaking Now Filter --}}
            <button @click="showBreakingOnly = !showBreakingOnly" 
                    :class="showBreakingOnly ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-white/5 text-gray-400 border-white/10 hover:border-red-500/30'"
                    class="px-5 py-3 rounded-2xl flex items-center gap-2 text-sm z-50 border transition-all active:scale-95">
                <div class="w-2 h-2 rounded-full" :class="showBreakingOnly ? 'bg-red-400 animate-pulse' : 'bg-gray-500'"></div>
                <span class="font-bold text-xs">Breaking Now</span>
            </button>

            {{-- High Opportunity Filter --}}
            <button @click="showHighChanceOnly = !showHighChanceOnly" 
                    :class="showHighChanceOnly ? 'bg-emerald-500 text-white border-emerald-400 shadow-[0_0_20px_rgba(52,211,153,0.3)]' : 'bg-white/5 text-gray-400 border-white/10 hover:border-emerald-500/50'"
                    class="px-5 py-3 rounded-2xl flex items-center gap-2 text-sm z-50 border transition-all active:scale-95">
                <i class="fas fa-rocket" :class="showHighChanceOnly ? 'text-white' : 'text-emerald-400'"></i>
                <span class="font-bold text-xs">High Opportunity</span>
                <template x-if="showHighChanceOnly">
                    <span class="ml-1 text-[10px] bg-white/20 px-1.5 rounded-full">ON</span>
                </template>
            </button>

            {{-- Refresh Button with Countdown --}}
            <button @click="refreshNews(true)" class="vn-btn vn-btn-primary px-6 py-3 rounded-2xl flex items-center gap-2 text-sm z-50 overflow-visible relative">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
                <span>Refresh</span>
                <span x-show="autoRefreshCountdown > 0" class="text-[9px] opacity-60 ml-1" x-text="autoRefreshCountdown + 's'"></span>
            </button>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'global-news-monitor'])

    <!-- Filters -->
    <div class="glass-card p-4 sm:p-6 mb-8 flex flex-col gap-8">
        
        <!-- 1. Region Selector (Top) -->
        <div class="flex flex-col md:flex-row md:items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border shrink-0" style="background: var(--card-bg); color: var(--text-muted); border-color: var(--glass-border);">
                <i class="fas fa-map-marker-alt text-xl"></i>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold uppercase tracking-widest mb-3" style="color: var(--text-muted);">Region</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($countryMap as $code => $country)
                        <button @click="changeRegion('{{ $code }}')" 
                                :class="region === '{{ $code }}' ? 'bg-primary-cyan text-black' : ''"
                                :style="region === '{{ $code }}' ? '' : 'background: var(--card-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'"
                                class="px-4 py-2.5 rounded-xl text-[11px] font-bold transition-all flex items-center gap-2 border-transparent hover:border-primary-cyan/50 min-w-[100px] justify-center sm:justify-start">
                            <span>{{ $country['flag'] }}</span>
                            <span>{{ $country['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Separator -->
        <div class="w-full h-px bg-white/5 opacity-50"></div>

        <!-- 2. Topic Selector (Bottom) -->
        <div class="flex flex-col md:flex-row md:items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center border shrink-0" style="background: var(--card-bg); color: var(--text-muted); border-color: var(--glass-border);">
                <i class="fas fa-layer-group text-xl"></i>
            </div>
            <div class="flex-1 overflow-x-auto no-scrollbar pb-2 sm:pb-0">
                <label class="block text-xs font-bold uppercase tracking-widest mb-3" style="color: var(--text-muted);">Category</label>
                <div class="flex items-center gap-2">
                    @foreach($topicsMap as $key => $name)
                        <button @click="changeTopic('{{ $key }}')"
                                :class="topic === '{{ $key }}' ? 'text-primary-cyan border-primary-cyan bg-primary-cyan/5' : ''"
                                :style="topic === '{{ $key }}' ? '' : 'background: var(--card-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'"
                                class="px-5 py-2.5 rounded-xl border text-[11px] font-bold transition-all whitespace-nowrap flex items-center gap-2 hover:border-white/20">
                            @if(!empty($name['icon']))
                                <i class="{{ $name['icon'] }} text-[10px]"></i>
                            @endif
                            <span>{{ $name['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- Stats Bar -->
    <div class="glass-card p-4 mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-6 text-[11px] font-bold" style="color: var(--text-muted);">
            <span><i class="fas fa-newspaper text-primary-cyan mr-1.5"></i> <span id="total-count">{{ count($googleNews) }}</span> articles</span>
            <span><i class="fas fa-rocket text-emerald-400 mr-1.5"></i> <span id="high-count">{{ collect($googleNews)->where('seo_score', '>=', $thresholdHigh)->count() }}</span> high opportunity</span>
            <span><i class="fas fa-bolt text-amber-400 mr-1.5"></i> <span id="moderate-count">{{ collect($googleNews)->where('seo_score', '>=', $thresholdModerate)->where('seo_score', '<', $thresholdHigh)->count() }}</span> moderate</span>
        </div>
        <div class="flex items-center gap-2 text-[10px]" style="color: var(--text-muted);">
            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
            <span>Auto-refresh in <span x-text="autoRefreshCountdown" class="font-bold text-primary-cyan"></span>s</span>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="glass-card overflow-hidden border border-white/10 relative min-h-[500px]">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-cyan/5 blur-3xl -mr-32 -mt-32 rounded-full"></div>
        
        <!-- Loading Overlay -->
        <div x-show="loading" class="absolute inset-0 z-20 flex flex-col items-center justify-center backdrop-blur-sm" style="background: rgba(var(--bg-color-rgb, 13, 14, 18), 0.6); display: none;">
            <div class="w-16 h-16 border-4 border-t-primary-cyan rounded-full animate-spin mb-4" style="border-color: var(--glass-border);"></div>
            <p class="text-primary-cyan font-bold tracking-widest animate-pulse">Scanning for opportunities...</p>
        </div>

        <div class="p-6 relative z-10">
            <h2 class="text-xl font-black flex items-center gap-3 mb-6" style="color: var(--text-main);">
                <i class="fas fa-crosshairs text-primary-cyan"></i>
                <span x-text="'Ranking Opportunities: ' + getTopicName()">Ranking Opportunities</span>
                <span class="px-2 py-0.5 bg-red-500/10 text-red-400 text-[10px] border border-red-500/20 rounded-full font-bold ml-2 animate-pulse flex items-center gap-1.5"><div class="w-1.5 h-1.5 bg-red-400 rounded-full"></div> LIVE</span>
            </h2>

            <div id="news-container">
                @include('globalnewsmonitor::partials.news_grid', [
                    'googleNews' => $googleNews, 
                    'region' => $region ?? 'EG',
                    'lang' => $lang ?? 'ar',
                    'thresholdHigh' => $thresholdHigh ?? 70,
                    'thresholdModerate' => $thresholdModerate ?? 45
                ])
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function newsMonitor() {
    return {
        region: '{{ $region ?? "EG" }}',
        topic: '{{ $topic ?? "WORLD" }}',
        lang: '{{ $lang ?? "ar" }}',
        loading: false,
        showHighChanceOnly: false,
        showBreakingOnly: false,
        autoRefreshCountdown: 300,
        autoRefreshTimer: null,
        topicsMap: @json($topicsMap),
        
        init() {
            console.log('News Intelligence Monitor Initialized');
            this.startAutoRefresh();
        },

        getTopicName() {
            return this.topicsMap[this.topic] ? this.topicsMap[this.topic].name : 'World';
        },

        changeRegion(newRegion) {
            if (this.region === newRegion) return;
            this.region = newRegion;
            this.refreshNews();
        },

        changeTopic(newTopic) {
            if (this.topic === newTopic) return;
            this.topic = newTopic;
            this.refreshNews();
        },

        startAutoRefresh() {
            if (this.autoRefreshTimer) clearInterval(this.autoRefreshTimer);
            this.autoRefreshCountdown = 300;
            this.autoRefreshTimer = setInterval(() => {
                this.autoRefreshCountdown--;
                if (this.autoRefreshCountdown <= 0) {
                    this.refreshNews(true);
                    this.autoRefreshCountdown = 300;
                }
            }, 1000);
        },

        refreshNews(force = false) {
            this.loading = true;
            this.autoRefreshCountdown = 300;
            let url = `{{ route('dashboard.global-news-monitor.index') }}?region=${this.region}&topic=${this.topic}`;
            if (force) url += '&refresh=1';

            fetch(url, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json, text/html'
                }
            })
            .then(res => {
                const contentType = res.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return res.json().then(data => ({ ok: res.ok, type: 'json', data }));
                }
                return res.text().then(html => ({ ok: res.ok, type: 'html', data: html }));
            })
            .then(({ ok, type, data }) => {
                if (!ok) {
                    if (type === 'json' && (data.error || data.message)) {
                        const msg = data.error || data.message;
                        if (msg.includes('Insufficient balance')) {
                            showInsufficientBalanceAlert(msg);
                        } else {
                            Swal.fire('Error', msg, 'error');
                        }
                    }
                    return;
                }
                if (type === 'html') {
                    document.getElementById('news-container').innerHTML = data;
                }
            })
            .finally(() => this.loading = false);
        }
    }
}

/**
 * AI Deep Analysis for a single article
 */
function analyzeArticle(el, title, description, country, lang, topic) {
    // Find the Alpine component
    const card = el.closest('[x-data]');
    const alpineData = Alpine.$data(card);
    
    alpineData.analyzing = true;
    
    fetch('{{ route("dashboard.global-news-monitor.analyze") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            title: title,
            description: description,
            country: country,
            lang: lang,
            topic: topic
        })
    })
    .then(res => res.json())
    .then(data => {
        alpineData.analyzing = false;
        if (data.success && data.analysis) {
            alpineData.analysisData = data.analysis;
            alpineData.showAnalysis = true;
        } else {
            const msg = data.message || 'Analysis failed. Please try again.';
            if (typeof Swal !== 'undefined') {
                Swal.fire('Analysis Error', msg, 'warning');
            } else {
                alert(msg);
            }
        }
    })
    .catch(err => {
        alpineData.analyzing = false;
        console.error('Analysis error:', err);
    });
}
</script>
@endpush

@push('styles')
<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.news-card {
    transition: transform 0.2s ease, box-shadow 0.3s ease;
}
.news-card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
@endsection
