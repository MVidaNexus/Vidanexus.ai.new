@extends('dramatrends::layouts.master')

@section('title', 'Drama Trends')

@section('content')
<div class="max-w-7xl mx-auto font-tajawal pb-20 px-4" x-data="dramaDashboard()" x-init="init()">

    {{-- ═══════ PREMIUM HERO ═══════ --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-[var(--hero-bg)] border border-[var(--border-glass)] shadow-2xl mb-10 group transition-colors duration-300">
        <!-- Abstract Background Glows -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-accent-purple/10 blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-accent-blue/10 blur-[120px] rounded-full"></div>
        
        <div class="relative p-10 lg:p-14 z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="text-left lg:max-w-2xl" dir="ltr">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--bg-glass)] border border-[var(--border-glass)] text-accent-gold text-xs font-bold mb-6 animate-pulse-subtle">
                    <i class="fas fa-bolt"></i>
                    <span>Real-time Trends Analysis</span>
                </div>
                <h1 class="text-4xl lg:text-5xl font-black text-[var(--text-main)] leading-tight mb-6 font-cairo">
                    Analyzing <span class="bg-gradient-to-r from-accent-gold via-yellow-400 to-amber-500 bg-clip-text text-transparent italic">Search & Trends</span>
                </h1>
                <p class="text-[var(--text-muted)] text-lg leading-relaxed mb-8 font-medium opacity-90">
                    Live and comparative monitoring of series performance during Ramadan 2026. Track audience pulse across search engines and digital viewing platforms accurately.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl p-2 gap-2 pr-4 transition-all focus-within:border-accent-purple/50 focus-within:ring-4 focus-within:ring-purple-500/10">
                        <i class="fas fa-calendar-alt text-accent-purple opacity-70"></i>
                        <input type="date" x-model="startDate" @change="fetchTrends(false)" class="bg-transparent border-none text-[var(--text-main)] text-sm focus:ring-0 w-36 cursor-pointer">
                        <span class="text-[var(--text-muted)] font-bold mx-1">—</span>
                        <input type="date" x-model="endDate" @change="fetchTrends(false)" class="bg-transparent border-none text-[var(--text-main)] text-sm focus:ring-0 w-36 cursor-pointer">
                    </div>
                    
                    <button @click="fetchTrends(false)" :disabled="loading" 
                        class="px-8 py-4 bg-gradient-to-r from-accent-purple to-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-purple-500/20 hover:scale-[1.03] active:scale-95 transition-all disabled:opacity-50 flex items-center gap-3">
                        <i class="fas fa-sync-alt" :class="loading ? 'animate-spin' : ''"></i>
                        <span x-text="loading ? 'Updating...' : 'Update Data'"></span>
                    </button>

                    <button @click="fetchTrends(true)" :disabled="loading" 
                        class="p-4 bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-main)] rounded-2xl hover:bg-[var(--bg-card)] transition-all border-dashed"
                        title="Force Update">
                        <i class="fas fa-bolt" :class="loading ? 'animate-spin' : ''"></i>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <select x-model="episodesFilter" @change="applyFilters()" class="bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-main)] rounded-2xl py-3 px-4 focus:outline-none focus:border-accent-purple transition-all font-bold text-sm cursor-pointer hover:bg-[var(--bg-card)] outline-none" dir="ltr">
                            <option value="all" class="bg-[var(--bg-deep)] text-white">All Series (15 & 30)</option>
                            <option value="15" class="bg-[var(--bg-deep)] text-white">15 Episodes Only</option>
                            <option value="30" class="bg-[var(--bg-deep)] text-white">30 Episodes Only</option>
                        </select>
                        <div class="flex items-center gap-2 bg-emerald-500/10 text-emerald-500 px-4 py-3 rounded-2xl border border-emerald-500/20 font-black text-xs">
                            <i class="fas fa-check-circle"></i>
                            <span>Egyptian Series Only</span>
                        </div>
                    </div>
                </div>
            </div>

             <div class="hidden lg:flex flex-col gap-4 w-64">
                <div class="glass-card p-6 border-accent-blue/20 bg-accent-blue/5 text-left">
                    <div class="flex items-center justify-between mb-4 flex-row-reverse">
                        <div class="w-10 h-10 rounded-xl bg-accent-blue/20 text-accent-blue flex items-center justify-center shadow-lg">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="text-[10px] text-[var(--text-muted)] font-black">LIVE</span>
                    </div>
                    <div class="text-xs text-[var(--text-muted)] font-bold mb-1 uppercase">Update Rate</div>
                    <div class="text-xl font-black text-[var(--text-main)]">Every 30 minutes</div>
                </div>
             </div>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'drama-trends'])



    <div x-show="loading" class="flex flex-col items-center justify-center py-40" x-transition x-cloak>
        <div class="relative w-20 h-20 mb-10">
            <div class="absolute inset-0 rounded-full border-4 border-[var(--border-glass)]"></div>
            <div class="absolute inset-0 rounded-full border-4 border-t-accent-purple animate-spin"></div>
        </div>
        <p class="text-xl font-black text-[var(--text-main)] font-cairo animate-pulse">Fetching search data now...</p>
    </div>

    <div x-show="error && !loading" x-cloak class="flex flex-col items-center justify-center py-32">
        <div class="w-20 h-20 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center text-3xl mb-6 border border-rose-500/20">
            <i class="fas fa-wifi-slash"></i>
        </div>
        <h3 class="text-[var(--text-main)] text-xl font-bold mb-2">Failed to fetch live data</h3>
        <p class="text-[var(--text-muted)] mb-8 max-w-sm text-center font-bold" x-text="error"></p>
        <button @click="fetchTrends(true)" class="px-8 py-3 bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-xl text-[var(--text-main)] font-bold hover:bg-[var(--bg-card)]">Retry</button>
    </div>

    <div x-show="!loading && hasData" x-transition x-cloak dir="ltr">

        <div class="glass-card p-8 mb-10 overflow-hidden relative text-left">
            <div class="absolute top-0 right-0 w-64 h-64 bg-accent-blue/5 blur-[100px] rounded-full"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-accent-blue/10 text-accent-blue flex items-center justify-center shadow-lg border border-accent-blue/20">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        Interest Over Time — Google Trends
                    </h2>
                </div>
                <div style="position: relative; height: 500px; width: 100%;">
                    <canvas id="interestTimelineChart"></canvas>
                </div>
                <div class="custom-legend mt-8 pt-8 border-t border-[var(--border-glass)]" id="customLegend"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-20 text-left">
            <div class="glass-card p-8 group relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-blue-500/5 blur-[80px] rounded-full"></div>
                <div class="flex items-center justify-between mb-8 relative z-10">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center shadow-lg border border-blue-500/20">
                            <i class="fab fa-google"></i>
                        </span>
                        Google Trends Ranking
                    </h2>
                    <button @click="exportCsv('google')" title="Export as CSV Spreadsheet" class="w-10 h-10 rounded-xl bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-muted)] hover:text-emerald-500 hover:border-emerald-500/30 hover:bg-emerald-500/10 flex items-center justify-center transition-all shadow-sm">
                        <i class="fas fa-file-csv text-lg"></i>
                    </button>
                </div>
                <div id="googleRankingList" class="space-y-4 custom-scroll overflow-y-auto pr-2 relative z-10" style="max-height: 700px;"></div>
            </div>

            <div class="glass-card p-8 group relative overflow-hidden">
                <div class="absolute top-0 left-0 w-32 h-32 bg-rose-500/5 blur-[80px] rounded-full"></div>
                <div class="flex items-center justify-between mb-8 relative z-10">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center shadow-lg border border-rose-500/20">
                            <i class="fas fa-play"></i>
                        </span>
                        WATCH IT Ranking
                    </h2>
                    <button @click="exportCsv('watchit')" title="Export as CSV Spreadsheet" class="w-10 h-10 rounded-xl bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-muted)] hover:text-emerald-500 hover:border-emerald-500/30 hover:bg-emerald-500/10 flex items-center justify-center transition-all shadow-sm">
                        <i class="fas fa-file-csv text-lg"></i>
                    </button>
                </div>
                <div id="watchItRankingList" class="space-y-4 custom-scroll overflow-y-auto pr-2 relative z-10" style="max-height: 700px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-card { background: var(--bg-card); backdrop-filter: blur(20px); border: 1px solid var(--border-glass); border-radius: 2.5rem; }
    .ranking-item { display: flex; align-items: center; gap: 20px; padding: 1.5rem; background: var(--bg-glass); border-radius: 1.5rem; border: 1px solid transparent; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .ranking-item:hover { background: var(--bg-card); border-color: var(--border-glass); transform: scale(1.02); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1); }
    .rank-badge { width: 48px; height: 48px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.25rem; font-family: 'Cairo'; flex-shrink: 0; }
    .rank-badge.gold { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); }
    .rank-badge.silver { background: rgba(226, 232, 240, 0.1); color: #94a3b8; border: 1px solid rgba(226, 232, 240, 0.1); }
    .rank-badge.bronze { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.1); }
    .rank-badge.default { background: var(--bg-glass); color: var(--text-muted); }
    .gov-tag { font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; background: var(--bg-glass); color: var(--text-muted); border: 1px solid var(--border-glass); }
    .animate-pulse-subtle { animation: pulse-subtle 3s ease-in-out infinite; }
    @keyframes pulse-subtle { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.8; transform: scale(0.98); } }
    .custom-legend { display: flex; flex-wrap: wrap; gap: 10px; }
    .custom-legend-item { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 12px; background: var(--bg-glass); border: 1px solid var(--border-glass); cursor: pointer; transition: all 0.2s; font-size: 0.75rem; }
    .custom-legend-item:hover { background: var(--bg-card); transform: translateY(-2px); }
    .custom-legend-item .dot { width: 8px; height: 8px; border-radius: 50%; }
    ::-webkit-scrollbar { display: none; }
