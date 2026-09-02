@php
    $customBoxId = $boxId ?? null; // Custom box ID (null for standard AR/EN)
    $customBoxColor = $boxColor ?? null;
    
    if ($customBoxId === 'direct_seed') {
        $colorVar = '#10b981';
        $colorClass = 'emerald-500';
        $icon = 'fas fa-crosshairs';
        $loadingModel = "loading['sync_direct_seed_{$lang}']";
        $syncProp = "sync_direct_seed_{$lang}";
        $boxKey = "direct_seed_{$lang}";
        $userSeeds = ($lang === 'en')
            ? (auth()->user()->settings['keywords_seed_topics_en'] ?? auth()->user()->settings['keywords_seed_topics'] ?? '')
            : (auth()->user()->settings['keywords_seed_topics'] ?? '');
        $hasCompetitors = !empty(trim((string)$userSeeds));
        $isAr = ($lang ?? 'ar') === 'ar';
        $htmlBoxId = 'kr-box-direct_seed_' . $lang;
    } elseif ($customBoxId) {
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
                style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:#0f172a;border:1px solid rgba(255,255,255,0.15);color:#ffffff;box-shadow:0 2px 10px rgba(0,0,0,0.2);cursor:pointer;transition:all 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='#0f172a'">
                <i class="fas fa-sort-amount-down text-[10px]" style="color: {{ $colorVar }};"></i> 
                <span x-text="currentSort" style="color: #ffffff;">Latest Published</span>
                <i class="fas fa-chevron-down text-[8px] opacity-70 transition-transform duration-200" :class="{'rotate-180': sortOpen}"></i>
            </button>
            <div x-show="sortOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-[-4px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:230px;max-width:calc(100vw - 24px);background:#0f172a !important;border:1px solid rgba(255,255,255,0.18) !important;border-radius:14px;overflow:hidden;z-index:99999;box-shadow:0 25px 60px rgba(0,0,0,0.85);backdrop-filter:blur(20px);">
                <div style="padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;border-bottom:1px solid rgba(255,255,255,0.1);background:#0b1120;">
                    <i class="fas fa-satellite-dish text-[9px] mr-1 text-amber-400"></i> Sort Radar Findings
                </div>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Sync'; window.executeKeywordSort('{{ $lang }}', 'pulldate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:11px 14px;background:transparent;color:#ffffff;border:none;cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(245,158,11,0.2);color:#f59e0b;flex-shrink:0;font-size:11px;"><i class="fas fa-satellite-dish"></i></span>
                    <span style="color:#ffffff;">Radar Detection</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'Newest Publish'; window.executeKeywordSort('{{ $lang }}', 'pubdate');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:11px 14px;background:transparent;color:#ffffff;border:none;border-top:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(14,165,233,0.2);color:#38bdf8;flex-shrink:0;font-size:11px;"><i class="fas fa-clock"></i></span>
                    <span style="color:#ffffff;">Market Timestamp</span>
                </button>
                <button type="button" @click="sortOpen = false; currentSort = 'A → Z'; window.executeKeywordSort('{{ $lang }}', 'alphabetical');"
                    style="display:flex;align-items:center;gap:10px;width:100%;text-align:{{ $isAr ? 'right' : 'left' }};padding:11px 14px;background:transparent;color:#ffffff;border:none;border-top:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:12px;font-weight:600;transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">
                    <span style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:rgba(168,85,247,0.2);color:#c084fc;flex-shrink:0;font-size:11px;"><i class="fas fa-sort-alpha-down"></i></span>
                    <span style="color:#ffffff;">Alphabetical (A-Z)</span>
                </button>
            </div>
        </div>

        {{-- Mode Selector + Time Filter + High Traffic + Sync --}}
        <div class="flex items-center gap-2 flex-wrap" x-data="{
                timeOpen: false,
                timeLabel: 'Last 60m',
                timeValue: '60m',
                hoverValue: null,
                highTrafficActive: false,
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
                modeOpen: false,
                modeLabel: '🎯 Smart Focus',
                modeValue: 'smart',
                modeOptions: [
                    { value: 'smart', label: '🎯 Smart Focus', title: 'Smart Focus (Filtered)', hint: 'Top viral & commercial keywords', icon: 'fas fa-bullseye', color: '#10b981' },
                    { value: 'deep', label: '🌐 Deep Coverage', title: 'Deep Coverage (All News)', hint: 'Process all headlines without filtering', icon: 'fas fa-globe', color: '#a855f7' },
                    { value: 'max', label: '🚀 Max Unlimited', title: 'Max Unlimited (All Content)', hint: 'Extract all articles & products without limits', icon: 'fas fa-bolt', color: '#00A8E6' },
                ],
                selectMode(opt) {
                    this.modeValue = opt.value;
                    this.modeLabel = opt.label;
                    this.modeOpen = false;
                },
                init() {
                    this.$nextTick(() => {
                        if (typeof window.applyKeywordTimeFilter === 'function') {
                            window.applyKeywordTimeFilter('{{ $lang }}', this.timeValue);
                        }
                    });
                }
             }">

            {{-- Mode Dropdown --}}
            <div class="relative" @click.away="modeOpen = false">
                <button type="button" @click="modeOpen = !modeOpen" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9999px;font-size:11px;font-weight:700;white-space:nowrap;background:#0f172a;border:1px solid rgba(255,255,255,0.15);color:#ffffff;box-shadow:0 2px 10px rgba(0,0,0,0.2);cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='#0f172a'">
                    <span x-text="modeLabel" style="color:#ffffff;">🎯 Smart Focus</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-70 transition-transform duration-200" :class="{'rotate-180': modeOpen}"></i>
                </button>
                <div x-show="modeOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-[-4px]"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:270px;max-width:calc(100vw - 24px);background:#0f172a !important;border:1px solid rgba(255,255,255,0.18) !important;border-radius:14px;z-index:99999;box-shadow:0 25px 60px rgba(0,0,0,0.85);backdrop-filter:blur(20px);overflow:hidden;">
                    <div style="padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;border-bottom:1px solid rgba(255,255,255,0.1);background:#0b1120;">
                        <i class="fas fa-sliders-h text-[9px] mr-1 text-purple-400"></i> Extraction Mode
                    </div>
                    <template x-for="(opt, idx) in modeOptions" :key="opt.value">
                        <button type="button" @click="selectMode(opt)"
                            style="display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;padding:12px 14px;text-align:{{ $isAr ? 'right' : 'left' }};color:#ffffff;border:none;cursor:pointer;transition:background 0.15s;background:transparent;"
                            :style="{ borderTop: idx > 0 ? '1px solid rgba(255,255,255,0.08)' : 'none', background: modeValue === opt.value ? opt.color + '26' : 'transparent' }"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                            :onmouseout="'this.style.background=' + (modeValue === opt.value ? JSON.stringify(opt.color + '26') : `'transparent'`)">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span :style="{ background: opt.color + '33', color: opt.color }" style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;font-size:12px;flex-shrink:0;">
                                    <i :class="opt.icon"></i>
                                </span>
                                <div style="display:flex;flex-direction:column;text-align:{{ $isAr ? 'right' : 'left' }};">
                                    <span style="font-size:12px;font-weight:700;color:#ffffff;" x-text="opt.title"></span>
                                    <span style="font-size:10px;color:#94a3b8;margin-top:2px;" x-text="opt.hint"></span>
                                </div>
                            </div>
                            <i class="fas fa-check text-[11px]" :style="{ color: opt.color }" x-show="modeValue === opt.value"></i>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Time Filter Button + Dropdown --}}
            <div class="relative" @click.away="timeOpen = false">
                <button type="button" @click="timeOpen = !timeOpen" 
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9999px;font-size:11px;font-weight:700;white-space:nowrap;background:#0f172a;border:1px solid rgba(255,255,255,0.15);color:#ffffff;box-shadow:0 2px 10px rgba(0,0,0,0.2);cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='#0f172a'">
                    <i class="fas fa-clock text-[10px]" style="color: #10b981;"></i> 
                    <span x-text="timeLabel" style="color:#ffffff;">Last 60m</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-70 transition-transform duration-200" :class="{'rotate-180': timeOpen}"></i>
                </button>
                <div x-show="timeOpen" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-[-4px]"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'right:0' : 'left:0' }};width:270px;max-width:calc(100vw - 24px);max-height:360px;overflow-y:auto;background:#0f172a !important;border:1px solid rgba(255,255,255,0.18) !important;border-radius:14px;z-index:99999;box-shadow:0 25px 60px rgba(0,0,0,0.85);backdrop-filter:blur(20px);">
                    <div style="padding:10px 14px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;border-bottom:1px solid rgba(255,255,255,0.1);position:sticky;top:0;background:#0b1120;z-index:1;">
                        <i class="fas fa-filter text-[9px] mr-1 text-emerald-400"></i> Select Time Range
                    </div>
                    <template x-for="(opt, idx) in timeOptions" :key="opt.value">
                        <button type="button"
                            @click="selectTime(opt)"
                            style="display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:56px;padding:12px 14px;text-align:{{ $isAr ? 'right' : 'left' }};color:#ffffff;border:none;cursor:pointer;transition:background 0.15s;background:transparent;"
                            :style="{
                                background: timeValue === opt.value ? opt.color + '26' : 'transparent',
                                borderTop: idx > 0 ? '1px solid rgba(255,255,255,0.08)' : 'none'
                            }"
                            onmouseover="this.style.background='rgba(255,255,255,0.08)'"
                            :onmouseout="'this.style.background=' + (timeValue === opt.value ? JSON.stringify(opt.color + '26') : `'transparent'`)">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span :style="{ background: opt.color + '33', color: opt.color }" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:9px;font-size:12px;flex-shrink:0;">
                                    <i :class="opt.icon"></i>
                                </span>
                                <div style="display:flex;flex-direction:column;gap:2px;text-align:{{ $isAr ? 'right' : 'left' }};">
                                    <span style="font-size:12px;font-weight:700;color:#ffffff;" x-text="opt.title"></span>
                                    <span style="font-size:10px;color:#94a3b8;" x-text="opt.hint"></span>
                                </div>
                            </div>
                            <i class="fas fa-check text-xs" :style="{ color: opt.color }" x-show="timeValue === opt.value"></i>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Sync Button --}}
            <button @click="syncCompetitors('{{ $lang }}', timeValue, '{{ $customBoxId ?? '' }}', modeValue)" :disabled="{{ $loadingModel }}" 
                style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:9999px;font-size:11px;font-weight:900;color:#fff;white-space:nowrap;border:1px solid rgba(255,255,255,0.2);background:{{ $colorVar }};box-shadow:0 4px 20px {{ $colorVar }}35;cursor:pointer;transition:transform 0.15s;"
                onmouseover="if(!this.disabled)this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fas fa-sync-alt text-[10px]" :class="{ 'fa-spin': {{ $loadingModel }} }"></i> 
                <span x-text="{{ $loadingModel }} ? 'Scanning...' : 'Refresh Radar'"></span>
            </button>

            {{-- High Traffic Filter Button --}}
            <button type="button" 
                @click="highTrafficActive = !highTrafficActive; window.toggleHighTrafficFilter('{{ $boxKey }}', highTrafficActive, '{{ $lang }}')"
                class="rounded-full inline-flex items-center gap-1.5 px-4 py-1.5 text-[11px] font-extrabold whitespace-nowrap cursor-pointer select-none transition-all duration-200"
                style="border-radius: 9999px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 6px 16px !important; font-size: 11px !important; font-weight: 800 !important; white-space: nowrap !important; cursor: pointer !important; transition: all 0.2s ease !important;"
                :style="highTrafficActive 
                    ? 'border-radius: 9999px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 6px 16px !important; font-size: 11px !important; font-weight: 800 !important; background: linear-gradient(135deg, #f59e0b, #ea580c) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.4) !important; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.45) !important; transform: scale(1.02);' 
                    : 'border-radius: 9999px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; padding: 6px 16px !important; font-size: 11px !important; font-weight: 800 !important; background: rgba(245, 158, 11, 0.1) !important; color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.35) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;'"
                title="Filter high-traffic search keywords with commercial & viral potential">
                <i class="fas fa-fire text-[11px]" :class="{'animate-bounce text-white': highTrafficActive, 'text-amber-400': !highTrafficActive}"></i>
                <span>High Traffic</span>
                <span x-show="highTrafficActive" x-cloak class="px-1.5 py-0.2 rounded-full text-[9px] font-black bg-black/40 text-amber-200 border border-white/25" id="high-traffic-badge-{{ $boxKey }}"></span>
            </button>
        </div>

        {{-- Settings --}}
        <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" 
            style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:9999px;font-size:11px;font-weight:700;border:1px solid rgba(168,85,247,0.3);color:#c084fc;background:rgba(168,85,247,0.08);white-space:nowrap;text-decoration:none;transition:all 0.2s;"
            onmouseover="this.style.background='rgba(168,85,247,0.18)';this.style.transform='scale(1.04)'" onmouseout="this.style.background='rgba(168,85,247,0.08)';this.style.transform='scale(1)'">
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

                    {{-- Intent Filter Dropdown --}}
                    <div class="relative" x-data="{ intentOpen: false, currentIntent: 'all', intentLabel: 'All Intents' }" @click.away="intentOpen = false">
                        <button type="button" @click="intentOpen = !intentOpen" 
                            style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);color:#ffffff;cursor:pointer;transition:all 0.2s;"
                            onmouseover="this.style.background='rgba(255,255,255,0.12)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                            <i class="fas fa-filter text-[10px] text-cyan-400"></i>
                            <span x-text="intentLabel">All Intents</span>
                            <i class="fas fa-chevron-down text-[8px] opacity-70"></i>
                        </button>
                        <div x-show="intentOpen" x-cloak
                             style="position:absolute;top:calc(100% + 6px);{{ $isAr ? 'left:0' : 'right:0' }};width:210px;background:#0f172a !important;border:1px solid rgba(255,255,255,0.18) !important;border-radius:14px;z-index:99999;box-shadow:0 25px 60px rgba(0,0,0,0.85);backdrop-filter:blur(20px);overflow:hidden;">
                            <button type="button" @click="currentIntent = 'all'; intentLabel = 'All Intents'; intentOpen = false; window.filterBoxByIntent('{{ $boxKey }}', 'all')"
                                style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 14px;background:transparent;color:#fff;border:none;cursor:pointer;font-size:11px;font-weight:700;text-align:{{ $isAr ? 'right' : 'left' }};">
                                <i class="fas fa-layer-group text-slate-400"></i> {{ $isAr ? 'جميع النوايا' : 'All Intents' }}
                            </button>
                            <button type="button" @click="currentIntent = 'commercial'; intentLabel = '🛒 Commercial'; intentOpen = false; window.filterBoxByIntent('{{ $boxKey }}', 'commercial')"
                                style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 14px;background:transparent;color:#10b981;border:none;border-top:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:11px;font-weight:700;text-align:{{ $isAr ? 'right' : 'left' }};">
                                <i class="fas fa-shopping-cart"></i> 🛒 {{ $isAr ? 'شرائي / تجاري' : 'Commercial' }}
                            </button>
                            <button type="button" @click="currentIntent = 'informational'; intentLabel = 'ℹ️ Informational'; intentOpen = false; window.filterBoxByIntent('{{ $boxKey }}', 'informational')"
                                style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 14px;background:transparent;color:#38bdf8;border:none;border-top:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:11px;font-weight:700;text-align:{{ $isAr ? 'right' : 'left' }};">
                                <i class="fas fa-info-circle"></i> ℹ️ {{ $isAr ? 'معلوماتي / أدلة' : 'Informational' }}
                            </button>
                            <button type="button" @click="currentIntent = 'trending'; intentLabel = '⚡ Trending'; intentOpen = false; window.filterBoxByIntent('{{ $boxKey }}', 'trending')"
                                style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 14px;background:transparent;color:#f59e0b;border:none;border-top:1px solid rgba(255,255,255,0.08);cursor:pointer;font-size:11px;font-weight:700;text-align:{{ $isAr ? 'right' : 'left' }};">
                                <i class="fas fa-bolt"></i> ⚡ {{ $isAr ? 'تريند / أحداث' : 'Trending' }}
                            </button>
                        </div>
                    </div>

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
            <div class="space-y-3 sm:space-y-4" id="keywords-container-{{ $boxKey }}">
                @foreach($headlineGroups as $headlineTitle => $groupKeywords)
                    @php
                        $firstKw = $groupKeywords[0] ?? [];
                        $source = is_array($firstKw) ? ($firstKw['source'] ?? 'AI') : 'AI';
                        $pubTime = is_array($firstKw) ? ($firstKw['published_at'] ?? null) : null;
                        $syncTime = is_array($firstKw) ? ($firstKw['synced_at'] ?? null) : null;
                        $pullTs = 0;
                        if (!empty($syncTime)) {
                            $pullTs = strtotime($syncTime) ?: 0;
                        } elseif (is_array($firstKw) && !empty($firstKw['created_at'])) {
                            $pullTs = strtotime($firstKw['created_at']) ?: 0;
                        }
                        $pubTs = 0;
                        if (!empty($pubTime)) {
                            $pubTs = strtotime($pubTime) ?: 0;
                        }
                        $groupKeywordTexts = [];
                        foreach ($groupKeywords as $gkw) {
                            $gt = is_array($gkw) ? trim($gkw['text'] ?? $gkw['keyword'] ?? '') : trim((string) $gkw);
                            if ($gt !== '') $groupKeywordTexts[] = $gt;
                        }
                        $primaryKeyword = $groupKeywordTexts[0] ?? '';
                        foreach ($groupKeywords as $gkw) {
                            if (!is_array($gkw)) continue;
                            $st = $gkw['synced_at'] ?? null;
                            if (!empty($st)) {
                                $ts = strtotime($st) ?: 0;
                                if ($ts > $pullTs) { $pullTs = $ts; $syncTime = $st; }
                            }
                            $pt = $gkw['published_at'] ?? null;
                            if (!empty($pt)) {
                                $ts = strtotime($pt) ?: 0;
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
                                        $intent = is_array($kw) ? ($kw['intent'] ?? \Modules\AIKeywordRadar\Support\KeywordPayload::detectSearchIntent($text, $lang)) : \Modules\AIKeywordRadar\Support\KeywordPayload::detectSearchIntent($text, $lang);
                                    @endphp
                                    @if(!empty($text))
                                    <div class="keyword-tag keyword-chip-row" 
                                         data-intent="{{ $intent['type'] ?? 'general' }}" 
                                         data-high-traffic="{{ \Modules\AIKeywordRadar\Support\KeywordPayload::isHighTraffic($text, $lang) ? '1' : '0' }}"
                                         style="display:inline-flex;align-items:center;gap:8px;max-width:100%;flex-wrap:wrap;">
                                        <input type="checkbox"
                                               class="kw-select-{{ $boxKey }}"
                                               value="{{ $text }}"
                                               style="accent-color:{{ $colorVar }};width:16px;height:16px;cursor:pointer;flex-shrink:0;"
                                               @change="toggleKeyword('{{ $boxKey }}', @js($text), $event.target.checked)">
                                        <span class="keyword-text font-black text-sm sm:text-base" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:12px;line-height:1.3;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.06);color:#ffffff;max-width:100%;text-align:{{ $isAr ? 'right' : 'left' }};">
                                            <i class="fas fa-hashtag text-[10px] flex-shrink-0" style="color:{{ $colorVar }};"></i>
                                            <span class="break-words font-black" style="color:#ffffff;">{{ $text }}</span>
                                        </span>
                                        @if(!empty($intent))
                                        <span class="intent-badge" style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:800;background:{{ $intent['badge_bg'] }};border:1px solid {{ $intent['badge_border'] }};color:{{ $intent['badge_color'] }};" title="{{ $intent['label'] }}">
                                            <i class="{{ $intent['icon'] }} text-[9px]"></i>
                                            <span>{{ $intent['label'] }}</span>
                                        </span>
                                        @endif
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
                                        <span style="font-size:10px;color:#ffffff;background:rgba(255,255,255,0.12);padding:3px 10px;border-radius:8px;font-weight:900;text-transform:uppercase;border:1px solid rgba(255,255,255,0.3);display:inline-flex;align-items:center;gap:5px;letter-spacing:0.3px;box-shadow:0 2px 8px rgba(0,0,0,0.4);"><i class="fas fa-globe text-[9px]" style="color:var(--primary-cyan, #0ea5e9);"></i> <span style="color:#ffffff !important;font-weight:900;">{{ $source }}</span></span>
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
                        <i class="{{ $customBoxId === 'direct_seed' ? 'fas fa-crosshairs' : 'fas fa-satellite-dish' }} text-2xl" style="color: {{ $colorVar }};"></i>
                    </div>
                    <h4 class="font-bold mb-2 text-sm" style="color: var(--text-main);">
                        {{ $customBoxId === 'direct_seed' ? ($isAr ? 'مستكشف الكلمات والمواضيع المباشرة' : 'Direct Seed Explorer') : ($isAr ? 'في وضع الاستعداد' : 'Intelligence Standby') }}
                    </h4>
                    <p class="text-sm mb-6 max-w-xs" style="color: var(--text-muted);">
                        {{ $customBoxId === 'direct_seed' ? ($isAr ? 'أضف كلمات ومواضيع مجالك الرئيسية (مثل: هواتف، عقارات، أسعار الذهب) لمسح آخر ما نُشر عنها واقتراحات البحث.' : 'Add your niche seed topics to scan latest published stories and search trends.') : ($isAr ? 'لم يتم إضافة منافسين بعد. أضف روابط المنافسين لبدء رصد السوق.' : 'No competitors tracked yet. Add domains to start receiving market intelligence.') }}
                    </p>
                    <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="inline-block px-5 py-2.5 rounded-xl text-sm font-bold transition border" style="background: {{ $colorVar }}20; color: {{ $colorVar }}; border-color: {{ $colorVar }}30;">
                        <i class="fas fa-cog me-2"></i> {{ $customBoxId === 'direct_seed' ? ($isAr ? 'إضافة الكلمات المستهدفة' : 'Configure Seed Topics') : ($isAr ? 'إضافة مصادر المنافسين' : 'Configure Intel Sources') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
