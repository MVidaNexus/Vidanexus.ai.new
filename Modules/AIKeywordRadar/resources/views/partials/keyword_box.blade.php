@php
    $customBoxId = $boxId ?? null; // Custom box ID (null for standard AR/EN)
    $customBoxColor = $boxColor ?? null;
    
    if ($customBoxId) {
        // Custom Box
        $colorVar = $customBoxColor;
        $colorClass = 'purple-500';
        $icon = 'fas fa-layer-group';
        $loadingModel = "loading['sync_{$customBoxId}']";
        $selectedModel = "selectedKeywords['{$customBoxId}']";
        $boxKey = $customBoxId;
        $boxSettings = collect(auth()->user()->settings['keywords_custom_boxes'] ?? [])->firstWhere('id', $customBoxId);
        $userCompetitors = $boxSettings['competitors'] ?? '';
        $hasCompetitors = !empty(trim($userCompetitors));
        $isAr = ($boxSettings['lang'] ?? 'ar') === 'ar';
        $htmlBoxId = 'kr-box-' . $customBoxId;
    } else {
        // Standard AR/EN
        $colorClass = $lang === 'ar' ? 'primary-cyan' : 'blue-500';
        $colorVar = $lang === 'ar' ? 'var(--primary-cyan)' : '#3b82f6';
        $icon = $lang === 'ar' ? 'fas fa-crosshairs' : 'fas fa-globe-americas';
        $loadingModel = $lang === 'ar' ? "loading['syncAr']" : "loading['syncEn']";
        $selectedModel = "selectedKeywords['{$lang}']";
        $boxKey = $lang;
        $userCompetitors = auth()->user()->settings['keywords_competitors' . ($lang === 'en' ? '_en' : '')] ?? '';
        $hasCompetitors = !empty(trim($userCompetitors));
        $isAr = $lang === 'ar';
        $htmlBoxId = 'kr-box-' . $lang;
    }
@endphp