</style>

    {{-- ═══════ DETAILED INSIGHTS MODAL ═══════ --}}
    <div x-show="showDetailModal" x-cloak x-transition.opacity
        class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xl">
        <div @click.away="showDetailModal = false" x-transition.scale.origin.center
            class="glass-card p-0 w-full max-w-4xl shadow-3xl overflow-hidden border border-accent-blue/20">
            
            {{-- Modal Header --}}
            <div class="p-8 border-b border-[var(--border-glass)] bg-gradient-to-r from-accent-blue/10 to-transparent flex justify-between items-center text-left" dir="ltr">
                <div>
                    <h3 class="text-3xl font-black text-[var(--text-main)] font-cairo mb-2" x-text="selectedSeries"></h3>
                    <p class="text-accent-blue font-bold text-sm tracking-widest uppercase">Detailed Insights & Digital Reach Depth</p>
                </div>
                <button @click="showDetailModal = false" class="w-12 h-12 rounded-2xl bg-[var(--bg-glass)] text-[var(--text-muted)] hover:text-white hover:bg-rose-500 transition-all shadow-xl">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 custom-scroll overflow-y-auto min-h-[400px]" style="max-height: 70vh;">
                
                {{-- Loading State --}}
                <div x-show="seriesDetail.loading" class="col-span-1 md:col-span-2 py-20 flex flex-col items-center justify-center gap-6">
                    <div class="w-20 h-20 border-4 border-accent-blue/20 border-t-accent-blue rounded-full animate-spin"></div>
                    <div class="text-xl font-black text-[var(--text-main)] font-cairo animate-pulse">Fetching and analyzing geographic data...</div>
                </div>

                <template x-if="!seriesDetail.loading">
                    <div class="contents">
                        {{-- Left: Governorates --}}
                        <div class="space-y-6 text-left" dir="ltr">
                            <div class="flex items-center justify-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-accent-gold/10 text-accent-gold flex items-center justify-center"><i class="fas fa-map-marker-alt"></i></div>
                                <h4 class="text-xl font-black text-[var(--text-main)] font-cairo">Top Searching Governorates</h4>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="(gov, idx) in seriesDetail.governorates" :key="idx">
                                    <div class="relative pt-1">
                                        <div class="flex items-center justify-between mb-2 px-1">
                                            <span class="text-sm font-black text-[var(--text-main)]" x-text="gov.name"></span>
                                            <span class="text-xs font-black text-accent-gold" x-text="gov.value + '%'"></span>
                                        </div>
                                        <div class="overflow-hidden h-2.5 text-xs flex rounded-full bg-[var(--bg-glass)] border border-[var(--border-glass)]">
                                            <div :style="`width: ${gov.value}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-accent-blue to-accent-purple transition-all duration-1000"></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!seriesDetail || !seriesDetail.governorates || seriesDetail.governorates.length === 0">
                                    <div class="py-10 text-center opacity-30 italic text-[var(--text-muted)]">Not enough geographic data currently available</div>
                                </template>
                            </div>
                        </div>

                        {{-- Right: Trending Episodes / Related Queries --}}
                        <div class="space-y-6 text-left" dir="ltr">
                            <div class="flex items-center justify-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-accent-purple/10 text-accent-purple flex items-center justify-center"><i class="fas fa-search-plus"></i></div>
                                <h4 class="text-xl font-black text-[var(--text-main)] font-cairo">Top Searched Episodes & Queries</h4>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <template x-for="(q, idx) in seriesDetail.queries" :key="idx">
                                    <div class="px-5 py-4 rounded-2xl bg-[var(--bg-glass)] border border-[var(--border-glass)] hover:border-accent-purple/40 transition-all group flex-1 min-w-[140px]">
                                        <div class="text-[var(--text-muted)] text-[9px] font-black mb-1 opacity-60 uppercase">Keyword</div>
                                        <div class="text-[var(--text-main)] font-black text-sm mb-2" x-text="q.query"></div>
                                        <div class="inline-flex px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-500 text-[10px] font-black" x-text="q.value"></div>
                                    </div>
                                </template>
                                <template x-if="!seriesDetail || !seriesDetail.queries || seriesDetail.queries.length === 0">
                                    <div class="w-full py-10 text-center opacity-30 italic text-[var(--text-muted)]">No keywords showing significant growth</div>
                                </template>
                            </div>

                            <div class="p-6 rounded-3xl bg-blue-500/5 border border-blue-500/10 mt-8">
                                <div class="flex gap-4">
                                    <div class="text-blue-500 text-xl pt-1"><i class="fas fa-lightbulb"></i></div>
                                    <div>
                                        <h5 class="text-blue-500 font-black text-sm mb-1 uppercase tracking-wider">Analytical Recommendation</h5>
                                        <p class="text-[var(--text-main)] text-xs font-bold leading-relaxed opacity-80">
                                            This distribution indicates the extent of the work's penetration in provinces compared to the capital. The increased search for episode numbers reflects the level of "deep follow-up" and interest in dramatic development.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-8 border-t border-[var(--border-glass)] flex justify-end">
                <button @click="showDetailModal = false" class="px-10 py-4 bg-accent-blue text-white font-black rounded-2xl hover:scale-105 transition-all shadow-xl shadow-blue-500/20">
                    Close Window and Return to Summary
                </button>
            </div>
        </div>
    </div>
