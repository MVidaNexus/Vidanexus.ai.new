@extends('discoverheadlines::layouts.master')

@section('title', 'Discover Smart Headlines')

@section('content')
{{-- Progress Overlay --}}
<div id="generation-progress-overlay" 
     class="fixed inset-0 bg-[#0d0e12]/80 backdrop-blur-xl z-[9999] flex items-center justify-center hidden opacity-0 transition-opacity duration-500">
    <div class="glass-card p-10 max-w-sm w-full mx-4 shadow-2xl relative overflow-hidden" id="progress-card">
        {{-- Animated background lines --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-cyan/5 to-primary-purple/5 pointer-events-none"></div>
        
        <div class="text-center space-y-8 relative z-10">
            <div class="relative w-28 h-28 mx-auto">
                {{-- Circular Progress SVG --}}
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="56" cy="56" r="50" stroke="rgba(255,255,255,0.05)" stroke-width="6" fill="transparent" />
                    <circle id="progress-circle" cx="56" cy="56" r="50" stroke="url(#gradient)" stroke-width="6" fill="transparent" 
                            class="transition-all duration-700 ease-out" 
                            stroke-dasharray="314.16" stroke-dashoffset="314.16" stroke-linecap="round" />
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#0ea5e9" />
                            <stop offset="100%" stop-color="#7000ff" />
                        </linearGradient>
                    </defs>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-brain text-4xl text-primary-cyan animate-pulse"></i>
                </div>
            </div>
            
            <div class="space-y-3">
                <h3 class="text-2xl font-black text-white tracking-tight" id="progress-title">Processing...</h3>
                <p class="text-gray-400 text-sm font-medium h-4" id="progress-message">Using AI to analyze trends...</p>
            </div>

            <div class="pt-2">
                <div class="flex flex-col gap-3">
                    <div class="flex justify-between text-[11px] font-bold text-primary-cyan uppercase tracking-[0.2em]">
                        <span>System Status</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden border border-white/5">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-primary-cyan to-primary-purple w-0 transition-all duration-700 ease-out"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto font-tajawal" dir="ltr" x-data="headlineGenerator()">
    
    {{-- Header Section --}}
    <div class="mb-12 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-cyan/10 border border-primary-cyan/20 text-primary-cyan text-xs font-black uppercase tracking-widest mb-4">
            <i class="fas fa-bolt"></i> Google Discover AI
        </div>
        <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight leading-tight">
            The Smartest <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-cyan to-primary-purple">Headline Generator</span>
        </h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg">
            Turn your ideas into a real trend. We combine advanced AI with the latest Google News Context to ensure you reach millions.
        </p>

        @include('partials.tool-usage-badge', ['slug' => 'discover-headlines'])
    </div>

    {{-- Main Control Panel --}}
    <div class="glass-card overflow-hidden shadow-[0_0_50px_rgba(0,0,0,0.3)] border-white/10 mb-12">
        <div class="p-8 sm:p-12">
            {{-- Mode Switcher --}}
            <div class="flex items-center gap-2 mb-10 bg-white/5 p-1.5 rounded-2xl w-fit mx-auto border border-white/5">
                <button @click="mode = 'keyword'; results = null;" 
                        :class="mode === 'keyword' ? 'bg-white/10 text-primary-cyan shadow-xl border-white/10' : 'text-gray-500 hover:text-white'"
                        class="px-8 py-3 rounded-xl text-sm font-black transition-all duration-300 border border-transparent">
                    <i class="fas fa-search-nodes ml-2"></i> Smart Keyword Search
                </button>
                <button @click="mode = 'content'; results = null;" 
                        :class="mode === 'content' ? 'bg-white/10 text-primary-cyan shadow-xl border-white/10' : 'text-gray-500 hover:text-white'"
                        class="px-8 py-3 rounded-xl text-sm font-black transition-all duration-300 border border-transparent">
                    <i class="fas fa-file-invoice ml-2"></i> Full Content Analysis
                </button>
            </div>

            <div class="space-y-8">
                {{-- Keyword Input --}}
                <div class="relative group" x-show="mode === 'keyword'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translateY-4">
                    <input type="text" x-model="keyword" @keydown.enter="generate()"
                           class="w-full px-8 py-6 bg-white/5 border-2 border-white/5 focus:border-primary-cyan/50 focus:bg-white/10 rounded-[2rem] outline-none transition-all duration-500 font-bold text-xl placeholder-gray-600 text-white shadow-2xl"
                           placeholder="Enter Topic (e.g., The Future of Crypto in 2026)...">
                    <div class="absolute inset-y-0 left-8 flex items-center pointer-events-none text-gray-600 group-focus-within:text-primary-cyan transition-colors">
                        <i class="fas fa-wand-sparkles text-2xl"></i>
                    </div>
                </div>

                {{-- Trending Suggestions --}}
                <div x-show="mode === 'keyword'" class="space-y-4">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-2 h-2 rounded-full bg-primary-purple animate-pulse"></div>
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Trending Suggestions</p>
                    </div>
                    <div class="flex flex-wrap gap-2.5">
                        <template x-for="item in trendingSuggestions" :key="item.keyword">
                            <button @click="keyword = item.keyword; generate()"
                                    class="px-5 py-2.5 bg-white/5 border border-white/10 text-gray-400 rounded-full text-xs font-bold hover:bg-primary-cyan/10 hover:text-primary-cyan hover:border-primary-cyan/30 transition-all duration-300">
                                <span x-text="item.keyword"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Content Input --}}
                <div x-show="mode === 'content'" x-cloak x-transition:enter="transition ease-out duration-300">
                    <textarea x-model="content" 
                              class="w-full px-8 py-8 bg-white/5 border-2 border-white/5 focus:border-primary-cyan/50 focus:bg-white/10 rounded-[2rem] outline-none transition-all duration-500 font-medium text-lg min-h-[250px] text-white shadow-2xl"
                              placeholder="Paste article draft or rough ideas here... We will analyze it and extract the most powerful magnetic headlines."></textarea>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-4 border-t border-white/5">
                    <div class="flex items-center gap-6 text-gray-500 text-xs font-bold">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-shield-halved text-primary-cyan"></i> 100% Safe
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-microchip text-primary-purple"></i> AI Powered
                        </div>
                    </div>
                    
                    <button @click="generate()" :disabled="loading || (mode === 'keyword' ? !keyword : !content)"
                            class="w-full sm:w-auto vn-btn vn-btn-primary px-12 py-5 rounded-2xl flex items-center justify-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed text-lg">
                        <span x-show="!loading">Start Magic Generation</span>
                        <i class="fas fa-sparkles" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Section --}}
    <div id="results-section-anchor" x-show="results" x-cloak class="space-y-8 pb-20 scroll-mt-24">
        <div class="flex items-end justify-between px-4">
            <div class="space-y-1">
                <p class="text-primary-purple text-xs font-black uppercase tracking-[0.3em]">Neural Output</p>
                <h3 class="text-3xl font-black text-white">Suggested Headlines</h3>
            </div>
            <button @click="results = null; window.scrollTo({top: 0, behavior: 'smooth'})" class="text-gray-500 hover:text-white transition-colors text-sm font-bold">
                <i class="fas fa-times-circle mr-2"></i> Clear Results
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <template x-for="(headline, index) in parsedHeadlines" :key="index">
                <div class="glass-card p-4 sm:p-5 group hover:border-primary-cyan/30 transition-all duration-500">
                    <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
                        <div class="flex-1 space-y-4">
                            {{-- Meta Info --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-md bg-white/5 flex items-center justify-center text-[10px] font-black text-gray-500">
                                        0<span x-text="index + 1"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <template x-if="headline.score">
                                            <div class="flex items-center gap-2 px-2.5 py-1 rounded-full bg-white/5 border border-white/10">
                                                <div class="w-1.5 h-1.5 rounded-full" :class="getScoreColor(headline.score)"></div>
                                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter" x-text="headline.grade.label"></span>
                                                <span class="text-[9px] font-black text-primary-cyan ml-1" x-text="headline.score + '%'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-[10px] font-bold text-gray-600 hidden sm:block">Option <span x-text="index + 1"></span></div>
                            </div>

                            {{-- Headline Headline --}}
                            <h4 class="text-lg sm:text-xl font-bold text-white group-hover:text-primary-cyan transition-colors duration-300 leading-snug" x-text="headline.text"></h4>

                            {{-- Quality Bar --}}
                            <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full transition-all duration-1000 ease-out rounded-full" 
                                     :style="'width: ' + headline.score + '%'"
                                     :class="getScoreBarColor(headline.score)"></div>
                            </div>

                            {{-- Intelligence Feedback --}}
                            <div class="flex flex-wrap gap-1.5" x-show="headline.feedback?.length">
                                <template x-for="fb in headline.feedback">
                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 border border-white/5 transform hover:scale-105 transition-transform cursor-default">
                                        <i class="fas fa-check-circle text-green-500 text-[9px]" x-show="fb.type === 'success'"></i>
                                        <i class="fas fa-info-circle text-blue-500 text-[9px]" x-show="fb.type === 'info'"></i>
                                        <i class="fas fa-exclamation-circle text-yellow-500 text-[9px]" x-show="fb.type === 'warning' || fb.type === 'danger'"></i>
                                        <span class="text-[9px] font-bold text-gray-400" x-text="fb.text"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Action Vertical Menu --}}
                        <div class="lg:w-40 shrink-0 flex flex-col justify-center">
                            <div class="flex lg:flex-col gap-2.5">
                                <button @click="copyToClipboard(headline.text)" class="flex-1 p-2.5 sm:p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-primary-cyan hover:text-black transition-all duration-300 group/btn flex flex-row lg:flex-col items-center justify-center gap-2">
                                    <i class="fas fa-copy sm:text-lg"></i>
                                    <span class="text-[10px] sm:text-[9px] font-black uppercase tracking-widest hidden sm:block">Copy Title</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest sm:hidden">Copy</span>
                                </button>
                                <a :href="'https://www.google.com/search?q=' + encodeURIComponent(headline.text)" target="_blank" class="flex-1 p-2.5 sm:p-3 rounded-xl bg-white/5 border border-white/10 hover:bg-primary-cyan hover:text-black transition-all duration-300 flex flex-row lg:flex-col items-center justify-center gap-2 group/btn">
                                    <i class="fab fa-google sm:text-lg"></i>
                                    <span class="text-[10px] sm:text-[9px] font-black uppercase tracking-widest hidden sm:block">Google Search</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest sm:hidden">Search</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Hidden Form Fallback --}}
