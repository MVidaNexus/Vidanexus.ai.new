@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="moneyPrinter()">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fa-solid fa-money-bill-trend-up fa-3x mb-3 text-success"></i>
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #1DB954, #1ed760); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            MoneyPrinter V2
                        </h2>
                        <p class="text-muted small mt-2">Automated content creation and revenue generation systems.</p>
                    </div>

                    <!-- Automation Engine Selection -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-7">
                            <label class="form-label text-muted small uppercase">Niche / Topic for Automation</label>
                            <input type="text" x-model="topic" class="form-control bg-dark text-white border-secondary py-3" placeholder="e.g. AI News, Fitness Tips, Coffee Reviews" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small uppercase">Target Platform</label>
                            <div class="d-flex gap-2">
                                <template x-for="p in platforms">
                                    <button 
                                        @click="selectedPlatform = p.id" 
                                        class="btn flex-grow-1 py-3 border-2 fw-bold" 
                                        :class="selectedPlatform === p.id ? 'btn-success border-success' : 'btn-outline-secondary border-secondary'"
                                        style="border-radius: 12px; transition: 0.3s;"
                                    >
                                        <i :class="p.icon" class="me-2"></i> <span x-text="p.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid mb-5">
                        <button 
                            @click="startMoneyMaker()" 
                            class="btn btn-success py-3 fw-bold shadow-lg" 
                            style="border-radius: 12px; background: #1DB954;"
                            :disabled="!topic || isProcessing"
                        >
                            <span x-show="!isProcessing"><i class="fa-solid fa-play me-2"></i> Launch Automation Engine</span>
                            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Generating Viral Content...</span>
                        </button>
                    </div>

                    <!-- Automation Feed -->
                    <template x-if="feed">
                        <div class="automation-feed mt-5 animate__animated animate__fadeIn">
                            <h6 class="text-muted small uppercase mb-4 border-bottom border-secondary pb-2"><i class="fa-solid fa-rss me-2"></i> Live Generation Results:</h6>
                            <div class="row g-4">
                                <template x-if="selectedPlatform === 'twitter'">
                                    <template x-for="post in feed.posts">
                                        <div class="col-md-12">
                                            <div class="p-3 bg-dark-soft border border-secondary" style="border-radius: 15px;">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="badge bg-info">X (Twitter) Post</span>
                                                    <i class="fa-brands fa-twitter text-info"></i>
                                                </div>
                                                <p x-text="post" class="small mb-0"></p>
                                                <div class="mt-2 text-end">
                                                    <button class="btn btn-sm btn-link text-white small p-0"><i class="fa-solid fa-paper-plane me-1"></i> Post Now</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                                <template x-if="selectedPlatform === 'shorts'">
                                    <div class="col-md-12">
                                        <div class="p-3 bg-dark-soft border border-secondary" style="border-radius: 15px;">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="badge bg-danger">YouTube Shorts Script</span>
                                                <i class="fa-brands fa-youtube text-danger"></i>
                                            </div>
                                            <pre x-text="feed.scripts[0]" class="small mb-0 text-white" style="white-space: pre-wrap; font-family: 'Inter', sans-serif;"></pre>
                                            <div class="mt-3 text-center">
                                                <span class="badge bg-success py-2 px-4 shadow-sm"><i class="fa-solid fa-check-circle me-1"></i> Ready for Rendering</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function moneyPrinter() {
    return {
        topic: '',
        selectedPlatform: 'twitter',
        isProcessing: false,
        feed: null,
        platforms: [
            { id: 'twitter', name: 'X', icon: 'fa-brands fa-twitter' },
            { id: 'shorts', name: 'Shorts', icon: 'fa-brands fa-youtube' },
            { id: 'affiliate', name: 'Affiliate', icon: 'fa-solid fa-link' }
        ],
        async startMoneyMaker() {
            this.isProcessing = true;
            this.feed = null;
            try {
                const response = await fetch('{{ route('dashboard.money-printer.run') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ topic: this.topic, platform: this.selectedPlatform })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.feed = data.data;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Automation error.');
            } finally {
                this.isProcessing = false;
            }
        }
    }
}
</script>

<style>
.bg-dark-soft { background: rgba(255,255,255,0.03); }
</style>
@endsection