@push('scripts')
<script>
const SERIES_COLORS = ['#ff4b4b', '#0ea5e9', '#fbbf24', '#8b5cf6', '#10b981', '#ec4899', '#3b82f6', '#ef4444', '#06b6d4', '#a855f7', '#f97316', '#14b8a6', '#e879f9', '#2563eb', '#84cc16'];
function dramaDashboard() {
    return {
        startDate: '2026-02-19', endDate: '2026-03-19',
        trendsData: {}, originalData: null, episodesFilter: 'all', lineChart: null,
        showDetailModal: false, selectedSeries: null, seriesDetail: { loading: false, governorates: [], queries: [] },
        init() { this.fetchTrends(false); },
        fetchTrends(forceRefresh) {
            this.loading = true; this.error = null;
            fetch('{{ route("dashboard.drama-trends.trends-data") }}', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ startDate: this.startDate, endDate: this.endDate, forceRefresh: forceRefresh })
            })
            .then(r => r.ok ? r.json() : r.json().then(d => { throw new Error(d.error || 'Error'); }))
            .then(data => {
                if (data.error) { 
                    this.error = data.error; 
                    this.hasData = false;
                } 
                else {
                    this.originalData = data;
                    this.applyFilters();
                }
                this.loading = false;
            })
            .catch(err => { this.error = err.message; this.loading = false; });
        },
        applyFilters() {
            if (!this.originalData) return;
            
            let filteredSeries = this.originalData.series || [];
            if (this.episodesFilter !== 'all') {
                const ep = parseInt(this.episodesFilter);
                filteredSeries = filteredSeries.filter(s => parseInt(s.episodes) === ep);
            }
            
            const allowedNames = filteredSeries.map(s => s.name);
            
            this.trendsData = {
                ...this.originalData,
                series: filteredSeries,
                google_trends: (this.originalData.google_trends || []).filter(s => allowedNames.includes(s.name)),
                watchit_ranking: (this.originalData.watchit_ranking || []).filter(s => allowedNames.includes(s.name))
            };
            
            this.isSimulated = this.trendsData.is_simulated || false; 
            this.insightSummary = this.trendsData.insight_summary || null; 
            this.hasData = !!(this.trendsData.series && this.trendsData.series.length > 0);
            this.$nextTick(() => this.renderAll());
        },
        renderAll() { 
            try { this.renderLineChart(); this.renderGoogleRanking(); this.renderWatchItRanking(); } 
            catch(e) { console.error(e); }
        },
        renderLineChart() {
            try {
                const ctx = document.getElementById('interestTimelineChart'); if (!ctx) return;
                if (this.lineChart) this.lineChart.destroy();
                const tc = typeof getChartColors === 'function' ? getChartColors() : { text: '#ccc', grid: 'rgba(255,255,255,0.05)' };
                const labels = this.trendsData.timeline || []; if (labels.length === 0) return;
                const datasets = (this.trendsData.series || []).map((s, i) => ({
                    label: s.name, data: this.trendsData.timeline_data[s.name] || [],
                    borderColor: SERIES_COLORS[i % SERIES_COLORS.length], backgroundColor: 'transparent',
                    borderWidth: 4, tension: 0.4, pointRadius: 0, hoverRadius: 8, hidden: i >= 5
                }));
                this.lineChart = new Chart(ctx, {
                    type: 'line', data: { labels, datasets },
                    options: {
                        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.95)', rtl: true } },
                        scales: {
                            x: { ticks: { color: tc.text, font: { family: 'Tajawal', weight: 'bold' } }, grid: { display: false } },
                            y: { min: 0, max: 100, ticks: { color: tc.text, font: { weight: 'bold' } }, grid: { color: tc.grid } }
                        }
                    }
                });
                document.getElementById('customLegend').innerHTML = (this.trendsData.series || []).map((s, i) => `
                    <div class="custom-legend-item" onclick="toggleDataset(${i})" id="legend-${i}" style="opacity: ${i >= 5 ? '0.3' : '1'}">
                        <span class="dot" style="background: ${SERIES_COLORS[i % SERIES_COLORS.length]}"></span>
                        <span class="text-[var(--text-main)] font-black">${s.name}</span>
                    </div>
                `).join('');
            } catch(e) { console.error(e); }
        },
        renderGoogleRanking() {
            try {
                const el = document.getElementById('googleRankingList'); if (!el) return;
                const seriesMap = {}; this.trendsData.series.forEach(s => seriesMap[s.name] = s);
                el.innerHTML = (this.trendsData.google_trends || []).map((s, i) => {
                    const rankClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : 'default'));
                    const govs = s.top_govs || [];
                    const tags = govs.map(g => {
                        const name = typeof g === 'object' ? g.name : g;
                        const val = (typeof g === 'object' && g.value) ? ` (${g.value}%)` : '';
                        return `<span class="gov-tag">${name}${val}</span>`;
                    }).join('');
                    const info = seriesMap[s.name] || {};
                    const epTag = info.episodes ? `<span class="text-[10px] bg-[var(--bg-glass)] px-2 py-0.5 rounded border border-[var(--border-glass)] mr-2">${info.episodes} Episodes</span>` : '';
                    const topEp = s.top_episode ? `<div class="mt-2 text-[10px] text-emerald-500 font-bold"><i class="fas fa-search mr-1"></i> Most Searched: ${s.top_episode}</div>` : '';

                    return `
                    <div @click="openSeriesDetails('${s.name}')" class="ranking-item group cursor-pointer">
                        <div class="rank-badge ${rankClass}">${s.rank}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[var(--text-main)] font-black font-cairo text-lg mb-1">${s.name} ${epTag}</div>
                            <div class="flex items-center gap-2">${tags}</div>
                            ${topEp}
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <div class="text-accent-gold font-black text-xl font-cairo shadow-sm">${s.score}%</div>
                            <div class="text-[9px] text-accent-blue font-black opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 italic" dir="ltr">View Insights <i class="fas fa-chevron-right ml-1"></i></div>
                        </div>
                    </div>`;
                }).join('');
            } catch(e) { console.error(e); }
        },
        renderWatchItRanking() {
            try {
                const el = document.getElementById('watchItRankingList'); if (!el) return;
                const seriesMap = {}; this.trendsData.series.forEach(s => seriesMap[s.name] = s);
                el.innerHTML = (this.trendsData.watchit_ranking || []).map((s, i) => {
                    const rankClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : 'default'));
                    const info = seriesMap[s.name] || {};
                    const epTag = info.episodes ? `<span class="text-[10px] bg-[var(--bg-glass)] px-2 py-0.5 rounded border border-[var(--border-glass)] mr-2">${info.episodes} Episodes</span>` : '';
                    const govs = s.top_govs || [];
                    const tags = govs.map(g => `<span class="gov-tag">${g}</span>`).join('');
                    const topEp = s.top_episode ? `<div class="mt-2 text-[10px] text-emerald-500 font-bold"><i class="fas fa-search mr-1"></i> Most Searched: ${s.top_episode}</div>` : '';

                    return `
                    <div @click="openSeriesDetails('${s.name}')" class="ranking-item group cursor-pointer">
                        <div class="rank-badge ${rankClass}">${s.rank}</div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[var(--text-main)] font-black font-cairo text-lg mb-1">${s.name} ${epTag}</div>
                            <div class="flex items-center gap-3 text-[11px] text-[var(--text-muted)] font-black opacity-80 group-hover:opacity-100 transition-opacity">
                                <span><i class="fas fa-star text-accent-gold mr-1"></i> ${s.lead || '-'}</span>
                                <span class="w-1 h-1 bg-[var(--border-glass)] rounded-full"></span>
                                <span>${s.company || '-'}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">${tags}</div>
                            ${topEp}
                        </div>
                        <div class="flex flex-col items-center gap-1">
                             <div class="text-rose-500 font-black text-xs tracking-widest bg-rose-500/10 px-2 py-1 rounded-lg border border-rose-500/20 shadow-sm">TOP ${s.rank}</div>
                             <div class="text-[9px] text-accent-blue font-black opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0 italic" dir="ltr">View Insights <i class="fas fa-chevron-right ml-1"></i></div>
                        </div>
                    </div>`;
                }).join('');
            } catch(e) { console.error(e); }
        },
        async openSeriesDetails(name) {
            this.selectedSeries = name;
            this.showDetailModal = true;
            this.seriesDetail = { loading: true, governorates: [], queries: [] };

            try {
                const response = await fetch('{{ route("dashboard.drama-trends.series-details") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: name, startDate: this.startDate, endDate: this.endDate })
                });
                const data = await response.json();
                this.seriesDetail = { ...data, loading: false };
            } catch (err) {
                console.error(err);
                this.seriesDetail = { loading: false, governorates: [], queries: [] };
            }
        },
        exportCsv(source) {
            let data = [];
            let filename = '';
            
            if (source === 'google') {
                data = this.trendsData.google_trends || [];
                filename = 'google_trends_ranking.csv';
            } else {
                data = this.trendsData.watchit_ranking || [];
                filename = 'watchit_ranking.csv';
            }

            if (data.length === 0) {
                alert('No data available to export');
                return;
            }

            let csvContent = '\uFEFF'; 
            csvContent += ['Rank', 'Series Name', source === 'google' ? 'Interest Rate (%)' : 'Source', 'Lead Actor', 'Production Company', 'Episodes', 'Top Governorates', 'Most Searched'].join(',') + '\n';
            
            data.forEach(s => {
                const info = this.trendsData.series.find(x => x.name === s.name) || {};
                const rank = s.rank || '';
                const name = `"${s.name || ''}"`;
                const value = source === 'google' ? (s.score || '0') : `"${s.source || ''}"`;
                const lead = `"${s.lead || info.lead || ''}"`;
                const company = `"${s.company || info.company || ''}"`;
                const eps = s.episodes || info.episodes || '';
                const govs = `"${(s.top_govs || []).map(g => typeof g === 'object' ? `${g.name} (${g.value}%)` : g).join(' - ')}"`;
                const topEp = `"${s.top_episode || ''}"`;
                
                csvContent += [rank, name, value, lead, company, eps, govs, topEp].join(',') + '\n';
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };
}
function toggleDataset(index) {
    const chart = Chart.getChart('interestTimelineChart'); if (!chart) return;
    const meta = chart.getDatasetMeta(index); meta.hidden = !meta.hidden; chart.update();
    const item = document.getElementById('legend-' + index); if (item) item.style.opacity = meta.hidden ? '0.3' : '1';
}
</script>
@endpush
@endsection
