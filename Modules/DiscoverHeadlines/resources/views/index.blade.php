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

            {{-- Minimize Button --}}
            <div class="pt-6 border-t border-white/5">
                <button type="button" @click="minimizeProgress()"
                        class="w-full py-3 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-primary-cyan hover:border-primary-cyan/50 text-[10px] font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-external-link-alt"></i> Run in Background
                </button>
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
                <button @click="setMode('keyword')"
                        :class="mode === 'keyword' ? 'bg-white/10 text-primary-cyan shadow-xl border-white/10' : 'text-gray-500 hover:text-white'"
                        class="px-8 py-3 rounded-xl text-sm font-black transition-all duration-300 border border-transparent">
                    <i class="fas fa-search-nodes ml-2"></i> Smart Keyword Search
                </button>
                <button @click="setMode('content')"
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

        <div class="grid grid-cols-1 gap-6">
            <template x-for="(headline, index) in parsedHeadlines" :key="index">
                <div class="glass-card p-6 sm:p-8 group hover:border-primary-cyan/30 transition-all duration-500 relative overflow-hidden">
                    {{-- Decorative sentiment glow --}}
                    <div class="absolute -right-20 -top-20 w-40 h-40 blur-[80px] opacity-10 transition-all duration-500 group-hover:opacity-20"
                         :class="getSentimentGlow(headline.sentiment)"></div>

                    <div class="flex flex-col lg:flex-row gap-6 sm:gap-10">
                        <div class="flex-1 space-y-6">
                            {{-- Meta Info & Sentiment --}}
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-xs font-black text-gray-500 border border-white/5">
                                        <span x-text="index + 1"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <template x-if="headline.score">
                                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 shadow-inner">
                                                <div class="w-2 h-2 rounded-full" :class="getScoreColor(headline.score)"></div>
                                                <span class="text-[10px] font-black text-gray-300 uppercase tracking-wider" x-text="headline.grade.label"></span>
                                                <span class="text-[10px] font-black text-primary-cyan ml-1" x-text="headline.score + '%'"></span>
                                            </div>
                                        </template>
                                        
                                        {{-- Sentiment Badge --}}
                                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/5 text-[10px] font-black uppercase tracking-widest shadow-lg"
                                             :class="getSentimentClass(headline.sentiment)">
                                            <i class="fas" :class="getSentimentIcon(headline.sentiment)"></i>
                                            <span x-text="headline.sentiment"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-[10px] font-bold text-gray-600 uppercase tracking-[0.2em]">Intel ID: HL-<span x-text="100 + index"></span></div>
                            </div>

                            {{-- Result Headline --}}
                            <h4 class="text-xl sm:text-2xl font-black text-white group-hover:text-primary-cyan transition-colors duration-300 leading-tight tracking-tight" x-text="headline.text"></h4>

                            {{-- SEO Semantic Cloud --}}
                            <div class="space-y-2 pt-2" x-show="headline.entities?.length || headline.lsi_keywords?.length">
                                <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest pl-1">Semantic Intelligence</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="entity in headline.entities">
                                        <span class="px-2.5 py-1 rounded bg-primary-cyan/5 border border-primary-cyan/20 text-primary-cyan text-[9px] font-black uppercase tracking-tighter hover:bg-primary-cyan/20 cursor-default transition-colors">
                                            <i class="fas fa-fingerprint mr-1"></i> <span x-text="entity"></span>
                                        </span>
                                    </template>
                                    <template x-for="lsi in headline.lsi_keywords">
                                        <span class="px-2.5 py-1 rounded bg-primary-purple/5 border border-primary-purple/20 text-primary-purple text-[9px] font-black uppercase tracking-tighter hover:bg-primary-purple/20 cursor-default transition-colors">
                                            <i class="fas fa-microchip mr-1"></i> <span x-text="lsi"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Intelligence Feedback --}}
                            <div class="flex flex-wrap gap-2" x-show="headline.feedback?.length">
                                <template x-for="fb in headline.feedback">
                                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/5 transform hover:scale-[1.02] transition-transform cursor-help">
                                        <i class="fas fa-check-circle text-green-500 text-[10px]" x-show="fb.type === 'success'"></i>
                                        <i class="fas fa-info-circle text-blue-500 text-[10px]" x-show="fb.type === 'info'"></i>
                                        <i class="fas fa-exclamation-circle text-yellow-500 text-[10px]" x-show="fb.type === 'warning' || fb.type === 'danger'"></i>
                                        <span class="text-[10px] font-bold text-gray-400" x-text="fb.text"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Advanced SEO Toolbox --}}
                        <div class="lg:w-48 shrink-0 flex flex-col justify-center border-l lg:border-white/5 lg:pl-10">
                            <div class="flex lg:flex-col gap-3">
                                <button @click="copyToClipboard(headline.text)" 
                                        class="flex-1 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-primary-cyan hover:text-black hover:border-primary-cyan transition-all duration-300 group/btn flex flex-col items-center justify-center gap-1.5 shadow-xl">
                                    <i class="fas fa-copy text-lg"></i>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-center">Copy Title</span>
                                </button>
                                
                                <button @click="showVisualAngle(headline)" 
                                        class="flex-1 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-primary-purple hover:text-white hover:border-primary-purple transition-all duration-300 flex flex-col items-center justify-center gap-1.5 shadow-xl">
                                    <i class="fas fa-image text-lg"></i>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-center">Visual Angle</span>
                                </button>

                                <a :href="'{{ route('dashboard.article-writer.index') }}?keyword=' + encodeURIComponent(headline.text)" target="_blank"
                                        class="flex-1 p-4 rounded-2xl bg-gradient-to-br from-[#a855f7] to-[#6366f1] border border-white/10 hover:scale-[1.05] transition-all duration-300 flex flex-col items-center justify-center gap-1.5 shadow-xl text-white no-underline">
                                    <i class="fas fa-pen-fancy text-lg"></i>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-center">Write with AI</span>
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
            // `terminalReached` blocks duplicate completed/error alerts when
            // multiple in-flight polls all observe the same terminal cache
            // state. clearInterval() only stops future iterations — it cannot
            // cancel callbacks that already started, so we need an explicit
            // flag that any concurrent poll can check before firing its alert.
            terminalReached: false,

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

            /**
             * Switch between keyword/content modes and clear the OTHER field so
             * a stale value (e.g. a `?keyword=...` URL parameter left over from
             * another tool) cannot silently override the user's new input.
             */
            setMode(next) {
                if (this.mode === next) return;
                this.mode = next;
                this.results = null;
                if (next === 'keyword') {
                    this.content = '';
                } else {
                    this.keyword = '';
                    // Also drop the URL parameter so a refresh doesn't repopulate it.
                    const url = new URL(window.location.href);
                    url.searchParams.delete('keyword');
                    window.history.replaceState({}, '', url);
                }
            },

            getScoreColor(score) {
                if (score >= 85) return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]';
                if (score >= 70) return 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]';
                if (score >= 40) return 'bg-yellow-400 shadow-[0_0_10px_rgba(250,204,21,0.5)]';
                return 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]';
            },

            getScoreBarColor(score) {
                if (score >= 85) return 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.3)]';
                if (score >= 70) return 'bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.3)]';
                if (score >= 40) return 'bg-yellow-400 shadow-[0_0_10px_rgba(250,204,21,0.3)]';
                return 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.3)]';
            },

            getSentimentClass(sentiment) {
                const s = (sentiment || '').toLowerCase();
                if (s.includes('positive')) return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
                if (s.includes('surprise') || s.includes('shock')) return 'bg-primary-purple/10 text-primary-purple border-primary-purple/20';
                if (s.includes('negative')) return 'bg-red-500/10 text-red-400 border-red-500/20';
                return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
            },

            getSentimentIcon(sentiment) {
                const s = (sentiment || '').toLowerCase();
                if (s.includes('positive')) return 'fa-smile-beam';
                if (s.includes('surprise')) return 'fa-bolt-lightning';
                if (s.includes('negative')) return 'fa-face-frown';
                return 'fa-newspaper';
            },

            getSentimentGlow(sentiment) {
                const s = (sentiment || '').toLowerCase();
                if (s.includes('positive')) return 'bg-emerald-500';
                if (s.includes('surprise')) return 'bg-primary-purple';
                if (s.includes('negative')) return 'bg-red-500';
                return 'bg-primary-cyan';
            },

            showVisualAngle(headline) {
                const concepts = (headline.visual_concepts && headline.visual_concepts.length)
                    ? headline.visual_concepts
                    : this.buildVisualConcepts(headline);

                const cards = concepts.map((c, i) => `
                    <div class="p-4 rounded-2xl border border-white/10 bg-white/[0.03] text-left">
                        <div class="text-[10px] font-black uppercase tracking-widest text-primary-cyan mb-2">Concept ${i + 1}</div>
                        <p class="text-slate-200 text-sm leading-relaxed mb-3">${this.escapeHtml(c.description || '')}</p>
                        <div class="grid grid-cols-2 gap-2 text-[10px]">
                            <div><span class="text-slate-500">Palette:</span> <span class="text-slate-300">${this.escapeHtml(c.color_palette || 'High contrast')}</span></div>
                            <div><span class="text-slate-500">Style:</span> <span class="text-slate-300">${this.escapeHtml(c.style || 'Editorial photo')}</span></div>
                        </div>
                        <p class="text-[10px] text-emerald-400 mt-2"><i class="fas fa-chart-line mr-1"></i>${this.escapeHtml(c.ctr_reason || 'Boosts Discover CTR with emotional hook')}</p>
                    </div>
                `).join('');

                Swal.fire({
                    title: '<span class="text-white font-black uppercase tracking-widest text-sm">Visual Angle Discovery</span>',
                    html: `<div class="space-y-3 max-h-[60vh] overflow-y-auto p-1">${cards}</div>`,
                    background: '#0d0e12',
                    width: '560px',
                    confirmButtonText: 'Understood',
                    confirmButtonColor: '#0ea5e9',
                    customClass: { popup: 'rounded-2xl' }
                });
            },

            buildVisualConcepts(headline) {
                const base = (headline.thumbnail_suggestion || headline.headline || '').trim();
                const safe = base.replace(/https?:\/\/example\.com[^\s]*/gi, '').trim()
                    || 'Dynamic editorial image highlighting the main subject with bold contrast and clear focal point.';

                return [
                    {
                        description: safe,
                        color_palette: 'Cyan + deep navy + white accents',
                        style: 'Photojournalistic close-up',
                        ctr_reason: 'Human face + action creates immediate emotional pull',
                    },
                    {
                        description: `Wide contextual scene supporting: ${safe.slice(0, 120)}`,
                        color_palette: 'Warm amber highlights on dark background',
                        style: 'Cinematic wide shot',
                        ctr_reason: 'Context framing increases trust and click curiosity',
                    },
                    {
                        description: `Minimal graphic treatment for: ${safe.slice(0, 100)}`,
                        color_palette: 'Monochrome with single accent color',
                        style: 'Minimal editorial composite',
                        ctr_reason: 'Clean layout stands out in crowded Discover feeds',
                    },
                ];
            },

            escapeHtml(str) {
                return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            },



            async generate() {
                if (this.loading) return;
                const pid = 'hl_' + Date.now();
                this.loading = true;
                this.results = null;

                // Reset the terminal-state guard and forcibly kill any
                // leftover poller from a previous (e.g. errored) run so we
                // can never accumulate two concurrent setInterval loops on
                // the same component instance.
                this.terminalReached = false;
                if (this.pInterval) {
                    clearInterval(this.pInterval);
                    this.pInterval = null;
                }

                // Show Progress
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.remove('hidden', 'pointer-events-none');
                    overlay.classList.replace('opacity-0', 'opacity-100');
                    overlay.classList.add('flex');
                }

                this.startPolling(pid);

                // Update URL — only meaningful in keyword mode. In content
                // mode we strip ?keyword= entirely so a refresh doesn't
                // re-introduce a stale word into the URL bar.
                const url = new URL(window.location.href);
                if (this.mode === 'keyword' && this.keyword) {
                    url.searchParams.set('keyword', this.keyword);
                } else {
                    url.searchParams.delete('keyword');
                }
                window.history.pushState({}, '', url);

                try {
                    // Submit only the active field. This complements the
                    // server-side guard in HeadlineController::generate — even
                    // if a user bypasses the JS layer, the controller will
                    // still drop the inactive field on its end.
                    const payload = {
                        type: this.mode,
                        progress_id: pid,
                    };
                    if (this.mode === 'keyword') {
                        payload.keyword = this.keyword;
                    } else {
                        payload.content = this.content;
                    }

                    const response = await fetch("{{ route('headlines.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: new URLSearchParams(payload)
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) throw new Error(data.message || 'Generation failed');
                    
                    // Note: We don't call handleResults here because it's just 'processing'
                    // The poller will handle the actual completion.

                } catch (e) {
                    this.stopProgress();
                    console.error('Generation Error:', e);
                    const msg = e.message || 'Unexpected error occurred';
                    if (msg.includes('balance') || msg.includes('credit')) {
                        if (window.showInsufficientBalanceAlert) showInsufficientBalanceAlert(msg);
                        else Swal.fire('Insufficient Balance', msg, 'warning');
                    } else {
                        Swal.fire('Error', msg, 'error');
                    }
                }
            },

            minimizeProgress() {
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => overlay.classList.add('hidden'), 500);
                }
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'info',
                    title: 'Processing in background...',
                    text: 'You will be notified once headlines are ready.',
                    showConfirmButton: false,
                    timer: 4000
                });
            },

            startPolling(pid) {
                this.pInterval = setInterval(async () => {
                    // Multiple polls can be in-flight simultaneously when
                    // the network is slow. If any one of them has already
                    // observed the terminal state and fired the alert, the
                    // others MUST bail out before doing it a second time.
                    if (this.terminalReached) return;

                    let data;
                    try {
                        const r = await fetch("{{ route('headlines.progress', ['id' => ':id']) }}".replace(':id', pid));
                        if (!r.ok) return;
                        data = await r.json();
                    } catch (e) {
                        return;
                    }

                    // The fetch was async — recheck the guard after the await
                    // because another concurrent poll may have raced ahead and
                    // already handled the terminal state while we were waiting.
                    if (this.terminalReached) return;

                    this.updateUI(data);

                    if (data.stage !== 'completed' && data.stage !== 'error') {
                        return;
                    }

                    // Latch the terminal flag IMMEDIATELY so the next
                    // concurrent poll (currently somewhere in await) returns
                    // at the recheck above instead of firing a duplicate alert.
                    this.terminalReached = true;
                    clearInterval(this.pInterval);
                    this.pInterval = null;

                    if (data.stage === 'completed') {
                        // The completed progress payload now carries the
                        // post-deduction `balance` field, so VidaCredits.apply
                        // animates the chip directly without a second fetch.
                        if (window.VidaCredits) window.VidaCredits.apply(data);
                        this.handleResults(data);
                        if (document.getElementById('generation-progress-overlay').classList.contains('hidden')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Extraction Complete!',
                                text: 'New headlines have been generated successfully.',
                                confirmButtonText: 'View Now'
                            }).then(() => {
                                document.getElementById('results-section-anchor').scrollIntoView({behavior: 'smooth'});
                            });
                        }
                    } else {
                        this.stopProgress();
                        Swal.fire('Error', data.message || 'Background processing failed', 'error');
                    }
                }, 1500);
            },

            updateUI(data) {
                const stages = { 'starting': 10, 'searching': 35, 'ai_processing': 75, 'completed': 100 };
                const p = stages[data.stage] || 50;
                
                const titleEl = document.getElementById('progress-title');
                const msgEl = document.getElementById('progress-message');
                const barEl = document.getElementById('progress-bar');
                const percentEl = document.getElementById('progress-percent');
                const circleEl = document.getElementById('progress-circle');

                if (titleEl) titleEl.innerText = data.stage === 'completed' ? 'Success!' : (data.stage === 'error' ? 'Failed' : 'Processing...');
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
                    sentiment: s.sentiment || 'Factual',
                    entities: s.entities || [],
                    lsi_keywords: s.lsi_keywords || [],
                    thumbnail_suggestion: s.thumbnail_suggestion || '',
                    generating: false,
                    categoryId: ''
                }));
                this.stopProgress();
                setTimeout(() => {
                    const el = document.getElementById('results-section-anchor');
                    if (el && !el.classList.contains('hidden')) {
                        el.scrollIntoView({behavior: 'smooth'});
                    }
                }, 500);
            },

            stopProgress() {
                this.loading = false;
                clearInterval(this.pInterval);
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                    setTimeout(() => {
                        overlay.classList.add('hidden');
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
