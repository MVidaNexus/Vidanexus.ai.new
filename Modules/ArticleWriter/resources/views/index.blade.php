@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="articleWriter()">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Glassmorphism Card -->
            <div class="card border-0 shadow-lg" style="border-radius: 24px; background: var(--glass-bg); backdrop-filter: var(--glass-blur); border: 1px solid var(--glass-border);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center p-3 mb-4" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(255, 0, 85, 0.1)); border-radius: 20px;">
                            <i class="fa-solid fa-pen-nib fa-3x text-gradient" style="background: linear-gradient(45deg, #00f3ff, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <h2 class="fw-bold tracking-tight mb-2">Pro AI Article Writer</h2>
                        <p class="text-muted">Create high-quality, SEO-optimized articles in seconds.</p>
                    </div>

                    <div class="row g-4">
                        <!-- Left Column: Input -->
                        <div class="col-lg-5">
                            <div class="p-4 rounded-4 bg-white-5" style="border: 1px solid var(--glass-border);">
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Topic or Headline</label>
                                    <textarea x-model="form.prompt" class="form-control bg-transparent text-main border-secondary py-3" placeholder="What should the article be about?" rows="4" style="border-radius: 12px; color: var(--text-main); background: var(--glass-bg); border-color: var(--glass-border);"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Writing Style</label>
                                    <select x-model="form.style" class="form-select text-main border-secondary py-3" style="border-radius: 12px; color: var(--text-main); background: var(--glass-bg); border-color: var(--glass-border);">
                                        <option value="professional">Professional & Informative</option>
                                        <option value="creative">Creative & Engaging</option>
                                        <option value="minimalist">Minimalist / News-like</option>
                                        <option value="persuasive">Persuasive / Marketing</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Language</label>
                                    <div class="d-flex gap-2">
                                        <button @click="form.lang = 'ar'" :class="form.lang === 'ar' ? 'btn-primary' : 'btn-outline-secondary'" class="btn flex-grow-1 border-2 py-2 fw-bold" style="border-radius: 10px;">Arabic</button>
                                        <button @click="form.lang = 'en'" :class="form.lang === 'en' ? 'btn-primary' : 'btn-outline-secondary'" class="btn flex-grow-1 border-2 py-2 fw-bold" style="border-radius: 10px;">English</button>
                                    </div>
                                </div>

                                <button 
                                    @click="generateArticle()" 
                                    class="btn btn-primary w-full py-3 fw-bold shadow-lg mt-2" 
                                    style="border-radius: 14px; font-size: 1.1rem;"
                                    :disabled="isProcessing || !form.prompt"
                                >
                                    <span x-show="!isProcessing"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Generate Article</span>
                                    <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Crafting Best Content...</span>
                                </button>
                            </div>
                        </div>

                        <!-- Right Column: Results -->
                        <div class="col-lg-7">
                            <div class="h-100 d-flex flex-column">
                                <div class="p-4 rounded-4 bg-white-5 flex-grow-1 position-relative" style="border: 1px solid var(--glass-border); min-height: 400px;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="text-muted small fw-bold text-uppercase tracking-wider mb-0">Generated Content</h6>
                                        <button x-show="result" @click="copyToClipboard()" class="btn btn-sm btn-outline-light border-0"><i class="fa-solid fa-copy me-1"></i> Copy</button>
                                    </div>

                                    <div x-show="!result && !isProcessing" class="position-absolute top-50 start-50 translate-middle text-center w-75 opacity-25">
                                        <i class="fa-solid fa-newspaper fa-5x mb-4"></i>
                                        <p class="fw-bold">Your masterpiece will appear here</p>
                                    </div>

                                    <div x-show="isProcessing" class="position-absolute top-50 start-50 translate-middle text-center">
                                        <div class="spinner-grow text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                                        <p class="mt-3 text-muted animate-pulse">AI is thinking and writing...</p>
                                    </div>

                                    <div x-show="result" class="article-content pr-2 custom-scrollbar" style="max-height: 500px; overflow-y: auto;">
                                        <div x-html="renderedResult" class="text-main lh-base" style="color: var(--text-main);"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function articleWriter() {
    return {
        isProcessing: false,
        result: null,
        form: {
            prompt: '',
            style: 'professional',
            lang: 'ar'
        },
        init() {
            // Support pre-filled keywords from other tools
            const params = new URLSearchParams(window.location.search);
            const kw = params.get('keyword');
            if (kw) {
                this.form.prompt = kw;
            }
        },
        async generateArticle() {
            this.isProcessing = true;
            this.result = null;
            
            // Construct the final prompt with style requirements
            const finalPrompt = `Write a ${this.form.style} article in ${this.form.lang === 'ar' ? 'Arabic' : 'English'} about: ${this.form.prompt}. \n\nEnsure proper heading structure and professional flow.`;

            try {
                const response = await fetch('{{ route('articlewriter.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ prompt: finalPrompt })
                });
                const data = await response.json();
                if (data.status === 'success' || data.text) {
                    this.result = data.text;
                } else {
                    alert('Error: ' + (data.message || 'Generation failed.'));
                }
            } catch (err) {
                alert('Connection error. Please try again.');
            } finally {
                this.isProcessing = false;
            }
        },
        get renderedResult() {
            if (!this.result) return '';
            // Basic markdown to HTML conversion for preview
            return this.result
                .replace(/^# (.*$)/gim, '<h3 class="fw-bold mb-3">$1</h3>')
                .replace(/^## (.*$)/gim, '<h4 class="fw-bold mt-4 mb-2">$1</h4>')
                .replace(/\n$/gim, '<br>')
                .replace(/\n/g, '<br>');
        },
        copyToClipboard() {
            navigator.clipboard.writeText(this.result);
            alert('Article copied to clipboard!');
        }
    }
}
</script>

<style>
.bg-white-5 { background: rgba(255,255,255,0.03); }
.text-gradient { -webkit-background-clip: text; -webkit-fill-color: transparent; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>
@endsection
