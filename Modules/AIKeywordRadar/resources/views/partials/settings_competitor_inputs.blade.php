{{-- 1-Click Niche Competitor Presets --}}
<div class="mb-5 p-3.5 rounded-xl border border-white/10 bg-white/[0.02]">
    <div class="flex items-center justify-between mb-2.5">
        <label class="text-xs font-bold text-slate-300 flex items-center gap-2">
            <i class="fas fa-magic" style="color: {{ $colorHex }};"></i>
            {{ $sectionId === 'en' ? 'Quick 1-Click Niche Presets (Add Authority Competitors):' : 'باقات منافسين جاهزة بنقرة واحدة (اختر نيتش مجالك):' }}
        </label>
        <span class="text-[10px] text-slate-400">{{ $sectionId === 'en' ? 'Click to inject' : 'اضغط للإضافة الفورية' }}</span>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($sectionId === 'en')
            <button type="button" onclick="applyNichePreset('en', 'ecommerce')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>🛒 E-Commerce</span>
            </button>
            <button type="button" onclick="applyNichePreset('en', 'tech')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>💻 Tech & AI</span>
            </button>
            <button type="button" onclick="applyNichePreset('en', 'health')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>🩺 Health & Fitness</span>
            </button>
            <button type="button" onclick="applyNichePreset('en', 'finance')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>📈 Finance & Crypto</span>
            </button>
            <button type="button" onclick="applyNichePreset('en', 'realestate')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>🏠 Real Estate</span>
            </button>
            <button type="button" onclick="applyNichePreset('en', 'sports')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-blue-400">
                <span>⚽ Sports</span>
            </button>
        @else
            <button type="button" onclick="applyNichePreset('ar', 'ecommerce')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>🛒 تجارة ومتاجر</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'realestate')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>🏠 عقارات واستثمار</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'health')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>🩺 صحة وطب</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'tech')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>💻 تقنية وهواتف</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'cars')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>🚗 سيارات ومحركات</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'finance')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>📈 اقتصاد وذهب</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'sports')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>⚽ رياضة وكورة</span>
            </button>
            <button type="button" onclick="applyNichePreset('ar', 'news')" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 transition flex items-center gap-1.5 hover:border-orange-400">
                <span>📰 أخبار عامة</span>
            </button>
        @endif
    </div>
</div>

{{-- Competitor Inputs (Add URL, AI Suggest, Import, Export, List) --}}
<label class="block text-sm font-bold mb-2" style="color: var(--text-main);">Add Competitor Website URL</label>
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="url" id="new_competitor_url_{{ $sectionId }}" 
           class="flex-1 px-4 py-3 rounded-xl border focus:ring-1 outline-none transition placeholder-gray-500"
           style="background: var(--card-bg); color: var(--text-main); border-color: var(--glass-border);"
           placeholder="e.g: {{ $placeholder }}">
    <button type="button" onclick="addCompetitor('{{ $sectionId }}')" 
            class="px-6 py-3 text-white rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap hover:opacity-90"
            style="background: {{ $colorHex }}; box-shadow: 0 0 15px {{ $colorHex }}30;">
        <i class="fas fa-plus"></i> Add Website
    </button>
    <button type="button" onclick="extractCompetitors('{{ $sectionId }}')" 
            class="px-6 py-3 bg-white/5 hover:bg-white/10 border rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}30;"
            id="btn_extract_{{ $sectionId }}">
        <i class="fas fa-robot"></i> AI Suggestions
    </button>
</div>
<div class="flex flex-wrap gap-2 mb-6">
    <button type="button" onclick="importCompetitors('{{ $sectionId }}')" 
            class="px-4 py-2 bg-white/5 hover:bg-white/10 border rounded-xl text-xs font-bold transition flex items-center gap-2"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}20;">
        <i class="fas fa-file-import"></i> Import List (.txt)
    </button>
    <button type="button" onclick="exportCompetitors('{{ $sectionId }}')" 
            class="px-4 py-2 bg-white/5 hover:bg-white/10 border rounded-xl text-xs font-bold transition flex items-center gap-2"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}20;">
        <i class="fas fa-file-export"></i> Export List (.txt)
    </button>
    <input type="file" id="import_file_{{ $sectionId }}" accept=".txt,.csv" class="hidden" onchange="handleImportFile('{{ $sectionId }}', this)">
</div>

<div id="competitors_list_{{ $sectionId }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3"></div>
<textarea id="{{ $fieldName }}" name="{{ $fieldName }}" class="hidden">{{ $competitors }}</textarea>
