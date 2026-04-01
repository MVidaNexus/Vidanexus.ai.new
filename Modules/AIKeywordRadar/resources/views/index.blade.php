@extends('aikeywordradar::layouts.master')

@section('title', 'AI Keyword Radar')

@section('content')
@push('styles')
<style>
    .keyword-row:hover {
        border-color: var(--primary-cyan) !important;
        background: rgba(14, 165, 233, 0.03) !important;
    }
    .keyword-action-btn:hover {
        color: var(--primary-cyan) !important;
        border-color: var(--primary-cyan) !important;
    }
</style>
@endpush
<div x-data="keywordRadar()" x-init="init()" @sort-keywords.window="sortKeywords($event.detail.lang, $event.detail.criteria)" class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-black mb-2 flex items-center justify-center gap-4" style="color: var(--text-main);">
            <i class="fas fa-satellite-dish text-primary-cyan animate-pulse"></i>
            <span>AI Keyword Radar</span>
        </h1>
        <p class="text-lg font-medium" style="color: var(--text-muted);">Intelligence engine for emerging high-value keywords.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-xl flex items-center justify-between gap-3 font-bold" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i>
                {{ session('success') }}
            </div>
            <button @click="show = false" class="text-emerald-500/50 hover:text-emerald-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl flex items-center justify-between gap-3 font-bold" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                {{ session('error') }}
            </div>
            <button @click="show = false" class="text-red-400/50 hover:text-red-400 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @include('partials.tool-usage-badge', ['slug' => 'ai-keyword-radar'])

    <div class="grid grid-cols-1 {{ $enableEn ? 'lg:grid-cols-2' : '' }} gap-8 items-start">
        @if($enableEn)
            @include('aikeywordradar::partials.keyword_box', ['lang' => 'en', 'title' => 'Competitor Keywords (EN)', 'targetKeywords' => $targetKeywordsEn])
        @endif
        @include('aikeywordradar::partials.keyword_box', ['lang' => 'ar', 'title' => 'Competitor Keywords (AR)', 'targetKeywords' => $targetKeywordsAr])
    </div>
</div>

