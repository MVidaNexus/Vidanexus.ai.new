{{-- Competitor Inputs (Add URL, AI Suggest, Import, Export, List) --}}
<label class="block text-sm font-bold mb-2" style="color: var(--text-main);">Add New Source</label>
<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="url" id="new_competitor_url_{{ $sectionId }}" 
           class="flex-1 px-4 py-3 rounded-xl border focus:ring-1 outline-none transition placeholder-gray-500"
           style="background: var(--card-bg); color: var(--text-main); border-color: var(--glass-border);"
           placeholder="e.g: {{ $placeholder }}">
    <button type="button" onclick="addCompetitor('{{ $sectionId }}')" 
            class="px-6 py-3 text-white rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap hover:opacity-90"
            style="background: {{ $colorHex }}; box-shadow: 0 0 15px {{ $colorHex }}30;">
        <i class="fas fa-plus"></i> Add
    </button>
    <button type="button" onclick="extractCompetitors('{{ $sectionId }}')" 
            class="px-6 py-3 bg-white/5 hover:bg-white/10 border rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}30;"
            id="btn_extract_{{ $sectionId }}">
        <i class="fas fa-robot"></i> AI Suggest
    </button>
</div>
<div class="flex flex-wrap gap-2 mb-6">
    <button type="button" onclick="importCompetitors('{{ $sectionId }}')" 
            class="px-4 py-2 bg-white/5 hover:bg-white/10 border rounded-xl text-xs font-bold transition flex items-center gap-2"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}20;">
        <i class="fas fa-file-import"></i> Import from File
    </button>
    <button type="button" onclick="exportCompetitors('{{ $sectionId }}')" 
            class="px-4 py-2 bg-white/5 hover:bg-white/10 border rounded-xl text-xs font-bold transition flex items-center gap-2"
            style="color: {{ $colorHex }}; border-color: {{ $colorHex }}20;">
        <i class="fas fa-file-export"></i> Export to File
    </button>
    <input type="file" id="import_file_{{ $sectionId }}" accept=".txt,.csv" class="hidden" onchange="handleImportFile('{{ $sectionId }}', this)">
</div>

<div id="competitors_list_{{ $sectionId }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3"></div>
<textarea id="{{ $fieldName }}" name="{{ $fieldName }}" class="hidden">{{ $competitors }}</textarea>
