@extends('dramatrends::layouts.master')

@section('title', 'Series Management')

@section('content')
<div class="max-w-7xl mx-auto font-tajawal pb-20 px-4" x-data="seriesManagement()" x-init="init()" dir="ltr">

    {{-- ═══════ PREMIUM COMPACT HERO ═══════ --}}
    <div class="hero-compact rounded-[2rem] border border-[var(--border-glass)] overflow-hidden mb-10 transition-colors duration-300 text-left">
        <h1 class="hero-title">Manage <span class="text-accent-gold italic">Drama Series</span></h1>
        <p class="text-[var(--text-muted)] font-black opacity-80 mb-8 max-w-2xl mx-auto leading-relaxed ml-0">
            Manage the list of tracked Egyptian series for Ramadan 2026. You can add, edit, or delete works and determine search keywords and episode counts.
        </p>

        <div class="action-bar-row shadow-2xl">
            <div class="input-group">
                <i class="fas fa-database text-accent-blue opacity-70"></i>
                <div class="flex flex-col text-left">
                    <span class="search-label">Total Series</span>
                    <span class="text-[var(--text-main)] font-black text-sm" x-text="series.length">0</span>
                </div>
            </div>

            <div class="flex items-center gap-3 pr-4">
                <button @click="openModal()" class="pill-btn-pro btn-blue">
                    <i class="fas fa-plus"></i>
                    <span>Add New Series</span>
                </button>

                <button @click="exportSeries()" class="pill-btn-pro" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fas fa-file-export"></i>
                    <span>Export</span>
                </button>

                <button @click="$refs.importInput.click()" class="pill-btn-pro" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fas fa-file-import"></i>
                    <span>Import</span>
                </button>
                <input type="file" x-ref="importInput" accept=".json" class="hidden" @change="importSeries">

                <button @click="saveSeries()" :disabled="saving" class="pill-btn-pro btn-purple">
                    <i class="fas fa-save" :class="saving && 'animate-spin'"></i>
                    <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'drama-trends'])

    {{-- Success / Error Messages Toast --}}
    <div x-show="successMsg" x-cloak
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-[-1rem] scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-[-1rem] scale-95"
         class="fixed top-8 left-1/2 -translate-x-1/2 z-[3000] glass-card p-4 px-8 border border-[var(--border-glass)] bg-emerald-500/20 backdrop-blur-xl shadow-[0_10px_40px_-10px_rgba(16,185,129,0.3)]">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/50">
                <i class="fas fa-check text-xl"></i>
            </div>
            <span class="text-[var(--text-main)] font-black text-lg font-cairo" x-text="successMsg"></span>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="flex flex-col items-center justify-center py-20" x-cloak>
        <div class="w-12 h-12 border-4 border-accent-purple border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Series Table --}}
    <div x-show="!loading" x-cloak class="glass-card p-6 mb-8 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="dt-table w-full">
                <thead>
                    <tr class="text-[var(--text-muted)] text-sm font-black border-b border-[var(--border-glass)]">
                        <th class="py-5 pr-6 text-left" style="width: 40px;">#</th>
                        <th class="py-5 text-left">Series Name</th>
                        <th class="py-5 text-left">Lead Actor</th>
                        <th class="py-5 text-left">Production Company</th>
                        <th class="py-5 text-left">Episodes</th>
                        <th class="py-5 text-left">Search Keyword</th>
                        <th class="py-5 text-center" style="width: 80px;">Baseline</th>
                        <th class="py-5 text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border-glass)]">
                    <template x-for="(item, index) in series" :key="index">
                        <tr class="hover:bg-[var(--bg-glass)] transition-colors group">
                            <td class="py-5 pr-6 text-[var(--text-muted)] font-black" x-text="index + 1"></td>
                            <td class="py-5">
                                <span class="text-[var(--text-main)] font-black font-cairo text-lg" x-text="item.name"></span>
                            </td>
                            <td class="py-5 text-[var(--text-muted)] font-bold" x-text="item.lead || '-'"></td>
                            <td class="py-5 text-[var(--text-muted)] font-bold" x-text="item.company || '-'"></td>
                            <td class="py-5">
                                <span x-show="item.episodes" class="px-3 py-1 rounded bg-slate-900/50 text-accent-gold text-xs font-black border border-amber-500/20" x-text="item.episodes + ' Episodes'"></span>
                                <span x-show="!item.episodes" class="text-xs opacity-30">—</span>
                            </td>
                            <td class="py-5">
                                <span x-show="item.searchKeyword" class="px-3 py-1 rounded-lg bg-accent-purple/10 text-accent-purple text-xs font-bold" x-text="item.searchKeyword"></span>
                                <span x-show="!item.searchKeyword" class="text-[var(--text-muted)] text-xs font-bold opacity-30 italic">Default Name</span>
                            </td>
                            <td class="py-5 text-center">
                                <input type="radio" name="baseline" :checked="item.isBaseline" @change="setBaseline(index)" class="w-5 h-5 accent-accent-blue cursor-pointer">
                            </td>
                            <td class="py-5">
                                <div class="flex justify-center gap-2">
                                    <button @click="openModal(index)" class="w-10 h-10 rounded-xl bg-[var(--bg-glass)] text-accent-blue hover:bg-accent-blue hover:text-white transition-all">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="deleteSeries(index)" class="w-10 h-10 rounded-xl bg-[var(--bg-glass)] text-rose-500 hover:bg-rose-500 hover:text-white transition-all">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- WATCH IT Ranking Management --}}
    <div x-show="!loading" x-cloak class="glass-card p-10 mb-20 border-t-4 border-accent-blue text-left">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-black text-[var(--text-main)] font-cairo mb-2">WATCH IT Ranking (Most Watched)</h3>
                <p class="text-[var(--text-muted)] text-sm font-bold">Manually rank the top 10 most watched series in Egypt (due to automated connection limits).</p>
            </div>
            <button @click="saveWatchItRanking()" :disabled="savingWatchIt" class="pill-btn-pro btn-blue">
                <i class="fas fa-save" :class="savingWatchIt && 'animate-spin'"></i>
                <span x-text="savingWatchIt ? 'Saving...' : 'Save WATCH IT Ranking'"></span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- NotebookLM Surgical Alignment -->
            <div class="bg-[var(--bg-glass)] backdrop-blur-md rounded-2xl border border-[var(--border-glass)] p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-black text-white flex items-center gap-3">
                            <i class="fas fa-robot text-accent-gold"></i>
                            Surgical Matching with AI Reports
                        </h2>
                        <p class="text-[var(--text-muted)] text-sm mt-1">Paste the NotebookLM report text here to match the dashboard with it 100% accurately.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="clearNotebookReport()" 
                                class="px-4 py-2 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 hover:bg-red-500/20 transition-all text-sm font-bold">
                            Clear Matching
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <textarea x-model="notebookReport" dir="rtl"
                              class="w-full h-40 bg-black/40 border border-[var(--border-glass)] rounded-xl p-4 text-sm text-white focus:border-accent-gold transition-all outline-none placeholder:text-gray-600"
                              placeholder="Paste the report here... Example:
