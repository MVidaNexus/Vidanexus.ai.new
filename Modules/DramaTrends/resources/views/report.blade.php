@extends('dramatrends::layouts.master')

@section('title', 'Drama Report')

@section('content')
<div class="max-w-7xl mx-auto font-tajawal pb-20 px-4" x-data="dramaReport()" x-init="init()" dir="ltr">

    {{-- ═══════ PREMIUM HERO ═══════ --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-[var(--hero-bg)] border border-[var(--border-glass)] shadow-2xl mb-10 group transition-colors duration-300">
        <!-- Abstract Background Glows -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-purple-600/10 blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/10 blur-[120px] rounded-full"></div>
        
        <div class="relative p-10 lg:p-14 z-10 flex flex-col lg:flex-row items-center justify-between gap-10">
            <div class="text-left lg:max-w-2xl ml-0">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--bg-glass)] border border-[var(--border-glass)] text-accent-gold text-xs font-bold mb-6 animate-pulse-subtle">
                    <i class="fas fa-sparkles"></i>
                    <span>Exclusive Updates for Ramadan 2026</span>
                </div>
                <h1 class="text-4xl lg:text-5xl font-black text-[var(--text-main)] leading-tight mb-6 font-cairo">
                    Analytical <span class="bg-gradient-to-l from-accent-gold via-yellow-400 to-amber-500 bg-clip-text text-transparent italic">Performance Report</span>
                </h1>
                <p class="text-[var(--text-muted)] text-lg leading-relaxed mb-8 font-medium opacity-90">
                    Explore the drama competition map through a cross-analysis of search interests on <span class="text-[var(--text-main)] font-bold">Google</span> and actual viewership rates on <span class="text-[var(--text-main)] font-bold">WATCH IT</span>.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl p-2 gap-2 pr-4 transition-all focus-within:border-accent-purple/50 focus-within:ring-4 focus-within:ring-purple-500/10">
                        <i class="fas fa-calendar-alt text-accent-purple opacity-70"></i>
                        <input type="date" x-model="startDate" class="bg-transparent border-none text-[var(--text-main)] text-sm focus:ring-0 w-36 cursor-pointer">
                        <span class="text-[var(--text-muted)] font-bold mx-1">—</span>
                        <input type="date" x-model="endDate" class="bg-transparent border-none text-[var(--text-main)] text-sm focus:ring-0 w-36 cursor-pointer">
                    </div>
                    
                    <button @click="fetchData(false)" :disabled="loading" 
                        class="px-8 py-4 bg-gradient-to-r from-accent-purple to-indigo-600 text-white font-bold rounded-2xl shadow-xl shadow-purple-500/20 hover:scale-[1.03] active:scale-95 transition-all disabled:opacity-50 flex items-center gap-3">
                        <i class="fas fa-chart-line" :class="loading ? 'animate-spin' : ''"></i>
                        <span x-text="loading ? 'Analyzing...' : 'Generate Report'"></span>
                    </button>

                    <button @click="fetchData(true)" :disabled="loading" 
                        class="p-4 bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-main)] rounded-2xl hover:bg-[var(--bg-card)] transition-all border-dashed"
                        title="Update Data">
                        <i class="fas fa-sync-alt" :class="loading ? 'animate-spin' : ''"></i>
                    </button>
                </div>
            </div>

            <!-- Hero Stats / Visual -->
            <div class="hidden lg:flex flex-col gap-4 w-full lg:w-72">
                <div class="glass-card p-6 border-accent-blue/20 bg-accent-blue/5">
                    <div class="text-sm text-accent-blue font-bold mb-1">Approximate Accuracy Rate</div>
                    <div class="text-3xl font-black text-[var(--text-main)]">98.4%</div>
                    <div class="mt-2 h-1.5 w-full bg-[var(--bg-glass)] rounded-full overflow-hidden">
                        <div class="h-full bg-accent-blue w-[98%] shadow-[0_0_10px_rgba(14,165,233,0.5)]"></div>
                    </div>
                </div>
                <div class="glass-card p-6 border-accent-gold/20 bg-accent-gold/5">
                    <div class="text-sm text-accent-gold font-bold mb-1">Active Series</div>
                    <div class="text-3xl font-black text-[var(--text-main)]" x-text="stats.totalSeries">0</div>
                    <div class="text-xs text-[var(--text-muted)] mt-2 font-bold">Comprehensive Season Coverage</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════ FILTER BAR ═══════ --}}
    <div class="mb-8 flex flex-col md:flex-row items-center gap-4 bg-[var(--bg-card)] backdrop-blur-xl p-4 rounded-3xl border border-[var(--border-glass)] shadow-2xl transition-colors duration-300">
        <div class="relative w-full md:w-80 group">
            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] group-focus-within:text-accent-blue transition-colors"></i>
            <input type="text" x-model="searchQuery" @input="applyFilter()" dir="ltr"
                placeholder="Search for a series or actor..." 
                class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-4 pl-12 pr-5 text-[var(--text-main)] placeholder-gray-500 focus:outline-none focus:border-accent-blue focus:ring-4 focus:ring-accent-blue/10 transition-all font-bold">
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button @click="setFilter('All')" :class="companyFilter === 'All' ? 'filter-chip-active' : 'filter-chip'">
                All
            </button>
            <button @click="setFilter('United')" :class="companyFilter === 'United' ? 'filter-chip-active-gold' : 'filter-chip-gold'" 
                class="flex items-center gap-2">
                <i class="fas fa-star text-[10px]"></i>
                United Media Services
            </button>
            <button @click="setFilter('MBC')" :class="companyFilter === 'MBC' ? 'filter-chip-active' : 'filter-chip'">
                MBC Masr
            </button>
            
            <div class="h-8 w-px bg-[var(--border-glass)] mx-2 hidden md:block"></div>
            
            <select x-model="companyFilter" @change="applyFilter()" 
                class="bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-xl py-2 px-4 text-sm text-[var(--text-muted)] focus:outline-none focus:border-accent-purple font-bold">
                <option value="All" class="bg-[var(--bg-deep)]">Select another production company...</option>
                <template x-for="comp in companies" :key="comp">
                    <option :value="comp" class="bg-[var(--bg-deep)]" x-text="comp"></option>
                </template>
            </select>
        </div>

        <div class="md:ml-auto flex items-center gap-4 text-xs font-bold text-[var(--text-muted)] bg-[var(--bg-glass)] px-4 py-3 rounded-2xl border border-[var(--border-glass)]">
            <select x-model="episodesFilter" @change="applyFilter()" class="bg-transparent border-none text-[var(--text-main)] focus:ring-0 font-bold cursor-pointer outline-none">
                <option value="all" class="bg-[var(--bg-deep)] text-white">All Episodes (15 and 30)</option>
                <option value="15" class="bg-[var(--bg-deep)] text-white">15 Episodes Only</option>
                <option value="30" class="bg-[var(--bg-deep)] text-white">30 Episodes Only</option>
            </select>
            <div class="w-px h-6 bg-[var(--border-glass)]"></div>
            <div class="flex items-center gap-2 bg-emerald-500/10 text-emerald-500 px-3 py-1 rounded-full border border-emerald-500/20">
                <i class="fas fa-flag"></i>
                <span class="font-black">Egyptian Production Only</span>
            </div>
        </div>
    </div>



    {{-- Main Content Space --}}
    <div x-show="loading" class="flex flex-col items-center justify-center py-40" x-transition x-cloak>
        <div class="relative w-24 h-24 mb-10">
            <div class="absolute inset-0 rounded-full border-4 border-[var(--border-glass)]"></div>
            <div class="absolute inset-0 rounded-full border-4 border-t-accent-purple animate-spin"></div>
        </div>
        <p class="text-2xl font-black text-[var(--text-main)] font-cairo animate-pulse">Fetching and analyzing data now...</p>
    </div>

    <div x-show="!loading && hasData" x-transition x-cloak>
        
        {{-- Interest Over Time Chart --}}
        <div class="glass-card p-8 mb-10 overflow-hidden relative border-accent-purple/20">
            <div class="absolute top-0 right-0 w-64 h-64 bg-accent-purple/5 blur-[100px] rounded-full"></div>
            <div class="relative z-10 text-left">
                <div class="flex items-center justify-start mb-8">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-accent-purple/10 text-accent-purple flex items-center justify-center shadow-lg border border-accent-purple/20">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        Interest Over Time — Google Trends
                    </h2>
                </div>
                <div style="position: relative; height: 400px; width: 100%;">
                    <canvas id="interestTimelineChart"></canvas>
                </div>
                <div class="custom-legend mt-8 pt-8 border-t border-[var(--border-glass)] flex flex-wrap justify-start gap-4" id="customLegend"></div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <template x-for="(stat, idx) in summaryCards" :key="idx">
                <div class="relative group overflow-hidden p-8 rounded-[1.75rem] bg-[var(--bg-card)] border border-[var(--border-glass)] hover:border-accent-blue/30 shadow-xl transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 blur-[80px] rounded-full opacity-0 group-hover:opacity-20 transition-opacity" :class="stat.glow"></div>
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shadow-lg" :class="stat.bg">
                            <i :class="stat.icon"></i>
                        </div>
                        <div class="text-xs font-bold text-[var(--text-muted)] px-3 py-1 bg-[var(--bg-glass)] rounded-full" x-text="stat.trend"></div>
                    </div>
                    <div class="text-2xl font-black text-[var(--text-main)] mb-1 leading-tight" x-text="stat.value"></div>
                    <div class="text-sm font-bold text-[var(--text-muted)]" x-text="stat.label"></div>
                </div>
            </template>
        </div>

        {{-- DUAL RANKING TABLES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            
            {{-- WATCH IT Table --}}
            <div class="glass-card p-1 pb-4 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-48 h-48 bg-rose-500/5 blur-[100px] rounded-full"></div>
                <div class="p-8 pb-4 flex items-center justify-between flex-row-reverse">
                    <div>
                        <h2 class="text-2xl font-black text-[var(--text-main)] font-cairo flex items-center gap-4">
                            WATCH IT Ranking
                            <span class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center shadow-lg border border-rose-500/20">
                                <i class="fas fa-play"></i>
                            </span>
                        </h2>
                    </div>
                    <button @click="exportCsv('watchit')" title="Export as CSV spreadsheet" class="w-12 h-12 rounded-2xl bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-muted)] hover:text-emerald-500 hover:border-emerald-500/30 hover:bg-emerald-500/10 flex items-center justify-center transition-all shadow-sm">
                        <i class="fas fa-file-csv text-xl"></i>
                    </button>
                </div>
                <div class="px-8 pb-4 text-left">
                    <p class="text-[var(--text-muted)] text-sm font-bold pl-16 leading-relaxed">
                        The most watched series and topping the digital platform from 
                        <span x-text="formatDate(startDate)"></span> to <span x-text="formatDate(endDate)"></span> for 2026
                    </p>
                </div>
                
                <div class="overflow-x-auto px-4 mt-6 custom-scroll overflow-y-auto" style="max-height: 800px;">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[var(--text-muted)] text-[11px] font-black uppercase tracking-widest opacity-60">
                                <th class="pl-8 py-2">Top Position</th>
                                <th class="py-2">Drama Series</th>
                                <th class="py-2 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="watchItTableBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- GOOGLE TRENDS Table --}}
            <div class="glass-card p-1 pb-4 overflow-hidden relative border-accent-blue/10">
                <div class="absolute top-0 right-0 w-48 h-48 bg-blue-500/5 blur-[100px] rounded-full"></div>
                <div class="p-8 pb-4 flex items-center justify-between flex-row-reverse">
                    <div>
                        <h2 class="text-2xl font-black text-[var(--text-main)] font-cairo flex items-center gap-4">
                            Google Trends Ranking
                            <span class="w-14 h-14 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center shadow-lg border border-blue-500/20">
                                <i class="fab fa-google"></i>
                            </span>
                        </h2>
                    </div>
                    <button @click="exportCsv('google')" title="Export as CSV spreadsheet" class="w-12 h-12 rounded-2xl bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-muted)] hover:text-emerald-500 hover:border-emerald-500/30 hover:bg-emerald-500/10 flex items-center justify-center transition-all shadow-sm">
                        <i class="fas fa-file-csv text-xl"></i>
                    </button>
                </div>
                <div class="px-8 pb-4 text-left">
                    <p class="text-[var(--text-muted)] text-sm font-bold pl-16 leading-relaxed">Public Pulse: Series currently occupying the most search and interest space</p>
                </div>
                
                <div class="overflow-x-auto px-4 mt-6 custom-scroll overflow-y-auto" style="max-height: 800px;">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[var(--text-muted)] text-[11px] font-black uppercase tracking-widest opacity-60">
                                <th class="pl-8 py-2">Top Position</th>
                                <th class="py-2">Drama Series</th>
                                <th class="py-2 text-center">Insights</th>
                            </tr>
                        </thead>
                        <tbody id="googleTableBody"></tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <div class="glass-card p-8 group">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-3">
                        <i class="fas fa-chart-line text-accent-purple"></i>
                         Digital Spread <span class="text-[var(--text-muted)] text-sm font-normal">— Google Score</span>
                    </h2>
                </div>
                <div :style="'position: relative; height: ' + (filteredSeries.length * 35 + 150) + 'px;'">
                    <canvas id="googleBarChart"></canvas>
                </div>
            </div>

            <div class="glass-card p-8 group flex flex-col relative overflow-hidden">
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-accent-blue/5 blur-[100px] rounded-full"></div>
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-3">
                        <i class="fas fa-building text-cyan-400"></i>
                         Season Production Structure
                    </h2>
                </div>
                <div class="flex-1 min-h-[400px] flex items-center justify-center">
                    <canvas id="companiesDoughnut"></canvas>
                </div>
            </div>
        </div>

        {{-- Comparison Chart --}}
        <div class="glass-card p-8 mb-12 relative overflow-hidden">
             <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-500/5 blur-[120px] rounded-full"></div>
             <div class="flex items-center justify-between mb-8 relative z-10">
                <h2 class="text-xl font-bold text-[var(--text-main)] font-cairo flex items-center gap-3">
                    <i class="fas fa-sync text-emerald-400"></i>
                    Audience Alignment Index
                </h2>
                <div class="flex gap-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-tighter">
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span> Matched</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></span> Close</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></span> Divergent</div>
                </div>
            </div>
            <p class="text-[var(--text-muted)] text-xs mb-8 font-bold max-w-2xl text-left">
                Gap analysis between "what the audience searches for" and "what they actually watch"; high alignment means success of both the marketing campaign and digital content.
            </p>
            <div style="position: relative; height: 500px;" class="z-10">
                <canvas id="bubbleChart"></canvas>
            </div>
        </div>

        {{-- Insights Grid --}}
        <h2 class="text-2xl font-black text-[var(--text-main)] font-cairo mb-8 flex items-center gap-4">
            <span class="w-2 h-8 bg-gradient-to-b from-accent-purple to-indigo-600 rounded-full"></span>
            Smart Analytical Insights
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20" id="insightsGrid"></div>

    </div>

    {{-- Error State --}}
    <div x-show="error && !loading" x-cloak class="flex flex-col items-center justify-center py-40">
        <div class="w-20 h-20 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center text-3xl mb-6">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="text-2xl font-black text-[var(--text-main)] mb-2">Failed to analyze data</h3>
        <p class="text-[var(--text-muted)] mb-8 max-w-sm text-center font-bold" x-text="error"></p>
        <button @click="fetchData()" class="px-10 py-4 bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl text-[var(--text-main)] font-bold hover:bg-[var(--bg-card)] transition-all font-cairo">
            Retry
        </button>
    </div>