{{-- Control Bar OUTSIDE the overflow-hidden card --}}
<div class="mb-2" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <div class="flex items-center gap-2 flex-wrap {{ $isAr ? 'justify-start' : 'justify-end flex-row-reverse' }}">
        
        {{-- Sort Dropdown --}}
        <div class="relative" x-data="{ sortOpen: false, currentSort: 'Newest Publish' }" @click.away="sortOpen = false">
            <button type="button" @click="sortOpen = !sortOpen" 
                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:#0f172a;border:1px solid rgba(255,255,255,0.15);color:#fff;cursor:pointer;">
                <i class="fas fa-sort-amount-down text-[10px]" style="color: {{ $colorVar }};"></i> 
                <span x-text="currentSort">Newest Publish</span>
                <i class="fas fa-chevron-down text-[8px] opacity-60 transition-transform duration-200" :class="{'rotate-180': sortOpen}"></i>
            </button>
            <div x-show="sortOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-[-4px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:220px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:14px;overflow:hidden;z-index:9999;box-shadow:0 15px 40px rgba(0,0,0,0.6);">
                <div style="padding:8px 14px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.3);border-bottom:1px solid rgba(255,255,255,0.06);">
                    <i class="fas fa-sort text-[8px] mr-1 opacity-40"></i> Sort Keywords By
                </div>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Sync'; window.executeKeywordSort('{{ $lang }}', 'pulldate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(245,158,11,0.15);color:#f59e0b;flex-shrink:0;font-size:10px;"><i class="fas fa-satellite-dish"></i></span>
                    <span>Newest Sync</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Publish'; window.executeKeywordSort('{{ $lang }}', 'pubdate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(14,165,233,0.15);color:#0ea5e9;flex-shrink:0;font-size:10px;"><i class="fas fa-history"></i></span>
                    <span>Newest Publish</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'A → Z'; window.executeKeywordSort('{{ $lang }}', 'alphabetical');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(168,85,247,0.15);color:#a855f7;flex-shrink:0;font-size:10px;"><i class="fas fa-sort-alpha-down"></i></span>
                    <span>Alphabetical (A-Z)</span>
                </button>
            </div>
        </div>

        {{-- Time Filter + Sync --}}
        <div class="relative" x-data="{ timeOpen: false, timeLabel: 'Last 60m', timeValue: '60m' }" @click.away="timeOpen = false">
            <div class="flex items-center gap-2">
                {{-- Time Filter Button --}}
                <button type="button" @click="timeOpen = !timeOpen" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:#0f172a;border:1px solid rgba(255,255,255,0.15);color:#fff;cursor:pointer;">
                    <i class="fas fa-clock text-[10px]" style="color: #10b981;"></i> 
                    <span x-text="timeLabel">Last 60m</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-60 transition-transform duration-200" :class="{'rotate-180': timeOpen}"></i>
                </button>

                {{-- Sync Button --}}
                <button @click="syncCompetitors('{{ $lang }}', timeValue, '{{ $customBoxId ?? '' }}')" :disabled="{{ $loadingModel }}" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:12px;font-size:11px;font-weight:900;color:#000;white-space:nowrap;border:1px solid rgba(255,255,255,0.1);background:{{ $colorVar }};box-shadow:0 4px 20px {{ $colorVar }}35;cursor:pointer;transition:transform 0.15s;"
                    onmouseover="if(!this.disabled)this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-sync-alt text-[10px]" :class="{ 'fa-spin': {{ $loadingModel }} }"></i> 
                    <span x-text="{{ $loadingModel }} ? 'Syncing...' : 'Sync {{ $customBoxId ? $title : strtoupper($lang) }}'"></span>
                </button>
            </div>

            {{-- Time Filter Dropdown --}}
            <div x-show="timeOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-[-4px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:240px;background:#0f172a;border:1px solid rgba(255,255,255,0.12);border-radius:14px;overflow:hidden;z-index:9999;box-shadow:0 15px 40px rgba(0,0,0,0.6);">
                <div style="padding:8px 14px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.3);border-bottom:1px solid rgba(255,255,255,0.06);">
                    <i class="fas fa-filter text-[8px] mr-1 opacity-40"></i> Select Time Range
                </div>

                {{-- 60 Minutes --}}
                <button type="button" @click="timeOpen = false; timeLabel = 'Last 60m'; timeValue = '60m'"
                    style="display:flex;align-items:center;justify-content:space-between;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;cursor:pointer;transition:background 0.15s;"
                    :style="timeValue === '60m' ? 'background:rgba(16,185,129,0.08)' : ''"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background=this.getAttribute(':style')?.includes('60m')?'rgba(16,185,129,0.08)':'transparent'">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(16,185,129,0.15);color:#10b981;flex-shrink:0;font-size:11px;"><i class="fas fa-history"></i></span>
                        <div style="display:flex;flex-direction:column;">
                            <span style="font-size:12px;font-weight:700;line-height:1.3;">Last 60 Minutes</span>
                            <span style="font-size:9px;color:rgba(255,255,255,0.35);font-weight:500;">Most recent content only</span>
                        </div>
                    </div>
                    <i class="fas fa-check text-[10px]" x-show="timeValue === '60m'" style="color:#10b981;"></i>
                </button>

                {{-- 24 Hours --}}
                <button type="button" @click="timeOpen = false; timeLabel = 'Last 24h'; timeValue = '24h'"
                    style="display:flex;align-items:center;justify-content:space-between;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;transition:background 0.15s;"
                    :style="timeValue === '24h' ? 'background:rgba(245,158,11,0.08)' : ''"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background=''">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(245,158,11,0.15);color:#f59e0b;flex-shrink:0;font-size:11px;"><i class="fas fa-calendar-day"></i></span>
                        <div style="display:flex;flex-direction:column;">
                            <span style="font-size:12px;font-weight:700;line-height:1.3;">Last 24 Hours</span>
                            <span style="font-size:9px;color:rgba(255,255,255,0.35);font-weight:500;">Today's trending content</span>
                        </div>
                    </div>
                    <i class="fas fa-check text-[10px]" x-show="timeValue === '24h'" style="color:#f59e0b;"></i>
                </button>

                {{-- All Time --}}
                <button type="button" @click="timeOpen = false; timeLabel = 'All Time'; timeValue = 'all'"
                    style="display:flex;align-items:center;justify-content:space-between;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:#fff;border:none;border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;transition:background 0.15s;"
                    :style="timeValue === 'all' ? 'background:rgba(59,130,246,0.08)' : ''"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background=''">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:rgba(59,130,246,0.15);color:#3b82f6;flex-shrink:0;font-size:11px;"><i class="fas fa-infinity"></i></span>
                        <div style="display:flex;flex-direction:column;">
                            <span style="font-size:12px;font-weight:700;line-height:1.3;">All Time Content</span>
                            <span style="font-size:9px;color:rgba(255,255,255,0.35);font-weight:500;">No time restriction</span>
                        </div>
                    </div>
                    <i class="fas fa-check text-[10px]" x-show="timeValue === 'all'" style="color:#3b82f6;"></i>
                </button>
            </div>
        </div>

        {{-- Settings --}}
        <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" 
            style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:12px;font-size:11px;font-weight:700;border:1px solid rgba(168,85,247,0.25);color:#a855f7;background:rgba(168,85,247,0.05);white-space:nowrap;text-decoration:none;transition:all 0.2s;"
            onmouseover="this.style.background='rgba(168,85,247,0.15)'" onmouseout="this.style.background='rgba(168,85,247,0.05)'">
            <i class="fas fa-cog text-[10px]"></i> Settings
        </a>
    </div>
