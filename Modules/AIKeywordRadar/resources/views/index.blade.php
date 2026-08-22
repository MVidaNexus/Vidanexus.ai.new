@extends('aikeywordradar::layouts.master')

@section('title', 'AI Keyword Radar')

@section('content')
@push('styles')
<style>
    /* Radar Spinner */
    .radar-spinner { position: relative; width: 64px; height: 64px; }
    .radar-circle { position: absolute; inset: 0; border-radius: 50%; border: 2px solid transparent; border-top-color: var(--primary-cyan, #0ea5e9); animation: radar-spin 1.2s linear infinite; }
    .radar-circle-2 { inset: 6px; border-top-color: rgba(14, 165, 233, 0.4); animation-duration: 1.8s; animation-direction: reverse; }
    .radar-dot { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 8px; height: 8px; background: var(--primary-cyan); border-radius: 50%; box-shadow: 0 0 12px var(--primary-cyan); animation: radar-pulse 1.5s ease-in-out infinite; }
    @keyframes radar-spin { to { transform: rotate(360deg); } }
    @keyframes radar-pulse { 0%, 100% { opacity:1; transform: translate(-50%,-50%) scale(1); } 50% { opacity:0.5; transform: translate(-50%,-50%) scale(1.5); } }
    
    /* Keyword row hover */
    .keyword-row:hover { border-color: var(--primary-cyan) !important; background: rgba(14, 165, 233, 0.03) !important; }
    
    /* Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--primary-cyan); }
    
    [x-cloak] { display: none !important; }
</style>
@endpush

<div x-data="keywordRadar()" x-init="init()" class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="text-center mb-16 pt-10">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-cyan/10 border border-primary-cyan/20 text-primary-cyan text-xs font-bold mb-6 animate-pulse">
            <i class="fas fa-satellite-dish"></i> RADAR SURVEILLANCE ACTIVE
        </div>
        <h1 class="text-6xl md:text-8xl font-black mb-6 tracking-tighter text-white" style="text-shadow: 0 0 30px rgba(0, 243, 255, 0.4); line-height: 1.1;">
            KEYWORD <span style="color: var(--primary-cyan);">SPY RADAR</span>
        </h1>
        <p class="text-xl md:text-2xl font-bold opacity-90 max-w-3xl mx-auto" style="color: var(--text-muted);">
            Spot your next big win before everyone else.
        </p>
    </div>

    <!-- Professional Keyword Surveillance Protocol (Redesigned) -->
    <div class="glass-card mb-16 border-white/5 bg-[#0f172a]/60 backdrop-blur-xl overflow-hidden shadow-2xl shadow-primary-cyan/5">
        <div class="p-6 md:p-10">
            <!-- Sleek Header -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 mb-10 border-b border-white/5 pb-8">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-cyan/20 to-blue-500/5 flex items-center justify-center text-primary-cyan border border-primary-cyan/30 shadow-[0_0_20px_rgba(0,243,255,0.15)]">
                        <i class="fas fa-satellite-dish text-2xl animate-pulse"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-black tracking-[0.2em] text-primary-cyan uppercase opacity-70">Extraction Protocol</span>
                            <div class="w-2 h-2 rounded-full bg-primary-cyan animate-pulse"></div>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                            Keyword <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-cyan to-blue-400">Surveillance</span>
                        </h2>
                    </div>
                </div>
                
                <div class="flex items-center gap-8 pr-4">
                    <div class="text-right">
                        <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">System Status</span>
                        <span class="text-xs font-bold text-emerald-400 flex items-center gap-2 justify-end">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> ACTIVE
                        </span>
                    </div>
                    <div class="h-12 w-px bg-white/10 hidden md:block"></div>
                    <div class="flex items-center gap-4 bg-white/5 px-6 py-3 rounded-2xl border border-white/10">
                        <div class="text-right">
                            <span class="block text-2xl font-black text-primary-cyan tabular-nums leading-none">12s</span>
                            <span class="text-[9px] uppercase tracking-tighter text-gray-400 font-bold">Extraction Speed</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Compact 4-Column Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Module 01: Competitor Gaps -->
                <div class="group p-5 md:p-6 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] hover:border-primary-cyan/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-primary-cyan/10 flex items-center justify-center text-primary-cyan text-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-500 tracking-wider">INTEL-01</span>
                    </div>
                    <h3 class="font-black text-white text-sm md:text-base mb-2 group-hover:text-primary-cyan transition-colors">Competitor Gaps</h3>
                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 italic">Dissect rival content footprints to move in first.</p>
                </div>

                <!-- Module 02: Trend Velocity -->
                <div class="group p-5 md:p-6 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] hover:border-purple-500/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400 text-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-500 tracking-wider">INTEL-02</span>
                    </div>
                    <h3 class="font-black text-white text-sm md:text-base mb-2 group-hover:text-purple-400 transition-colors">Trend Velocity</h3>
                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 italic">Catch shifts in real-time before saturation.</p>
                </div>

                <!-- Module 03: Niche Intelligence -->
                <div class="group p-5 md:p-6 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] hover:border-emerald-500/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400 text-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-500 tracking-wider">INTEL-03</span>
                    </div>
                    <h3 class="font-black text-white text-sm md:text-base mb-2 group-hover:text-emerald-400 transition-colors">Niche Intelligence</h3>
                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 italic">Map underlying sub-topic silos automatically.</p>
                </div>

                <!-- Module 04: Pulse Detection -->
                <div class="group p-5 md:p-6 rounded-2xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.04] hover:border-orange-500/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-400 text-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-wave-square"></i>
                        </div>
                        <span class="text-[10px] font-black text-gray-500 tracking-wider">INTEL-04</span>
                    </div>
                    <h3 class="font-black text-white text-sm md:text-base mb-2 group-hover:text-orange-400 transition-colors">Pulse Detection</h3>
                    <p class="text-[11px] text-gray-500 leading-relaxed line-clamp-2 italic">Identify 0-day opportunities from news feeds.</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-[#0f172a] border border-emerald-500/30 text-emerald-400 rounded-xl flex items-center justify-between gap-3 font-bold shadow-lg shadow-emerald-500/5" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                </div>
                {{ session('success') }}
            </div>
            <button @click="show = false" class="text-emerald-500/50 hover:text-emerald-500 transition-colors p-2"><i class="fas fa-times"></i></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 bg-[#0f172a] border border-red-500/30 text-red-400 rounded-xl flex items-center justify-between gap-3 font-bold shadow-lg shadow-red-500/5" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                </div>
                {{ session('error') }}
            </div>
            <button @click="show = false" class="text-red-400/50 hover:text-red-400 transition-colors p-2"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @include('partials.tool-usage-badge', ['slug' => 'ai-keyword-radar'])

    <div class="grid grid-cols-1 {{ $enableEn ? 'lg:grid-cols-2' : '' }} gap-8 items-start">
        @if($enableEn)
            <div>
                @include('aikeywordradar::partials.keyword_box', ['lang' => 'en', 'title' => 'Competitor Keywords (EN)', 'targetKeywords' => $targetKeywordsEn])
            </div>
        @endif
        <div>
            @include('aikeywordradar::partials.keyword_box', ['lang' => 'ar', 'title' => 'Competitor Keywords (AR)', 'targetKeywords' => $targetKeywordsAr])
        </div>
    </div>

    {{-- Custom Boxes --}}
    @if(!empty($customBoxes))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mt-8">
            @foreach($customBoxes as $box)
                <div>
                    @include('aikeywordradar::partials.keyword_box', [
                        'lang' => $box['lang'] ?? 'ar',
                        'title' => $box['name'] ?? 'Custom',
                        'targetKeywords' => $customBoxKeywords[$box['id']] ?? [],
                        'boxId' => $box['id'] ?? null,
                        'boxColor' => $box['color'] ?? '#a855f7',
                    ])
                </div>
            @endforeach
        </div>
    @endif

    <!-- Footer Action -->
    <div class="mt-16 mb-20 flex justify-center">
        <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="px-8 py-4 bg-white/5 border border-white/10 text-gray-400 font-bold rounded-2xl hover:border-primary-cyan/50 hover:text-white transition-all flex items-center gap-3">
            <i class="fas fa-cog"></i>
            Radar Settings
        </a>
    </div>
</div>

@push('scripts')
<script>
function keywordRadar() {
    return {
        selectedKeywords: { ar: [], en: [] },
        loading: { syncAr: false, syncEn: false },
        syncStatus: {}, // Track box syncing status
        currentTime: Date.now(),
        
        init() {
            // Update currentTime every 30s to refresh relative timestamps
            setInterval(() => { this.currentTime = Date.now(); }, 30000);
            
            console.log('Keyword Radar Initialized');
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('synced')) {
                const msg = urlParams.get('synced');
                setTimeout(() => {
                if (msg) {
                    Swal.fire({ 
                        toast: true, 
                        position: 'top-end', 
                        icon: 'success', 
                        title: msg, 
                        showConfirmButton: false, 
                        timer: 3000, 
                        timerProgressBar: true,
                        background: '#0f172a', 
                        color: '#fff' 
                    });
                }
                }, 500);
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            setTimeout(() => {
                document.querySelectorAll('[class*="keyword-container-"]').forEach(container => {
                    const classes = container.className.split(' ');
                    const targetClass = classes.find(c => c.startsWith('keyword-container-'));
                    if (targetClass) {
                        const boxKey = targetClass.replace('keyword-container-', '');
                        this.sortKeywords(boxKey, 'pubdate');
                    }
                });
            }, 100);
        },

        showSyncNotification(lang, message, type = 'error') {
            const el = document.getElementById('sync-notification-' + lang);
            if (!el) return;
            const configs = {
                error: { bg:'rgba(255,75,75,0.08)', border:'rgba(255,75,75,0.25)', color:'#ff6b6b', icon:'fas fa-exclamation-circle' },
                warning: { bg:'rgba(255,170,0,0.08)', border:'rgba(255,170,0,0.25)', color:'#ffaa00', icon:'fas fa-exclamation-triangle' },
                info: { bg:'rgba(0,102,255,0.08)', border:'rgba(0,102,255,0.25)', color:'#66b3ff', icon:'fas fa-info-circle' },
                success: { bg:'rgba(16,185,129,0.08)', border:'rgba(16,185,129,0.25)', color:'#10b981', icon:'fas fa-check-circle' },
            };
            const c = configs[type] || configs.error;
            const hasSettings = message.includes('Settings') || message.includes('Add');
            const settingsLink = hasSettings
                ? `<a href="{{ route('dashboard.ai-keyword-radar.settings') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:8px 16px;background:${c.color}22;color:${c.color};border:1px solid ${c.border};border-radius:10px;font-size:0.8rem;font-weight:700;text-decoration:none;"><i class="fas fa-cog"></i> Radar Settings</a>`
                : '';
            el.innerHTML = `
                <div style="display:flex;align-items:flex-start;gap:12px;padding:16px 20px;background:${c.bg};border:1px solid ${c.border};border-radius:16px;position:relative;">
                    <div style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:${c.color}18;display:flex;align-items:center;justify-content:center;color:${c.color};font-size:1.1rem;"><i class="${c.icon}"></i></div>
                    <div style="flex:1;"><p style="margin:0;font-size:0.85rem;font-weight:600;color:${c.color};line-height:1.6;">${message}</p>${settingsLink}</div>
                    <button onclick="this.closest('#sync-notification-${lang}').style.display='none'" style="position:absolute;top:8px;right:10px;background:none;border:none;color:${c.color};cursor:pointer;opacity:0.5;font-size:0.8rem;">&times;</button>
                </div>`;
            el.style.display = 'block';
        },

        syncCompetitors(lang, timeFilter = '60m', boxId = '') {
            const prop = boxId ? `sync_${boxId}` : (lang === 'ar' ? 'syncAr' : 'syncEn');
            if (this.loading[prop]) return;
            this.loading[prop] = true;
            const notifEl = document.getElementById('sync-notification-' + lang);
            if (notifEl) notifEl.style.display = 'none';
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lang', lang);
            formData.append('time_filter', timeFilter);
            if (boxId) formData.append('box_id', boxId);
            fetch(`{{ route('dashboard.ai-keyword-radar.sync') }}`, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body:formData })
            .then(async res => {
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    const raw = await res.text();
                    if (res.status >= 500 || raw.startsWith('<!') || raw.startsWith('<html')) throw new Error('Server error. Please try again.');
                    throw new Error('Unexpected response (Status: ' + res.status + ')');
                }
                return res.json().then(d => ({ ok:res.ok, status:res.status, data:d }));
            })
            .then(({ ok, status, data }) => {
                if (!ok || !data.success) {
                    this.loading[prop] = false;
                    if ((status === 403 || status === 402) && data.message?.includes('Insufficient balance')) { showInsufficientBalanceAlert(data.message); return; }
                    this.showSyncNotification(lang, data.message || 'Unknown error', status === 422 ? 'warning' : 'error');
                    return;
                }
                
                if (data.status === 'completed') {
                    this.loading[prop] = false;
                    Swal.fire({
                        title: '🎯 Radar Scanning Complete',
                        text: data.message,
                        icon: 'success',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        background: '#0f172a',
                        color: '#fff',
                    });
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }

                // If job dispatched to background — show toast and start polling
                Swal.fire({
                    title: '📡 Radar Scanning',
                    text: data.message,
                    icon: 'info',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 8000,
                    timerProgressBar: true,
                    background: '#0f172a',
                    color: '#fff',
                });

                // Start polling for new keywords every 12 seconds
                const initialCount = data.current_count || 0;
                const pollLang = data.lang || lang;
                const pollBoxId = data.box_id || boxId;
                this._startSyncPolling(prop, pollLang, pollBoxId, initialCount);
            })
            .catch(error => {
                this.loading[prop] = false;
                console.error('[Sync Error]', error);
                Swal.fire({
                    title: 'Radar Error',
                    text: error.message || 'Failed to initialize radar scanning.',
                    icon: 'error',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#ef4444',
                });
            });
        },

        _startSyncPolling(prop, lang, boxId, initialCount) {
            let pollCount = 0;
            const maxPolls = 100; // 20 minutes max
            const pollInterval = 12000; // 12 seconds
            
            console.log(`[Sync Poll] Started for ${prop}. Initial count: ${initialCount}, lang: ${lang}`);

            const pollFn = async () => {
                pollCount++;
                if (pollCount > maxPolls) {
                    this.loading[prop] = false;
                    Swal.fire({
                        title: 'Radar Timeout',
                        text: 'Intelligence scan is taking longer than usual. Please refresh shortly.',
                        icon: 'warning',
                        background: '#0f172a',
                        color: '#fff',
                        confirmButtonColor: '#0ea5e9',
                    });
                    return;
                }

                try {
                    const url = `{{ route('dashboard.ai-keyword-radar.get-keywords') }}?lang=${lang}${boxId ? '&box_id='+boxId : ''}`;
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();
                    
                    if (data.success) {
                        const newCount = (data.keywords || []).length;
                        const syncRunning = data.sync_running || false;

                        // Case 1: New keywords found! Perfect.
                        if (newCount > initialCount) {
                            this.loading[prop] = false;
                            const added = newCount - initialCount;
                            await Swal.fire({
                                title: `🎯 ${added} New Leads!`,
                                text: `Intelligence update complete. Found ${added} high-value keywords.`,
                                icon: 'success',
                                timer: 3500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                background: '#0f172a',
                                color: '#fff',
                            });
                            window.location.reload();
                            return;
                        }

                        // Case 2: No new keywords, but the job has FINISHED (sync_lock is gone)
                        // We skip the first few polls to give the job time to start and acquire the lock
                        if (!syncRunning && pollCount > 1) { 
                            this.loading[prop] = false;
                            Swal.fire({
                                title: 'Insight Update Complete',
                                text: 'No new market shifts detected in the selected timeframe.',
                                icon: 'info',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                background: '#0f172a',
                                color: '#fff'
                            });
                            // Refresh anyway to update the 'synced_at' and relative times
                            setTimeout(() => window.location.reload(), 3000);
                            return;
                        }
                    }
                } catch (e) {
                    console.warn('[Sync Poll] Error:', e.message);
                }

                // Continue polling
                setTimeout(pollFn, pollInterval);
            };

            // Start first poll after delay
            setTimeout(pollFn, pollInterval);
        },

        async refreshBoxData(lang, boxId) {
            const prop = boxId ? `sync_${boxId}` : (lang === 'ar' ? 'syncAr' : 'syncEn');
            this.loading[prop] = true;
            try {
                const url = `{{ route('dashboard.ai-keyword-radar.get-keywords') }}?lang=${lang}${boxId ? '&box_id='+boxId : ''}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.success) {
                    if (boxId) { this.customBoxKeywords[boxId] = data.keywords; } 
                    else if (lang === 'ar') { this.targetKeywordsAr = data.keywords; } 
                    else { this.targetKeywordsEn = data.keywords; }
                    delete this.syncStatus[prop];
                    this.loading[prop] = false;
                    Swal.fire({ 
                        title: 'Updated!', 
                        icon: 'success', 
                        toast: true, 
                        position: 'top-end', 
                        timer: 3000, 
                        timerProgressBar: true,
                        showConfirmButton: false,
                        background: '#0f172a',
                        color: '#fff'
                    });
                }
            } catch (e) { this.loading[prop] = false; console.error(e); }
        },

        toggleSelectAll(boxKey, allKeywords) {
            if (!this.selectedKeywords[boxKey]) {
                this.selectedKeywords[boxKey] = [];
            }
            this.selectedKeywords[boxKey] = this.selectedKeywords[boxKey].length === allKeywords.length ? [] : [...allKeywords];
        },

        copySelectedKeywords(boxKey) {
            const list = this.selectedKeywords[boxKey] || [];
            if (list.length === 0) return;
            copyToClipboard(list.join('\n'));
        },

        sortKeywords(boxKey, criteria) {
            const container = document.querySelector(`.keyword-container-${boxKey}`);
            if (!container) return;
            const rows = Array.from(container.querySelectorAll('.keyword-row'));
            rows.sort((a, b) => {
                if (criteria === 'alphabetical') {
                    return (a.querySelector('.font-bold')?.textContent.trim() || '').localeCompare(b.querySelector('.font-bold')?.textContent.trim() || '', 'ar');
                }
                return (parseFloat(b.dataset[criteria]) || 0) - (parseFloat(a.dataset[criteria]) || 0);
            });
            rows.forEach((row, i) => {
                container.appendChild(row);
                const n = row.querySelector('.keyword-num');
                if (n) n.textContent = String(i + 1).padStart(2, '0');
            });
        },

        getRelativeTime(ts, lang = 'ar') {
            if (!ts) return '';
            
            // Normalize timestamp (handle strings, ms, s)
            let date = typeof ts === 'number' ? new Date(ts * (ts < 1e12 ? 1000 : 1)) : new Date(ts);
            if (isNaN(date.getTime())) return '';
            
            let diffSeconds = Math.floor((this.currentTime - date.getTime()) / 1000);
            if (diffSeconds < 0) diffSeconds = 0; // Prevent negative diffs due to clock skew
            
            const diffMinutes = Math.floor(diffSeconds / 60);
            const diffHours = Math.floor(diffMinutes / 60);
            const diffDays = Math.floor(diffHours / 24);

            if (lang === 'ar') {
                if (diffSeconds < 60) return 'الآن';
                if (diffMinutes === 1) return 'منذ دقيقة';
                if (diffMinutes === 2) return 'منذ دقيقتين';
                if (diffMinutes >= 3 && diffMinutes <= 10) return `منذ ${diffMinutes} دقائق`;
                if (diffMinutes < 60) return `منذ ${diffMinutes} دقيقة`;
                
                if (diffHours === 1) return 'منذ ساعة';
                if (diffHours === 2) return 'منذ ساعتين';
                if (diffHours >= 3 && diffHours <= 10) return `منذ ${diffHours} ساعات`;
                if (diffHours < 24) return `منذ ${diffHours} ساعة`;
                
                if (diffDays === 1) return 'منذ يوم';
                if (diffDays === 2) return 'منذ يومين';
                return `منذ ${diffDays} أيام`;
            } else {
                if (diffSeconds < 60) return 'Now';
                if (diffMinutes < 60) return `${diffMinutes}m ago`;
                if (diffHours < 24) return `${diffHours}h ago`;
                return `${diffDays}d ago`;
            }
        }
    }
}

window.executeKeywordSort = function(boxKey, criteria) {
    const container = document.querySelector(`.keyword-container-${boxKey}`);
    if (!container) return;
    const rows = Array.from(container.querySelectorAll('.keyword-row'));
    rows.sort((a, b) => {
        if (criteria === 'alphabetical') {
            return (a.querySelector('.font-bold')?.textContent.trim() || '').localeCompare(b.querySelector('.font-bold')?.textContent.trim() || '', 'ar');
        }
        return (parseFloat(b.dataset[criteria]) || 0) - (parseFloat(a.dataset[criteria]) || 0);
    });
    rows.forEach((row, i) => {
        container.appendChild(row);
        const n = row.querySelector('.keyword-num');
        if (n) n.textContent = String(i + 1).padStart(2, '0');
    });
};

window.filterKeywordsByTime = function(boxKey, timeValue) {
    const container = document.querySelector(`.keyword-container-${boxKey}`);
    if (!container) return;
    const rows = container.querySelectorAll('.keyword-row');
    const now = Date.now();
    
    let limitMs = 24 * 60 * 60 * 1000; // Default 24h
    if (timeValue === '60m') {
        limitMs = 60 * 60 * 1000;
    } else if (timeValue === 'all') {
        limitMs = 30 * 24 * 60 * 60 * 1000; // 30 days
    }

    let visibleCount = 0;
    rows.forEach(row => {
        const pubdate = parseInt(row.dataset.pubdate) * 1000;
        const pulldate = parseInt(row.dataset.pulldate) * 1000;
        const effectiveTime = pubdate > 0 ? pubdate : pulldate;
        
        if ((now - effectiveTime) <= limitMs) {
            row.style.setProperty('display', 'flex', 'important');
            visibleCount++;
        } else {
            row.style.setProperty('display', 'none', 'important');
        }
    });

    // Update count badge
    const badge = document.querySelector(`.keyword-count-badge-${boxKey}`);
    if (badge) {
        badge.textContent = `${visibleCount} Keywords`;
    }

    // Re-index only visible rows
    let visibleIndex = 1;
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const numEl = row.querySelector('.keyword-num');
            if (numEl) {
                numEl.textContent = String(visibleIndex++).padStart(2, '0');
            }
        }
    });
};

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Copied Successfully', showConfirmButton:false, timer:1500, background:'var(--card-bg)', color:'var(--text-main)' });
    });
}

function showInsufficientBalanceAlert(msg) {
    Swal.fire({
        icon: 'error',
        title: 'Insufficient Credits',
        text: msg,
        background: 'var(--card-bg)',
        color: 'var(--text-main)',
        showCancelButton: true,
        confirmButtonText: 'Buy Credits',
        cancelButtonText: 'Close',
        confirmButtonColor: 'var(--primary-cyan)',
        cancelButtonColor: 'rgba(255,255,255,0.05)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/dashboard/wallet';
        }
    });
}
</script>
@endpush
@endsection