1. Ali Clay: Sharqia (71%), North Sinai (71%) ... 79%"></textarea>
                    
                    <button @click="parseAndApplyReport()" 
                            :disabled="isParsing || !notebookReport.trim()"
                            class="w-full py-4 rounded-xl bg-gradient-to-r from-accent-gold to-yellow-600 text-black font-black hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isParsing">Apply Report & Surgical Matching</span>
                        <span x-show="isParsing"><i class="fas fa-spinner fa-spin mr-2"></i> Processing and matching...</span>
                    </button>
                </div>
            </div>

            {{-- Current Ranking List --}}
            <div class="bg-slate-900/40 rounded-3xl p-6 border border-[var(--border-glass)] text-left">
                <h4 class="text-accent-gold font-black mb-6 flex items-center gap-2">
                    <i class="fas fa-list-ol"></i>
                    <span>Current Ranking (Top 10)</span>
                </h4>
                <div class="space-y-3">
                    <template x-for="(name, idx) in watchItRanking" :key="idx">
                        <div class="flex items-center gap-4 p-4 bg-[var(--bg-glass)] rounded-2xl border border-[var(--border-glass)] hover:border-accent-blue/50 transition-all group">
                            <span class="w-8 h-8 rounded-full bg-accent-blue/20 text-accent-blue flex items-center justify-center font-black text-sm" x-text="idx + 1"></span>
                            <span class="flex-1 text-[var(--text-main)] font-bold" x-text="name"></span>
                            <button @click="removeRank(idx)" class="text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="watchItRanking.length === 0" class="py-10 text-center text-[var(--text-muted)] opacity-50 italic font-bold">
                        No ranking selected yet
                    </div>
                </div>
            </div>

            {{-- Pick from Series List --}}
            <div class="text-left">
                <h4 class="text-accent-blue font-black mb-6 flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i>
                    <span>Choose from Added Series</span>
                </h4>
                <div class="flex flex-wrap gap-2 max-h-[400px] overflow-y-auto p-2 scrollbar-thin font-cairo">
                    <template x-for="s in series" :key="s.name">
                        <button 
                            @click="addToRank(s.name)" 
                            :disabled="watchItRanking.includes(s.name) || watchItRanking.length >= 10"
                            class="px-4 py-2 rounded-xl border border-[var(--border-glass)] text-sm font-bold transition-all"
                            :class="watchItRanking.includes(s.name) ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30' : 'bg-[var(--bg-glass)] text-[var(--text-muted)] hover:bg-accent-blue/10 hover:text-accent-blue hover:border-accent-blue/30'"
                        >
                            <span x-text="s.name"></span>
                        </button>
                    </template>
                </div>
                
                <div class="mt-8 pt-8 border-t border-[var(--border-glass)]">
                    <label class="block text-sm text-[var(--text-muted)] mb-3 font-black">Add Manual Series (Not in List)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="manualShow" placeholder="Series Name..." @keyup.enter="addManualRank()" class="flex-1 bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-bold focus:outline-none focus:border-accent-blue transition-all">
                        <button @click="addManualRank()" class="w-12 h-12 bg-accent-blue text-white rounded-2xl flex items-center justify-center hover:scale-105 transition-transform">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Google Trends CSV Upload --}}
    <div x-show="!loading" x-cloak class="glass-card p-10 mb-20 border-t-4 border-emerald-500 text-left">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-black text-[var(--text-main)] font-cairo mb-2">Upload Google Trends Data (CSV)</h3>
                <p class="text-[var(--text-muted)] text-sm font-bold">You can upload comparative CSV files to update the data. They will be relied upon instead of direct contact with Google.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="$refs.csvInput.click()" class="pill-btn-pro btn-purple" :disabled="uploadingCsv">
                    <i class="fas fa-file-upload" :class="uploadingCsv && 'animate-bounce'"></i>
                    <span x-text="uploadingCsv ? 'Uploading...' : 'Select CSV Files'"></span>
                </button>
                <button @click="clearCsvs()" class="pill-btn-pro" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;" :disabled="uploadingCsv">
                    <i class="fas fa-trash"></i>
                    <span>Clear and Return to Auto</span>
                </button>
                <input type="file" x-ref="csvInput" multiple accept=".csv" class="hidden" @change="uploadCsvs">
            </div>
        </div>
    </div>

    {{-- ═══════ MODAL ═══════ --}}
    <div x-show="showModal" x-cloak x-transition.opacity
        class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
        <div @click.away="closeModal()" x-transition.scale.origin.center
            class="glass-card p-10 w-full max-w-lg shadow-3xl">

            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-[var(--text-main)] font-cairo" x-text="editIndex !== null ? 'Edit Series Data' : 'Add New Series'"></h3>
                <button @click="closeModal()" class="w-10 h-10 rounded-full bg-[var(--bg-glass)] text-[var(--text-muted)] hover:text-white hover:bg-rose-500 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-6 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm text-[var(--text-muted)] mb-2 font-black px-1">Series Name *</label>
                        <input type="text" x-model="form.name" class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-bold focus:outline-none focus:border-accent-blue transition-all" dir="rtl">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-[var(--text-muted)] mb-2 font-black px-1">Lead Actor</label>
                        <input type="text" x-model="form.lead" class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-bold focus:outline-none focus:border-accent-blue transition-all" dir="rtl">
                    </div>
                    <div>
                        <label class="block text-sm text-[var(--text-muted)] mb-2 font-black px-1">Number of Episodes</label>
                        <select x-model="form.episodes" class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-black focus:outline-none focus:border-accent-blue transition-all">
                            <option value="30">30 Episodes</option>
                            <option value="15">15 Episodes</option>
                            <option value="10">10 Episodes</option>
                            <option value="45">45 Episodes</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-[var(--text-muted)] mb-2 font-black px-1">Production Company</label>
                    <input type="text" x-model="form.company" class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-bold focus:outline-none focus:border-accent-blue transition-all" dir="rtl">
                </div>
                <div>
                    <label class="block text-sm text-[var(--text-muted)] mb-2 font-black px-1">Custom Search Keyword (Google Trends - Optional)</label>
                    <input type="text" x-model="form.searchKeyword" placeholder="Default name will be used if left blank" class="w-full bg-[var(--bg-glass)] border border-[var(--border-glass)] rounded-2xl py-3 px-5 text-[var(--text-main)] font-bold focus:outline-none focus:border-accent-blue transition-all" dir="rtl">
                </div>
            </div>

            <div class="flex gap-4 mt-8">
                <button @click="closeModal()" class="px-8 py-4 bg-[var(--bg-glass)] text-[var(--text-muted)] font-bold rounded-2xl hover:bg-[var(--border-glass)] transition-all">
                    Cancel
                </button>
                <button @click="saveForm()" class="flex-1 py-4 bg-accent-blue text-white font-black rounded-2xl hover:scale-[1.02] shadow-xl shadow-blue-500/20 transition-all">
                    <span x-text="editIndex !== null ? 'Update Data' : 'Add to List'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function seriesManagement() {
    return {
        loading: false, saving: false, showModal: false, series: [], editIndex: null, successMsg: '', errorMsg: '',
        watchItRanking: [], savingWatchIt: false, manualShow: '', uploadingCsv: false,
        notebookReport: '', isParsing: false,
        form: { name: '', lead: '', company: '', episodes: 30, searchKeyword: '' },
        init() { this.loadSeries(); this.loadWatchItRanking(); },
        loadSeries() {
            this.loading = true;
            fetch('{{ route("dashboard.drama-trends.api.series.get") }}')
                .then(r => r.json())
                .then(data => { this.series = data || []; this.loading = false; })
                .catch(() => { this.loading = false; });
        },
        loadWatchItRanking() {
            fetch('{{ route("dashboard.drama-trends.api.watchit.get") }}')
                .then(r => r.json())
                .then(data => { this.watchItRanking = data || []; });
        },
        openModal(idx = null) {
            this.editIndex = idx;
            if (idx !== null) { const s = this.series[idx]; this.form = { ...s }; }
            else { this.form = { name: '', lead: '', company: '', episodes: 30, searchKeyword: '' }; }
            this.showModal = true;
        },
        closeModal() { this.showModal = false; },
        saveForm() {
            if (!this.form.name.trim()) return;
            if (this.editIndex !== null) { this.series[this.editIndex] = { ...this.form }; }
            else { this.series.push({ ...this.form, isBaseline: this.series.length === 0 }); }
            this.closeModal();
        },
        deleteSeries(idx) { 
            const name = this.series[idx].name;
            if (confirm('Delete the series?')) {
                this.series.splice(idx, 1);
                // Also remove from ranking if exists
                this.watchItRanking = this.watchItRanking.filter(n => n !== name);
            }
        },
        setBaseline(idx) { this.series.forEach((s, i) => s.isBaseline = (i === idx)); },
        saveSeries() {
            this.saving = true;
            fetch('{{ route("dashboard.drama-trends.api.series.save") }}', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ series: this.series })
            })
            .then(r => r.json())
            .then(data => { this.saving = false; if (data.success) { this.successMsg = 'Series list saved!'; setTimeout(() => this.successMsg = '', 3000); } })
            .catch(() => this.saving = false);
        },
        
        // WATCH IT Ranking Logic
        addToRank(name) {
            if (this.watchItRanking.length < 10 && !this.watchItRanking.includes(name)) {
                this.watchItRanking.push(name);
            }
        },
        removeRank(idx) {
            this.watchItRanking.splice(idx, 1);
        },
        addManualRank() {
            if (this.manualShow.trim() && this.watchItRanking.length < 10 && !this.watchItRanking.includes(this.manualShow.trim())) {
                this.watchItRanking.push(this.manualShow.trim());
                this.manualShow = '';
            }
        },
        saveWatchItRanking() {
            this.savingWatchIt = true;
            fetch('{{ route("dashboard.drama-trends.api.watchit.save") }}', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ranking: this.watchItRanking })
            })
            .then(r => r.json())
            .then(data => { 
                this.savingWatchIt = false; 
                if (data.success) { 
                    this.successMsg = 'WATCH IT Ranking saved!'; 
                    setTimeout(() => this.successMsg = '', 3000); 
                } 
            })
            .catch(() => this.savingWatchIt = false);
        },
        
        // CSV Upload Logic
        uploadCsvs(e) {
            const files = e.target.files;
            if (!files.length) return;
            
            this.uploadingCsv = true;
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('csv_files[]', files[i]);
            }
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route("dashboard.drama-trends.api.csv.upload") }}', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                this.uploadingCsv = false;
                if (data.success) {
                    this.successMsg = 'Successfully uploaded, analyzed, and accurately added data to series!';
                    setTimeout(() => this.successMsg = '', 5000);
                } else {
                    alert(data.error || 'An error occurred during upload.');
                }
                this.$refs.csvInput.value = ''; // reset
            })
            .catch(() => {
                this.uploadingCsv = false;
                alert('A server connection error occurred.');
                this.$refs.csvInput.value = ''; // reset
            });
        },
        
        clearCsvs() {
            if (!confirm('Are you sure you want to clear CSV data and return to automatic mode?')) return;
            fetch('{{ route("dashboard.drama-trends.api.csv.clear") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.successMsg = data.message;
                    setTimeout(() => this.successMsg = '', 3000);
                }
            });
        },
        
        normalizeArabic(str) {
            if (!str) return '';
            let s = str.trim().toLowerCase();
            s = s.replace(/[أإآ]/g, 'ا');
            s = s.replace(/[ى]/g, 'ي');
            s = s.replace(/[ة]/g, 'ه');
            // Remove non-arabic/non-digit chars (optional but good for consistency)
            s = s.replace(/[^\u0621-\u064A\s\d]/g, '');
            return s.trim();
        },
        
        // Notebook Report Logic
        parseAndApplyReport() {
            if (!this.notebookReport.trim()) return;
            this.isParsing = true;
            
            try {
                const lines = this.notebookReport.split('\n');
                const override = {};
                
                lines.forEach(line => {
                    // Regex 1: Matches "1. علي كلاي ... 79%"
                    // Matches rank, name, and the final percentage at the end of the line
                    const mainMatch = line.match(/(?:\*\*)?(\d+)(?:\*\*)?[\.\s]+(?:\*\*)?([^\*:]+)(?:\*\*)?.*?(\d+)\s*%/);
                    if (mainMatch) {
                        const name = mainMatch[2].trim();
                        const score = parseInt(mainMatch[3]);
                        
                        // Regex 2: Matches "Cairo (80%)" inside the line
                        const regMatches = [...line.matchAll(/([^\(\s,،]+)\s*\((\d+)%\)/g)];
                        const regional = regMatches.slice(0, 3).map(m => ({
                            name: m[1].trim(),
                            value: parseInt(m[2])
                        }));

                        // Fuzzy match name against series list
                        const normName = this.normalizeArabic(name);
                        let matchedSeries = this.series.find(s => {
                            const sName = this.normalizeArabic(s.name);
                            return sName === normName || 
                                   normName.includes(sName) || 
                                   sName.includes(normName);
                        });

                        if (matchedSeries) {
                            override[matchedSeries.name] = { score, regional };
                        }
                    }
                });

                if (Object.keys(override).length === 0) {
                    alert('No valid data found. Ensure the report contains names and percentages.');
                    return;
                }

                fetch('{{ route("dashboard.drama-trends.api.notebook.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ override })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Report and surgical matching applied successfully!');
                        this.notebookReport = '';
                    } else {
                        alert('Error: ' + (data.error || 'Save failed'));
                    }
                });

            } catch (e) {
                console.error(e);
                alert('An error occurred while processing the report.');
            } finally {
                this.isParsing = false;
            }
        },

        clearNotebookReport() {
            if (!confirm('Are you sure you want to clear the surgical match and return to automatic results?')) return;
            
            fetch('{{ route("dashboard.drama-trends.api.notebook.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully cleared.');
                }
            });
        },
        
        // Series Backup Logic
        exportSeries() {
            if (this.series.length === 0) return alert('No series to export');
            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(this.series, null, 2));
            const downloadAnchorNode = document.createElement('a');
            downloadAnchorNode.setAttribute("href", dataStr);
            downloadAnchorNode.setAttribute("download", "drama_series_backup.json");
            document.body.appendChild(downloadAnchorNode);
            downloadAnchorNode.click();
            downloadAnchorNode.remove();
        },
        
        importSeries(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    const imported = JSON.parse(event.target.result);
                    if (!Array.isArray(imported)) throw new Error('Invalid format');
                    
                    if (confirm('Warning: This will replace the current list with imported data. Are you sure?')) {
                        this.series = imported;
                        this.successMsg = "Series imported successfully! Don't forget to save.";
                        setTimeout(() => this.successMsg = '', 4000);
                    }
                } catch (err) {
                    alert('Failed to read file: Make sure it is a valid JSON file.');
                }
                e.target.value = ''; // Reset input
            };
            reader.readAsText(file);
        }
    };
}
</script>
@endsection
