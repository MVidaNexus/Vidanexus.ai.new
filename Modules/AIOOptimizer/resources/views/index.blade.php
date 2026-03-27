@extends('aiooptimizer::layouts.master')

@section('title', 'AIO Optimizer — The Master Blueprint')

@section('content')
<div class="max-w-6xl mx-auto" x-data="aioOptimizer()" x-cloak>

    {{-- ═══════════════ THE MASTER BLUEPRINT HEADER ═══════════════ --}}
    <div class="text-center mb-16 relative">
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 opacity-10 pointer-events-none">
            <i class="fa-solid fa-microchip text-[120px] text-violet-500"></i>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-[10px] font-black tracking-[0.2em] uppercase mb-6">
            <i class="fa-solid fa-code-branch"></i> The Master Blueprint
        </div>
        <h1 class="text-5xl md:text-6xl font-black tracking-tighter mb-4">
            <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-400 bg-clip-text text-transparent italic">AIO</span> Optimizer
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed font-tajawal">
            Predict and maximize your visibility in <span class="text-white font-bold">Google AI Overviews</span>.
            <br><span class="text-gray-500 text-sm">Powered by Semantic Gap Analysis & Citation Probability Scoring.</span>
        </p>
    </div>

    {{-- ═══════════════ INPUT ENGINE ═══════════════ --}}
    <div class="glass-card p-4 md:p-6 mb-12 border-violet-500/20 shadow-[0_0_50px_rgba(139,92,246,0.1)]">
        <div class="flex flex-col md:flex-row gap-4 items-stretch">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                    <i class="fa-solid fa-link text-violet-500/50"></i>
                </div>
                <input
                    type="url"
                    x-model="url"
                    @keydown.enter="runAnalysis()"
                    class="w-full pl-14 pr-6 py-5 rounded-2xl bg-white/[0.03] border border-white/10 text-white placeholder-gray-600 focus:outline-none focus:border-violet-500/50 focus:ring-4 focus:ring-violet-500/10 transition-all text-lg font-medium"
                    placeholder="Enter URL to Optimize for AIO..."
                />
            </div>
            <button
                @click="runAnalysis()"
                :disabled="!url || isProcessing"
                class="px-12 py-5 rounded-2xl font-black text-sm tracking-widest uppercase transition-all duration-500 disabled:opacity-40 flex items-center justify-center gap-3 min-w-[240px]"
                :class="isProcessing ? 'bg-white/5 text-gray-500' : 'bg-gradient-to-r from-violet-600 to-cyan-600 text-white hover:shadow-[0_0_40px_rgba(139,92,246,0.4)] hover:scale-[1.02] active:scale-95'"
            >
                <template x-if="!isProcessing">
                    <span class="flex items-center gap-2 italic"><i class="fa-solid fa-bolt-lightning"></i> Optimize Now</span>
                </template>
                <template x-if="isProcessing">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-atom fa-spin"></i> Processing...</span>
                </template>
            </button>
        </div>
        <div x-show="errorMsg" x-transition class="mt-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-sm font-medium">
            <i class="fa-solid fa-circle-exclamation mr-2"></i><span x-text="errorMsg"></span>
        </div>
    </div>

    {{-- ═══════════════ ANALYZING STATE ═══════════════ --}}
    <div x-show="isProcessing" x-transition class="space-y-8 animate-in fade-in zoom-in duration-500">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-card h-48 animate-pulse bg-white/5"></div>
            <div class="glass-card h-48 animate-pulse bg-white/5"></div>
            <div class="glass-card h-48 animate-pulse bg-white/5"></div>
        </div>
        <div class="text-center">
            <div class="inline-block px-6 py-2 rounded-full bg-white/5 border border-white/10 text-[10px] font-black tracking-widest text-gray-500 uppercase">
                Phase 3: Vector Similarity Check...
            </div>
        </div>
    </div>

    {{-- ═══════════════ THE OUTPUT BLUEPRINT ═══════════════ --}}
    <template x-if="data">
        <div class="space-y-12 pb-20" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0">

            {{-- 1. Blueprint Action Plan (Actionable To-Do List) --}}
            <div class="glass-card p-8 border-violet-500/30">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-violet-500/10 flex items-center justify-center text-violet-400 text-xl"><i class="fa-solid fa-list-check"></i></div>
                    <div>
                        <h3 class="text-2xl font-black text-white italic">Strategic Blueprint</h3>
                        <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Step-by-Step Optimization Roadmap</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(task, tIdx) in data.blueprint_tasks" :key="'task-'+tIdx">
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:border-violet-500/20 transition-all group">
                            <div class="mt-1 w-8 h-8 rounded-lg flex items-center justify-center text-sm" 
                                :class="{
                                    'bg-red-500/10 text-red-400': task.priority === 'Critical',
                                    'bg-orange-500/10 text-orange-400': task.priority === 'High',
                                    'bg-blue-500/10 text-blue-400': task.priority === 'Medium'
                                }">
                                <i class="fa-solid" :class="task.icon"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest" :class="{
                                        'text-red-500': task.priority === 'Critical',
                                        'text-orange-500': task.priority === 'High',
                                        'text-blue-500': task.priority === 'Medium'
                                    }" x-text="task.priority"></span>
                                    <i class="fa-solid fa-circle-check text-gray-800 group-hover:text-violet-500/30 transition-colors"></i>
                                </div>
                                <p class="text-white text-sm font-medium leading-relaxed font-tajawal" x-text="task.task"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- 2. Visibility Meter --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <div class="lg:col-span-12">
                    <div class="glass-card overflow-hidden relative">
                        {{-- Background Accent --}}
                        <div class="absolute top-0 right-0 w-64 h-64 bg-violet-600/10 blur-[100px] rounded-full -mr-32 -mt-32"></div>
                        <div class="p-8 md:p-12 flex flex-col md:flex-row items-center gap-12 relative z-10">
                            {{-- Radial Gauge --}}
                            <div class="relative w-56 h-56 flex-shrink-0">
                                <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="12"/>
                                    <circle cx="60" cy="60" r="52" fill="none"
                                        :stroke="scoreColor"
                                        stroke-width="12"
                                        stroke-linecap="round"
                                        :stroke-dasharray="(data.aio_score / 100 * 326.73) + ' 326.73'"
                                        class="transition-all duration-[2000ms] ease-out-expo"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-6xl font-black italic tracking-tighter" :class="scoreTextColor" x-text="data.aio_score"></span>
                                    <span class="text-[10px] text-gray-500 font-black tracking-[0.3em] uppercase mt-1">Probability</span>
                                </div>
                            </div>
                            
                            {{-- Metrics Details --}}
                            <div class="flex-1 space-y-8">
                                <div>
                                    <h2 class="text-3xl font-black text-white italic mb-2">Visibility Meter</h2>
                                    <p class="text-gray-400 font-medium font-tajawal" x-text="scoreLabel"></p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                                        <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Factualness</div>
                                        <div class="text-xl font-bold text-white italic" x-text="data.visibility_metrics.factualness + '/40'"></div>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                                        <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Answer Density</div>
                                        <div class="text-xl font-bold text-white italic" x-text="data.visibility_metrics.answer_density + '/30'"></div>
                                    </div>
                                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5">
                                        <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Structure</div>
                                        <div class="text-xl font-bold text-white italic" x-text="data.visibility_metrics.structure + '/30'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. The Missing Link (Entity Map) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="glass-card p-8 group overflow-hidden relative">
                    <div class="absolute top-4 right-4 text-emerald-500/20 text-4xl group-hover:scale-110 transition-transform"><i class="fa-solid fa-link"></i></div>
                    <h3 class="text-xl font-black text-white italic mb-6">Detected Bridge</h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(e, i) in data.entity_map.detected" :key="'det-'+i">
                            <span class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold" x-text="e"></span>
                        </template>
                    </div>
                </div>
                <div class="glass-card p-8 group border-red-500/30 shadow-[0_0_30px_rgba(239,68,68,0.05)] relative overflow-hidden">
                    <div class="absolute top-4 right-4 text-red-500/20 text-4xl group-hover:rotate-12 transition-transform"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h3 class="text-xl font-black text-white italic mb-6">The Missing Link</h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(e, i) in data.entity_map.missing" :key="'mis-'+i">
                            <span class="px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-black uppercase tracking-wider" x-text="e"></span>
                        </template>
                        <template x-if="data.entity_map.missing.length === 0">
                            <span class="text-gray-500 italic text-sm">No missing semantic entities found. Content is well-aligned!</span>
                        </template>
                    </div>
                </div>
            </div>

            {{-- 4. Snippet Architect (Content Rewrite) --}}
            <div class="glass-card overflow-hidden">
                <div class="p-8 border-b border-white/5 flex items-center justify-between bg-white/[0.01]">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 text-xl"><i class="fa-solid fa-pen-nib"></i></div>
                        <div>
                            <h3 class="text-2xl font-black text-white italic">Snippet Architect</h3>
                            <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">AI-Ready Content Refactoring</p>
                        </div>
                    </div>
                    <span class="px-4 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-[10px] font-black tracking-widest text-cyan-400 uppercase">Recommended</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="p-8 border-r border-white/5 space-y-4">
                        <div class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">Original Paragraph</div>
                        <div class="p-6 rounded-2xl bg-white/[0.02] border border-white/5 text-gray-400 text-sm italic leading-relaxed font-tajawal" x-text="data.snippet_architect.original"></div>
                    </div>
                    <div class="p-8 space-y-4 bg-cyan-500/[0.02]">
                        <div class="text-[10px] font-black text-cyan-500/70 uppercase tracking-[0.2em] flex items-center justify-between">
                            AI-Optimized Snippet
                            <button @click="copyToClipboard(data.snippet_architect.optimized)" class="hover:text-cyan-400 transition-colors"><i class="fa-solid fa-copy"></i></button>
                        </div>
                        <div class="p-6 rounded-2xl bg-cyan-500/[0.05] border border-cyan-500/20 text-white text-lg font-medium leading-relaxed font-tajawal" x-text="data.snippet_architect.optimized"></div>
                        <p class="text-[10px] text-gray-600 italic leading-relaxed">
                            <i class="fa-solid fa-circle-info mr-1"></i> This version uses concise definition patterns that Google AIO favors for citation selection.
                        </p>
                    </div>
                </div>
                {{-- NEW: Missing Content Block --}}
                <div class="p-8 bg-violet-600/5 border-t border-white/5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-[10px] font-black text-violet-400 uppercase tracking-widest italic">Essential Content Additions</div>
                        <button @click="copyToClipboard(data.missing_content_block)" class="text-violet-400 hover:text-white transition-colors"><i class="fa-solid fa-copy"></i> Copy Section</button>
                    </div>
                    <div class="p-6 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-100 text-lg font-medium italic leading-relaxed font-tajawal" x-text="data.missing_content_block"></div>
                    <p class="mt-4 text-[10px] text-violet-400/50 uppercase tracking-widest font-black">Pro Tip: Add this as a new paragraph or as part of your "Overview" section.</p>
                </div>
            </div>

            {{-- 4. Technical Injection (Schema Generator) --}}
            <div class="glass-card p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-xl"><i class="fa-solid fa-terminal"></i></div>
                        <div>
                            <h3 class="text-2xl font-black text-white italic">Technical Injection</h3>
                            <p class="text-xs text-gray-500 uppercase tracking-widest font-bold">Automatic JSON-LD Schema (Speakable, FAQ, HowTo)</p>
                        </div>
                    </div>
                    <button 
                        @click="copyAllSchema()" 
                        class="px-6 py-3 rounded-2xl bg-emerald-500 text-white font-black text-xs tracking-widest uppercase hover:shadow-[0_0_30px_rgba(16,185,129,0.3)] transition-all active:scale-95"
                    >
                        Copy All Schema
                    </button>
                </div>
                
                <div class="space-y-6">
                    <template x-for="(schema, idx) in data.technical_injection" :key="'sch-'+idx">
                        <div class="group relative">
                            <div class="absolute top-3 right-4 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-[10px] font-black text-gray-600 uppercase" x-text="schema['@type']"></span>
                                <button @click="copyToClipboard(JSON.stringify(schema, null, 2))" class="text-emerald-500 hover:text-emerald-400 transition-colors"><i class="fa-solid fa-copy"></i></button>
                            </div>
                            <div class="p-6 rounded-2xl bg-[#0a0b0e] border border-white/5 overflow-x-auto">
                                <pre class="text-xs font-mono text-emerald-500/80 scrollbar-hide"><code x-text="JSON.stringify(schema, null, 2)"></code></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function aioOptimizer() {
    return {
        url: '',
        isProcessing: false,
        data: null,
        errorMsg: '',

        get scoreColor() {
            if (!this.data) return '#333';
            const s = this.data.aio_score;
            if (s >= 80) return '#10b981'; // Emerald
            if (s >= 60) return '#0ea5e9'; // Cyan
            if (s >= 40) return '#f59e0b'; // Amber
            return '#ef4444'; // Red
        },

        get scoreTextColor() {
            if (!this.data) return 'text-gray-400';
            const s = this.data.aio_score;
            if (s >= 80) return 'text-emerald-400';
            if (s >= 60) return 'text-cyan-400';
            if (s >= 40) return 'text-amber-400';
            return 'text-red-400';
        },

        get scoreLabel() {
            if (!this.data) return '';
            const s = this.data.aio_score;
            if (s >= 85) return 'Elite Performance — Exceptional Citation Probability.';
            if (s >= 70) return 'High Visibility — Strong Semantic Alignment detected.';
            if (s >= 50) return 'Moderate — Entity gaps are hindering AIO selection.';
            return 'Critical Optimization required. Content logic is fragmented.';
        },

        async runAnalysis() {
            if (!this.url || this.isProcessing) return;
            this.isProcessing = true;
            this.data = null;
            this.errorMsg = '';

            try {
                const response = await fetch('{{ route("dashboard.aio-optimizer.analyze") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.url })
                });

                const res = await response.json();

                if (res.status === 'success' && res.data) {
                    this.data = res.data;
                    this.$nextTick(() => {
                        window.scrollTo({ top: 400, behavior: 'smooth' });
                    });
                } else {
                    this.errorMsg = res.message || 'Analysis failed. The Ingestion Engine encountered an error.';
                }
            } catch (err) {
                this.errorMsg = 'Connection Pipeline Interrupted. Verify URL and try again.';
            } finally {
                this.isProcessing = false;
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: 'success',
                    title: 'Copied to Clipboard!',
                    background: '#1a1b23',
                    color: '#fff'
                });
            });
        },

        copyAllSchema() {
            if (!this.data?.technical_injection) return;
            const fullCode = this.data.technical_injection.map(s => 
                '<script type="application/ld+json">\n' + JSON.stringify(s, null, 2) + '\n<\/script>'
            ).join('\n\n');
            this.copyToClipboard(fullCode);
        }
    };
}
</script>
@endpush

@push('styles')
<style>
    @font-face {
        font-family: 'Tajawal';
        src: url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');
    }
    .font-tajawal { font-family: 'Tajawal', sans-serif; }

    .ease-out-expo {
        transition-timing-function: cubic-bezier(0.19, 1, 0.22, 1);
    }
    
    pre::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    .glass-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 100% 0%, rgba(139, 92, 246, 0.05), transparent 40%);
        pointer-events: none;
    }
</style>
@endpush
