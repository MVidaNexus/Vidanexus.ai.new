@extends('nlpentitiesanalysis::layouts.master')

@section('title', 'NLP & Entities Analysis')

@section('content')
<div x-data="nlpAnalyzer()" class="relative">
    
    <!-- Hero Section -->
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-black mb-4 flex items-center gap-4">
            <span class="p-3 rounded-2xl bg-primary-cyan/10 text-primary-cyan">
                <i class="fas fa-brain"></i>
            </span>
            <span>NLP & <span class="text-primary-cyan">Entities</span> Analyst</span>
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl">
            Analyze your content for Search Intent, E-E-A-T, and Topical Authority using advanced NLP models.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Input Section -->
        <div class="lg:col-span-12 glass-card p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Target Keyword (Optional)</label>
                    <input type="text" x-model="targetKeyword" placeholder="e.g. Best SEO Tools 2024" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-primary-cyan/50 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Content to Analyze</label>
                <textarea x-model="content" rows="12" placeholder="Paste your article or content draft here..."
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-primary-cyan/50 transition-all resize-none"></textarea>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="runAnalysis()" :disabled="loading || !content"
                    class="group relative px-10 py-4 bg-primary-cyan text-black font-black rounded-2xl overflow-hidden transition-all hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="relative z-10 flex items-center gap-3">
                        <template x-if="!loading">
                            <i class="fas fa-bolt"></i>
                        </template>
                        <template x-if="loading">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </template>
                        <span x-text="loading ? 'Analyzing Content...' : 'Run Intelligence Audit'"></span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Results Section -->
        <template x-if="results">
            <div class="lg:col-span-12 grid grid-cols-1 lg:grid-cols-12 gap-8" x-transition>
                
                <!-- Search Intent & Readability -->
                <div class="lg:col-span-4 space-y-8">
                    <div class="glass-card p-6">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <i class="fas fa-magnifying-glass text-primary-cyan"></i>
                            Search Intent
                        </h3>
                        <p class="text-gray-300 leading-relaxed" x-text="results.search_intent"></p>
                    </div>

                    <div class="glass-card p-6 border-l-4 border-primary-cyan">
                        <h3 class="text-xl font-bold mb-4 flex items-center gap-3">
                            <i class="fas fa-gauge-high text-primary-cyan"></i>
                            Readability
                        </h3>
                        <p class="text-gray-300" x-text="results.nlp_readability"></p>
                    </div>
                </div>

                <!-- EEAT & Entities -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- EEAT Score -->
                    <div class="glass-card p-8">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-black flex items-center gap-3">
                                <i class="fas fa-shield-halved text-primary-cyan"></i>
                                E-E-A-T Analysis
                            </h3>
                            <div class="px-4 py-2 bg-primary-cyan/20 border border-primary-cyan text-primary-cyan rounded-xl font-black text-xl">
                                <span x-text="results.eeat_score"></span>/10
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white/5 rounded-2xl p-4">
                                <h4 class="font-bold text-primary-cyan mb-2">Key Gaps</h4>
                                <ul class="space-y-2">
                                    <template x-for="gap in results.content_gaps">
                                        <li class="flex items-start gap-2 text-sm text-gray-300">
                                            <i class="fas fa-circle-exclamation text-yellow-500 mt-1"></i>
                                            <span x-text="gap"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="bg-white/5 rounded-2xl p-4">
                                <h4 class="font-bold text-primary-cyan mb-2">Optimizations</h4>
                                <ul class="space-y-2">
                                    <template x-for="sug in results.eeat_suggestions">
                                        <li class="flex items-start gap-2 text-sm text-gray-300">
                                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                            <span x-text="sug"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Entities Matrix -->
                    <div class="glass-card p-8">
                        <h3 class="text-2xl font-black mb-6 flex items-center gap-3">
                            <i class="fas fa-dna text-primary-cyan"></i>
                            Entity Matrix
                        </h3>
                        <div class="flex flex-wrap gap-3 mb-8">
                            <template x-for="entity in results.entities">
                                <span class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm hover:border-primary-cyan/30 transition-colors">
                                    <span x-text="entity.name"></span>
                                    <span class="ml-2 text-[10px] text-primary-cyan" x-text="Math.round(entity.relevance * 100) + '%'"></span>
                                </span>
                            </template>
                        </div>
                        <div class="p-4 bg-primary-purple/10 border border-primary-purple/30 rounded-2xl">
                            <h4 class="font-bold text-primary-purple mb-4 flex items-center gap-2">
                                <i class="fas fa-plus-circle"></i> Missing Targeted Entities
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="mentity in results.missing_entities">
                                    <span class="px-3 py-1 bg-primary-purple/20 text-xs rounded-lg text-primary-purple border border-primary-purple/20" x-text="mentity"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Floating AI Bot -->
    <div class="fixed bottom-8 right-8 z-50">
        <button @click="toggleChat()" 
            class="w-16 h-16 bg-primary-cyan text-black rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-transform">
            <i class="fas fa-robot text-2xl"></i>
        </button>

        <div x-show="chatOpen" x-cloak x-transition
            class="absolute bottom-20 right-0 w-[400px] max-w-[90vw] glass-card shadow-2xl flex flex-col overflow-hidden" style="height: 500px;">
            <div class="p-4 bg-primary-cyan text-black flex justify-between items-center font-black">
                <span>AI Content Strategist</span>
                <button @click="toggleChat()"><i class="fas fa-times"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-box">
                <template x-for="msg in messages">
                    <div :class="msg.role === 'user' ? 'text-right' : 'text-left'">
                        <div :class="msg.role === 'user' ? 'bg-primary-cyan text-black' : 'bg-white/10 dark:bg-white/10 text-main dark:text-white'"
                            class="inline-block px-4 py-3 rounded-2xl text-sm max-w-[85%] leading-relaxed"
                            style="background: msg.role === 'user' ? 'var(--primary-cyan)' : 'var(--feature-item-bg)'; color: msg.role === 'user' ? '#000' : 'var(--text-main)';"
                            x-text="msg.content"></div>
                    </div>
                </template>
                <div x-show="chatLoading" class="text-left">
                    <div class="inline-block px-4 py-3 bg-white/10 dark:bg-white/10 text-main dark:text-white rounded-2xl text-sm" style="background: var(--feature-item-bg); color: var(--text-main);">
                        <i class="fas fa-circle-notch fa-spin"></i> Thinking...
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-white/10 bg-black/40">
                <div class="flex gap-2">
                    <input type="text" x-model="userInput" @keyup.enter="sendMessage()" 
                        placeholder="Ask about the analysis..."
                        class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-2 focus:outline-none focus:border-primary-cyan/50 text-sm">
                    <button @click="sendMessage()" class="p-2 text-primary-cyan hover:scale-110 transition-transform">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function nlpAnalyzer() {
    return {
        content: '',
        targetKeyword: '',
        loading: false,
        results: null,
        chatOpen: false,
        chatLoading: false,
        userInput: '',
        messages: [
            { role: 'assistant', content: 'Hi! I can help you dive deeper into your SEO and NLP analysis. What would you like to know?' }
        ],

        async runAnalysis() {
            if (!this.content) return;
            this.loading = true;
            this.results = null;

            try {
                const response = await fetch('{{ route("dashboard.nlp-entities.analyze") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        content: this.content,
                        target_keyword: this.targetKeyword
                    })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    this.results = data.data;
                    Swal.fire({
                        icon: 'success',
                        title: 'Audit Complete',
                        text: 'Content intelligence report generated successfully.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire('Error', error.message || 'Analysis failed.', 'error');
            } finally {
                this.loading = false;
            }
        },

        toggleChat() {
            this.chatOpen = !this.chatOpen;
            if (this.chatOpen) {
                this.$nextTick(() => {
                    const box = document.getElementById('chat-box');
                    box.scrollTop = box.scrollHeight;
                });
            }
        },

        async sendMessage() {
            if (!this.userInput.trim() || this.chatLoading) return;

            const text = this.userInput;
            this.userInput = '';
            this.messages.push({ role: 'user', content: text });
            
            this.chatLoading = true;
            
            // Auto-scroll
            this.$nextTick(() => {
                const box = document.getElementById('chat-box');
                box.scrollTop = box.scrollHeight;
            });

            try {
                const context = this.results ? JSON.stringify(this.results) : 'No analysis ran yet.';
                const response = await fetch('/api/ai/chat', { // Assuming a global chat endpoint exists or we use module one
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        message: `User Question: ${text}\n\nContext of current NLP Analysis: ${context}\n\nAnalyze the user's question relative to the content analysis provided.`,
                        tool_context: 'nlp-entities-analysis'
                    })
                });

                const data = await response.json();
                this.messages.push({ role: 'assistant', content: data.response });
            } catch (error) {
                this.messages.push({ role: 'assistant', content: 'Sorry, I encountered an error. Please try again.' });
            } finally {
                this.chatLoading = false;
                this.$nextTick(() => {
                    const box = document.getElementById('chat-box');
                    box.scrollTop = box.scrollHeight;
                });
            }
        }
    }
}
</script>
@endpush
