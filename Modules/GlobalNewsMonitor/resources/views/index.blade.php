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
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'global-news-monitor'])

    <!-- Filters -->
    <div class="glass-card p-4 sm:p-6 mb-8 flex flex-col gap-6 sm:gap-8">
        
        <!-- 1. Region Selector (Top) -->
        <div class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center border shrink-0" style="background: var(--card-bg); color: var(--text-muted); border-color: var(--glass-border);">
                <i class="fas fa-map-marker-alt text-base sm:text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-bold uppercase tracking-widest mb-3" style="color: var(--text-muted);">Region</label>
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                    @foreach($countryMap as $code => $country)
                        <button @click="changeRegion('{{ $code }}')" 
                                :class="region === '{{ $code }}' ? 'bg-primary-cyan text-black border-primary-cyan shadow-[0_0_15px_rgba(0,168,230,0.3)] font-black' : ''"
                                :style="region === '{{ $code }}' ? '' : 'background: var(--card-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'"
                                class="px-3.5 py-2.5 rounded-xl border text-[11px] font-bold transition-all flex items-center gap-2 hover:border-primary-cyan/50 justify-center sm:justify-start">
                            <span class="text-sm sm:text-base">{{ $country['flag'] }}</span>
                            <span class="truncate">{{ $country['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Separator -->
        <div class="w-full h-px bg-white/5 opacity-50"></div>

        <!-- 2. Topic Selector (Middle) -->
        <div class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center border shrink-0" style="background: var(--card-bg); color: var(--text-muted); border-color: var(--glass-border);">
                <i class="fas fa-layer-group text-base sm:text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-bold uppercase tracking-widest mb-3" style="color: var(--text-muted);">Category</label>
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                    @foreach($topicsMap as $key => $name)
                        <button @click="changeTopic('{{ $key }}')"
                                :class="topic === '{{ $key }}' ? 'bg-primary-cyan text-black border-primary-cyan shadow-[0_0_15px_rgba(0,168,230,0.3)] font-black' : ''"
                                :style="topic === '{{ $key }}' ? '' : 'background: var(--card-bg); color: var(--text-muted); border: 1px solid var(--glass-border);'"
                                class="px-3.5 py-2.5 rounded-xl border text-[11px] font-bold transition-all flex items-center gap-2 hover:border-primary-cyan/50 justify-center sm:justify-start">
                            @if(!empty($name['icon']))
                                <i class="{{ $name['icon'] }} text-[11px]"></i>
                            @endif
                            <span class="truncate">{{ $name['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Separator -->
        <div class="w-full h-px bg-white/5 opacity-50"></div>

        <!-- 3. Action Section (Bottom) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-1">
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-400 text-center sm:text-left">
                <i class="fas fa-info-circle text-primary-cyan text-sm"></i>
                <span>Choose your target <strong>Region</strong> and <strong>Category</strong>, then click <strong>Get News</strong> to scan live opportunities.</span>
            </div>

            <button @click="refreshNews(true)" 
                    :disabled="loading"
                    class="w-full sm:w-auto vn-btn vn-btn-primary px-8 py-3.5 rounded-2xl flex items-center justify-center gap-2.5 text-sm font-black shadow-lg shadow-primary-cyan/20 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
                <span>Get News</span>
                <i class="fas fa-arrow-right text-xs ml-1"></i>
            </button>
        </div>

    </div>

    <div class="glass-card p-4 mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-6 text-[11px] font-bold" style="color: var(--text-muted);">
            <span>
                <i class="fas fa-newspaper text-primary-cyan mr-1.5"></i> 
                <span x-text="showHighChanceOnly ? stats.high : stats.total"></span> articles
            </span>
            <span>
                <i class="fas fa-rocket text-emerald-400 mr-1.5"></i> 
                <span x-text="stats.high"></span> high opportunity
            </span>
            <span>
                <i class="fas fa-bolt text-amber-400 mr-1.5"></i> 
                <span x-text="stats.moderate"></span> moderate
            </span>
            <span x-show="selectedTitles.length > 0" class="text-primary-cyan">
                <i class="fas fa-check-square mr-1.5"></i>
                <span x-text="selectedTitles.length"></span> selected
            </span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <template x-if="selectedTitles.length > 0">
                <div class="flex items-center gap-2">
                    <button @click="clearSelection()" type="button"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-bold border border-white/10 text-gray-400 hover:text-white transition-colors">
                        Clear
                    </button>
                    <button @click="generateAnalysis()" type="button"
                            :disabled="generatingBrief || selectedTitles.length === 0"
                            class="vn-btn vn-btn-primary px-4 py-1.5 rounded-lg text-[10px] font-bold flex items-center gap-1.5 disabled:opacity-40">
                        <i class="fas fa-spinner animate-spin" x-show="generatingBrief"></i>
                        <i class="fas fa-wand-magic-sparkles" x-show="!generatingBrief"></i>
                        Generate Analysis
                    </button>
                </div>
            </template>
            <div class="flex items-center gap-2 text-[10px]" style="color: var(--text-muted);">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                <span>System Operational</span>
            </div>
        </div>
    </div>

    {{-- Multi-select summary panel --}}
    <div x-show="selectedTitles.length > 0" x-transition
         class="glass-card p-5 mb-6 border border-primary-cyan/20"
         style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <h3 class="text-sm font-black text-white mb-3 flex items-center gap-2">
                    <i class="fas fa-list-check text-primary-cyan"></i> Selected Titles
                </h3>
                <ul class="space-y-2 max-h-40 overflow-y-auto">
                    <template x-for="(title, idx) in selectedTitles" :key="idx">
                        <li class="text-[11px] text-gray-400 flex items-start gap-2">
                            <button @click="removeTitle(title)" type="button" class="text-red-400 hover:text-red-300 mt-0.5 flex-shrink-0">
                                <i class="fas fa-times text-[9px]"></i>
                            </button>
                            <span class="line-clamp-2" x-text="title"></span>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="lg:col-span-1">
                <h3 class="text-sm font-black text-white mb-3 flex items-center gap-2">
                    <i class="fas fa-tags text-primary-cyan"></i> Combined Keywords
                </h3>
                <div class="flex flex-wrap gap-1.5 min-h-[2rem]">
                    <template x-if="combinedKeywords.length === 0">
                        <span class="text-[11px] text-gray-600 italic">Select titles to extract keywords…</span>
                    </template>
                    <template x-for="kw in combinedKeywords" :key="kw">
                        <span class="text-[10px] px-2 py-0.5 rounded-md font-bold"
                              style="background: rgba(14, 165, 233, 0.08); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.15);"
                              x-text="'#' + kw"></span>
                    </template>
                </div>
            </div>
            <div class="lg:col-span-1" x-show="briefResult">
                <h3 class="text-sm font-black text-white mb-3 flex items-center gap-2">
                    <i class="fas fa-file-lines text-primary-cyan"></i> Content Brief
                </h3>
                <template x-if="briefResult">
                    <div class="space-y-2 text-[11px]" style="color: var(--text-muted);">
                        <p class="font-bold text-white" x-text="briefResult.headline"></p>
                        <p x-text="briefResult.summary"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="glass-card overflow-hidden border border-white/10 relative min-h-[500px]">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-cyan/5 blur-3xl -mr-32 -mt-32 rounded-full"></div>
        
        <!-- Loading Overlay -->
        <div x-show="loading" class="absolute inset-0 z-50 flex flex-col items-center justify-center backdrop-blur-md" style="background: #0a0b0e; display: none;">
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
                    'thresholdModerate' => $thresholdModerate ?? 45,
                    'isInitial' => $isInitial ?? false
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
        isInitial: {{ $isInitial ? 'true' : 'false' }},
        selectedTitles: [],
        combinedKeywords: [],
        generatingBrief: false,
        briefResult: null,
        stats: {
            total: {{ count($googleNews) }},
            high: {{ collect($googleNews)->where('seo_score', '>=', $thresholdHigh)->count() }},
            moderate: {{ collect($googleNews)->where('seo_score', '>=', $thresholdModerate)->where('seo_score', '<', $thresholdHigh)->count() }}
        },
        topicsMap: @json($topicsMap),
        
        init() {
            console.log('News Intelligence Monitor Initialized');
            this.updateStats();
            this.bindSelectionDelegation();
            if (this.isInitial) {
                this.refreshNews(true);
            }
        },

        bindSelectionDelegation() {
            const container = document.getElementById('news-container');
            if (!container || container._selectionBound) return;
            container._selectionBound = true;
            container.addEventListener('change', (e) => {
                if (!e.target.matches('.news-select-checkbox')) return;
                const title = e.target.value;
                if (e.target.checked) {
                    if (!this.selectedTitles.includes(title)) {
                        this.selectedTitles.push(title);
                    }
                } else {
                    this.selectedTitles = this.selectedTitles.filter(t => t !== title);
                }
                this.refreshKeywords();
            });
        },

        syncCheckboxStates() {
            document.querySelectorAll('.news-select-checkbox').forEach(cb => {
                cb.checked = this.selectedTitles.includes(cb.value);
            });
        },

        async refreshKeywords() {
            if (!this.selectedTitles.length) {
                this.combinedKeywords = [];
                return;
            }
            try {
                const res = await fetch('{{ route("dashboard.global-news-monitor.extract-keywords") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ titles: this.selectedTitles })
                });
                const data = await res.json();
                if (data.success) {
                    this.combinedKeywords = data.keywords || [];
                }
            } catch (err) {
                console.error('Keyword extraction failed', err);
            }
        },

        removeTitle(title) {
            this.selectedTitles = this.selectedTitles.filter(t => t !== title);
            this.syncCheckboxStates();
            this.refreshKeywords();
            if (!this.selectedTitles.length) {
                this.briefResult = null;
            }
        },

        clearSelection() {
            this.selectedTitles = [];
            this.combinedKeywords = [];
            this.briefResult = null;
            this.syncCheckboxStates();
        },

        async generateAnalysis() {
            if (!this.selectedTitles.length || this.generatingBrief) return;
            this.generatingBrief = true;
            this.briefResult = null;

            try {
                const res = await fetch('{{ route("dashboard.global-news-monitor.generate-brief") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        titles: this.selectedTitles,
                        keywords: this.combinedKeywords,
                        country: this.region,
                        lang: this.lang,
                        topic: this.topic
                    })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Brief generation failed.');
                }
                this.briefResult = data.brief;
                if (data.keywords) {
                    this.combinedKeywords = data.keywords;
                }
                if (window.VidaCredits) window.VidaCredits.apply(data);

                Swal.fire({
                    title: 'Content Brief Ready',
                    html: this.renderBriefHtml(data.brief),
                    background: '#0d0e12',
                    color: '#fff',
                    width: '560px',
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#0ea5e9'
                });
            } catch (err) {
                Swal.fire('Analysis Error', err.message || 'Could not generate brief.', 'error');
            } finally {
                this.generatingBrief = false;
            }
        },

        renderBriefHtml(brief) {
            if (!brief) return '';
            const list = (items) => (items || []).map(i => `<li class="text-sm text-gray-300">${this.escapeHtml(i)}</li>`).join('');
            const kws = (brief.suggested_keywords || []).map(k => `<span class="text-[10px] px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-400 border border-purple-500/20 mr-1">${this.escapeHtml(k)}</span>`).join('');
            return `
                <div class="text-left space-y-4">
                    <div><div class="text-[9px] font-black text-gray-500 uppercase mb-1">Headline</div><p class="text-white font-bold">${this.escapeHtml(brief.headline || '')}</p></div>
                    <div><div class="text-[9px] font-black text-gray-500 uppercase mb-1">Summary</div><p class="text-sm text-gray-300">${this.escapeHtml(brief.summary || '')}</p></div>
                    <div><div class="text-[9px] font-black text-gray-500 uppercase mb-1">Key Themes</div><ul class="list-disc pl-4">${list(brief.key_themes)}</ul></div>
                    <div><div class="text-[9px] font-black text-gray-500 uppercase mb-1">Content Outline</div><ul class="list-disc pl-4">${list(brief.content_outline)}</ul></div>
                    <div><div class="text-[9px] font-black text-gray-500 uppercase mb-1">Angle</div><p class="text-sm text-white">${this.escapeHtml(brief.recommended_angle || '')}</p></div>
                    <div class="flex flex-wrap gap-1">${kws}</div>
                </div>`;
        },

        escapeHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        },
        
        updateStats() {
            // Count visible items if needed, or update from backend during refresh
            // For now, we update these after each fetch
        },

        getTopicName() {
            return this.topicsMap[this.topic] ? this.topicsMap[this.topic].name : 'World';
        },

        changeRegion(newRegion) {
            if (this.region === newRegion) return;
            this.region = newRegion;
            this.refreshNews(true);
        },

        changeTopic(newTopic) {
            if (this.topic === newTopic) return;
            this.topic = newTopic;
            this.refreshNews(true);
        },


        refreshNews(force = false) {
            this.loading = true;
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
                if (type === 'json' && data.html) {
                    document.getElementById('news-container').innerHTML = data.html;
                    this.isInitial = false;
                    this.bindSelectionDelegation();
                    this.syncCheckboxStates();
                    if (data.stats) {
                        this.stats = data.stats;
                    }
                    if (window.VidaCredits) window.VidaCredits.apply(data);
                } else if (type === 'html') {
                    document.getElementById('news-container').innerHTML = data;
                    this.isInitial = false;
                    this.bindSelectionDelegation();
                    this.syncCheckboxStates();
                    if (window.VidaCredits) window.VidaCredits.refresh();
                }
            })
            .catch((err) => {
                console.error('[News Monitor]', err);
                Swal.fire('Fetch Error', 'Could not load news. Check your connection and click Get News again.', 'error');
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
            if (window.VidaCredits) window.VidaCredits.apply(data);
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
