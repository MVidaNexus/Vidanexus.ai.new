@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="seoAuditor()">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fa-solid fa-magnifying-glass-chart fa-3x mb-3 text-success"></i>
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #10b981, #00bfff); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            AI SEO Auditor
                        </h2>
                        <p class="text-muted small mt-2">Sitemap & Content Gap Intelligence.</p>
                    </div>

                    <!-- Input Area -->
                    <div class="row g-3 mb-5 justify-content-center">
                        <div class="col-md-9">
                            <input type="url" x-model="url" class="form-control bg-dark text-white border-secondary py-3" placeholder="https://your-domain.com" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-2">
                            <button @click="runAudit()" class="btn btn-success py-3 w-100 fw-bold shadow-lg" style="border-radius: 12px;" :disabled="!url || isProcessing">
                                <span x-show="!isProcessing"><i class="fa-solid fa-play"></i> Audit</span>
                                <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </div>

                    <!-- Audit Results -->
                    <template x-if="data">
                        <div class="audit-results mt-5">
                            <h5 class="fw-bold border-bottom border-secondary pb-3 mb-4"><i class="fa-solid fa-list-check me-2 text-success"></i> Prioritized SEO Action Plan</h5>
                            
                            <!-- Summary Grid -->
                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="p-3 bg-dark-soft text-center" style="border-radius: 15px;">
                                        <h3 x-text="data.total_urls" class="fw-bold text-success mb-0"></h3>
                                        <p class="text-muted small mb-0 uppercase">URLs Found</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-dark-soft text-center" style="border-radius: 15px;">
                                        <i class="fa-solid fa-check-circle fa-2x mb-2" :class="data.sitemap_found ? 'text-success' : 'text-danger'"></i>
                                        <p class="text-muted small mb-0 uppercase" x-text="data.sitemap_found ? 'Sitemap Detected' : 'No Sitemap Found'"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-dark-soft text-center" style="border-radius: 15px;">
                                        <h3 class="fw-bold text-success mb-0">5</h3>
                                        <p class="text-muted small mb-0 uppercase">Pages Audited</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Priority Table -->
                            <div class="table-responsive">
                                <table class="table table-dark table-hover align-middle">
                                    <thead class="bg-dark-soft">
                                        <tr>
                                            <th>Page URL</th>
                                            <th>Fix Suggestion</th>
                                            <th>Impact</th>
                                            <th>Effort</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(page, index) in data.audit_sample" :key="index">
                                            <tr>
                                                <td class="small truncate" style="max-width: 200px;" x-text="page.url"></td>
                                                <td>
                                                    <span x-show="page.title_length > 60" class="text-warning">Title too long (>60)</span>
                                                    <span x-show="page.title_length < 30" class="text-warning">Title too short (<30)</span>
                                                    <span x-show="!page.description" class="text-danger">Meta Description MISSING</span>
                                                    <span x-show="page.title && page.description" class="text-success">Optimized</span>
                                                </td>
                                                <td><span class="badge bg-danger rounded-pill px-3">HIGH</span></td>
                                                <td><span class="badge bg-success rounded-pill px-3">EASY</span></td>
                                                <td><i class="fa-solid fa-circle-exclamation text-warning"></i></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function seoAuditor() {
    return {
        url: '',
        isProcessing: false,
        data: null,
        async runAudit() {
            this.isProcessing = true;
            try {
                const response = await fetch('{{ route('dashboard.seo-auditor.analyze') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ url: this.url })
                });
                const res = await response.json();
                if (res.status === 'success') {
                    this.data = res.data;
                } else {
                    alert('Audit Failed: ' + res.message);
                }
            } catch (err) {
                alert('Connection error.');
            } finally {
                this.isProcessing = false;
            }
        }
    }
}
</script>

<style>
.bg-dark-soft { background: rgba(255,255,255,0.05); }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection
