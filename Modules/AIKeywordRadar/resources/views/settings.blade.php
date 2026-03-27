@extends('aikeywordradar::layouts.master')

@section('title', 'AI Keyword Radar Settings')

@section('content')
<div class="max-w-4xl mx-auto pb-12">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black flex items-center gap-3" style="color: var(--text-main);">
                <i class="fas fa-cog text-primary-cyan animate-spin-slow"></i>
                AI Keyword Radar Settings
            </h1>
            <p class="mt-2" style="color: var(--text-muted);">Customize monitoring sources and AI providers to extract emerging trends</p>
        </div>
        <a href="{{ route('dashboard.ai-keyword-radar.index') }}" class="px-5 py-2.5 bg-white/5 hover:bg-white/10 text-white rounded-xl font-bold transition flex items-center gap-2">
            <i class="fas fa-arrow-right"></i> Back to Radar
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-emerald-500 rounded-xl flex items-center gap-3 font-bold">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-500">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            {{ session('success') }}
        </div>
    @endif

    <form id="settingsForm" action="{{ route('dashboard.ai-keyword-radar.settings.update') }}" method="POST">
        @csrf
        <div class="space-y-6">

            {{-- Arabic Competitors --}}
            <div class="glass-card overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center text-orange-500">
                        <i class="fas fa-globe-africa text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold" style="color: var(--text-main);">Monitor Competitors (Ar - Arabic)</h3>
                        <p class="text-[10px]" style="color: var(--text-muted);">Add direct news site URLs or RSS links.</p>
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

                    <label class="block text-sm font-bold mb-2" style="color: var(--text-main);">Add New Source</label>
                    <div class="flex flex-col sm:flex-row gap-3 mb-6">
                        <input type="url" id="new_competitor_url_ar" 
                               class="flex-1 px-4 py-3 rounded-xl border focus:ring-1 outline-none transition placeholder-gray-500"
                               style="background: var(--card-bg); color: var(--text-main); border-color: var(--glass-border);"
                               placeholder="e.g: https://youm7.com">
                        <button type="button" onclick="addCompetitor('ar')" 
                                class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold transition shadow-[0_0_15px_rgba(249,115,22,0.3)] flex items-center justify-center gap-2 whitespace-nowrap">
                            <i class="fas fa-plus"></i> Add
                        </button>
                        <button type="button" onclick="extractCompetitors('ar')" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 text-orange-500 border border-orange-500/20 rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap"
                                id="btn_extract_ar">
                            <i class="fas fa-magic"></i> Extract Competitors
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button type="button" onclick="importCompetitors('ar')" 
                                class="px-4 py-2 bg-white/5 hover:bg-white/10 text-orange-400 border border-orange-500/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-file-import"></i> Import from File
                        </button>
                        <button type="button" onclick="exportCompetitors('ar')" 
                                class="px-4 py-2 bg-white/5 hover:bg-white/10 text-orange-400 border border-orange-500/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export to File
                        </button>
                        <input type="file" id="import_file_ar" accept=".txt,.csv" class="hidden" onchange="handleImportFile('ar', this)">
                    </div>

                    <div id="competitors_list_ar" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3"></div>
                    <textarea id="keywords_competitors" name="keywords_competitors" class="hidden">{{ $settings['keywords_competitors'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- English Competitors --}}
            <div class="glass-card overflow-hidden">
                <div class="px-6 py-4 border-b flex flex-col sm:flex-row justify-between sm:items-center gap-4" style="border-color: var(--glass-border); background: rgba(0,0,0,0.02);">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-500 flex-shrink-0">
                            <i class="fas fa-globe-americas text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold" style="color: var(--text-main);">Monitor Competitors (En - English)</h3>
                                
                                {{-- Enable Toggle next to title --}}
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="enable_keywords_en" value="1" class="sr-only peer" 
                                           {{ !empty($settings['enable_keywords_en']) ? 'checked' : '' }} 
                                           onchange="document.getElementById('en_competitors_content').style.display = this.checked ? 'block' : 'none'">
                                    <div class="relative w-9 h-5 bg-gray-500/30 rounded-full peer peer-checked:bg-green-500 peer-checked:shadow-[0_0_10px_rgba(34,197,94,0.4)] transition-all after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"></div>
                                    <span class="ms-2 text-xs font-bold" style="color: var(--text-main);">Enable</span>
                                </label>
                            </div>
                            <p class="text-[10px]" style="color: var(--text-muted);">Add English news site URLs.</p>
                        </div>
                    </div>
                </div>

                <div id="en_competitors_content" class="p-6" style="display: {{ !empty($settings['enable_keywords_en']) ? 'block' : 'none' }};">
                    <label class="block text-sm font-bold mb-2" style="color: var(--text-main);">Add New Source</label>
                    <div class="flex flex-col sm:flex-row gap-3 mb-6">
                        <input type="url" id="new_competitor_url_en" 
                               class="flex-1 px-4 py-3 rounded-xl border focus:ring-1 outline-none transition placeholder-gray-500"
                               style="background: var(--card-bg); color: var(--text-main); border-color: var(--glass-border);"
                               placeholder="e.g: https://techcrunch.com">
                        <button type="button" onclick="addCompetitor('en')" 
                                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-bold transition shadow-[0_0_15px_rgba(59,130,246,0.3)] flex items-center justify-center gap-2 whitespace-nowrap">
                            <i class="fas fa-plus"></i> Add
                        </button>
                        <button type="button" onclick="extractCompetitors('en')" 
                                class="px-6 py-3 bg-white/5 hover:bg-white/10 text-blue-500 border border-blue-500/20 rounded-xl font-bold transition flex items-center justify-center gap-2 whitespace-nowrap"
                                id="btn_extract_en">
                            <i class="fas fa-magic"></i> Extract Competitors
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button type="button" onclick="importCompetitors('en')" 
                                class="px-4 py-2 bg-white/5 hover:bg-white/10 text-blue-400 border border-blue-500/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-file-import"></i> Import from File
                        </button>
                        <button type="button" onclick="exportCompetitors('en')" 
                                class="px-4 py-2 bg-white/5 hover:bg-white/10 text-blue-400 border border-blue-500/20 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <i class="fas fa-file-export"></i> Export to File
                        </button>
                        <input type="file" id="import_file_en" accept=".txt,.csv" class="hidden" onchange="handleImportFile('en', this)">
                    </div>

                    <div id="competitors_list_en" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3"></div>
                    <textarea id="keywords_competitors_en" name="keywords_competitors_en" class="hidden">{{ $settings['keywords_competitors_en'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end pt-4">
                <button type="submit" class="px-8 py-3.5 rounded-xl text-lg flex items-center gap-3 font-bold transition-all hover:scale-105 active:scale-95" style="
                    background: linear-gradient(135deg, var(--primary-cyan), #0066ff);
                    color: #000;
                    box-shadow: 0 4px 20px rgba(14, 165, 233,0.3), 0 0 30px rgba(14, 165, 233,0.1);
                    border: 1px solid rgba(14, 165, 233,0.3);
                ">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
            
        </div>
    </form>
</div>

@push('scripts')
<script>
    let competitors = {
        ar: [],
        en: []
    };
    
    let statuses = {}; // Store status per URL: { url: { state: 'idle|loading|success|error', message: '', count: 0 } }

    function initCompetitors() {
        const arText = document.getElementById('keywords_competitors');
        const enText = document.getElementById('keywords_competitors_en');
        
        if (arText) competitors.ar = arText.value.split('\n').map(u => u.trim()).filter(u => u !== '');
        if (enText) competitors.en = enText.value.split('\n').map(u => u.trim()).filter(u => u !== '');
        
        // Pre-fill statuses for existing ones (optional: could test them all, but let's just mark as added)
        [...competitors.ar, ...competitors.en].forEach(url => {
            if (!statuses[url]) statuses[url] = { state: 'success', message: 'Successfully Monitored', count: '?' };
        });

        renderCompetitors('ar');
        renderCompetitors('en');

        // Unsaved changes warning
        let originalCompetitors = JSON.stringify(competitors);
        let isSubmitting = false;

        window.addEventListener('beforeunload', (e) => {
            if (isSubmitting) return;
            if (JSON.stringify(competitors) !== originalCompetitors) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        // Disable warning on form submit
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', () => {
                isSubmitting = true;
            });
        }
    }

    function renderCompetitors(lang) {
        const list = document.getElementById(`competitors_list_${lang}`);
        const textarea = document.getElementById(lang === 'ar' ? 'keywords_competitors' : 'keywords_competitors_en');
        if (!list || !textarea) return;
        
        list.innerHTML = '';
        const listItems = competitors[lang];
        const colorClass = lang === 'ar' ? 'orange' : 'blue';
        
        if (listItems.length === 0) {
            list.innerHTML = `<div class="col-span-full text-center py-6 text-gray-500 text-sm border border-dashed border-white/10 rounded-xl">No sources currently added</div>`;
        } else {
            listItems.forEach((url, index) => {
                const status = statuses[url] || { state: 'idle', message: 'Waiting for check', count: 0 };
                let statusBadge = '';
                
                if (status.state === 'loading') {
                    statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 animate-pulse flex items-center gap-1"><i class="fas fa-circle-notch fa-spin"></i> Scanning...</span>`;
                } else if (status.state === 'success') {
                    statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 flex items-center gap-1"><i class="fas fa-check-circle"></i> Successfully Monitored (${status.count})</span>`;
                } else if (status.state === 'error') {
                    statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> Fetch Failed</span>`;
                }

                list.innerHTML += `
                    <div class="flex items-center justify-between p-3.5 bg-white/5 border border-white/10 rounded-2xl group hover:border-${colorClass}-500/50 transition-all">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-[#0a0b0e] flex items-center justify-center text-gray-400 group-hover:text-${colorClass}-500 transition-colors shrink-0 shadow-inner">
                                <i class="fas fa-rss text-sm"></i>
                            </div>
                            <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold break-words font-sans" style="color: var(--text-main);" dir="ltr">${url}</span>
                                <div class="mt-1">${statusBadge}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 ml-2">
                            <button type="button" onclick="testConnection('${lang}', '${url}', this)" 
                                    class="w-9 h-9 flex items-center justify-center text-primary-cyan hover:bg-primary-cyan/10 rounded-xl transition-colors shrink-0" title="Quick Scan">
                                <i class="fas fa-bolt text-xs"></i>
                            </button>
                            <button type="button" onclick="removeCompetitor('${lang}', ${index})" 
                                    class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-xl transition-colors shrink-0" title="Delete">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        textarea.value = listItems.join('\n');
    }

    async function testConnection(lang, url, btn = null) {
        if (!statuses[url]) statuses[url] = {};
        statuses[url].state = 'loading';
        renderCompetitors(lang);

        try {
            const response = await fetch('{{ route("dashboard.ai-keyword-radar.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url, lang })
            });

            const data = await response.json();

            if (data.success) {
                statuses[url] = { state: 'success', message: 'Successfully Monitored', count: data.count };
                
                if (btn) { // If manual click, show SWAL
                    let headlinesHtml = '<ul class="text-start text-xs space-y-1 mt-2">';
                    data.headlines.forEach(h => {
                        headlinesHtml += `<li class="border-b border-white/5 pb-1 truncate">🔹 ${h.title}</li>`;
                    });
                    headlinesHtml += '</ul>';

                    Swal.fire({
                        icon: 'success',
                        title: 'Connection Successful!',
                        html: `Found <b>${data.count}</b> headlines via <b>${data.strategy}</b>.<br>${headlinesHtml}`,
                        background: '#1a1b1f',
                        color: '#fff',
                        confirmButtonColor: '#0ea5e9'
                    });
                }
            } else {
                statuses[url] = { state: 'error', message: 'Fetch Failed', count: 0 };
            }
        } catch (e) {
            statuses[url] = { state: 'error', message: 'Connection Error', count: 0 };
        } finally {
            renderCompetitors(lang);
        }
    }

    function addCompetitor(lang) {
        const input = document.getElementById(`new_competitor_url_${lang}`);
        if (!input) return;
        const url = input.value.trim();
        
        if (url && !competitors[lang].includes(url)) {
            try {
                new URL(url); // Basic validation
                competitors[lang].push(url);
                input.value = '';
                
                // Set initial loading state
                statuses[url] = { state: 'loading', message: 'Scanning...', count: 0 };
                renderCompetitors(lang);
                
                // Auto test
                testConnection(lang, url);
                
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid URL',
                    text: 'Please enter a valid link starting with http:// or https://',
                    background: '#1a1b1f',
                    color: '#fff',
                    confirmButtonColor: '#0ea5e9'
                });
            }
        }
    }

    function removeCompetitor(lang, index) {
        const url = competitors[lang][index];
        competitors[lang].splice(index, 1);
        delete statuses[url];
        renderCompetitors(lang);
    }

    // Handle Enter key on inputs
    ['ar', 'en'].forEach(lang => {
        const input = document.getElementById(`new_competitor_url_${lang}`);
        if (input) {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addCompetitor(lang);
                }
            });
        }
    });

    async function extractCompetitors(lang) {
        const btn = document.getElementById(`btn_extract_${lang}`);
        const originalContent = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i> Extracting...`;

        try {
            const response = await fetch('{{ route("dashboard.ai-keyword-radar.suggest-competitors") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ lang })
            });

            const data = await response.json();

            if (data.success && data.urls.length > 0) {
                let addedCount = 0;
                data.urls.forEach(url => {
                    if (!competitors[lang].includes(url)) {
                        competitors[lang].push(url);
                        statuses[url] = { state: 'idle', message: 'Added from AI', count: 0 };
                        addedCount++;
                    }
                });

                renderCompetitors(lang);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Competitors Extracted!',
                    text: `Successfully added ${addedCount} new competitors for ${lang === 'ar' ? 'Arabic' : 'English'}.`,
                    background: '#1a1b1f',
                    color: '#fff',
                    confirmButtonColor: '#0ea5e9'
                });
            } else {
                throw new Error(data.message || 'No competitors found.');
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Extraction Failed',
                text: e.message,
                background: '#1a1b1f',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    function importCompetitors(lang) {
        document.getElementById(`import_file_${lang}`).click();
    }

    function handleImportFile(lang, input) {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const urls = text.split(/[\r\n]+/).map(u => u.trim()).filter(u => {
                if (!u || u.startsWith('#')) return false;
                try { new URL(u); return true; } catch { return false; }
            });

            let addedCount = 0;
            urls.forEach(url => {
                if (!competitors[lang].includes(url)) {
                    competitors[lang].push(url);
                    statuses[url] = { state: 'idle', message: 'Imported', count: 0 };
                    addedCount++;
                }
            });

            renderCompetitors(lang);
            input.value = '';

            Swal.fire({
                icon: addedCount > 0 ? 'success' : 'info',
                title: addedCount > 0 ? 'Import Successful!' : 'No New URLs',
                text: addedCount > 0 
                    ? `Added ${addedCount} new competitor(s) for ${lang === 'ar' ? 'Arabic' : 'English'}. Don't forget to click "Save All Settings".`
                    : 'All URLs in the file are either already added or invalid.',
                background: '#1a1b1f',
                color: '#fff',
                confirmButtonColor: '#0ea5e9'
            });
        };
        reader.readAsText(file);
    }

    function exportCompetitors(lang) {
        const urls = competitors[lang];
        if (urls.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Nothing to Export',
                text: `No competitors added for ${lang === 'ar' ? 'Arabic' : 'English'} yet.`,
                background: '#1a1b1f',
                color: '#fff',
                confirmButtonColor: '#0ea5e9'
            });
            return;
        }

        const content = urls.join('\n');
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `competitors_${lang}_${new Date().toISOString().slice(0,10)}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initCompetitors);
</script>
@endpush
@endsection
