@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="imgCompress()">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <img src="{{ asset('assets/img/img-compress-logo.png') }}" alt="ImgCompress" class="mb-3" style="width: 120px;">
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #00f3ff, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            ImgCompress
                        </h2>
                        <p class="text-muted small mt-2">Premium local image toolkit without cloud limits.</p>
                    </div>

                    <!-- Settings Grid -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label text-muted small uppercase">Output Format</label>
                            <select x-model="settings.format" class="form-select bg-dark text-white border-secondary" style="border-radius: 10px;">
                                <option value="webp">WebP (Best for Web)</option>
                                <option value="avif">AVIF (Best Compression)</option>
                                <option value="png">PNG (Lossless)</option>
                                <option value="jpg">JPG (Compatible)</option>
                                <option value="pdf">Convert to PDF</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small uppercase">Compression Quality: <span x-text="settings.quality"></span>%</label>
                            <input type="range" x-model="settings.quality" class="form-range" min="1" max="100">
                            <div class="d-flex justify-content-between text-muted x-small">
                                <span>Smaller (60)</span>
                                <span>Balanced (85)</span>
                                <span>Max (100)</span>
                            </div>
                        </div>
                    </div>

                    <!-- AI Toggles -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="form-check form-switch p-0">
                                <label class="d-flex align-items-center justify-content-between" style="cursor: pointer;">
                                    <span class="text-muted small uppercase">Remove Background with AI (u2net)</span>
                                    <input class="form-check-input ms-3 mt-0" type="checkbox" x-model="settings.remove_bg">
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch p-0">
                                <label class="d-flex align-items-center justify-content-between" style="cursor: pointer;">
                                    <span class="text-muted small uppercase">Resize Width</span>
                                    <input class="form-check-input ms-3 mt-0" type="checkbox" x-model="settings.use_resize">
                                </label>
                            </div>
                            <template x-if="settings.use_resize">
                                <input type="number" x-model="settings.resize_width" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Width in PX">
                            </template>
                        </div>
                    </div>

                    <!-- Drop Zone -->
                    <div 
                        @dragover.prevent="dragging = true" 
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="dragging ? 'border-primary' : 'border-secondary'"
                        class="drop-zone border-2 border-dashed p-5 text-center mb-4 cursor-pointer" 
                        style="border-radius: 15px; background: rgba(255,255,255,0.02); transition: 0.3s;"
                        @click="$refs.fileInput.click()"
                    >
                        <input type="file" x-ref="fileInput" class="d-none" multiple @change="handleFiles($event)">
                        <i class="fa-solid fa-cloud-arrow-up fa-3x mb-3 text-muted"></i>
                        <h5 class="fw-bold">Drag & drop images or PDFs here, or click to select</h5>
                    </div>

                    <!-- File List -->
                    <template x-if="files.length > 0">
                        <div class="file-list mb-5">
                            <h6 class="text-muted small uppercase mb-3">Files to process:</h6>
                            <div class="list-group">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center" style="border-radius: 10px; margin-bottom: 5px;">
                                        <div class="d-flex align-items-center">
                                            <i class="fa-regular fa-image me-3 text-primary"></i>
                                            <span x-text="file.name" class="small truncate" style="max-width: 250px;"></span>
                                        </div>
                                        <button @click="removeFile(index)" class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Results Area -->
                    <template x-if="results.length > 0">
                        <div class="results-area mt-5 animate__animated animate__fadeIn">
                            <h5 class="fw-bold border-bottom border-secondary pb-3 mb-4 text-success">
                                <i class="fa-solid fa-check-circle me-2"></i> Processed Successfully
                            </h5>
                            <div class="row g-3">
                                <template x-for="(res, idx) in results" :key="idx">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-dark-soft d-flex justify-content-between align-items-center" style="border-radius: 12px; border: 1px solid rgba(0, 255, 170, 0.1);">
                                            <div class="overflow-hidden">
                                                <p class="mb-0 small fw-bold text-white truncate" x-text="res.original_name"></p>
                                                <span class="x-small text-muted" x-text="(res.size / 1024).toFixed(1) + ' KB'"></span>
                                            </div>
                                            <a :href="res.download_url" download class="btn btn-sm btn-success px-3" style="border-radius: 8px;">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-4 text-center">
                                <button @click="results = []" class="btn btn-link text-muted small"><i class="fa-solid fa-rotate-left me-1"></i> Start New Batch</button>
                            </div>
                        </div>
                    </template>

                    <!-- Actions -->
                    <div class="d-flex gap-3">
                        <button 
                            @click="startProcessing()" 
                            class="btn btn-primary flex-grow-1 py-3 fw-bold shadow-lg" 
                            style="border-radius: 12px; font-size: 1.1rem;"
                            :disabled="isProcessing || files.length === 0"
                        >
                            <span x-show="!isProcessing"><i class="fa-solid fa-play me-2"></i> Start Converting</span>
                            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Processing...</span>
                        </button>
                        <button @click="clearFiles()" class="btn btn-outline-secondary px-4 py-3 border-2" style="border-radius: 12px;"><i class="fa-solid fa-broom"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function imgCompress() {
    return {
        dragging: false,
        isProcessing: false,
        files: [],
        results: [],
        settings: {
            format: 'webp',
            quality: 85,
            remove_bg: false,
            use_resize: false,
            resize_width: 1200
        },
        handleDrop(e) {
            this.dragging = false;
            this.addFiles(Array.from(e.dataTransfer.files));
        },
        handleFiles(e) {
            this.addFiles(Array.from(e.target.files));
        },
        addFiles(newFiles) {
            this.files = [...this.files, ...newFiles];
        },
        removeFile(index) {
            this.files.splice(index, 1);
        },
        clearFiles() {
            this.files = [];
            this.results = [];
        },
        async startProcessing() {
            this.isProcessing = true;
            const formData = new FormData();
            this.files.forEach(file => formData.append('files[]', file));
            formData.append('format', this.settings.format);
            formData.append('quality', this.settings.quality);
            formData.append('remove_bg', this.settings.remove_bg ? 1 : 0);
            formData.append('resize_width', this.settings.use_resize ? this.settings.resize_width : 0);

            try {
                const response = await fetch('{{ route('dashboard.img-compress.process') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    this.results = result.files;
                    this.files = []; // Clear input files on success
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                alert('An unexpected error occurred.');
            } finally {
                this.isProcessing = false;
            }
        }
    }
}
</script>

<style>
.text-gradient { -webkit-background-clip: text; -webkit-fill-color: transparent; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cursor-pointer { cursor: pointer; }
.drop-zone:hover { background: rgba(255,255,255,0.05) !important; border-color: #00f3ff !important; }
</style>
@endsection
