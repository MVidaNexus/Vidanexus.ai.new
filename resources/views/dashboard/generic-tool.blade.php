@extends('dashboard.layouts.app')

@section('title', $tool['name'])

@section('content')
<div x-data="genericTool()" class="container position-relative pb-5">
    
    <!-- Header Section -->
    <div class="mb-5 mt-4">
        <div class="d-flex align-items-center gap-4 mb-4">
            <div class="rounded-3 d-flex align-items-center justify-content-center shadow border flex-shrink-0" 
                style="width: 70px; height: 70px; font-size: 2.2rem; background: {{ $tool['color'] }}15; color: {{ $tool['color'] }}; border-color: {{ $tool['color'] }}30;">
                <i class="fas {{ $tool['icon'] }}"></i>
            </div>
            <div>
                <h1 class="display-5 fw-bold mb-2" style="color: var(--text-main); letter-spacing: -1px;">{{ $tool['name'] }}</h1>
                <p class="fs-5 mb-0" style="color: var(--text-muted);">{{ $tool['tagline'] }}</p>
            </div>
        </div>
        <div class="p-3 rounded-3 d-inline-flex align-items-center gap-3 shadow-sm" style="background: var(--glass-bg); border: 1px solid var(--glass-border);">
            <span class="badge rounded-pill" style="background: rgba(0, 168, 230, 0.15); color: var(--primary-cyan); border: 1px solid rgba(0, 168, 230, 0.3); font-size: 0.75rem; padding: 0.5rem 0.8rem; letter-spacing: 1px; text-transform: uppercase;">
                <i class="fas fa-shopping-bag me-1"></i> Marketplace Module
            </span>
            <div style="width: 1px; height: 16px; background: var(--glass-border);"></div>
            <span class="d-flex align-items-center gap-2" style="color: var(--text-muted); font-size: 0.85rem; font-weight: bold;">
                <i class="fas fa-coins" style="color: var(--primary-cyan);"></i>
                Pay-per-Action ({{ auth()->user()->getToolCreditCost($tool['slug']) }} CR)
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Input Panel -->
        <div class="col-12">
            <div class="card p-4 p-md-5 shadow-sm">
                <h3 class="fs-4 fw-bold mb-4 d-flex align-items-center gap-3" style="color: var(--text-main);">
                    <i class="fas fa-terminal" style="color: var(--primary-cyan);"></i>
                    Intelligence Parameters
                </h3>
                
                <div class="mb-4">
                    <label class="d-block text-uppercase fw-bold mb-2" style="font-size: 0.8rem; letter-spacing: 2px; color: var(--text-muted);">Your Requirement / Input</label>
                    <textarea x-model="userInput" rows="6" 
                        placeholder="Describe what you need the AI to generate for {{ $tool['name'] }}..."
                        class="premium-generic-input"></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button @click="generate()" :disabled="loading || !userInput"
                        class="btn border-0 fw-bold shadow px-5 py-3 rounded-3 d-flex align-items-center gap-3" 
                        style="background: var(--primary-cyan); color: #000; font-size: 1.1rem; transition: all 0.3s;" 
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 20px rgba(0, 168, 230, 0.3)';" 
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
                        <i class="fas" :class="loading ? 'fa-spinner fa-spin' : '{{ $tool['icon'] }}'"></i>
                        <span x-text="loading ? 'Generating Intelligence...' : 'Execute AI Generation'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Output Panel -->
        <template x-if="response">
            <div class="col-12" x-transition>
                <div class="card p-4 p-md-5 border-start border-4 shadow" style="border-left-color: {{ $tool['color'] }} !important;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fs-3 fw-bold d-flex align-items-center gap-3 m-0" style="color: var(--text-main);">
                            <i class="fas fa-microchip" style="color: {{ $tool['color'] }};"></i>
                            Generated Intelligence Output
                        </h3>
                        <button @click="copyToClipboard()" class="btn btn-sm" style="background: var(--glass-bg); border: 1px solid var(--glass-border); color: var(--text-muted);" title="Copy">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>

                    <div class="premium-generic-output" x-text="response"></div>

                    <div class="mt-4 p-3 rounded-3 d-flex gap-3 align-items-start" style="background: rgba(0, 168, 230, 0.05); border: 1px solid rgba(0, 168, 230, 0.1);">
                        <i class="fas fa-info-circle mt-1" style="color: var(--primary-cyan);"></i>
                        <span style="color: var(--text-muted); font-size: 0.9rem; font-style: italic;">
                            This output was generated using the VidaNexus AI Absolute Mode for high-fidelity technical precision.
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Features Matrix -->
        <div class="col-12 mt-5">
            <h3 class="text-center text-uppercase fw-bold mb-4" style="font-size: 0.85rem; letter-spacing: 3px; color: var(--text-muted);">Module Capabilities Matrix</h3>
            <div class="row g-4">
                @foreach($tool['features'] as $feature)
                    <div class="col-12 col-md-4">
                        <div class="premium-generic-feature p-4 d-flex align-items-start gap-3 h-100">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(255, 255, 255, 0.05); color: var(--primary-cyan); font-size: 1.2rem;">
                                <i class="fas {{ $feature['icon'] }}"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-2" style="font-size: 1.1rem; color: var(--text-main);">{{ $feature['title'] }}</h4>
                                <p class="mb-0" style="font-size: 0.85rem; line-height: 1.6; color: var(--text-muted);">{{ $feature['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function genericTool() {
    return {
        userInput: '',
        loading: false,
        response: '',
        
        async generate() {
            if (!this.userInput || this.loading) return;
            this.loading = true;
            this.response = '';

            try {
                const res = await fetch('{{ route(Illuminate\Support\Str::replaceFirst("index", "generate", $tool["route"])) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ input: this.userInput })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    this.response = data.response;
                    if (window.VidaCredits) {
                        if (typeof data.balance !== 'undefined') {
                            window.VidaCredits.updateAll(data.balance);
                        } else {
                            window.VidaCredits.refresh();
                        }
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Intelligence Generated',
                        text: 'The AI output is ready for review.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    throw new Error(data.message);
                }
            } catch (err) {
                Swal.fire('Audit Failed', err.message || 'Error communicating with AI cluster.', 'error');
            } finally {
                this.loading = false;
            }
        },

        copyToClipboard() {
            navigator.clipboard.writeText(this.response);
            Swal.fire({
                icon: 'info',
                title: 'Copied',
                text: 'Output copied to clipboard.',
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 2000
            });
        }
    }
}
</script>
@endpush