</div>

{{-- Main Card - NO overflow-hidden on the wrapper, only on content area --}}
<div x-init="if(!selectedKeywords['{{ $boxKey }}']) selectedKeywords['{{ $boxKey }}'] = []"
     class="glass-card flex flex-col h-[75vh] lg:h-[calc(100vh-160px)] relative border border-white/5 glass-border-light" style="border-top-color: {{ $colorVar }}40; overflow: hidden;" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    {{-- Full-Box Loading Overlay --}}
    <div id="sync-loading-{{ $lang }}" x-show="{{ $loadingModel }}" class="absolute inset-0 z-50 flex items-center justify-center backdrop-blur-md" style="display: none; background: var(--glass-bg);">
        <div class="flex flex-col items-center">
            <div class="radar-spinner mb-5">
                <div class="radar-circle"></div>
                <div class="radar-circle radar-circle-2"></div>
                <div class="radar-dot"></div>
            </div>
            <span class="text-sm font-bold" style="color: var(--text-main);">Scanning competitor headlines...</span>
            <span class="text-[10px] mt-2" style="color: var(--text-muted);">Extracting keywords using AI — this may take up to 2 minutes</span>
        </div>
    </div>

    {{-- Decorative Gradient Orbs --}}
    <div class="absolute top-0 {{ $isAr ? 'right-0' : 'left-0' }} w-40 h-40 blur-3xl rounded-full opacity-[0.07] pointer-events-none" style="background: {{ $colorVar }}; margin-{{ $isAr ? 'right' : 'left' }}: -5rem; margin-top: -5rem;"></div>

    {{-- Box Header: Title + Actions --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.05);background:rgba(255,255,255,0.02);flex-shrink:0;" class="{{ $isAr ? '' : 'flex-row-reverse' }}">
        <h2 class="text-lg sm:text-xl font-black flex items-center gap-2 sm:gap-3 whitespace-nowrap" style="color: var(--text-main);">
            <div style="width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid {{ $colorVar }}25;background:{{ $colorVar }}12;color:{{ $colorVar }};" class="hidden sm:flex">
                <i class="{{ $icon }} text-xs sm:text-sm"></i>
            </div>
            <span>{{ $title }}</span>
        </h2>
        
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap {{ $isAr ? '' : 'flex-row-reverse' }}">
            @if(!empty($targetKeywords))
                <div class="flex items-center gap-2 bg-white/5 px-2 py-1 rounded-lg border border-white/5 whitespace-nowrap">
                    <input type="checkbox" 
                           @click="toggleSelectAll('{{ $boxKey }}', @js(array_map(fn($kw) => is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw, $targetKeywords)))"
                           :checked="{{ $selectedModel }}.length === {{ count($targetKeywords) }} && {{ count($targetKeywords) }} > 0"
                           class="w-4 h-4 rounded border-gray-300 dark:border-white/10 bg-white/5 cursor-pointer" style="color: {{ $colorVar }};">
                    <span class="text-[10px] font-bold text-gray-400">All</span>
                </div>

                <template x-if="{{ $selectedModel }} && {{ $selectedModel }}.length > 0">
                    <button @click="copySelectedKeywords('{{ $boxKey }}')" 
                        style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;color:#000;border-radius:8px;font-size:9px;font-weight:900;white-space:nowrap;background:{{ $colorVar }};cursor:pointer;">
                        <i class="far fa-copy"></i>
                        <span>Copy (<span x-text="{{ $selectedModel }}.length"></span>)</span>
                    </button>
                </template>

                <form action="{{ route('dashboard.ai-keyword-radar.delete-all', ['lang' => $lang]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all keywords?')" class="flex-shrink-0">
                    @csrf
                    @if($customBoxId)
                        <input type="hidden" name="box_id" value="{{ $customBoxId }}">
                    @endif
                    <button type="submit" style="padding:4px 10px;background:rgba(239,68,68,0.1);color:#ef4444;border-radius:8px;font-size:10px;font-weight:700;border:1px solid rgba(239,68,68,0.2);white-space:nowrap;cursor:pointer;" 
                        onmouseover="this.style.background='#ef4444';this.style.color='#fff'" onmouseout="this.style.background='rgba(239,68,68,0.1)';this.style.color='#ef4444'">
                        <i class="fas fa-trash-alt mr-1"></i> Delete 
                    </button>
                </form>
            @endif
            <span style="font-size:10px;font-weight:700;padding:4px 10px;border-radius:8px;background:rgba(255,255,255,0.05);color:#94a3b8;white-space:nowrap;">{{ count($targetKeywords ?? []) }} Keywords</span>
        </div>
    </div>

    {{-- Keywords Content --}}
    <div class="p-3 sm:p-5 flex-1 relative overflow-y-auto custom-scrollbar" style="scrollbar-width: thin; scrollbar-color: {{ $colorVar }} transparent;">
        
        <div id="sync-notification-{{ $lang }}" style="display: none;" class="mb-4"></div>

        @if(!empty($targetKeywords))
            <div class="space-y-2.5 keyword-container-{{ $lang }}">
                @foreach($targetKeywords as $kw)
                    @php
                        $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
                        $source = is_array($kw) ? ($kw['source'] ?? 'AI') : 'AI';
                        $createdAt = is_array($kw) && isset($kw['created_at']) ? \Carbon\Carbon::parse($kw['created_at']) : null;
                    @endphp
                    @if(!empty($text))
                    <div data-pulldate="{{ isset($kw['synced_at']) ? \Carbon\Carbon::parse($kw['synced_at'])->timestamp : ($createdAt ? $createdAt->timestamp : 0) }}" data-pubdate="{{ isset($kw['published_at']) ? \Carbon\Carbon::parse($kw['published_at'])->timestamp : 0 }}" class="keyword-row group" style="display:flex;align-items:flex-start;justify-content:space-between;padding:12px 16px;border-radius:16px;border:1px solid var(--glass-border);background:var(--glass-bg);gap:10px;transition:all 0.25s ease;">
                        <div class="flex items-start gap-2.5 flex-1 min-w-0">
                            {{-- Selection & Index --}}
                            <div class="flex flex-col items-center gap-2 mt-1">
                                <input type="checkbox" 
                                       x-model="{{ $selectedModel }}" 
                                       value="{{ $text }}"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-white/10 bg-white/5 flex-shrink-0 cursor-pointer" style="color: {{ $colorVar }};">
                                <span class="keyword-num" style="flex-shrink:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-weight:900;font-size:10px;background:var(--card-bg);border:1px solid var(--glass-border);color:{{ $colorVar }};box-shadow:0 2px 6px rgba(0,0,0,0.15);">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            {{-- Content Area --}}
                            <div class="flex flex-col min-w-0 flex-1 pt-0.5">
                                <span class="font-bold text-sm sm:text-base break-words leading-tight mb-1.5" style="color: var(--text-main);">{{ $text }}</span>
                                
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span style="font-size:8px;color:#f87171;background:rgba(248,113,113,0.1);padding:2px 8px;border-radius:8px;font-weight:700;text-transform:uppercase;border:1px solid rgba(248,113,113,0.2);">{{ $source }}</span>
                                </div>

                                {{-- Metadata / Timestamps — Admin Only --}}
                                @if(auth()->user()->isAdmin())
                                <div style="display:grid;grid-template-columns:1fr;gap:6px;border-{{ $isAr ? 'right' : 'left' }}:2px solid {{ $colorVar }}20;padding-{{ $isAr ? 'right' : 'left' }}:0.75rem;">
                                    @if(isset($kw['published_at']))
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-history text-[9px] opacity-50" style="color: var(--primary-cyan);"></i>
                                            <span class="text-[9px] font-bold" style="color: var(--text-muted);">
                                                <span class="opacity-50 font-normal">{{ $isAr ? 'نُشر:' : 'Published:' }}</span>
                                                <span style="color: var(--primary-cyan);" x-text="getRelativeTime('{{ $kw['published_at'] }}', '{{ $lang }}')"></span>
                                            </span>
                                        </div>
                                    @endif
                                    @php
                                        $syncTime = $kw['synced_at'] ?? $kw['created_at'] ?? null;
                                    @endphp
                                    @if($syncTime)
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-satellite-dish text-[9px] opacity-50" style="color: #f59e0b;"></i>
                                            <span class="text-[9px] font-bold" style="color: var(--text-muted);">
                                                <span class="opacity-50 font-normal">{{ $isAr ? 'سُحب:' : 'Synced:' }}</span>
                                                <span style="color: #f59e0b;" x-text="getRelativeTime('{{ $syncTime }}', '{{ $lang }}')"></span>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Actions Sidebar --}}
                        <div class="flex flex-col sm:flex-row lg:flex-row items-center gap-1.5 flex-shrink-0 pt-1">
                            <div class="flex flex-col gap-1.5">
                                <button onclick="copyToClipboard('{{ addslashes($text) }}')" class="keyword-action-btn" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--card-bg);border:1px solid var(--glass-border);color:var(--text-muted);cursor:pointer;transition:all 0.2s;" title="Copy"
                                    onmouseover="this.style.color='var(--primary-cyan)';this.style.borderColor='var(--primary-cyan)'" onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--glass-border)'">
                                    <i class="far fa-copy text-[10px]"></i>
                                </button>
                                <a href="https://www.google.com/search?q={{ urlencode($text) }}&gl={{ $lang === 'en' ? 'US' : 'EG' }}" target="_blank" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--card-bg);border:1px solid var(--glass-border);color:var(--text-muted);transition:all 0.2s;text-decoration:none;" title="Google Search"
                                    onmouseover="this.style.color='var(--primary-cyan)';this.style.borderColor='var(--primary-cyan)'" onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--glass-border)'">
                                    <i class="fab fa-google text-[10px]"></i>
                                </a>
                            </div>
                            <a href="{{ route('headlines.index', ['keyword' => $text]) }}" target="_blank" 
                                style="display:inline-flex;align-items:center;justify-content:center;gap:4px;border-radius:10px;font-size:0.65rem;font-weight:900;padding:0.4rem 0.6rem;min-width:32px;height:32px;text-decoration:none;color:#fff;background:linear-gradient(135deg, var(--primary-cyan, #0ea5e9), #6366f1);border:1px solid rgba(255,255,255,0.15);transition:all 0.2s;cursor:pointer;" 
                                title="Discover"
                                onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 4px 15px rgba(14,165,233,0.3)'" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='none'">
                                <i class="fas fa-magic text-[10px]"></i>
                                <span class="hidden sm:inline">Magic</span>
                            </a>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full text-center py-12 px-4">
                @if($hasCompetitors)
                    <div style="width:80px;height:80px;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;border:1px solid {{ $colorVar }}30;background:var(--card-bg);color:{{ $colorVar }};box-shadow:0 0 30px {{ $colorVar }}15;">
                        <i class="fas fa-robot text-3xl"></i>
                    </div>
                    <h4 class="font-black text-xl mb-3" style="color: var(--text-main);">Ready to extract smart keywords</h4>
                    <p class="text-sm mb-8 max-w-sm mx-auto leading-relaxed" style="color: var(--text-muted);">Competitors added successfully. Click Sync to extract hidden trends.</p>
                    <div class="flex items-center gap-2">
                        <button @click="syncCompetitors('{{ $lang }}', timeValue, '{{ $customBoxId ?? '' }}')" :disabled="{{ $loadingModel }}" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-primary-cyan/30 text-primary-cyan hover:bg-primary-cyan/10 transition-all text-xs disabled:opacity-50">
                            <i class="fas fa-sync-alt" :class="{{ $loadingModel }} ? 'fa-spin' : ''"></i>
                            <span>Sync {{ strtoupper($lang) }}</span>
                        </button>
                        <div x-show="syncStatus['{{ $lang === 'ar' ? 'syncAr' : ($lang === 'en' ? 'syncEn' : 'sync_' . ($customBoxId ?? '')) }}'] === 'syncing'" class="flex items-center gap-3" x-cloak>
                            <span class="text-[10px] text-primary-cyan animate-pulse">
                                 <i class="fas fa-circle text-[6px] mr-1"></i> Syncing...
                            </span>
                            <button @click="refreshBoxData('{{ $lang }}', '{{ $customBoxId ?? '' }}')" 
                                class="text-[10px] bg-primary-cyan/20 px-2 py-0.5 rounded border border-primary-cyan/50 text-primary-cyan hover:bg-primary-cyan/30 transition-all flex items-center gap-1">
                                <i class="fas fa-redo-alt text-[8px]" :class="{{ $loadingModel }} ? 'fa-spin' : ''"></i>
                                <span>Check for Updates</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border" style="background: var(--card-bg); border-color: var(--glass-border); color: var(--text-muted);">
                        <i class="fas fa-satellite-dish text-2xl"></i>
                    </div>
                    <h4 class="font-bold mb-2 text-sm" style="color: var(--text-main);">No Competitor Keywords</h4>
                    <p class="text-sm mb-6 max-w-xs" style="color: var(--text-muted);">Add competitor links and sync to extract keywords.</p>
                    <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="inline-block px-5 py-2.5 rounded-xl text-sm font-bold transition border" style="background: {{ $colorVar }}20; color: {{ $colorVar }}; border-color: {{ $colorVar }}30;">
                        <i class="fas fa-cog me-2"></i> Setup Competitors
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
