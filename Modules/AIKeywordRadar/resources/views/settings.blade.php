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
            @include('aikeywordradar::partials.settings_competitor_section', [
                'sectionId' => 'ar',
                'title' => 'Monitor Competitors (Ar - Arabic)',
                'subtitle' => 'Add direct news site URLs or RSS links.',
                'icon' => 'fas fa-globe-africa',
                'color' => 'orange',
                'colorHex' => '#f97316',
                'placeholder' => 'https://youm7.com',
                'fieldName' => 'keywords_competitors',
                'competitors' => $settings['keywords_competitors'] ?? '',
            ])

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
                    @include('aikeywordradar::partials.settings_competitor_inputs', [
                        'sectionId' => 'en',
                        'color' => 'blue',
                        'colorHex' => '#3b82f6',
                        'placeholder' => 'https://techcrunch.com',
                        'fieldName' => 'keywords_competitors_en',
                        'competitors' => $settings['keywords_competitors_en'] ?? '',
                    ])
                </div>
            </div>

            {{-- Custom Boxes Section --}}
            <div class="glass-card overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-500">
                        <i class="fas fa-layer-group text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold" style="color: var(--text-main);">Custom Competitor Groups</h3>
                        <p class="text-[10px]" style="color: var(--text-muted);">Create topic-specific monitoring groups (e.g. Sports, Health, Tech)</p>
                    </div>
                    <button type="button" onclick="showCreateBoxModal()" 
                            class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all hover:scale-105 flex items-center gap-2"
                            style="background: linear-gradient(135deg, #a855f7, #6366f1); color: white; box-shadow: 0 4px 15px rgba(168,85,247,0.3);">
                        <i class="fas fa-plus"></i> New Group
                    </button>
                </div>

                <div id="custom_boxes_container" class="p-6 space-y-4">
                    {{-- Custom boxes will be rendered by JS --}}
                </div>
                <!-- Hidden file inputs for individual box imports -->
                <div id="custom_box_import_inputs" class="hidden"></div>
                <textarea id="keywords_custom_boxes" name="keywords_custom_boxes" class="hidden">{{ json_encode($settings['keywords_custom_boxes'] ?? []) }}</textarea>
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

