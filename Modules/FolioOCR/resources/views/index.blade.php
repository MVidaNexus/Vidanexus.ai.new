@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="folioOcr()">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Glassmorphism Card -->
            <div class="card bg-dark text-white border-0 shadow-lg" style="border-radius: 20px; background: rgba(30,30,30,0.8); backdrop-filter: blur(10px);">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fa-solid fa-file-invoice fa-3x mb-3 text-primary"></i>
                        <h2 class="fw-bold tracking-tight text-gradient" style="background: linear-gradient(45deg, #00f3ff, #ff0055); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            Folio-OCR
                        </h2>
                        <p class="text-muted small mt-2">Extract Arabic & English text from images & PDFs locally.</p>
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
                        <input type="file" x-ref="fileInput" class="d-none" @change="handleFiles($event)">
                        <i class="fa-solid fa-file-circle-plus fa-3x mb-3 text-muted"></i>
                        <h5 class="fw-bold">Upload an image or PDF to start OCR</h5>
                    </div>

                    <!-- File Info -->
                    <template x-if="file">
                        <div class="alert bg-dark border-secondary text-white d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <i class="fa-solid fa-paperclip me-2 text-primary"></i>
                                <span x-text="file.name"></span>
                            </div>
                            <button @click="file = null" class="btn btn-sm btn-link text-danger"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </template>

                    <!-- Progress & Actions -->
                    <div class="d-grid mb-5">
                        <button 
                            @click="processOCR()" 
                            class="btn btn-primary py-3 fw-bold" 
                            style="border-radius: 12px;"
                            :disabled="!file || isProcessing"
                        >
                            <span x-show="!isProcessing"><i class="fa-solid fa-bolt me-2"></i> Extract Text Now</span>
                            <span x-show="isProcessing"><i class="fa-solid fa-spinner fa-spin me-2"></i> Analyzing Document...</span>
                        </button>
                    </div>

                    <!-- Results Area -->
                    <template x-if="result">
                        <div class="results-area mt-5 animate__animated animate__fadeIn">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-muted small uppercase">Extracted Text:</h6>
                                <button @click="copyText()" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-copy me-2"></i> Copy All</button>
                            </div>
                            <div class="p-4 bg-dark border border-secondary" style="border-radius: 15px; max-height: 400px; overflow-y: auto;">
                                <pre x-text="result" class="text-white mb-0" style="white-space: pre-wrap; font-family: 'Inter', sans-serif; direction: rtl;"></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function folioOcr() {
    return {
        dragging: false,
        isProcessing: false,
        file: null,
        result: null,
        handleDrop(e) {
            this.dragging = false;
            this.file = e.dataTransfer.files[0];
        },
        handleFiles(e) {
            this.file = e.target.files[0];
        },
        async processOCR() {
            this.isProcessing = true;
            this.result = null;
            const formData = new FormData();
            formData.append('file', this.file);

            try {
                const response = await fetch('{{ route('dashboard.folio-ocr.process') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    this.result = data.data.full_text;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('OCR failed. Check server logs.');
            } finally {
                this.isProcessing = false;
            }
        },
        copyText() {
            navigator.clipboard.writeText(this.result);
            alert('Text copied to clipboard!');
        }
    }
}
</script>
@endsection
