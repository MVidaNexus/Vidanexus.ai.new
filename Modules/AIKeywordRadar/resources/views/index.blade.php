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
    <div class="glass-card mb-16 border-white/5 backdrop-blur-xl overflow-hidden shadow-2xl shadow-primary-cyan/5">
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
        <div class="mb-6 p-4 glass-card border border-emerald-500/30 text-emerald-400 rounded-xl flex items-center justify-between gap-3 font-bold shadow-lg shadow-emerald-500/5" x-data="{ show: true }" x-show="show">
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
        <div class="mb-6 p-4 glass-card border border-red-500/30 text-red-400 rounded-xl flex items-center justify-between gap-3 font-bold shadow-lg shadow-red-500/5" x-data="{ show: true }" x-show="show">
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

    {{-- Dedicated Direct Seed Keywords Explorer Box (Always Visible) --}}
    <div class="mt-10">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-black text-white flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm shadow-sm">
                    <i class="fas fa-crosshairs"></i>
                </div>
                <span>{{ __('Direct Seed Keywords Explorer (الكلمات المفتاحية المباشرة المستهدفة)') }}</span>
            </h3>
            <span class="text-xs font-bold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full">
                <i class="fas fa-bolt text-[10px] mr-1"></i> {{ __('Direct Search Intent & Autocomplete Intelligence') }}
            </span>
        </div>

        <div class="grid grid-cols-1 {{ $enableEn ? 'lg:grid-cols-2' : '' }} gap-8 items-start">
            @if($enableEn)
                <div>
                    @include('aikeywordradar::partials.keyword_box', [
                        'lang' => 'en',
                        'title' => 'Direct Seed Explorer (EN)',
                        'targetKeywords' => $directSeedKeywordsEn ?? [],
                        'boxId' => 'direct_seed',
                        'boxColor' => '#10b981',
                    ])
                </div>
            @endif
            <div>
                @include('aikeywordradar::partials.keyword_box', [
                    'lang' => 'ar',
                    'title' => 'الكلمات المفتاحية المباشرة (AR)',
                    'targetKeywords' => $directSeedKeywordsAr ?? [],
                    'boxId' => 'direct_seed',
                    'boxColor' => '#10b981',
                ])
            </div>
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

    <div class="mt-16 mb-20 flex justify-center">
        <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="px-8 py-4 bg-white/5 border border-white/10 text-gray-400 font-bold rounded-2xl hover:bg-white/10 hover:text-white transition-all flex items-center gap-3" style="background: var(--card-bg); border-color: var(--glass-border); color: var(--text-main); box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <i class="fas fa-cog" style="color: var(--primary-cyan);"></i>
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
        syncStatus: {},
        syncCountdown: {},
        currentTime: Date.now(),
        
        init() {
            this.selectedKeywords['direct_seed_ar'] = [];
            this.selectedKeywords['direct_seed_en'] = [];
            this.loading['sync_direct_seed_ar'] = false;
            this.loading['sync_direct_seed_en'] = false;
            @foreach($customBoxes ?? [] as $box)
            this.selectedKeywords['{{ $box['id'] }}'] = [];
            @endforeach
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
                this.sortKeywords('ar', 'pubdate');
                if (document.querySelector('.keyword-container-en')) this.sortKeywords('en', 'pubdate');
            }, 100);

            this._recoverActiveSyncOnLoad();
        },

        async _recoverActiveSyncOnLoad() {
            // Check active sync state silently for AR and EN so mobile users see progress if already running
            try {
                const res = await fetch(`{{ route('dashboard.ai-keyword-radar.get-keywords') }}?lang=ar`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data && data.sync_running) {
                    this._startSyncPolling('syncing_ar', 'ar', null, (data.keywords || []).length);
                }
            } catch (e) {
                // Ignore silent recovery errors
            }
        },

        _waitForActiveSync(prop, lang, boxId, timeFilter) {
            if (this.syncCountdown[prop]?._waitingActive) return;

            if (this.syncCountdown[prop]) {
                this.syncCountdown[prop] = { ...this.syncCountdown[prop], waiting: true, _waitingActive: true };
            }

            let polls = 0;
            const maxPolls = 40;
            const pollMs = 5000;

            const pollFn = async () => {
                polls++;
                if (polls > maxPolls) {
                    this._finishSyncLoading(prop);
                    this.showSyncNotification(lang, lang === 'ar'
                        ? 'انتهت مهلة الانتظار. حدّث الصفحة أو أعد المحاولة.'
                        : 'Wait timed out. Refresh the page or try again.', 'warning');
                    return;
                }

                try {
                    const url = `{{ route('dashboard.ai-keyword-radar.get-keywords') }}?lang=${lang}${boxId ? '&box_id='+boxId : ''}`;
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const data = await res.json();

                    if (data.success && !data.sync_running) {
                        this._finishSyncLoading(prop);
                        const count = (data.keywords || []).length;
                        if (count > 0) {
                            window.location.reload();
                            return;
                        }
                        Swal.fire({
                            title: lang === 'ar' ? 'اكتمل المسح' : 'Scan complete',
                            text: lang === 'ar' ? 'لم يتم العثور على كلمات جديدة. جرّب آخر 24 ساعة.' : 'No new keywords found. Try Last 24h.',
                            icon: 'info',
                            timer: 4000,
                            showConfirmButton: false,
                            background: '#0f172a',
                            color: '#fff',
                        });
                        return;
                    }
                } catch (e) {
                    console.warn('[Sync Wait]', e.message);
                }

                setTimeout(pollFn, pollMs);
            };

            setTimeout(pollFn, pollMs);
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
                    <div style="flex:1;"><p dir="ltr" style="margin:0;font-size:0.85rem;font-weight:600;color:${c.color};line-height:1.6;text-align:left;">${message}</p>${settingsLink}</div>
                    <button onclick="this.closest('#sync-notification-${lang}').style.display='none'" style="position:absolute;top:8px;right:10px;background:none;border:none;color:${c.color};cursor:pointer;opacity:0.5;font-size:0.8rem;">&times;</button>
                </div>`;
            el.style.display = 'block';
        },

        _estimateSyncSeconds(timeFilter) {
            const t = String(timeFilter || '60m').toLowerCase().trim();
            if (t === 'all' || t === 'any' || t === 'unlimited') return 90;
            if (t === '24h' || t === '1d') return 75;
            return 60;
        },

        _startSyncCountdown(prop, timeFilter, initialSeconds = null) {
            this._stopSyncCountdown(prop);
            const total = initialSeconds ?? this._estimateSyncSeconds(timeFilter);
            const tick = () => {
                const state = this.syncCountdown[prop];
                if (!state) return;
                const interval = state._interval;
                if (state.remaining > 0) {
                    this.syncCountdown[prop] = { ...state, remaining: state.remaining - 1, _interval: interval };
                } else {
                    this.syncCountdown[prop] = { ...state, overtime: state.overtime + 1, _interval: interval };
                }
            };
            const intervalId = setInterval(tick, 1000);
            this.syncCountdown[prop] = { remaining: total, total, overtime: 0, waiting: false, _interval: intervalId };
        },

        _stopSyncCountdown(prop) {
            const state = this.syncCountdown[prop];
            if (state?._interval) clearInterval(state._interval);
            delete this.syncCountdown[prop];
        },

        _clearWaitingFlag(prop) {
            if (this.syncCountdown[prop]) {
                this.syncCountdown[prop] = { ...this.syncCountdown[prop], _waitingActive: false };
            }
        },

        _finishSyncLoading(prop) {
            this._clearWaitingFlag(prop);
            this._stopSyncCountdown(prop);
            this.loading[prop] = false;
        },

        syncCountdownClock(prop) {
            const c = this.syncCountdown[prop];
            if (!c) return '--:--';
            const secs = c.remaining > 0 ? c.remaining : c.overtime;
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            const prefix = c.remaining > 0 ? '' : '+';
            return prefix + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        syncCountdownLabel(prop, lang = 'ar') {
            const c = this.syncCountdown[prop];
            if (!c) return '';
            if (c.waiting) {
                return lang === 'ar' ? 'مسح قيد التشغيل — يرجى الانتظار' : 'Scan in progress — please wait';
            }
            if (c.remaining > 0) {
                return lang === 'ar' ? 'الوقت المتبقي تقريباً' : 'approx. time remaining';
            }
            return lang === 'ar' ? 'يستغرق وقتاً أطول من المعتاد…' : 'Taking longer than usual…';
        },

        syncCountdownPercent(prop) {
            const c = this.syncCountdown[prop];
            if (!c || !c.total) return 0;
            if (c.remaining > 0) {
                return Math.min(100, Math.round(((c.total - c.remaining) / c.total) * 100));
            }
            return 100;
        },

        syncCompetitors(lang, timeFilter = '60m', boxId = '', mode = 'smart') {
            const prop = boxId ? (boxId === 'direct_seed' ? `sync_direct_seed_${lang}` : `sync_${boxId}`) : (lang === 'ar' ? 'syncAr' : 'syncEn');
            if (this.loading[prop]) return;
            this.loading[prop] = true;
            this._startSyncCountdown(prop, timeFilter);
            const notifEl = document.getElementById('sync-notification-' + lang);
            if (notifEl) notifEl.style.display = 'none';
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lang', lang);
            formData.append('time_filter', timeFilter);
            formData.append('mode', mode || 'smart');
            if (boxId) formData.append('box_id', boxId);
            const controller = new AbortController();
            const syncTimeoutMs = 240 * 1000; // 4 minutes generous timeout so browser never aborts prematurely
            const syncTimeout = setTimeout(() => controller.abort(), syncTimeoutMs);
            fetch(`{{ route('dashboard.ai-keyword-radar.sync') }}`, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body:formData, signal: controller.signal })
            .then(async res => {
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    const raw = await res.text();
                    console.error('[Sync Error] Non-JSON response', { status: res.status, preview: raw.slice(0, 200) });
                    throw new Error('FETCH_FAILED');
                }
                return res.json().then(d => ({ ok:res.ok, status:res.status, data:d }));
            })
            .then(({ ok, status, data }) => {
                clearTimeout(syncTimeout);
                if (!ok || !data.success) {
                    if (status === 429 && (data.sync_running || data.error_code === 'ALREADY_PROCESSING' || (data.message || '').toLowerCase().includes('already') || (data.message || '').toLowerCase().includes('progress'))) {
                        const remain = parseInt(data.lock_remaining_seconds, 10);
                        if (remain > 0) {
                            this._startSyncCountdown(prop, timeFilter, remain);
                        }
                        this._waitForActiveSync(prop, lang, boxId, timeFilter);
                        return;
                    }
                    this._finishSyncLoading(prop);
                    if (data.error_code === 'NO_COMPETITORS') {
                        Swal.fire({
                            title: lang === 'ar' ? '🎯 أضف روابط المنافسين للبدء' : '🎯 Add Competitors to Start',
                            text: data.message,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: lang === 'ar' ? '<i class="fas fa-cog"></i> فتح إعدادات الرادار' : '<i class="fas fa-cog"></i> Go to Radar Settings',
                            cancelButtonText: lang === 'ar' ? 'إغلاق' : 'Close',
                            confirmButtonColor: '#0ea5e9',
                            background: '#0f172a',
                            color: '#fff',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('dashboard.ai-keyword-radar.settings') }}";
                            }
                        });
                        this.showSyncNotification(lang, data.message, 'warning');
                        return;
                    }
                    const msg = this.resolveSyncError(data, status);
                    if (data.error_code === 'INSUFFICIENT_CREDITS' || status === 402) {
                        showInsufficientBalanceAlert(msg);
                        return;
                    }
                    this.showSyncNotification(lang, msg, status === 422 ? 'warning' : 'error');
                    return;
                }

                // Live-credit chip: animate the new wallet balance immediately,
                // before any modal/redirect — so the user sees the deduction
                // even if we later reload to refresh the keyword list.
                if (window.VidaCredits) window.VidaCredits.apply(data);

                if (data.status === 'completed') {
                    this._finishSyncLoading(prop);
                    const added = data.new_count || 0;
                    const icon = added > 0 ? 'success' : (data.headlines > 0 ? 'info' : 'warning');

                    // Only reload when there is fresh keyword data to render,
                    // otherwise just toast — credits already animated in place
                    // and the existing list is still accurate.
                    const shouldReload = added > 0 || (data.keywords && data.keywords.length > 0);
                    Swal.fire({
                        title: added > 0 ? (lang === 'ar' ? `🎯 تم اكتشاف ${added} اتجاه جديد!` : `🎯 ${added} New Leads!`) : (lang === 'ar' ? '✅ اكتمل تحديث الرادار' : 'Insight Update Complete'),
                        text: data.message,
                        icon: added > 0 ? 'success' : 'info',
                        timer: added > 0 ? 2500 : 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        background: '#0f172a',
                        color: '#fff',
                    }).then(() => { if (shouldReload) window.location.reload(); });
                    return;
                }

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

                const initialCount = data.current_count || 0;
                const pollLang = data.lang || lang;
                const pollBoxId = data.box_id || boxId;
                this._startSyncPolling(prop, pollLang, pollBoxId, initialCount);
            })
            .catch(async error => {
                clearTimeout(syncTimeout);
                console.warn('[Sync Disconnect/Timeout]', error);

                // Mobile resilience check: verify if background sync is actively running or completed on server
                try {
                    const checkUrl = `{{ route('dashboard.ai-keyword-radar.get-keywords') }}?lang=${lang}${boxId ? '&box_id='+boxId : ''}`;
                    const res = await fetch(checkUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const checkData = await res.json();

                    if (checkData && checkData.sync_running) {
                        Swal.fire({
                            title: '📡 Radar Scanning',
                            text: lang === 'ar' ? 'الرادار يواصل المسح الذكي في الخلفية...' : 'Radar is scanning in background...',
                            icon: 'info',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 6000,
                            timerProgressBar: true,
                            background: '#0f172a',
                            color: '#fff',
                        });
                        this._waitForActiveSync(prop, lang, boxId, timeFilter);
                        return;
                    }

                    if (checkData && checkData.success && (checkData.keywords || []).length > 0) {
                        this._finishSyncLoading(prop);
                        window.location.reload();
                        return;
                    }
                } catch (recoveryErr) {
                    console.warn('[Recovery Check Failed]', recoveryErr);
                }

                this._finishSyncLoading(prop);
                const code = error.name === 'AbortError' ? 'NETWORK_ERROR' : (error.message || 'FETCH_FAILED');
                const msg = this.resolveSyncError({ error_code: code }, error.name === 'AbortError' ? 408 : 500);
                Swal.fire({
                    title: 'Radar Status',
                    text: msg,
                    icon: 'warning',
                    background: '#0f172a',
                    color: '#fff',
                    confirmButtonColor: '#0ea5e9',
                });
            });
        },

        resolveSyncError(data, status) {
            if (data?.message && String(data.message).trim() !== '') return data.message;
            const code = data?.error_code || '';
            const messages = {
                NO_COMPETITORS: 'No competitors found. Please add competitor website links in Radar Settings.',
                INSUFFICIENT_CREDITS: 'Insufficient credits. Please purchase more.',
                FETCH_FAILED: 'Unable to fetch fresh data. Service may be down.',
                ALREADY_PROCESSING: 'Refresh in progress. Please wait.',
                NETWORK_ERROR: 'Sync took too long and was cancelled. Try Last 24h or fewer competitors.',
                VALIDATION_ERROR: 'Please check your inputs and try again.',
                AUTH_REQUIRED: 'Please log in to continue.',
                TOOL_LOCKED: 'You need to unlock this tool first.',
                SERVER_ERROR: 'Something went wrong on our side. Please try again.',
            };
            if (messages[code]) return messages[code];
            if (status === 422) return messages.VALIDATION_ERROR;
            if (status === 408 || status === 504) return messages.NETWORK_ERROR;
            if (status >= 500) return messages.FETCH_FAILED;
            return messages.SERVER_ERROR;
        },

        _startSyncPolling(prop, lang, boxId, initialCount) {
            let pollCount = 0;
            const maxPolls = 30; // 90 seconds max
            const pollInterval = 3000; // 3 seconds fast check
            
            console.log(`[Sync Poll] Started for ${prop}. Initial count: ${initialCount}, lang: ${lang}`);

            const pollFn = async () => {
                pollCount++;
                if (pollCount > maxPolls) {
                    this._finishSyncLoading(prop);
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

                        // Case 1: New keywords found
                        if (newCount > initialCount) {
                            this._finishSyncLoading(prop);
                            const added = newCount - initialCount;
                            if (window.VidaCredits) window.VidaCredits.refresh();
                            await Swal.fire({
                                title: `🎯 ${added} New Leads!`,
                                text: `Intelligence update complete. Found ${added} high-value keywords.`,
                                icon: 'success',
                                timer: 2500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                background: '#0f172a',
                                color: '#fff',
                            });
                            window.location.reload();
                            return;
                        }

                        // Case 2: Job completed
                        if (!syncRunning && pollCount > 1) {
                            this._finishSyncLoading(prop);
                            if (window.VidaCredits) window.VidaCredits.refresh();
                            await Swal.fire({
                                title: lang === 'ar' ? 'اكتمل المسح' : 'Insight Update Complete',
                                text: lang === 'ar' ? 'تم تحديث الرادار بأحدث البيانات.' : 'Radar updated with latest news.',
                                icon: 'info',
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                background: '#0f172a',
                                color: '#fff'
                            });
                            window.location.reload();
                            return;
                        }
                    }
                } catch (e) {
                    console.warn('[Sync Poll] Error:', e.message);
                }

                // Continue polling
                setTimeout(pollFn, pollInterval);
            };

            // Start first poll quickly after 2s
            setTimeout(pollFn, 2000);
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
                    this._finishSyncLoading(prop);
                    // Re-apply the active time filter after Alpine has patched
                    // the DOM with the new rows from the server.
                    this.$nextTick(() => {
                        const active = window.__keywordTimeFilterState && window.__keywordTimeFilterState[lang];
                        if (active && typeof window.applyKeywordTimeFilter === 'function') {
                            window.applyKeywordTimeFilter(lang, active);
                        }
                    });
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
            } catch (e) { this._finishSyncLoading(prop); console.error(e); }
        },

        toggleSelectAll(boxKey, allKeywords) {
            if (!this.selectedKeywords[boxKey]) {
                this.selectedKeywords[boxKey] = [];
            }
            if (this.selectedKeywords[boxKey].length === allKeywords.length) {
                this.selectedKeywords[boxKey] = [];
            } else {
                this.selectedKeywords[boxKey] = [...allKeywords];
            }
            this.syncKeywordCheckboxes(boxKey);
            const selectAllCb = document.querySelector(`.kw-select-all-${boxKey}`);
            if (selectAllCb) {
                selectAllCb.checked = this.selectedKeywords[boxKey].length > 0 && this.selectedKeywords[boxKey].length === allKeywords.length;
            }
        },

        clearSelection(boxKey) {
            this.selectedKeywords[boxKey] = [];
            this.syncKeywordCheckboxes(boxKey);
            const selectAllCb = document.querySelector(`.kw-select-all-${boxKey}`);
            if (selectAllCb) selectAllCb.checked = false;
        },

        toggleKeyword(boxKey, keyword, checked) {
            if (!this.selectedKeywords[boxKey]) {
                this.selectedKeywords[boxKey] = [];
            }
            if (checked) {
                if (!this.selectedKeywords[boxKey].includes(keyword)) {
                    this.selectedKeywords[boxKey].push(keyword);
                }
            } else {
                this.selectedKeywords[boxKey] = this.selectedKeywords[boxKey].filter(k => k !== keyword);
            }
            const allCheckboxes = document.querySelectorAll(`.kw-select-${boxKey}`);
            const selectAllCb = document.querySelector(`.kw-select-all-${boxKey}`);
            if (selectAllCb && allCheckboxes.length > 0) {
                selectAllCb.checked = this.selectedKeywords[boxKey].length === allCheckboxes.length;
            }
        },

        syncKeywordCheckboxes(boxKey) {
            const selected = this.selectedKeywords[boxKey] || [];
            document.querySelectorAll(`.kw-select-${boxKey}`).forEach(cb => {
                cb.checked = selected.includes(cb.value);
            });
        },

        selectedCount(boxKey) {
            return (this.selectedKeywords[boxKey] || []).length;
        },

        copySelectedKeywords(boxKey) {
            const list = this.selectedKeywords[boxKey] || [];
            if (list.length === 0) return;
            const text = list.join('\n');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            const isAr = boxKey === 'ar';
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: isAr ? `تم نسخ ${list.length} كلمة بنجاح!` : `Copied ${list.length} keywords!`,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
                background: '#0f172a',
                color: '#fff',
            });
        },

        confirmDeleteAll(boxKey, lang, boxId = '') {
            const isAr = lang === 'ar';
            Swal.fire({
                title: isAr ? 'هل أنت متأكد من حذف الكلمات؟' : 'Delete All Keywords?',
                text: isAr ? 'سيتم مسح جميع الكلمات والعناوين المستخرجة لهذا الصندوق نهائياً.' : 'This will permanently remove all extracted keywords for this box.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: isAr ? 'نعم، احذف الكل' : 'Yes, Delete All',
                cancelButtonText: isAr ? 'إلغاء' : 'Cancel',
                background: '#0f172a',
                color: '#fff',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById(`delete-all-form-${boxKey}`);
                    if (form) form.submit();
                }
            });
        },

        sortKeywords(lang, criteria) {
            const container = document.querySelector(`.keyword-container-${lang}`);
            if (!container) return;
            const cards = Array.from(container.querySelectorAll('.headline-card, .keyword-row:not(.headline-card)'));
            cards.sort((a, b) => {
                if (criteria === 'alphabetical') {
                    const titleA = a.querySelector('h3')?.textContent.trim() || a.querySelector('.font-bold')?.textContent.trim() || '';
                    const titleB = b.querySelector('h3')?.textContent.trim() || b.querySelector('.font-bold')?.textContent.trim() || '';
                    return titleA.localeCompare(titleB, lang);
                }
                return (parseFloat(b.dataset[criteria]) || 0) - (parseFloat(a.dataset[criteria]) || 0);
            });
            cards.forEach((card) => container.appendChild(card));
        },

        getRelativeTime(ts, lang = 'ar') {
            if (!ts) return '';
            
            // Normalize timestamp (handle strings, ms, s)
            let date = typeof ts === 'number' ? new Date(ts * (ts < 1e12 ? 1000 : 1)) : new Date(ts);
            if (isNaN(date)) return '';
            
            const diffSeconds = Math.floor((this.currentTime - date.getTime()) / 1000);
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

window.executeKeywordSort = function(lang, criteria) {
    const container = document.querySelector(`.keyword-container-${lang}`);
    if (!container) return;
    const cards = Array.from(container.querySelectorAll('.headline-card, .keyword-row:not(.headline-card)'));
    cards.sort((a, b) => {
        if (criteria === 'alphabetical') {
            const titleA = a.querySelector('h3')?.textContent.trim() || a.querySelector('.font-bold')?.textContent.trim() || '';
            const titleB = b.querySelector('h3')?.textContent.trim() || b.querySelector('.font-bold')?.textContent.trim() || '';
            return titleA.localeCompare(titleB, lang);
        }
        return (parseFloat(b.dataset[criteria]) || 0) - (parseFloat(a.dataset[criteria]) || 0);
    });
    cards.forEach((card) => container.appendChild(card));
    if (window.__keywordTimeFilterState && window.__keywordTimeFilterState[lang]) {
        window.applyKeywordTimeFilter(lang, window.__keywordTimeFilterState[lang]);
    }
};

// Keep track of the active time filter per box so other handlers
// (sort, post-sync refresh) can re-apply it without re-querying the UI.
window.__keywordTimeFilterState = window.__keywordTimeFilterState || {};

/**
 * Hide keyword rows whose detection timestamp is older than the selected window.
 * `value` is one of '60m', '24h', '6h', '1d', 'all', etc. — the same tokens the
 * sync endpoint accepts. We use the row's data-pulldate (sync time) as the
 * source of truth, falling back to data-pubdate when sync is missing.
 */
window.applyKeywordTimeFilter = function(lang, value) {
    window.__keywordTimeFilterState[lang] = value;
    const container = document.querySelector(`.keyword-container-${lang}`);
    if (!container) return;

    const minutes = (function tokenToMinutes(token) {
        if (!token) return 60;
        const t = String(token).toLowerCase().trim();
        if (t === '' || t === 'all' || t === 'any' || t === 'unlimited') return null;
        let m = t.match(/^(\d+)\s*(m|min|mins|minute|minutes)$/);
        if (m) return Math.max(1, parseInt(m[1], 10));
        m = t.match(/^(\d+)\s*(h|hr|hrs|hour|hours)$/);
        if (m) return Math.max(1, parseInt(m[1], 10)) * 60;
        m = t.match(/^(\d+)\s*(d|day|days)$/);
        if (m) return Math.max(1, parseInt(m[1], 10)) * 60 * 24;
        if (/^\d+$/.test(t)) return Math.max(1, parseInt(t, 10));
        return 60;
    })(value);

    const cutoffSec = minutes === null
        ? null
        : Math.floor(Date.now() / 1000) - minutes * 60;

    const cards = Array.from(container.querySelectorAll('.headline-card, .keyword-row:not(.headline-card)'));
    const totalKeywords = container.querySelectorAll('.keyword-chip-row').length;
    let visibleKeywords = 0;

    cards.forEach((card) => {
        const pull = parseFloat(card.dataset.pulldate) || 0;
        const pub  = parseFloat(card.dataset.pubdate) || 0;
        const stamp = pull > 0 ? pull : pub;
        const shouldShow = cutoffSec === null || stamp === 0 || stamp >= cutoffSec;
        card.style.display = shouldShow ? '' : 'none';
        if (shouldShow) {
            visibleKeywords += card.querySelectorAll('.keyword-chip-row').length;
        }
    });

    const card = container.closest('.glass-card');
    const badge = card ? card.querySelector('[data-keyword-count]') : null;
    if (badge) {
        badge.textContent = totalKeywords === visibleKeywords
            ? `${visibleKeywords} Keywords`
            : `${visibleKeywords} / ${totalKeywords} Keywords`;
    }
};

window.filterBoxByIntent = function(boxKey, intent) {
    const container = document.getElementById(`keywords-container-${boxKey}`);
    if (!container) return;

    const cards = container.querySelectorAll('.headline-card, .keyword-row');
    const totalKeywords = container.querySelectorAll('.keyword-chip-row').length;
    let visibleKeywords = 0;

    cards.forEach(card => {
        const chips = card.querySelectorAll('.keyword-chip-row');
        if (intent === 'all') {
            card.style.display = '';
            chips.forEach(chip => {
                chip.style.display = '';
                visibleKeywords++;
            });
            return;
        }

        let hasMatch = false;
        chips.forEach(chip => {
            const chipIntent = chip.getAttribute('data-intent') || 'general';
            if (chipIntent === intent) {
                chip.style.display = '';
                hasMatch = true;
                visibleKeywords++;
            } else {
                chip.style.display = 'none';
            }
        });

        card.style.display = hasMatch ? '' : 'none';
    });

    const card = container.closest('.glass-card');
    const badge = card ? card.querySelector('[data-keyword-count]') : null;
    if (badge) {
        badge.textContent = totalKeywords === visibleKeywords
            ? `${visibleKeywords} Keywords`
            : `${visibleKeywords} / ${totalKeywords} Keywords`;
    }
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