{{-- Create Box Modal --}}
<div id="createBoxModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(8px);" onclick="if(event.target===this)this.style.display='none'">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:90%; max-width:460px; background:var(--card-bg); border:1px solid var(--glass-border); border-radius:20px; padding:28px; box-shadow: 0 25px 60px rgba(0,0,0,0.5);">
        <h3 id="modal_title" style="color:var(--text-main); font-size:1.25rem; font-weight:800; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-layer-group text-purple-500"></i> <span id="modal_title_text">Create Competitor Group</span>
        </h3>
        
        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-main); margin-bottom:6px;">Group Name</label>
            <input type="text" id="new_box_name" placeholder="e.g. Sports, Health, News" 
                   style="width:100%; padding:12px 16px; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:12px; color:var(--text-main); font-size:0.9rem; outline:none;"
                   onfocus="this.style.borderColor='#a855f7'" onblur="this.style.borderColor='var(--glass-border)'">
        </div>
        
        <div style="margin-bottom:16px;">
            <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-main); margin-bottom:6px;">Language</label>
            <select id="new_box_lang" style="width:100%; padding:12px 16px; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:12px; color:var(--text-main); font-size:0.9rem; outline:none;">
                <option value="ar" style="background:#1a1b1f;">Arabic (AR)</option>
                <option value="en" style="background:#1a1b1f;">English (EN)</option>
            </select>
        </div>
        
        <div style="margin-bottom:20px;">
            <label style="display:block; font-size:0.8rem; font-weight:700; color:var(--text-main); margin-bottom:8px;">Theme Color</label>
            <div id="color_picker" style="display:flex; gap:8px; flex-wrap:wrap;"></div>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <button type="button" onclick="document.getElementById('createBoxModal').style.display='none'" 
                    style="padding:10px 20px; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); border-radius:12px; color:var(--text-muted); font-weight:700; font-size:0.85rem; cursor:pointer;">
                Cancel
            </button>
            <button type="button" id="modal_submit_btn" onclick="saveCustomBox()" 
                    style="padding:10px 24px; background:linear-gradient(135deg,#a855f7,#6366f1); border:none; border-radius:12px; color:white; font-weight:700; font-size:0.85rem; cursor:pointer; box-shadow:0 4px 15px rgba(168,85,247,0.3);">
                <i class="fas fa-save"></i> <span id="modal_submit_text">Create</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const BOX_COLORS = [
        { name: 'Green', hex: '#10b981' },
        { name: 'Purple', hex: '#a855f7' },
        { name: 'Pink', hex: '#ec4899' },
        { name: 'Teal', hex: '#14b8a6' },
        { name: 'Amber', hex: '#f59e0b' },
        { name: 'Rose', hex: '#f43f5e' },
        { name: 'Indigo', hex: '#6366f1' },
        { name: 'Cyan', hex: '#06b6d4' },
    ];

    let competitors = { ar: [], en: [] };
    let customBoxes = [];
    let statuses = {};
    let selectedBoxColor = BOX_COLORS[0].hex;
    let editingBoxId = null;

    function normalizeUrl(url) {
        if (!url) return "";
        let u = url.trim().toLowerCase();
        u = u.replace(/^https?:\/\//, ''); // Remove protocol
        u = u.replace(/^www\./, '');      // Remove www.
        if (u.endsWith('/')) u = u.slice(0, -1);
        return u;
    }

    function initCompetitors() {
        const arText = document.getElementById('keywords_competitors');
        const enText = document.getElementById('keywords_competitors_en');
        
        if (arText) {
            const rawAr = arText.value.split('\n').map(u => u.trim()).filter(u => u !== '');
            const normalizedAr = [];
            competitors.ar = rawAr.filter(u => {
                const norm = normalizeUrl(u);
                if (normalizedAr.includes(norm)) return false;
                normalizedAr.push(norm);
                return true;
            });
            arText.value = competitors.ar.join('\n');
        }
        if (enText) {
            const rawEn = enText.value.split('\n').map(u => u.trim()).filter(u => u !== '');
            const normalizedEn = [];
            competitors.en = rawEn.filter(u => {
                const norm = normalizeUrl(u);
                if (normalizedEn.includes(norm)) return false;
                normalizedEn.push(norm);
                return true;
            });
            enText.value = competitors.en.join('\n');
        }
        
        // Load custom boxes
        const boxesJson = document.getElementById('keywords_custom_boxes');
        if (boxesJson) {
            try { customBoxes = JSON.parse(boxesJson.value) || []; } catch(e) { customBoxes = []; }
        }
        
        // Pre-fill statuses
        [...competitors.ar, ...competitors.en].forEach(url => {
            if (!statuses[url]) statuses[url] = { state: 'success', message: 'Successfully Monitored', count: '?' };
        });
        customBoxes.forEach(box => {
            (box.competitors || '').split('\n').filter(u => u.trim()).forEach(url => {
                if (!statuses[url]) statuses[url] = { state: 'success', message: 'Monitored', count: '?' };
            });
        });

        renderCompetitors('ar');
        renderCompetitors('en');
        renderCustomBoxes();
        renderColorPicker();

        // Unsaved changes warning
        let originalState = JSON.stringify({ competitors, customBoxes });
        let isSubmitting = false;

        window.addEventListener('beforeunload', (e) => {
            if (isSubmitting) return;
            if (JSON.stringify({ competitors, customBoxes }) !== originalState) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', () => {
                isSubmitting = true;
                // Sync custom boxes to hidden field
                document.getElementById('keywords_custom_boxes').value = JSON.stringify(customBoxes);
            });
        }
    }

    function renderColorPicker() {
        const picker = document.getElementById('color_picker');
        if (!picker) return;
        picker.innerHTML = BOX_COLORS.map(c => `
            <button type="button" onclick="selectBoxColor('${c.hex}', this)" 
                    data-color="${c.hex}"
                    style="width:36px; height:36px; border-radius:10px; background:${c.hex}; border:3px solid ${c.hex === selectedBoxColor ? 'white' : 'transparent'}; cursor:pointer; transition:all 0.2s; box-shadow: 0 2px 8px ${c.hex}40;"
                    title="${c.name}"></button>
        `).join('');
    }

    function selectBoxColor(hex, el) {
        selectedBoxColor = hex;
        document.querySelectorAll('#color_picker button').forEach(b => {
            b.style.borderColor = b.dataset.color === hex ? 'white' : 'transparent';
        });
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
                list.innerHTML += renderCompetitorCard(url, lang, index, colorClass);
            });
        }

        textarea.value = listItems.join('\n');
    }

    function renderCompetitorCard(url, lang, index, colorClass, boxId = null) {
        const status = statuses[url] || { state: 'idle', message: 'Waiting', count: 0 };
        let statusBadge = '';
        
        if (status.state === 'loading') {
            statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 animate-pulse flex items-center gap-1"><i class="fas fa-circle-notch fa-spin"></i> Scanning...</span>`;
        } else if (status.state === 'success') {
            statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 flex items-center gap-1"><i class="fas fa-check-circle"></i> Monitored (${status.count})</span>`;
        } else if (status.state === 'error') {
            statusBadge = `<span class="text-[9px] px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> Failed</span>`;
        }

        const removeAction = boxId 
            ? `removeBoxCompetitor('${boxId}', ${index})`
            : `removeCompetitor('${lang}', ${index})`;
        const testAction = boxId 
            ? `testConnection('${lang}', '${url}', this)`
            : `testConnection('${lang}', '${url}', this)`;

        return `
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
                    <button type="button" onclick="${testAction}" 
                            class="w-9 h-9 flex items-center justify-center text-primary-cyan hover:bg-primary-cyan/10 rounded-xl transition-colors shrink-0" title="Quick Scan">
                        <i class="fas fa-bolt text-xs"></i>
                    </button>
                    <button type="button" onclick="${removeAction}" 
                            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-xl transition-colors shrink-0" title="Delete">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Custom Boxes
    function renderCustomBoxes() {
        const container = document.getElementById('custom_boxes_container');
        if (!container) return;

        if (customBoxes.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 border border-dashed border-white/10 rounded-2xl">
                    <i class="fas fa-layer-group text-3xl text-purple-500/30 mb-3"></i>
                    <p class="text-gray-500 text-sm font-bold">No custom groups yet</p>
                    <p class="text-gray-600 text-xs mt-1">Click "New Group" to create topic-specific monitoring</p>
                </div>`;
            return;
        }

        container.innerHTML = customBoxes.map((box, idx) => {
            const urls = (box.competitors || '').split('\n').filter(u => u.trim());
            const langLabel = box.lang === 'en' ? 'EN' : 'AR';
            
            return `
            <div class="glass-card overflow-hidden" style="border-color: ${box.color}30;">
                <div class="px-5 py-3 border-b flex items-center gap-3" style="border-color: ${box.color}20; background: ${box.color}08;">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-black" style="background: ${box.color};">
                        ${box.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-sm truncate" style="color: var(--text-main);">${box.name}</h4>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background:${box.color}15; color:${box.color};">${langLabel} · ${urls.length} sources</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="editCustomBox('${box.id}')"
                                class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-purple-400 hover:bg-purple-400/10 rounded-lg transition-colors" title="Edit Group Colors/Name">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button type="button" onclick="deleteCustomBox('${box.id}')" 
                                class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors" title="Delete Group">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <input type="url" id="new_url_box_${box.id}" 
                               class="flex-1 px-4 py-2.5 rounded-xl border text-sm focus:ring-1 outline-none transition placeholder-gray-500"
                               style="background: var(--card-bg); color: var(--text-main); border-color: var(--glass-border);"
                               placeholder="Add competitor URL..."
                               onkeypress="if(event.key==='Enter'){event.preventDefault();addBoxCompetitor('${box.id}')}">
                        
                        <div class="flex gap-2">
                            <button type="button" onclick="addBoxCompetitor('${box.id}')" 
                                    class="px-5 py-2.5 text-white rounded-xl font-bold text-sm transition shadow-lg flex items-center justify-center gap-2 whitespace-nowrap hover:opacity-90"
                                    style="background: ${box.color}; box-shadow: 0 0 15px ${box.color}30;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                            
                            <div class="flex items-center bg-white/5 rounded-xl border border-white/10 p-1">
                                <button type="button" id="btn_extract_box_${box.id}" onclick="extractBoxCompetitors('${box.id}', '${box.lang}', '${box.name}')" 
                                        class="w-9 h-9 flex items-center justify-center text-primary-cyan hover:bg-primary-cyan/10 rounded-lg transition-colors" title="AI Suggest Competitors">
                                    <i class="fas fa-robot text-xs"></i>
                                </button>
                                <button type="button" onclick="importBoxCompetitors('${box.id}', '${box.lang}')" 
                                        class="w-9 h-9 flex items-center justify-center text-emerald-500 hover:bg-emerald-500/10 rounded-lg transition-colors" title="Import TXT">
                                    <i class="fas fa-file-import text-xs"></i>
                                </button>
                                <button type="button" onclick="exportBoxCompetitors('${box.id}')" 
                                        class="w-9 h-9 flex items-center justify-center text-amber-500 hover:bg-amber-500/10 rounded-lg transition-colors" title="Export TXT">
                                    <i class="fas fa-file-export text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="box_list_${box.id}" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        ${urls.length === 0 
                            ? '<div class="col-span-full text-center py-4 text-gray-500 text-xs border border-dashed border-white/10 rounded-xl">No sources added</div>'
                            : urls.map((url, i) => renderCompetitorCard(url.trim(), box.lang, i, 'purple', box.id)).join('')
                        }
                    </div>
                </div>
            </div>`;
        }).join('');

        // Sync to hidden field
        document.getElementById('keywords_custom_boxes').value = JSON.stringify(customBoxes);
    }

    function showCreateBoxModal() {
        if (customBoxes.length >= 5) {
            Swal.fire({ icon: 'warning', title: 'Limit Reached', text: 'Maximum 5 custom groups allowed.', background: '#0f172a', color: '#fff', confirmButtonColor: '#a855f7' });
            return;
        }
        editingBoxId = null;
        document.getElementById('modal_title_text').textContent = 'Create Competitor Group';
        document.getElementById('modal_submit_text').textContent = 'Create';
        document.getElementById('new_box_name').value = '';
        document.getElementById('new_box_lang').value = 'ar';
        selectedBoxColor = BOX_COLORS[0].hex;
        renderColorPicker();
        document.getElementById('createBoxModal').style.display = 'block';
    }

    function editCustomBox(boxId) {
        const box = customBoxes.find(b => b.id === boxId);
        if (!box) return;
        
        editingBoxId = boxId;
        document.getElementById('modal_title_text').textContent = 'Edit Competitor Group';
        document.getElementById('modal_submit_text').textContent = 'Save Changes';
        document.getElementById('new_box_name').value = box.name;
        document.getElementById('new_box_lang').value = box.lang;
        selectedBoxColor = box.color || BOX_COLORS[0].hex;
        renderColorPicker();
        document.getElementById('createBoxModal').style.display = 'block';
    }

    function saveCustomBox() {
        const name = document.getElementById('new_box_name').value.trim();
        const lang = document.getElementById('new_box_lang').value;
        
        if (!name) {
            Swal.fire({ icon: 'error', title: 'Name Required', text: 'Please enter a group name.', background: '#0f172a', color: '#fff', confirmButtonColor: '#ef4444' });
            return;
        }

        if (editingBoxId) {
            // Update existing
            const box = customBoxes.find(b => b.id === editingBoxId);
            if (box) {
                box.name = name;
                box.lang = lang;
                box.color = selectedBoxColor;
            }
        } else {
            // Create new
            const box = {
                id: 'box_' + Date.now(),
                name: name,
                lang: lang,
                color: selectedBoxColor,
                competitors: ''
            };
            customBoxes.push(box);
        }

        renderCustomBoxes();
        document.getElementById('createBoxModal').style.display = 'none';
        
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: editingBoxId ? `Changes saved!` : `"${name}" group created!`, text: 'Don\'t forget to Save All Settings.', showConfirmButton: false, timer: 2500, timerProgressBar: true, background: '#0f172a', color: '#fff' });
        editingBoxId = null;
    }

    function deleteCustomBox(boxId) {
        Swal.fire({
            title: 'Delete Group?',
            text: 'This will remove the group and all its competitors.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#333',
            confirmButtonText: 'Delete',
            background: '#0f172a',
            color: '#fff'
        }).then(result => {
            if (result.isConfirmed) {
                customBoxes = customBoxes.filter(b => b.id !== boxId);
                renderCustomBoxes();
            }
        });
    }

    function addBoxCompetitor(boxId) {
        const input = document.getElementById(`new_url_box_${boxId}`);
        if (!input) return;
        const url = input.value.trim();
        if (!url) return;

        try {
            new URL(url);
        } catch(e) {
            Swal.fire({ icon: 'error', title: 'Invalid URL', text: 'Please enter a valid URL.', background: '#0f172a', color: '#fff', confirmButtonColor: '#ef4444' });
            return;
        }

        const box = customBoxes.find(b => b.id === boxId);
        if (!box) return;
        
        const existing = (box.competitors || '').split('\n').map(u => u.trim()).filter(u => u !== '');
        const normNew = normalizeUrl(url);
        if (existing.some(u => normalizeUrl(u) === normNew)) {
            Swal.fire({ icon: 'warning', title: 'Already Exists', text: 'This competitor is already in this box.', background: '#0f172a', color: '#fff', confirmButtonColor: '#f59e0b' });
            return;
        }
        
        existing.push(url);
        box.competitors = existing.join('\n');
        input.value = '';
        
        statuses[url] = { state: 'loading', message: 'Scanning...', count: 0 };
        renderCustomBoxes();
        testConnection(box.lang, url);
    }

    function removeBoxCompetitor(boxId, index) {
        const box = customBoxes.find(b => b.id === boxId);
        if (!box) return;
        
        const urls = (box.competitors || '').split('\n').filter(u => u.trim());
        const removed = urls.splice(index, 1);
        if (removed[0]) delete statuses[removed[0]];
        box.competitors = urls.join('\n');
        renderCustomBoxes();
    }

    // Standard Functions
    async function testConnection(lang, url, btn = null) {
        if (!statuses[url]) statuses[url] = {};
        statuses[url].state = 'loading';
        renderCompetitors(lang);
        renderCustomBoxes();

        try {
            const response = await fetch('{{ route("dashboard.ai-keyword-radar.test-connection") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ url, lang })
            });
            const data = await response.json();

            if (data.success) {
                statuses[url] = { state: 'success', message: 'Monitored', count: data.count };
                if (btn) {
                    let headlinesHtml = '<ul class="text-start text-xs space-y-1 mt-2">';
                    data.headlines.forEach(h => { headlinesHtml += `<li class="border-b border-white/5 pb-1 truncate">🔹 ${h.title}</li>`; });
                    headlinesHtml += '</ul>';
                    Swal.fire({ icon: 'success', title: 'Connection Successful!', html: `Found <b>${data.count}</b> headlines via <b>${data.strategy}</b>.<br>${headlinesHtml}`, background: '#0f172a', color: '#fff', confirmButtonColor: '#0ea5e9' });
                }
            } else {
                statuses[url] = { state: 'error', message: 'Failed', count: 0 };
            }
        } catch (e) {
            statuses[url] = { state: 'error', message: 'Connection Error', count: 0 };
        } finally {
            renderCompetitors(lang);
            renderCustomBoxes();
        }
    }

    function addCompetitor(lang) {
        const input = document.getElementById(`new_competitor_url_${lang}`);
        if (!input) return;
        const url = input.value.trim();
        
        if (!url) return;
        const normNew = normalizeUrl(url);

        if (competitors[lang].some(u => normalizeUrl(u) === normNew)) {
            Swal.fire({ icon: 'warning', title: 'Already Exists', text: 'This competitor is already in this list.', background: '#0f172a', color: '#fff', confirmButtonColor: '#f59e0b' });
            return;
        }

        try {
            new URL(url);
            competitors[lang].push(url);
            input.value = '';
            statuses[url] = { state: 'loading', message: 'Scanning...', count: 0 };
            renderCompetitors(lang);
            testConnection(lang, url);
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Invalid URL', text: 'Please enter a valid link starting with http:// or https://', background: '#0f172a', color: '#fff', confirmButtonColor: '#0ea5e9' });
        }
    }

    function removeCompetitor(lang, index) {
        const url = competitors[lang][index];
        competitors[lang].splice(index, 1);
        delete statuses[url];
        renderCompetitors(lang);
    }

    // Handle Enter key
    ['ar', 'en'].forEach(lang => {
        const input = document.getElementById(`new_competitor_url_${lang}`);
        if (input) {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); addCompetitor(lang); }
            });
        }
    });

    async function extractCompetitors(lang, topic = null, boxId = null) {
        const btnId = boxId ? `btn_extract_box_${boxId}` : `btn_extract_${lang}`;
        const btn = document.getElementById(btnId);
        if (!btn) return;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i> Suggesting...`;

        try {
            const response = await fetch('{{ route("dashboard.ai-keyword-radar.suggest-competitors") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ lang, topic })
            });
            const data = await response.json();

            if (data.success && data.urls.length > 0) {
                let addedCount = 0;
                const container = boxId ? customBoxes.find(b => b.id === boxId) : null;
                const targetArray = boxId ? (container.competitors || '').split('\n').filter(u => u.trim()) : competitors[lang];

                data.urls.forEach(url => {
                    const normUrl = normalizeUrl(url);
                    const isDup = targetArray.some(u => normalizeUrl(u) === normUrl);
                    if (!isDup) {
                        targetArray.push(url);
                        statuses[url] = { state: 'idle', message: 'AI Suggested', count: 0 };
                        addedCount++;
                    }
                });

                if (boxId) {
                    container.competitors = targetArray.join('\n');
                    renderCustomBoxes();
                } else {
                    renderCompetitors(lang);
                }
                Swal.fire({ icon: 'success', title: 'AI Suggestions Ready!', text: `Added ${addedCount} new competitor(s).`, background: '#0f172a', color: '#fff', confirmButtonColor: '#0ea5e9' });
            } else {
                throw new Error(data.message || 'No competitors found.');
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Suggestion Failed', text: e.message, background: '#0f172a', color: '#fff', confirmButtonColor: '#ef4444' });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    function extractBoxCompetitors(boxId, lang, topic) {
        extractCompetitors(lang, topic, boxId);
    }

    function importBoxCompetitors(boxId, lang) {
        let input = document.getElementById(`import_file_box_${boxId}`);
        if (!input) {
            const container = document.getElementById('custom_box_import_inputs');
            container.insertAdjacentHTML('beforeend', `<input type="file" id="import_file_box_${boxId}" accept=".txt" class="hidden" onchange="handleImportFileBox('${boxId}', '${lang}', this)">`);
            input = document.getElementById(`import_file_box_${boxId}`);
        }
        input.click();
    }

    function handleImportFileBox(boxId, lang, input) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const urls = e.target.result.split(/[\r\n]+/).map(u => u.trim()).filter(u => {
                if (!u || u.startsWith('#')) return false;
                try { new URL(u); return true; } catch { return false; }
            });
            
            const box = customBoxes.find(b => b.id === boxId);
            if (!box) return;

            let addedCount = 0;
            const existing = (box.competitors || '').split('\n').filter(u => u.trim());
            
            urls.forEach(url => {
                if (!existing.includes(url)) {
                    existing.push(url);
                    statuses[url] = { state: 'idle', message: 'Imported', count: 0 };
                    addedCount++;
                }
            });
            box.competitors = existing.join('\n');
            renderCustomBoxes();
            input.value = '';
            Swal.fire({
                icon: addedCount > 0 ? 'success' : 'info',
                title: addedCount > 0 ? 'Import Successful!' : 'No New URLs',
                text: addedCount > 0 ? `Added ${addedCount} new competitor(s). Don't forget to Save.` : 'All URLs already added or invalid.',
                background: '#1a1b1f', color: '#fff', confirmButtonColor: '#0ea5e9'
            });
        };
        reader.readAsText(file);
    }

    function exportBoxCompetitors(boxId) {
        const box = customBoxes.find(b => b.id === boxId);
        if (!box || !box.competitors) {
            Swal.fire({ icon: 'info', title: 'Nothing to Export', text: `No competitors added yet.`, background: '#0f172a', color: '#fff', confirmButtonColor: '#0ea5e9' });
            return;
        }
        const blob = new Blob([box.competitors], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `competitors_${box.name.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function importCompetitors(lang) { document.getElementById(`import_file_${lang}`).click(); }

    function handleImportFile(lang, input) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const urls = e.target.result.split(/[\r\n]+/).map(u => u.trim()).filter(u => {
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
                text: addedCount > 0 ? `Added ${addedCount} new competitor(s). Don't forget to Save.` : 'All URLs already added or invalid.',
                background: '#1a1b1f', color: '#fff', confirmButtonColor: '#0ea5e9'
            });
        };
        reader.readAsText(file);
    }

    function exportCompetitors(lang) {
        const urls = competitors[lang];
        if (urls.length === 0) {
            Swal.fire({ icon: 'info', title: 'Nothing to Export', text: `No competitors added yet.`, background: '#0f172a', color: '#fff', confirmButtonColor: '#0ea5e9' });
            return;
        }
        const blob = new Blob([urls.join('\n')], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `competitors_${lang}_${new Date().toISOString().slice(0,10)}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    document.addEventListener('DOMContentLoaded', initCompetitors);
</script>
@endpush
@endsection