<form id="headline-trigger-form" method="POST" action="{{ route('headlines.generate') }}" style="display:none">
    @csrf
    <input type="hidden" name="type" id="form-trigger-type">
    <input type="hidden" name="keyword" id="form-trigger-keyword">
    <input type="hidden" name="content" id="form-trigger-content">
    <input type="hidden" name="progress_id" id="form-trigger-progress-id">
</form>

@endsection

@push('scripts')
<script>
    function headlineGenerator() {
        return {
            keyword: @js($prefilledKeyword ?? ''),
            mode: 'keyword',
            content: '',
            loading: false,
            results: null,
            resultKeyword: '',
            parsedHeadlines: [],
            trendingSuggestions: @js($trendingSuggestions ?? []),
            categories: @js($categories ?? []),
            pInterval: null,

            init() {
                // If we have flashed results through redirect
                @if(session('headlineResults'))
                    this.handleResults({
                        status: 'success',
                        headlines: @js(session('headlineResults')),
                        scored: @js(session('scoredHeadlines')),
                        keyword: @js(session('headlineKeyword'))
                    });
                @endif

                // Handle URL keyword if no results
                const urlParams = new URLSearchParams(window.location.search);
                const kw = urlParams.get('keyword');
                if (kw && !this.results) {
                    this.keyword = kw;
                }
            },

            getScoreColor(score) {
                if (score >= 85) return 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]';
                if (score >= 70) return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]';
                if (score >= 40) return 'bg-yellow-400 shadow-[0_0_10px_rgba(250,204,21,0.5)]';
                return 'bg-red-400 shadow-[0_0_10px_rgba(248,113,113,0.5)]';
            },
            getScoreGradientClass(score) {
                if (score >= 85) return 'bg-gradient-to-r from-emerald-600 to-emerald-500';
                if (score >= 70) return 'bg-gradient-to-r from-emerald-500 to-blue-400';
                if (score >= 55) return 'bg-gradient-to-r from-blue-600 to-blue-400';
                return 'bg-gradient-to-r from-red-600 to-red-400';
            },

            async generate() {
                if (this.loading) return;
                const pid = 'hl_' + Date.now();
                this.loading = true;
                this.results = null;

                // Show Progress
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    setTimeout(() => overlay.classList.replace('opacity-0', 'opacity-100'), 50);
                }

                this.startPolling(pid);

                // Update URL
                const url = new URL(window.location.href);
                url.searchParams.set('keyword', this.keyword);
                window.history.pushState({}, '', url);

                try {
                    const response = await fetch("{{ route('headlines.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: new URLSearchParams({
                            type: this.mode,
                            keyword: this.keyword,
                            content: this.content,
                            progress_id: pid
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || data.error || 'Generation failed');
                    }
                    
                    if (data.status === 'success') {
                        this.handleResults(data);
                    } else {
                        throw new Error(data.message);
                    }
                } catch (e) {
                    this.stopProgress();
                    console.error('Generation Error:', e);
                    const msg = e.message || 'Unexpected error occurred';
                    
                    // Lenient check for balance/limit issues
                    const isBalanceError = msg.toLowerCase().includes('balance') || 
                                         msg.toLowerCase().includes('credit');

                    if (isBalanceError) {
                        if (window.showInsufficientBalanceAlert) {
                            showInsufficientBalanceAlert(msg);
                        } else {
                            // Fallback if global helper fails but SweetAlert is available
                            Swal.fire({
                                title: 'Insufficient Balance',
                                text: msg,
                                icon: 'warning',
                                confirmButtonText: 'Recharge Balance',
                                showCancelButton: true
                            }).then(res => {
                                if (res.isConfirmed) window.location.href = '/pricing';
                            });
                        }
                    } else {
                        Swal.fire('Error', msg, 'error');
                    }
                }
            },

            startPolling(pid) {
                this.pInterval = setInterval(async () => {
                    try {
                        const r = await fetch("{{ route('headlines.progress', ['id' => ':id']) }}".replace(':id', pid));
                        if (!r.ok) return;
                        const data = await r.json();
                        this.updateUI(data);
                        if (data.stage === 'completed' || data.stage === 'error') {
                            clearInterval(this.pInterval);
                            if (data.stage === 'completed' && !this.results) {
                                this.handleResults(data);
                            }
                        }
                    } catch (e) { }
                }, 1500);
            },

            updateUI(data) {
                const stages = { 'starting': 15, 'searching': 40, 'ai_processing': 80, 'completed': 100 };
                const p = stages[data.stage] || 50;
                
                const titleEl = document.getElementById('progress-title');
                const msgEl = document.getElementById('progress-message');
                const barEl = document.getElementById('progress-bar');
                const percentEl = document.getElementById('progress-percent');
                const circleEl = document.getElementById('progress-circle');

                if (titleEl) titleEl.innerText = data.stage === 'completed' ? 'Completed!' : 'Working...';
                if (msgEl) msgEl.innerText = data.message;
                if (barEl) barEl.style.width = p + '%';
                if (percentEl) percentEl.innerText = p + '%';
                if (circleEl) circleEl.style.strokeDashoffset = 314.16 - (314.16 * p / 100);
            },

            handleResults(data) {
                this.results = data.headlines;
                this.resultKeyword = data.keyword;
                this.parsedHeadlines = data.scored.map(s => ({
                    text: s.headline,
                    grade: s.grade,
                    score: s.score,
                    feedback: s.feedback,
                    generating: false,
                    categoryId: ''
                }));
                this.stopProgress();
                setTimeout(() => document.getElementById('results-section-anchor').scrollIntoView({behavior: 'smooth'}), 500);
            },

            stopProgress() {
                this.loading = false;
                clearInterval(this.pInterval);
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                    overlay.classList.remove('opacity-100');
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                        overlay.classList.remove('flex');
                    }, 500);
                }
            },

            copyToClipboard(text) {
                navigator.clipboard.writeText(text);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Copied Successfully',
                    showConfirmButton: false,
                    timer: 2000
                });
            },

            async generateArticle(headline) {
                if (headline.generating) return;
                headline.generating = true;
                
                // For now, article generation is just a placeholder until we port that logic
                Swal.fire({
                    title: 'Starting...',
                    text: 'You will be redirected to the article editor upon completion.',
                    timer: 3000,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                }).then(() => {
                    headline.generating = false;
                    Swal.fire('Done!', 'Feature is currently under final development.', 'info');
                });
            }
        };
    }
</script>
@endpush