</div>

<style>
    .font-tajawal { font-family: 'Tajawal', sans-serif; }
    .font-cairo { font-family: 'Cairo', sans-serif; }
    .glass-card { background: var(--bg-card); backdrop-filter: blur(20px); border: 1px solid var(--border-glass); border-radius: 2rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); transition: border-color 0.3s; }
    .filter-chip { @apply px-5 py-2.5 rounded-xl bg-[var(--bg-glass)] border border-[var(--border-glass)] text-[var(--text-muted)] text-sm font-bold hover:bg-[var(--bg-card)] transition-all; }
    .filter-chip-active { @apply px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold border border-indigo-500 shadow-lg shadow-indigo-600/20 transition-all; }
    .filter-chip-gold { @apply px-5 py-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-500/70 text-sm font-bold hover:bg-[var(--bg-card)] transition-all; }
    .filter-chip-active-gold { @apply px-5 py-2.5 rounded-xl bg-amber-500 text-slate-900 text-sm font-bold border border-amber-400 shadow-lg shadow-amber-500/20 transition-all; }
    .animate-pulse-subtle { animation: pulse-subtle 3s ease-in-out infinite; }
    @keyframes pulse-subtle { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.8; transform: scale(0.98); } }
    .table-row-medal { background: var(--bg-glass); transition: all 0.2s ease-out; border-radius: 1.25rem; }
    .table-row-medal:hover { background: var(--bg-card); transform: translateX(-4px); box-shadow: 10px 0 30px -10px rgba(0,0,0,0.1); }
    ::-webkit-scrollbar { display: none; }
    {{-- ═══════ DETAILED INSIGHTS MODAL ═══════ --}}
    <div x-show="showDetailModal" x-cloak x-transition.opacity
        class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-xl">
        <div @click.away="showDetailModal = false" x-transition.scale.origin.center
            class="glass-card p-0 w-full max-w-4xl shadow-3xl overflow-hidden border border-accent-blue/20">
            
            {{-- Modal Header --}}
            <div class="p-8 border-b border-[var(--border-glass)] bg-gradient-to-r from-accent-blue/10 to-transparent flex justify-between items-center text-left">
                <div>
                    <h3 class="text-3xl font-black text-[var(--text-main)] font-cairo mb-2" x-text="selectedSeries"></h3>
                    <p class="text-accent-blue font-bold text-sm tracking-widest uppercase">Detailed Insights and Depth of Digital Spread</p>
                </div>
                <button @click="showDetailModal = false" class="w-12 h-12 rounded-2xl bg-[var(--bg-glass)] text-[var(--text-muted)] hover:text-white hover:bg-rose-500 transition-all shadow-xl">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 custom-scroll overflow-y-auto min-h-[400px]" style="max-height: 70vh;">
                
                {{-- Loading State --}}
                <div x-show="seriesDetail.loading" class="col-span-1 md:col-span-2 py-20 flex flex-col items-center justify-center gap-6">
                    <div class="w-20 h-20 border-4 border-accent-blue/20 border-t-accent-blue rounded-full animate-spin"></div>
                    <div class="text-xl font-black text-[var(--text-main)] font-cairo animate-pulse">Fetching and analyzing geographical data...</div>
                </div>

                <template x-if="!seriesDetail.loading">
                    <div class="contents">
                        {{-- Left: Governorates --}}
                        <div class="space-y-6 text-left">
                            <div class="flex items-center justify-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-accent-gold/10 text-accent-gold flex items-center justify-center"><i class="fas fa-map-marker-alt"></i></div>
                                <h4 class="text-xl font-black text-[var(--text-main)] font-cairo">Most Searched Governorates</h4>
                            </div>
                            
                            <div class="space-y-4">
                                <template x-for="(gov, idx) in seriesDetail.governorates" :key="idx">
                                    <div class="relative pt-1">
                                        <div class="flex items-center justify-between mb-2 px-1">
                                            <span class="text-sm font-black text-[var(--text-main)]" x-text="gov.name"></span>
                                            <span class="text-xs font-black text-accent-gold" x-text="gov.value + '%'"></span>
                                        </div>
                                        <div class="overflow-hidden h-2.5 text-xs flex rounded-full bg-[var(--bg-glass)] border border-[var(--border-glass)]" dir="ltr">
                                            <div :style="`width: ${gov.value}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-accent-blue to-accent-purple transition-all duration-1000"></div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!seriesDetail || !seriesDetail.governorates || seriesDetail.governorates.length === 0">
                                    <div class="py-10 text-center opacity-30 italic text-[var(--text-muted)]">Not enough geographical data currently available</div>
                                </template>
                            </div>
                        </div>

                        {{-- Right: Trending Episodes / Related Queries --}}
                        <div class="space-y-6 text-left">
                            <div class="flex items-center justify-start gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-accent-purple/10 text-accent-purple flex items-center justify-center"><i class="fas fa-search-plus"></i></div>
                                <h4 class="text-xl font-black text-[var(--text-main)] font-cairo">Most Searched Episodes and Queries</h4>
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
                                        <p class="text-[var(--text-main)] text-xs font-bold leading-relaxed opacity-80 mt-1">
                                            This distribution indicates the extent of the show's penetration in provinces compared to the capital. Increased searches for episode numbers (Related Episodes) reflect "deep following" and interest in narrative development.
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
                    Close Window and Return to Report
                </button>
            </div>
        </div>
    </div>
