@php
    $customBoxId = $boxId ?? null; // Custom box ID (null for standard AR/EN)
    $customBoxColor = $boxColor ?? null;
    
    if ($customBoxId) {
        // Custom Box
        $colorVar = $customBoxColor;
        $colorClass = 'purple-500';
        $icon = 'fas fa-layer-group';
        $loadingModel = "loading['sync_{$customBoxId}']";
        $syncProp = "sync_{$customBoxId}";
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
        $syncProp = $lang === 'ar' ? 'syncAr' : 'syncEn';
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
                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:var(--card-bg);border:1px solid var(--glass-border);color:var(--text-main);box-shadow:0 2px 10px rgba(0,0,0,0.05);cursor:pointer;transition:all 0.2s;"
                onmouseover="this.style.background='var(--glass-bg)'" onmouseout="this.style.background='var(--card-bg)'">
                <i class="fas fa-sort-amount-down text-[10px]" style="color: {{ $colorVar }};"></i> 
                <span x-text="currentSort">Latest Published</span>
                <i class="fas fa-chevron-down text-[8px] opacity-60 transition-transform duration-200" :class="{'rotate-180': sortOpen}"></i>
            </button>
            <div x-show="sortOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-[-4px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:220px;background:var(--card-bg);border:1px solid var(--glass-border);border-radius:14px;overflow:hidden;z-index:9999;box-shadow:0 15px 40px rgba(0,0,0,0.15);">
                <div style="padding:8px 14px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);border-bottom:1px solid var(--glass-border);">
                    <i class="fas fa-satellite-dish text-[8px] mr-1 opacity-40"></i> Sort Radar Findings
                </div>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Sync'; window.executeKeywordSort('{{ $lang }}', 'pulldate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:var(--text-main);border:none;cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='var(--glass-bg)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(245,158,11,0.15);color:#f59e0b;flex-shrink:0;font-size:10px;"><i class="fas fa-satellite-dish"></i></span>
                    <span>Radar Detection</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Publish'; window.executeKeywordSort('{{ $lang }}', 'pubdate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:var(--text-main);border:none;border-top:1px solid var(--glass-border);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='var(--glass-bg)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(14,165,233,0.15);color:#0ea5e9;flex-shrink:0;font-size:10px;"><i class="fas fa-clock"></i></span>
                    <span>Market Timestamp</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'A → Z'; window.executeKeywordSort('{{ $lang }}', 'alphabetical');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:10px 14px;background:transparent;color:var(--text-main);border:none;border-top:1px solid var(--glass-border);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='var(--glass-bg)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:7px;background:rgba(168,85,247,0.15);color:#a855f7;flex-shrink:0;font-size:10px;"><i class="fas fa-sort-alpha-down"></i></span>
                    <span>Alphabetical (A-Z)</span>
                </button>
            </div>
        </div>

        {{-- Time Filter + Sync --}}
        <div class="relative" x-data="{
                timeOpen: false,
                timeLabel: 'Last 60m',
                timeValue: '60m',
                hoverValue: null,
                timeOptions: [
                    { value: '60m',  label: 'Last 60m',  title: 'Last 60 Minutes',  hint: 'Most recent content only',         icon: 'fas fa-history',       color: '#10b981' },
                    { value: '24h',  label: 'Last 24h',  title: 'Last 24 Hours',    hint: 'Today\u2019s trending content',    icon: 'fas fa-calendar-day',  color: '#f59e0b' },
                    { value: 'all',  label: 'All Time',  title: 'All Time Content', hint: 'No time restriction',              icon: 'fas fa-infinity',      color: '#3b82f6' },
                ],
                selectTime(opt) {
                    this.timeValue = opt.value;
                    this.timeLabel = opt.label;
                    this.timeOpen = false;
                    if (typeof window.applyKeywordTimeFilter === 'function') {
                        window.applyKeywordTimeFilter('{{ $lang }}', opt.value);
                    }
                },
                optionBg(opt) {
                    if (this.hoverValue === opt.value) return 'var(--glass-bg)';
                    if (this.timeValue === opt.value) return opt.color + '22';
                    return 'transparent';
                },
                init() {
                    this.$nextTick(() => {
                        if (typeof window.applyKeywordTimeFilter === 'function') {
                            window.applyKeywordTimeFilter('{{ $lang }}', this.timeValue);
                        }
                    });
                }
             }" @click.away="timeOpen = false">
            <div class="flex items-center gap-2">
                {{-- Time Filter Button --}}
                <button type="button" @click="timeOpen = !timeOpen" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:var(--card-bg);border:1px solid var(--glass-border);color:var(--text-main);box-shadow:0 2px 10px rgba(0,0,0,0.05);cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='var(--glass-bg)'" onmouseout="this.style.background='var(--card-bg)'">
                    <i class="fas fa-clock text-[10px]" style="color: #10b981;"></i> 
                    <span x-text="timeLabel">Last 60m</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-60 transition-transform duration-200" :class="{'rotate-180': timeOpen}"></i>
                </button>

                {{-- Sync Button --}}
                <button @click="syncCompetitors('{{ $lang }}', timeValue, '{{ $customBoxId ?? '' }}')" :disabled="{{ $loadingModel }}" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:12px;font-size:11px;font-weight:900;color:#fff;white-space:nowrap;border:1px solid rgba(255,255,255,0.2);background:{{ $colorVar }};box-shadow:0 4px 20px {{ $colorVar }}35;cursor:pointer;transition:transform 0.15s;"
                    onmouseover="if(!this.disabled)this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-sync-alt text-[10px]" :class="{ 'fa-spin': {{ $loadingModel }} }"></i> 
                    <span x-text="{{ $loadingModel }} ? 'Initial Scanning...' : 'Refresh Radar'"></span>
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
                 style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:260px;max-height:360px;overflow-y:auto;background:var(--card-bg);border:1px solid var(--glass-border);border-radius:14px;z-index:9999;box-shadow:0 15px 40px rgba(0,0,0,0.15);">
                <div style="padding:8px 14px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);border-bottom:1px solid var(--glass-border);position:sticky;top:0;background:var(--card-bg);z-index:1;">
                    <i class="fas fa-filter text-[8px] mr-1 opacity-40"></i> Select Time Range
                </div>

                <template x-for="(opt, idx) in timeOptions" :key="opt.value">
                    <button type="button"
                        @click="selectTime(opt)"
                        @mouseenter="hoverValue = opt.value"
                        @mouseleave="hoverValue = null"
                        style="display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:56px;padding:12px 14px;text-align:{{ $isAr ? 'right' : 'left' }};color:var(--text-main);border:none;cursor:pointer;transition:background 0.15s;background:transparent;"
                        :style="{
                            background: optionBg(opt),
                            borderTop: idx > 0 ? '1px solid var(--glass-border)' : 'none'
                        }">
                        <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1;">
                            <span
                                style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;flex-shrink:0;font-size:12px;"
                                :style="{
                                    background: opt.color + '26',
                                    color: opt.color
                                }">
                                <i :class="opt.icon"></i>
                            </span>
                            <div style="display:flex;flex-direction:column;min-width:0;">
                                <span style="font-size:12px;font-weight:700;line-height:1.3;color:var(--text-main);" x-text="opt.title"></span>
                                <span style="font-size:10px;line-height:1.4;color:var(--text-muted);font-weight:500;margin-top:2px;" x-text="opt.hint"></span>
                            </div>
                        </div>
                        <i class="fas fa-check"
                           style="font-size:11px;flex-shrink:0;"
                           x-show="timeValue === opt.value"
                           :style="{ color: opt.color }"></i>
                    </button>
                </template>
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
<div class="glass-card flex flex-col h-[75vh] lg:h-[calc(100vh-160px)] relative border border-white/5 glass-border-light" style="border-top-color: {{ $colorVar }}40; overflow: hidden;" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    {{-- Full-Box Loading Overlay --}}
    <div id="sync-loading-{{ $lang }}" x-show="{{ $loadingModel }}" class="absolute inset-0 z-50 flex items-center justify-center backdrop-blur-md" style="display: none; background: var(--glass-bg);">
        <div class="flex flex-col items-center px-6 text-center max-w-xs">
            <div class="radar-spinner mb-5">
                <div class="radar-circle"></div>
                <div class="radar-circle radar-circle-2"></div>
                <div class="radar-dot"></div>
            </div>
            <span class="text-sm font-bold" style="color: var(--text-main);"
                  x-text="syncCountdown['{{ $syncProp }}']?.waiting
                    ? '{{ $isAr ? 'مسح قيد التشغيل — يرجى الانتظار' : 'Scan in progress — please wait' }}'
                    : '{{ $isAr ? 'جاري مسح عناوين المنافسين…' : 'Scanning competitor headlines…' }}'">
                {{ $isAr ? 'جاري مسح عناوين المنافسين…' : 'Scanning competitor headlines…' }}
            </span>

            <template x-if="syncCountdown['{{ $syncProp }}']">
                <div class="mt-4 w-full flex flex-col items-center gap-2">
                    <span class="text-3xl font-black tabular-nums tracking-wider"
                          :style="{ color: (syncCountdown['{{ $syncProp }}']?.remaining > 0) ? '{{ $colorVar }}' : '#f59e0b' }"
                          x-text="syncCountdownClock('{{ $syncProp }}')">--:--</span>
                    <span class="text-[11px] font-semibold" style="color: var(--text-muted);"
                          x-text="syncCountdownLabel('{{ $syncProp }}', '{{ $lang }}')"></span>
                    <div class="w-full h-1.5 rounded-full overflow-hidden mt-1" style="background: var(--glass-border);">
                        <div class="h-full rounded-full transition-all duration-1000 ease-linear"
                             style="background: {{ $colorVar }};"
                             :style="{ width: syncCountdownPercent('{{ $syncProp }}') + '%' }"></div>
                    </div>
                </div>
            </template>

            <span class="text-[10px] mt-3 leading-relaxed" style="color: var(--text-muted);">
                {{ $isAr ? 'معالجة كل عنوان بالذكاء الاصطناعي — ابقَ على هذه الصفحة' : 'Processing each headline with AI — keep this page open' }}
            </span>
        </div>
    </div>

    {{-- Decorative Gradient Orbs --}}
    <div class="absolute top-0 {{ $isAr ? 'right-0' : 'left-0' }} w-40 h-40 blur-3xl rounded-full opacity-[0.07] pointer-events-none" style="background: {{ $colorVar }}; margin-{{ $isAr ? 'right' : 'left' }}: -5rem; margin-top: -5rem;"></div>

    {{-- Box Header: Title + Actions --}}
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.02);flex-shrink:0;">
        <h2 class="text-lg sm:text-xl font-black flex items-center gap-2.5 whitespace-nowrap text-white">
            <div style="width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid {{ $colorVar }}35;background:{{ $colorVar }}18;color:{{ $colorVar }};" class="hidden sm:flex shadow-sm">
                <i class="{{ $icon }} text-sm"></i>
            </div>
            <span>{{ $title }}</span>
        </h2>
        
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
            @if(!empty($targetKeywords))
                @php
                    $allKeywordTexts = array_values(array_filter(array_map(
                        fn ($kw) => is_array($kw) ? trim($kw['text'] ?? $kw['keyword'] ?? '') : trim((string) $kw),
                        $targetKeywords
                    )));
                @endphp
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Select All Button --}}
                    <label class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold cursor-pointer transition-all border border-white/10 bg-white/[0.04] hover:bg-white/[0.08] hover:border-white/20 text-slate-200 select-none shadow-sm">
                        <input type="checkbox"
                               class="kw-select-all-{{ $boxKey }}"
                               style="accent-color:{{ $colorVar }};width:15px;height:15px;cursor:pointer;border-radius:4px;"
                               @change="toggleSelectAll('{{ $boxKey }}', @js($allKeywordTexts))">
                        <span>{{ $isAr ? 'تحديد الكل' : 'Select All' }}</span>
                    </label>

                    {{-- Copy Selected Button --}}
                    <button type="button"
                            @click="copySelectedKeywords('{{ $boxKey }}')"
                            :disabled="selectedCount('{{ $boxKey }}') === 0"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap transition-all shadow-md select-none border"
                            :class="selectedCount('{{ $boxKey }}') > 0 ? 'bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white border-white/20 hover:scale-105 cursor-pointer shadow-cyan-500/20' : 'bg-white/5 text-slate-500 border-white/5 cursor-not-allowed opacity-50'">
                        <i class="far fa-copy text-xs"></i>
                        <span>{{ $isAr ? 'نسخ المحدد' : 'Copy Selected' }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black bg-black/30 text-cyan-200 border border-white/10" x-text="selectedCount('{{ $boxKey }}')">0</span>
                    </button>

                    {{-- Clear Selection Button --}}
                    <button type="button"
                            @click="clearSelection('{{ $boxKey }}')"
                            x-show="selectedCount('{{ $boxKey }}') > 0"
                            x-cloak
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all border border-white/10 bg-white/[0.04] hover:bg-white/[0.08] text-slate-400 hover:text-white cursor-pointer shadow-sm">
                        <i class="fas fa-times text-[10px]"></i>
                        <span>{{ $isAr ? 'إلغاء التحديد' : 'Clear' }}</span>
                    </button>

                    {{-- Delete All Button --}}
                    <form id="delete-all-form-{{ $boxKey }}" action="{{ route('dashboard.ai-keyword-radar.delete-all', ['lang' => $lang]) }}" method="POST" class="inline-block">
                        @csrf
                        @if($customBoxId)
                            <input type="hidden" name="box_id" value="{{ $customBoxId }}">
                        @endif
                        <button type="button" 
                                @click="confirmDeleteAll('{{ $boxKey }}', '{{ $lang }}', '{{ $customBoxId ?? '' }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all border border-red-500/30 bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 cursor-pointer shadow-sm">
                            <i class="fas fa-trash-alt text-[11px]"></i> 
                            <span>{{ $isAr ? 'حذف الكل' : 'Delete All' }}</span>
                        </button>
                    </form>
                </div>
            @endif

            {{-- Total Count Badge --}}
            <span data-keyword-count class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white/[0.04] border border-white/10 text-slate-300 shadow-sm whitespace-nowrap">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>{{ count($targetKeywords ?? []) }} {{ $isAr ? 'كلمة' : 'Keywords' }}</span>
            </span>
        </div>
    </div>

    {{-- Keywords Content --}}
    <div class="p-3 sm:p-5 flex-1 relative overflow-y-auto custom-scrollbar" style="scrollbar-width: thin; scrollbar-color: {{ $colorVar }} transparent;">
        
        <div id="sync-notification-{{ $lang }}" style="display: none;" class="mb-4"></div>

        @if(!empty($targetKeywords))
            @php
                $headlineGroups = [];
                $ungroupedKeywords = [];
                foreach ($targetKeywords as $kw) {
                    $headlineTitle = is_array($kw) ? trim($kw['headline_title'] ?? '') : '';
                    if ($headlineTitle !== '' && \Modules\AIKeywordRadar\Services\KeywordService::headlineMatchesLanguage($headlineTitle, $lang)) {
                        $headlineGroups[$headlineTitle][] = $kw;
                    } else {
                        $ungroupedKeywords[] = $kw;
                    }
                }
            @endphp
            <div class="space-y-4 keyword-container-{{ $lang }}">
                @foreach($headlineGroups as $headlineTitle => $groupKeywords)
                    @php
                        $firstKw = $groupKeywords[0];
                        $source = is_array($firstKw) ? ($firstKw['source'] ?? 'AI') : 'AI';
                        $syncTime = null;
                        $pubTime = null;
                        $pullTs = 0;
                        $pubTs = 0;
                        $groupKeywordTexts = [];
                        $primaryKeyword = '';
                        foreach ($groupKeywords as $gkw) {
                            $kwText = is_array($gkw) ? trim($gkw['text'] ?? $gkw['keyword'] ?? '') : trim((string) $gkw);
                            if ($kwText !== '') {
                                $groupKeywordTexts[] = $kwText;
                                if ($primaryKeyword === '') {
                                    $primaryKeyword = $kwText;
                                }
                            }
                            $st = $gkw['synced_at'] ?? $gkw['created_at'] ?? null;
                            $pt = $gkw['published_at'] ?? null;
                            if ($st) {
                                $ts = \Carbon\Carbon::parse($st)->timestamp;
                                if ($ts > $pullTs) { $pullTs = $ts; $syncTime = $st; }
                            }
                            if ($pt) {
                                $ts = \Carbon\Carbon::parse($pt)->timestamp;
                                if ($ts > $pubTs) { $pubTs = $ts; $pubTime = $pt; }
                            }
                        }
                        $actionQuery = $primaryKeyword !== '' ? $primaryKeyword : $headlineTitle;
                    @endphp
                    <div data-pulldate="{{ $pullTs }}" data-pubdate="{{ $pubTs }}" class="headline-card keyword-row group" style="border-radius:18px;border:1px solid var(--glass-border);background:var(--card-bg);overflow:hidden;transition:all 0.25s ease;">
                        {{-- Top Header: Target Keyword(s) + Action Buttons --}}
                        <div style="padding:14px 16px 12px;border-bottom:1px solid var(--glass-border);background:var(--glass-bg);">
                            <div class="flex items-center justify-between gap-3 {{ $isAr ? '' : 'flex-row-reverse' }}">
                                {{-- Keyword chips with checkboxes --}}
                                <div class="flex-1 min-w-0 flex flex-wrap items-center gap-2">
                                @foreach($groupKeywords as $kw)
                                    @php
                                        $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
                                    @endphp
                                    @if(!empty($text))
                                    <div class="keyword-tag keyword-chip-row" style="display:inline-flex;align-items:center;gap:8px;max-width:100%;">
                                        <input type="checkbox"
                                               class="kw-select-{{ $boxKey }}"
                                               value="{{ $text }}"
                                               style="accent-color:{{ $colorVar }};width:16px;height:16px;cursor:pointer;flex-shrink:0;"
                                               @change="toggleKeyword('{{ $boxKey }}', @js($text), $event.target.checked)">
                                        <span class="keyword-text font-black text-sm sm:text-base" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;line-height:1.3;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:#ffffff;max-width:100%;text-align:{{ $isAr ? 'right' : 'left' }};">
                                            <i class="fas fa-hashtag text-[10px] flex-shrink-0" style="color:{{ $colorVar }};"></i>
                                            <span class="break-words font-black" style="color:#ffffff;">{{ $text }}</span>
                                        </span>
                                    </div>
                                    @endif
                                @endforeach
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    @if(!empty($groupKeywordTexts))
                                    <a href="https://www.google.com/search?q={{ urlencode($actionQuery) }}&gl={{ \App\Support\CountryRegistry::defaultRegion($lang) }}" target="_blank"
                                        style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:var(--card-bg);border:1px solid var(--glass-border);color:var(--text-muted);text-decoration:none;transition:all 0.2s;"
                                        title="Google"
                                        onmouseover="this.style.color='{{ $colorVar }}';this.style.borderColor='{{ $colorVar }}'"
                                        onmouseout="this.style.color='var(--text-muted)';this.style.borderColor='var(--glass-border)'">
                                        <i class="fab fa-google text-xs"></i>
                                    </a>
                                    <a href="{{ route('headlines.index', ['keyword' => $actionQuery]) }}" target="_blank"
                                        style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:#fff;background:linear-gradient(135deg,var(--primary-cyan,#0ea5e9),#6366f1);border:1px solid rgba(255,255,255,0.15);text-decoration:none;transition:all 0.2s;"
                                        title="{{ $isAr ? 'اكتشاف' : 'Discover' }}"
                                        onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fas fa-magic text-xs"></i>
                                    </a>
                                    <a href="{{ route('dashboard.article-writer.index', ['keyword' => $actionQuery]) }}" target="_blank"
                                        style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:10px;color:#fff;background:linear-gradient(135deg,#a855f7,#6366f1);border:1px solid rgba(255,255,255,0.15);text-decoration:none;transition:all 0.2s;"
                                        title="{{ $isAr ? 'كتابة' : 'Write' }}"
                                        onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fas fa-pen-fancy text-xs"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Body: Scraped Headline & Metadata --}}
                        <div class="p-3 sm:p-4" style="background:var(--card-bg);">
                            <div class="flex items-start gap-2.5 {{ $isAr ? '' : 'flex-row-reverse' }}">
                                <span style="flex-shrink:0;width:26px;height:26px;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:10px;background:{{ $colorVar }}15;border:1px solid {{ $colorVar }}30;color:{{ $colorVar }};">
                                    <i class="fas fa-newspaper text-[10px]"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-xs sm:text-sm break-words leading-relaxed mb-2" style="color:{{ $colorVar }};opacity:0.95;">{{ $headlineTitle }}</h4>
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <span style="font-size:9.5px;color:#38bdf8;background:rgba(14,165,233,0.12);padding:2.5px 9px;border-radius:8px;font-weight:800;text-transform:uppercase;border:1px solid rgba(56,189,248,0.35);display:inline-flex;align-items:center;gap:4px;box-shadow:0 0 10px rgba(14,165,233,0.1);"><i class="fas fa-globe text-[8px] opacity-75"></i> {{ $source }}</span>
                                        @if($pubTime)
                                            @php 
                                                $pubCarbon = \Carbon\Carbon::parse($pubTime); 
                                                if ($isAr) $pubCarbon->locale('ar');
                                            @endphp
                                            <span class="text-[9px] font-bold" style="color:var(--text-muted);"
                                                  title="{{ $pubCarbon->timezone(config('app.timezone'))->format('Y-m-d H:i:s T') }}">
                                                <i class="fas fa-clock text-[8px] opacity-50" style="color:var(--primary-cyan);"></i>
                                                {{ $isAr ? 'نُشر:' : 'Published:' }}
                                                <span style="color:var(--primary-cyan);">{{ $pubCarbon->diffForHumans() }}</span>
                                            </span>
                                        @endif
                                        @if($syncTime)
                                            @php 
                                                $syncCarbon = \Carbon\Carbon::parse($syncTime); 
                                                if ($isAr) $syncCarbon->locale('ar');
                                            @endphp
                                            <span class="text-[9px] font-bold" style="color:var(--text-muted);"
                                                  title="{{ $syncCarbon->timezone(config('app.timezone'))->format('Y-m-d H:i:s T') }}">
                                                <i class="fas fa-sync text-[8px] opacity-50" style="color:#f59e0b;"></i>
                                                {{ $isAr ? 'تم السحب:' : 'Fetched:' }}
                                                <span style="color:#f59e0b;">{{ $syncCarbon->diffForHumans() }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Legacy keywords without headline grouping — tags only --}}
                @if(!empty($ungroupedKeywords))
                <div data-pulldate="0" data-pubdate="0" class="keyword-row" style="padding:12px 16px;border-radius:16px;border:1px solid var(--glass-border);background:var(--glass-bg);">
                    <div class="flex flex-wrap gap-2">
                    @foreach($ungroupedKeywords as $kw)
                        @php
                            $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
                        @endphp
                        @if(!empty($text))
                        <label class="keyword-tag keyword-chip-row" style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox"
                                   class="kw-select-{{ $boxKey }}"
                                   value="{{ $text }}"
                                   style="accent-color:{{ $colorVar }};width:15px;height:15px;cursor:pointer;"
                                   @change="toggleKeyword('{{ $boxKey }}', @js($text), $event.target.checked)">
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid {{ $colorVar }}35;background:{{ $colorVar }}12;color:{{ $colorVar }};">
                                <i class="fas fa-hashtag text-[9px] opacity-60"></i>
                                <span>{{ $text }}</span>
                            </span>
                        </label>
                        @endif
                    @endforeach
                    </div>
                </div>
                @endif
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full text-center py-12 px-4">
                @if($hasCompetitors)
                    <div style="width:80px;height:80px;border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;border:1px solid {{ $colorVar }}30;background:var(--card-bg);color:{{ $colorVar }};box-shadow:0 0 30px {{ $colorVar }}15;">
                        <i class="fas fa-robot text-3xl"></i>
                    </div>
                    <h4 class="font-black text-xl mb-3" style="color: var(--text-main);">Radar Intelligence Active</h4>
                    @php
                        $storedCategory = $customBoxId ? "Target:{$customBoxId}" : 'Target';
                        $storedCount = \Modules\AIKeywordRadar\Models\Keyword::where('user_id', auth()->id())
                            ->where('category', $storedCategory)->where('lang', $lang)->count();
                    @endphp
                    @if($storedCount > 0)
                        <p class="text-sm mb-4 max-w-sm mx-auto leading-relaxed" style="color: #f59e0b;">
                            You have {{ $storedCount }} saved keyword(s) outside the current {{ $stats['retention_hours'] ?? 24 }}h window.
                            Click <strong>Refresh Radar</strong> (use <strong>Last 24h</strong>) to pull fresh results.
                        </p>
                    @else
                        <p class="text-sm mb-4 max-w-sm mx-auto leading-relaxed" style="color: var(--text-muted);">
                            {{ $isAr ? 'لا توجد نتائج في نافذة الوقت الحالية. جرّب' : 'No results in the current time window. Try' }}
                            <strong>{{ $isAr ? 'آخر 24 ساعة' : 'Last 24h' }}</strong>
                            {{ $isAr ? 'أو' : 'or' }}
                            <strong>{{ $isAr ? 'كل الوقت' : 'All Time' }}</strong>
                            {{ $isAr ? 'ثم اضغط تحديث الرادار.' : 'then click Refresh Radar.' }}
                        </p>
                    @endif
                    <div class="flex items-center gap-2">
                        <button @click="syncCompetitors('{{ $lang }}', timeValue, '{{ $customBoxId ?? '' }}')" :disabled="{{ $loadingModel }}" 
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-primary-cyan/30 text-primary-cyan hover:bg-primary-cyan/10 transition-all text-xs disabled:opacity-50">
                            <i class="fas fa-sync-alt" :class="{{ $loadingModel }} ? 'fa-spin' : ''"></i>
                            <span>Sync {{ strtoupper($lang) }}</span>
                        </button>
                        <div x-show="syncStatus['{{ $lang === 'ar' ? 'syncAr' : ($lang === 'en' ? 'syncEn' : 'sync_' . ($customBoxId ?? '')) }}'] === 'syncing'" class="flex items-center gap-3" x-cloak>
                            <span class="text-[10px] text-primary-cyan animate-pulse">
                                 <i class="fas fa-circle text-[6px] mr-1"></i> Running Intel Scanning...
                            </span>
                            <button @click="refreshBoxData('{{ $lang }}', '{{ $customBoxId ?? '' }}')" 
                                class="text-[10px] bg-primary-cyan/20 px-2 py-0.5 rounded border border-primary-cyan/50 text-primary-cyan hover:bg-primary-cyan/30 transition-all flex items-center gap-1">
                                <i class="fas fa-redo-alt text-[8px]" :class="{{ $loadingModel }} ? 'fa-spin' : ''"></i>
                                <span>Fetch Updates</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 border" style="background: var(--card-bg); border-color: var(--glass-border); color: var(--text-muted);">
                        <i class="fas fa-satellite-dish text-2xl"></i>
                    </div>
                    <h4 class="font-bold mb-2 text-sm" style="color: var(--text-main);">Intelligence Standby</h4>
                    <p class="text-sm mb-6 max-w-xs" style="color: var(--text-muted);">No competitors tracked yet. Add domains to start receiving market intelligence.</p>
                    <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="inline-block px-5 py-2.5 rounded-xl text-sm font-bold transition border" style="background: {{ $colorVar }}20; color: {{ $colorVar }}; border-color: {{ $colorVar }}30;">
                        <i class="fas fa-cog me-2"></i> Configure Intel Sources
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
