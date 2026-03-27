@extends('dashboard.layouts.app')

@section('title', $tool['name'])

@section('content')
<div x-data="genericTool()" class="max-w-7xl mx-auto pb-20 relative">
    
    <!-- Background Decoration -->
    <div class="fixed top-20 right-0 w-96 h-96 bg-primary-cyan/5 blur-3xl -z-10 rounded-full"></div>
    <div class="fixed bottom-0 left-0 w-64 h-64 bg-primary-purple/5 blur-3xl -z-10 rounded-full"></div>

    <!-- Header Section -->
    <div class="mb-12">
        <div class="flex items-center gap-6 mb-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shadow-lg border" 
                style="background: {{ $tool['color'] }}15; color: {{ $tool['color'] }}; border-color: {{ $tool['color'] }}30;">
                <i class="fas {{ $tool['icon'] }}"></i>
            </div>
            <div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight">{{ $tool['name'] }}</h1>
                <p class="text-gray-400 text-lg font-medium">{{ $tool['tagline'] }}</p>
            </div>
        </div>
        <div class="p-4 bg-white/5 border border-white/10 rounded-2xl inline-flex items-center gap-4">
            <span class="px-3 py-1 bg-primary-cyan/20 text-primary-cyan border border-primary-cyan/30 rounded-lg text-xs font-black uppercase tracking-wider">
                <i class="fas fa-shopping-bag mr-2"></i> Marketplace Module
            </span>
            <div class="h-4 w-px bg-white/10"></div>
            <span class="text-gray-400 text-xs font-bold flex items-center gap-2">
                <i class="fas fa-coins text-primary-cyan"></i>
                Pay-per-Action ({{ $tool['credit_cost_per_action'] ?? 1 }} CR)
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Input Panel -->
        <div class="lg:col-span-12 glass-card p-8">
            <h3 class="text-xl font-bold mb-6 flex items-center gap-3">
                <i class="fas fa-terminal text-primary-cyan"></i>
                Intelligence Parameters
            </h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Your Requirement / Input</label>
                    <textarea x-model="userInput" rows="6" 
                        placeholder="Describe what you need the AI to generate for {{ $tool['name'] }}..."
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 focus:outline-none focus:border-primary-cyan/50 transition-all text-white placeholder-gray-600 resize-none"></textarea>
                </div>

                <div class="flex justify-end">
                    <button @click="generate()" :disabled="loading || !userInput"
                        class="group relative px-12 py-4 bg-primary-cyan text-black font-black rounded-2xl overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-xl disabled:opacity-50 disabled:grayscale">
                        <span class="relative z-10 flex items-center gap-3">
                            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : '{{ $tool['icon'] }}'"></i>
                            <span x-text="loading ? 'Generating Intelligence...' : 'Execute AI Generation'"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Output Panel -->
        <template x-if="response">
            <div class="lg:col-span-12 glass-card p-8 border-l-4" style="border-left-color: {{ $tool['color'] }};" x-transition>
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-2xl font-black tracking-tight flex items-center gap-4">
                        <i class="fas fa-microchip" style="color: {{ $tool['color'] }};"></i>
                        Generated Intelligence Output
                    </h3>
                    <div class="flex gap-2">
                        <button @click="copyToClipboard()" class="p-3 bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-all group relative">
                            <i class="fas fa-copy text-gray-400 group-hover:text-primary-cyan"></i>
                            <span class="absolute -top-10 left-1/2 -translate-x-1/2 px-2 py-1 bg-black text-white text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity">Copy</span>
                        </button>
                    </div>
                </div>

                <div class="p-6 bg-black/40 border border-white/5 rounded-2xl leading-relaxed text-gray-200 whitespace-pre-wrap font-medium" x-text="response"></div>

                <div class="mt-8 p-4 bg-primary-cyan/5 border border-primary-cyan/10 rounded-2xl italic text-sm text-gray-400">
                    <i class="fas fa-info-circle mr-2 text-primary-cyan"></i>
                    This output was generated using the VidaNexus AI Absolute Mode for high-fidelity technical precision.
                </div>
            </div>
        </template>

        <!-- Features Matrix -->
        <div class="lg:col-span-12 mt-12">
            <h3 class="text-sm font-black text-gray-500 uppercase tracking-[0.2em] mb-8 text-center">Module Capabilities Matrix</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($tool['features'] as $feature)
                    <div class="glass-card p-6 flex items-start gap-4 hover:border-white/20 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-primary-cyan group-hover:scale-110 transition-transform Shrink-0">
                            <i class="fas {{ $feature['icon'] }}"></i>
                        </div>
                        <div>
                            <h4 class="font-bold mb-1 text-gray-200">{{ $feature['title'] }}</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
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