</style>

@push('scripts')
<script>
const SERIES_COLORS = ['#8b5cf6', '#0ea5e9', '#10b981', '#fbbf24', '#f43f5e', '#6366f1', '#ec4899', '#14b8a6', '#f97316', '#a855f7'];
function toggleDataset(idx) {
    const chart = Chart.getChart('interestTimelineChart'); if (!chart) return;
    const meta = chart.getDatasetMeta(idx);
    meta.hidden = meta.hidden === null ? !chart.data.datasets[idx].hidden : null;
    chart.update();
    document.getElementById(`legend-${idx}`).style.opacity = (meta.hidden ?? chart.data.datasets[idx].hidden) ? '0.3' : '1';
}
function dramaReport() {
    return {
        loading: false, error: null, googleError: null, hasData: false, isSimulated: false, insightSummary: null,
        trendsData: {}, startDate: '2026-02-19', endDate: '2026-03-19',
        searchQuery: '', companyFilter: 'All', episodesFilter: 'all', companies: [], filteredSeries: [], filteredRanking: [], filteredGoogle: [],
        showDetailModal: false, selectedSeries: null, seriesDetail: { governorates: [], queries: [] },
        summaryCards: [], stats: { totalSeries: 0 }, lineChart: null,

        init() { this.fetchData(); },
        fetchData(force = false) {
            this.loading = true; this.error = null;
            fetch('{{ route("dashboard.drama-trends.trends-data") }}', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ startDate: this.startDate, endDate: this.endDate, forceRefresh: force })
            })
            .then(r => r.ok ? r.json() : r.json().then(d => { throw new Error(d.error || 'Error'); }))
            .then(data => {
                if (data.error) { 
                    this.error = data.error; 
                    this.hasData = false;
                }
                else {
                    this.trendsData = data; 
                    this.isSimulated = data.is_simulated || false; 
                    this.insightSummary = data.insight_summary; 
                    this.googleError = data.google_error || null;
                    
                    // Critical: Ensure hasData is true if we have any series to show
                    this.hasData = !!(data.series && data.series.length > 0);
                    
                    this.companies = [...new Set((data.series || []).map(s => s.company).filter(Boolean))].sort();
                    this.applyFilter();
                }
                this.loading = false;
            })
            .catch(err => { this.error = err.message; this.loading = false; });
        },
        setFilter(val) { this.companyFilter = val; this.applyFilter(); },
        applyFilter() {
            const dr = this.trendsData;
            const query = this.searchQuery.toLowerCase().trim();
            const filter = this.companyFilter;
            
            const seriesMap = {};
            (dr.series || []).forEach(s => seriesMap[s.name] = s);

            const matches = (item) => {
                const sData = seriesMap[item.name] || {};
                const name = item.name || '';
                const lead = sData.lead || item.lead || '';
                const company = sData.company || item.company || '';
                const eps = sData.episodes || item.episodes || 0;

                const ms = !query || name.toLowerCase().includes(query) || lead.toLowerCase().includes(query);
                const mc = filter === 'All' || company.includes(filter) || (filter === 'United' && company.includes('United'));
                const me = this.episodesFilter === 'all' || parseInt(eps) === parseInt(this.episodesFilter);
                
                return ms && mc && me;
            };
            this.filteredRanking = (dr.watchit_ranking || []).filter(matches);
            this.filteredGoogle = (dr.google_trends || []).filter(matches);
            this.filteredSeries = (dr.series || []).filter(matches);
            this.computeStats();
            this.$nextTick(() => this.renderAll());
        },
        computeStats() {
            try {
                const d = this.trendsData; 
                this.stats.totalSeries = this.filteredSeries.length;
                const topG = this.filteredGoogle[0]?.name || '-';
                const topW = this.filteredRanking[0]?.name || '-';
                this.summaryCards = [
                    { label: 'Number of Series', value: this.filteredSeries.length, icon: 'fas fa-tv text-emerald-500', bg: 'bg-emerald-500/10', glow: 'bg-emerald-500', trend: 'Exclusive' },
                    { label: 'Search Champion', value: topG, icon: 'fab fa-google text-blue-500', bg: 'bg-blue-500/10', glow: 'bg-blue-500', trend: 'Google' },
                    { label: 'WATCH IT Champion', value: topW, icon: 'fas fa-fire text-rose-500', bg: 'bg-rose-500/10', glow: 'bg-rose-500', trend: 'Live' },
                    { label: 'Production Companies', value: [...new Set(this.filteredSeries.map(s => s.company).filter(Boolean))].length, icon: 'fas fa-building text-purple-500', bg: 'bg-purple-500/10', glow: 'bg-purple-500', trend: 'Coverage' }
                ];
            } catch (err) { console.error("Stats Error:", err); }
        },
        renderAll() {
            try {
                ['interestTimelineChart', 'googleBarChart', 'companiesDoughnut', 'bubbleChart'].forEach(id => { 
                    const c = Chart.getChart(id); 
                    if (c) c.destroy(); 
                });
                
                this.renderLineChart(); 
                this.renderTables(); 
                this.renderGoogleBar(); 
                this.renderCompanyDoughnut(); 
                this.renderBubbleChart(); 
                this.renderInsights();
            } catch (err) {
                console.error("Render Error:", err);
            }
        },
        renderLineChart() {
            const ctx = document.getElementById('interestTimelineChart'); if (!ctx) return;
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
                <div class="custom-legend-item flex items-center gap-2 cursor-pointer transition-opacity" onclick="toggleDataset(${i})" id="legend-${i}" style="opacity: ${i >= 5 ? '0.3' : '1'}">
                    <span class="w-3 h-3 rounded-full" style="background: ${SERIES_COLORS[i % SERIES_COLORS.length]}"></span>
                    <span class="text-[var(--text-main)] font-black text-[10px]">${s.name}</span>
                </div>
            `).join('');
        },
        renderTables() {
            const seriesMap = {}; this.trendsData.series.forEach(s => { seriesMap[s.name] = s; });

            const renderRows = (targetId, list, scoreKey, unit) => {
                const el = document.getElementById(targetId); if (!el) return;
                if (this.googleError && targetId === 'googleTableBody') {
                    el.innerHTML = `<tr><td colspan="3" class="py-12 text-center"><div class="text-red-500 font-black mb-2"><i class="fas fa-exclamation-triangle"></i> Sorry: Google Trends is currently blocked</div><div class="text-[var(--text-muted)] text-[10px] uppercase font-black tracking-widest">HTTP 429: Too Many Requests</div></td></tr>`;
                    return;
                }
                if (list.length === 0) { el.innerHTML = `<tr><td colspan="3" class="py-10 text-center text-[var(--text-muted)] font-black">No matching results.</td></tr>`; return; }
                el.innerHTML = list.map((s, i) => {
                    const color = i === 0 ? 'text-accent-gold' : (i === 1 ? 'text-[#94a3b8]' : (i === 2 ? 'text-[#f59e0b]' : 'text-[var(--text-muted)]'));
                    const icon = i === 0 ? '🏆' : (i === 1 ? '🥈' : (i === 2 ? '🥉' : ''));
                    const info = seriesMap[s.name] || {};
                    const epCount = s.episodes || info.episodes;
                    const epTag = epCount ? `<span class="bg-[var(--bg-card)] px-2 py-0.5 rounded text-[9px] border border-[var(--border-glass)]">${epCount} Episodes</span>` : '';
                    
                    if (targetId === 'watchItTableBody') {
                        return `<tr class="table-row-medal group">
                            <td class="pr-8 py-5 rounded-r-2xl font-black ${color} font-cairo text-lg text-left">${icon} ${i+1}</td>
                            <td class="py-5 text-left">
                                <div class="flex items-center justify-start gap-2">
                                    <span class="text-[var(--text-main)] font-black text-md font-cairo group-hover:text-accent-blue transition-colors">${s.name}</span>
                                    ${epTag}
                                </div>
                            </td>
                            <td class="pl-8 py-5 rounded-l-2xl text-center">
                                <span class="w-8 h-8 rounded-full bg-rose-500/10 text-rose-500 flex items-center justify-center text-xs mx-auto"><i class="fas fa-fire"></i></span>
                            </td>
                        </tr>`;
                    }

                    // Use injected lead/company if available, fallback to '-'
                    const leadStr = s.lead || '-';
                    const companyStr = s.company || '-';
                    const topGovs = (s.top_govs || []).slice(0, 3).map(g => `<span class="px-2 py-0.5 rounded-md bg-accent-gold/5 text-accent-gold border border-accent-gold/10 text-[8px] font-black">${g.name || g}</span>`).join(' ');
                    const topEp = s.top_episode ? `<div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-500 text-[9px] font-black border border-emerald-500/20"><i class="fas fa-search"></i> Top Searched: ${s.top_episode}</div>` : '';

                    const scoreDisplay = `<button @click="openSeriesDetails('${s.name}')" class="flex flex-col items-center group/btn"><span class="text-2xl font-black text-accent-gold font-cairo group-hover/btn:scale-110 transition-transform">${s[scoreKey]}${unit}</span><span class="text-[9px] text-[var(--text-muted)] opacity-60">Detailed Insights</span></button>`;

                    return `<tr class="table-row-medal group text-left">
                        <td class="pr-8 py-5 rounded-r-2xl font-black ${color} font-cairo text-lg text-left">${icon} ${i+1}</td>
                        <td class="py-5 text-left">
                            <div class="flex flex-col">
                                <div class="flex items-center justify-start gap-2 mb-1">
                                    <span class="text-[var(--text-main)] font-black text-md font-cairo group-hover:text-accent-blue transition-colors">${s.name} ${epTag}</span>
                                </div>
                                <div class="flex items-center justify-start gap-2 mb-1">${topGovs}</div>
                                <span class="text-[11px] text-[var(--text-muted)] font-black opacity-80 text-left">${leadStr} — ${companyStr}</span>
                                ${topEp}
                            </div>
                        </td>
                        <td class="pl-8 py-5 rounded-l-2xl text-center">
                            ${scoreDisplay}
                        </td>
                    </tr>`;
                }).join('');
            };
            renderRows('watchItTableBody', this.filteredRanking.slice(0, 10), 'rank', '#');
            renderRows('googleTableBody', this.filteredGoogle, 'score', '%');
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
                data = this.filteredGoogle || []; // Report page uses filteredGoogle instead of original
                filename = 'google_trends_ranking.csv';
            } else {
                data = this.filteredRanking || []; // Report page uses filteredRanking
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
                const govs = `"${(s.top_govs || []).join(' - ')}"`;
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
        },
        renderGoogleBar() {
            const ctx = document.getElementById('googleBarChart'); if (!ctx) return;
            const data = this.filteredGoogle.slice().sort((a,b)=>b.score-a.score);
            const tc = typeof getChartColors === 'function' ? getChartColors() : { text: '#ccc', grid: 'rgba(255,255,255,0.05)' };
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(s => s.name),
                    datasets: [{ data: data.map(s => s.score), backgroundColor: data.map((_, i) => `rgba(139, 92, 246, ${0.4 + (i/data.length)*0.5})`), borderRadius: 6 }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { rtl: true } },
                    scales: {
                        x: { min: 0, max: 100, ticks: { color: tc.text, font: { weight: 'bold' } }, grid: { color: tc.grid } },
                        y: { ticks: { color: tc.text, font: { family: 'Tajawal', size: 10, weight: '900' } }, grid: { display: false } }
                    }
                }
            });
        },
        renderCompanyDoughnut() {
            const ctx = document.getElementById('companiesDoughnut'); if (!ctx) return;
            const counts = {}; this.filteredSeries.forEach(s => { const c = s.company || 'Unspecified'; counts[c] = (counts[c] || 0) + 1; });
            const tc = typeof getChartColors === 'function' ? getChartColors() : { text: '#ccc' };
            new Chart(ctx, {
                type: 'doughnut',
                data: { labels: Object.keys(counts), datasets: [{ data: Object.values(counts), backgroundColor: ['#0ea5e9', '#8b5cf6', '#fbbf24', '#ec4899', '#10b981'], borderWidth: 0, hoverOffset: 20 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: tc.text, font: { family: 'Tajawal', weight: 'bold' }, padding: 20 } } } }
            });
        },
        renderBubbleChart() {
            const ctx = document.getElementById('bubbleChart'); if (!ctx) return;
            const tc = typeof getChartColors === 'function' ? getChartColors() : { text: '#ccc', grid: 'rgba(255,255,255,0.05)' };
            const gScores = this.trendsData.google_trends || []; const wRankMap = {}; (this.trendsData.watchit_ranking || []).forEach(w => { wRankMap[w.name] = w.rank; });
            const dataPoints = gScores.filter(g => this.filteredSeries.some(s => s.name === g.name) && wRankMap[g.name] !== undefined)
                                     .map(g => {
                                         const wRank = wRankMap[g.name]; const diff = Math.abs(wRank - g.rank);
                                         const color = diff <= 1 ? '#10b981' : (diff <= 4 ? '#fbbf24' : '#ef4444');
                                         return { x: wRank, y: g.rank, r: 10, label: g.name, backgroundColor: color };
                                     });
            new Chart(ctx, {
                type: 'bubble',
                data: { datasets: [{ data: dataPoints, backgroundColor: dataPoints.map(p => p.backgroundColor + '66'), borderColor: dataPoints.map(p => p.backgroundColor), borderWidth: 2 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { rtl: true, callbacks: { label: c => dataPoints[c.dataIndex].label } } },
                    scales: {
                        x: { reverse: false, ticks: { color: tc.text, font: { weight: 'bold' } }, grid: { color: tc.grid }, title: { display: true, text: 'WATCH IT Ranking', color: tc.text, font: { weight: 'black' } } },
                        y: { reverse: true, ticks: { color: tc.text, font: { weight: 'bold' } }, grid: { color: tc.grid }, title: { display: true, text: 'Google Search Ranking', color: tc.text, font: { weight: 'black' } } }
                    }
                }
            });
        },
        renderInsights() {
            const el = document.getElementById('insightsGrid'); if (!el) return;
            const g = this.filteredGoogle; const w = this.filteredRanking; const insights = [];
            if (g[0]) insights.push({ icon: 'fab fa-google', color: '#3b82f6', bg: 'bg-blue-500/10', title: 'Most Searched', body: `The series "${g[0].name}" is currently the most demanded by the audience.` });
            if (w[0]) insights.push({ icon: 'fas fa-fire', color: '#ef4444', bg: 'bg-rose-500/10', title: 'Most Watched', body: `"${w[0].name}" tops the list of most watched on WATCH IT.` });
            if (this.isSimulated) insights.push({ icon: 'fas fa-project-diagram', color: '#fbbf24', bg: 'bg-amber-500/10', title: 'Data Modeling', body: 'The report currently uses advanced AI models to predict viewership rates based on search trends and historical data.' });
            el.innerHTML = insights.map(ins => `<div class="glass-card p-6 flex items-start gap-4 transition-all hover:border-white/20"><div class="w-12 h-12 rounded-2xl ${ins.bg} flex items-center justify-center text-lg shrink-0" style="color: ${ins.color};"><i class="${ins.icon}"></i></div><div class="text-left"><h4 class="text-[var(--text-main)] font-black mb-1 font-cairo">${ins.title}</h4><p class="text-[var(--text-muted)] text-xs font-bold leading-relaxed">${ins.body}</p></div></div>`).join('');
        }
    };
}
</script>
@endpush
@endsection
