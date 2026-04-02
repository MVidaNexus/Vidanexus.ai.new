{{-- Competitor Section Card (for AR) --}}
<div class="glass-card overflow-hidden">
    <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $colorHex }}20; color: {{ $colorHex }};">
            <i class="{{ $icon }} text-lg"></i>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold" style="color: var(--text-main);">{{ $title }}</h3>
            <p class="text-[10px]" style="color: var(--text-muted);">{{ $subtitle }}</p>
        </div>
    </div>

    <div class="p-6">
        <div class="mb-4 p-4 bg-primary-cyan/5 border border-primary-cyan/10 rounded-xl flex items-start gap-3">
            <i class="fas fa-info-circle text-primary-cyan mt-1"></i>
            <p class="text-xs text-gray-400 leading-relaxed">
                You can directly add main site URLs (e.g. <code class="text-primary-cyan">https://youm7.com</code>). 
                The system is smart enough to extract news even if you don't provide a direct RSS link.
            </p>
        </div>

        @include('aikeywordradar::partials.settings_competitor_inputs', [
            'sectionId' => $sectionId,
            'color' => $color,
            'colorHex' => $colorHex,
            'placeholder' => $placeholder,
            'fieldName' => $fieldName,
            'competitors' => $competitors,
        ])
    </div>
</div>
