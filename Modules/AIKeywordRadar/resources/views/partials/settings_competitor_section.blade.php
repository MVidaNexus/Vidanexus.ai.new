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
        <div class="mb-4 p-4 bg-primary-cyan/[0.03] border border-primary-cyan/10 rounded-xl flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-primary-cyan/10 flex items-center justify-center text-primary-cyan flex-shrink-0">
                <i class="fas fa-satellite-dish text-sm"></i>
            </div>
            <p class="text-xs text-gray-400 leading-relaxed">
                <strong class="text-primary-cyan block mb-1">Radar Intelligence Injection</strong>
                Enter direct URLs (e.g., <code class="text-white">https://techcrunch.com</code>). 
                The Radar's extraction engine performs deep-content analysis to identify high-velocity feeds automatically—no manual setup required.
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
