@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="auditX()">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fa-solid fa-chart-pie fa-3x mb-3 text-warning"></i>
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #ffcc00, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            AuditX (CRO)
                        </h2>
                        <p class="text-muted small mt-2">Professional E-commerce Conversion Intelligence.</p>
                    </div>

                    <!-- Input Form -->
                    <div class="row g-3 mb-5 align-items-end justify-content-center">
                        <div class="col-md-8">
                            <label class="form-label text-muted small uppercase">E-commerce Website URL</label>
                            <input type="url" x-model="url" class="form-control bg-dark text-white border-secondary py-3" placeholder="https://ecommerce-site.com" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-3">
                            <button 
                                @click="runAudit()" 
                                class="btn btn-warning py-3 w-100 fw-bold shadow-lg" 
                                style="border-radius: 12px;"
                                :disabled="!url || isProcessing"
                            >
                                <span x-show="!isProcessing"><i class="fa-solid fa-bolt me-2"></i> Start Audit</span>
                                <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Analyzing...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Report Viewer -->
                    <template x-if="report">
                        <div class="report-container mt-5 animate__animated animate__fadeInUp">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                                <h4 class="fw-bold text-warning mb-0"><i class="fa-solid fa-file-signature me-2"></i> Store Analysis Report</h4>
                                <div>
                                    <button @click="window.print()" class="btn btn-sm btn-outline-light me-2"><i class="fa-solid fa-print"></i></button>
                                    <button @click="copyReport()" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-copy"></i></button>
                                </div>
                            </div>
                            
                            <!-- Arabic Report Content -->
                            <div class="p-4 bg-dark-soft" style="border-radius: 20px; direction: ltr; line-height: 1.8;">
                                <div x-html="formattedReport" class="text-light"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function auditX() {
    return {
        url: '',
        isProcessing: false,
        report: null,
        async runAudit() {
            this.isProcessing = true;
            this.report = null;
            try {
                const response = await fetch('{{ route('dashboard.audit-x.analyze') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ url: this.url })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.report = data.report;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Audit failed. Please check connection.');
            } finally {
                this.isProcessing = false;
            }
        },
        get formattedReport() {
            if (!this.report) return '';
            // Simple markdown-to-html converter (basic bullets and headers)
            return this.report
                .replace(/^# (.*$)/gm, '<h3 class="text-warning mt-4">$1</h3>')
                .replace(/^## (.*$)/gm, '<h4 class="text-light mt-3">$1</h4>')
                .replace(/^- (.*$)/gm, '<li class="mb-2">$1</li>')
                .replace(/\n\n/g, '<br>')
                .replace(/\n/g, '<br>');
        },
        copyReport() {
            navigator.clipboard.writeText(this.report);
            alert('Report copied!');
        }
    }
}
</script>

<style>
.bg-dark-soft { background: rgba(255,255,255,0.03); }
.arabic-text { font-family: 'Inter', 'Amiri', serif; font-size: 1.1rem; }
@media print {
    body * { visibility: hidden; }
    .report-container, .report-container * { visibility: visible; }
    .report-container { position: absolute; left: 0; top: 0; width: 100%; surface: #fff !important; color: #000 !important; }
}
</style>
@endsection
