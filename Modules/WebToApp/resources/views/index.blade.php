@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="webToApp()">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fa-solid fa-mobile-screen-button fa-4x mb-3 text-success" style="color: #3DDC84 !important;"></i>
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #3DDC84, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            WebToApp Creator
                        </h2>
                        <p class="text-muted small mt-2">Generate Android source packages from any URL instantly.</p>
                    </div>

                    <!-- Configuration Form -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-12">
                            <label class="form-label text-muted small uppercase">Website URL</label>
                            <input type="url" x-model="form.url" class="form-control bg-dark text-white border-secondary py-3" placeholder="https://your-site.com" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small uppercase">App Name</label>
                            <input type="text" x-model="form.name" class="form-control bg-dark text-white border-secondary py-3" placeholder="My Awesome App" style="border-radius: 12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small uppercase">Package ID</label>
                            <input type="text" x-model="form.package" class="form-control bg-dark text-white border-secondary py-3" placeholder="com.company.myapp" style="border-radius: 12px;">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-grid mb-5">
                        <button 
                            @click="generateApp()" 
                            class="btn btn-success py-3 fw-bold shadow-lg" 
                            style="border-radius: 12px; background-color: #3DDC84; border-color: #3DDC84;"
                            :disabled="isProcessing || !form.url || !form.name"
                        >
                            <span x-show="!isProcessing"><i class="fa-solid fa-code me-2"></i> Generate Source Package</span>
                            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Packaging Android Project...</span>
                        </button>
                    </div>

                    <!-- Download Area -->
                    <template x-if="downloadUrl">
                        <div class="download-box p-4 border border-success text-center animate__animated animate__bounceIn" style="border-radius: 20px; background: rgba(61, 220, 132, 0.05);">
                            <h5 class="fw-bold mb-3"><i class="fa-solid fa-check-circle me-2 text-success"></i> Generation Complete!</h5>
                            <p class="text-muted small">Your Android Studio source package is ready for download.</p>
                            <a :href="downloadUrl" class="btn btn-outline-success px-5 py-2 border-2 fw-bold" download>
                                <i class="fa-solid fa-download me-2"></i> Download ZIP
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function webToApp() {
    return {
        isProcessing: false,
        downloadUrl: null,
        form: {
            url: '',
            name: '',
            package: 'com.vidanexus.app'
        },
        async generateApp() {
            this.isProcessing = true;
            this.downloadUrl = null;
            try {
                const response = await fetch('{{ route('dashboard.web-to-app.generate') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        url: this.form.url,
                        app_name: this.form.name,
                        package_name: this.form.package
                    })
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.downloadUrl = data.download_url;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Generation failed. Please check parameters.');
            } finally {
                this.isProcessing = false;
            }
        }
    }
}
</script>
@endsection
