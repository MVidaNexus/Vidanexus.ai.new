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
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-black mb-2 flex items-center justify-center gap-4" style="color: var(--text-main);">
            <i class="fas fa-satellite-dish text-primary-cyan animate-pulse"></i>
            <span>AI Keyword Radar</span>
        </h1>
        <p class="text-lg font-medium" style="color: var(--text-muted);">Intelligence engine for emerging high-value keywords.</p>
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
                this.sortKeywords('ar', 'pubdate');
                if (document.querySelector('.keyword-container-en')) this.sortKeywords('en', 'pubdate');
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
                
                // Job dispatched to background — show toast and start polling
                Swal.fire({
                    title: '🔄 Sync Running',
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
                    title: 'Sync Error',
                    text: error.message || 'Failed to start sync. Please try again.',
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
                        title: 'Sync Timeout',
                        text: 'Sync is taking longer than expected. Please refresh the page in a few minutes.',
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
                                title: `✅ ${added} New Keywords!`,
                                text: `Sync complete. Found ${added} new keywords. Reloading...`,
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
                                title: 'Sync Finished',
                                text: 'No new keywords found in the selected time range.',
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

        sortKeywords(lang, criteria) {
            const container = document.querySelector(`.keyword-container-${lang}`);
            if (!container) return;
            const rows = Array.from(container.querySelectorAll('.keyword-row'));
            rows.sort((a, b) => {
                if (criteria === 'alphabetical') {
                    return (a.querySelector('.font-bold')?.textContent.trim() || '').localeCompare(b.querySelector('.font-bold')?.textContent.trim() || '', lang);
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
    const rows = Array.from(container.querySelectorAll('.keyword-row'));
    rows.sort((a, b) => {
        if (criteria === 'alphabetical') {
            return (a.querySelector('.font-bold')?.textContent.trim() || '').localeCompare(b.querySelector('.font-bold')?.textContent.trim() || '', lang);
        }
        return (parseFloat(b.dataset[criteria]) || 0) - (parseFloat(a.dataset[criteria]) || 0);
    });
    rows.forEach((row, i) => {
        container.appendChild(row);
        const n = row.querySelector('.keyword-num');
        if (n) n.textContent = String(i + 1).padStart(2, '0');
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