@push('scripts')
<script>
function keywordRadar() {
    return {
        selectedKeywordsAr: [],
        selectedKeywordsEn: [],
        loading: {
            syncAr: false,
            syncEn: false
        },
        
        init() {
            console.log('Keyword Radar Initialized');
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('synced')) {
                const msg = urlParams.get('synced');
                setTimeout(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: msg,
                        showConfirmButton: true,
                        confirmButtonText: 'Ok',
                        background: 'var(--card-bg)',
                        color: 'var(--text-main)'
                    });
                }, 500);
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            // Set default sorting to newest publish after a tick for the DOM
            setTimeout(() => {
                this.sortKeywords('ar', 'pubdate');
                if (document.querySelector('.keyword-container-en')) {
                    this.sortKeywords('en', 'pubdate');
                }
            }, 100);
        },

        showSyncNotification(lang, message, type = 'error') {
            const el = document.getElementById('sync-notification-' + lang);
            if (!el) return;

            const configs = {
                error: { bg: 'rgba(255,75,75,0.08)', border: 'rgba(255,75,75,0.25)', color: '#ff6b6b', icon: 'fas fa-exclamation-circle' },
                warning: { bg: 'rgba(255,170,0,0.08)', border: 'rgba(255,170,0,0.25)', color: '#ffaa00', icon: 'fas fa-exclamation-triangle' },
                info: { bg: 'rgba(0,102,255,0.08)', border: 'rgba(0,102,255,0.25)', color: '#66b3ff', icon: 'fas fa-info-circle' },
                success: { bg: 'rgba(16,185,129,0.08)', border: 'rgba(16,185,129,0.25)', color: '#10b981', icon: 'fas fa-check-circle' },
            };
            const c = configs[type] || configs.error;

            const hasSettings = message.includes('Settings') || message.includes('Add');
            const settingsLink = hasSettings
                ? `<a href="{{ route('dashboard.ai-keyword-radar.settings') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:8px 16px;background:${c.color}22;color:${c.color};border:1px solid ${c.border};border-radius:10px;font-size:0.8rem;font-weight:700;text-decoration:none;transition:all 0.2s;"><i class="fas fa-cog"></i> Radar Settings</a>`
                : '';

            el.innerHTML = `
                <div style="display:flex;align-items:flex-start;gap:12px;padding:16px 20px;background:${c.bg};border:1px solid ${c.border};border-radius:16px;position:relative;">
                    <div style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:${c.color}18;display:flex;align-items:center;justify-content:center;color:${c.color};font-size:1.1rem;">
                        <i class="${c.icon}"></i>
                    </div>
                    <div style="flex:1;">
                        <p style="margin:0;font-size:0.85rem;font-weight:600;color:${c.color};line-height:1.6;">${message}</p>
                        ${settingsLink}
                    </div>
                    <button onclick="this.closest('#sync-notification-${lang}').style.display='none'" style="position:absolute;top:8px;right:10px;background:none;border:none;color:${c.color};cursor:pointer;opacity:0.5;font-size:0.8rem;">&times;</button>
                </div>
            `;
            el.style.display = 'block';
        },

        syncCompetitors(lang) {
            const prop = lang === 'ar' ? 'syncAr' : 'syncEn';
            if (this.loading[prop]) return;
            this.loading[prop] = true;
            
            const notifEl = document.getElementById('sync-notification-' + lang);
            if (notifEl) notifEl.style.display = 'none';
            
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('lang', lang);

            fetch(`{{ route('dashboard.ai-keyword-radar.sync') }}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const contentType = res.headers.get('content-type') || '';
                
                // Guard: If server returned HTML instead of JSON (proxy timeout / 502 / 504)
                if (!contentType.includes('application/json')) {
                    const rawText = await res.text();
                    console.error('Non-JSON response:', res.status, rawText.substring(0, 200));
                    
                    if (res.status >= 500 || rawText.startsWith('<!') || rawText.startsWith('<html')) {
                        throw new Error('Server timeout — the sync is processing too many competitors. Please try again in a moment, the server may still be working in the background.');
                    }
                    throw new Error('Unexpected server response (Status: ' + res.status + ')');
                }
                
                return res.json().then(d => ({ ok: res.ok, status: res.status, data: d }));
            })
            .then(({ ok, status, data }) => {
                this.loading[prop] = false;
                if (!ok || !data.success) {
                    let type = 'error';
                    if (status === 422) type = 'warning';
                    if (status === 403 || status === 402) {
                        if (data.message && data.message.includes('Insufficient balance')) {
                            showInsufficientBalanceAlert(data.message);
                            return;
                        }
                    }
                    this.showSyncNotification(lang, data.message || 'Unknown error occurred', type);
                    return;
                }
                
                window.location.href = window.location.pathname + '?synced=' + encodeURIComponent(data.message);
            })
            .catch(error => {
                console.error('Error during sync:', error);
                this.loading[prop] = false;
                this.showSyncNotification(lang, error.message || 'Error during synchronization', 'error');
            });
        },

        toggleSelectAll(lang, allKeywords) {
            const prop = lang === 'ar' ? 'selectedKeywordsAr' : 'selectedKeywordsEn';
            if (this[prop].length === allKeywords.length) {
                this[prop] = [];
            } else {
                this[prop] = [...allKeywords];
            }
        },

        copySelectedKeywords(lang) {
            const prop = lang === 'ar' ? 'selectedKeywordsAr' : 'selectedKeywordsEn';
            if (this[prop].length === 0) return;
            const textToCopy = this[prop].join('\n');
            copyToClipboard(textToCopy);
        },

        sortKeywords(lang, criteria) {
            const container = document.querySelector(`.keyword-container-${lang}`);
            if (!container) return;
            
            const rows = Array.from(container.querySelectorAll('.keyword-row'));
            
            rows.sort((a, b) => {
                if (criteria === 'alphabetical') {
                    // Get text content of the keyword string
                    const elA = a.querySelector('.font-bold');
                    const elB = b.querySelector('.font-bold');
                    const textA = elA ? elA.textContent.trim() : '';
                    const textB = elB ? elB.textContent.trim() : '';
                    return textA.localeCompare(textB, lang); // Ascending A-Z
                } else {
                    const valA = parseFloat(a.dataset[criteria]) || 0;
                    const valB = parseFloat(b.dataset[criteria]) || 0;
                    return valB - valA; // Descending order for dates
                }
            });

            // Re-append to container to change the order visually
            rows.forEach((row, index) => {
                container.appendChild(row);
                // Update numerical indicator to match new visual order
                const numSpan = row.querySelector('.keyword-num');
                if (numSpan) {
                    numSpan.textContent = String(index + 1).padStart(2, '0');
                }
            });
        }
    }
}

// Bind to window to avoid any Alpine nesting/scope issues
document.addEventListener('alpine:init', () => {
    // We bind a fallback in case the internal method isn't reachable
});

window.executeKeywordSort = function(lang, criteria) {
    const container = document.querySelector(`.keyword-container-${lang}`);
    if (!container) return;
    const rows = Array.from(container.querySelectorAll('.keyword-row'));
    rows.sort((a, b) => {
        if (criteria === 'alphabetical') {
            const elA = a.querySelector('.font-bold');
            const elB = b.querySelector('.font-bold');
            const textA = elA ? elA.textContent.trim() : '';
            const textB = elB ? elB.textContent.trim() : '';
            return textA.localeCompare(textB, lang);
        } else {
            const valA = parseFloat(a.dataset[criteria]) || 0;
            const valB = parseFloat(b.dataset[criteria]) || 0;
            return valB - valA;
        }
    });
    rows.forEach((row, index) => {
        container.appendChild(row);
        const numSpan = row.querySelector('.keyword-num');
        if (numSpan) {
            numSpan.textContent = String(index + 1).padStart(2, '0');
        }
    });
};

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Copied Successfully',
            showConfirmButton: false,
            timer: 1500,
            background: 'var(--card-bg)',
            color: 'var(--text-main)'
        });
    });
}
</script>
@endpush

@push('styles')
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--primary-cyan);
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
@endpush
@endsection
