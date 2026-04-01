@php
    $colorClass = $lang === 'ar' ? 'primary-cyan' : 'blue-500';
    $colorVar = $lang === 'ar' ? 'var(--primary-cyan)' : '#3b82f6';
    $icon = $lang === 'ar' ? 'fas fa-crosshairs' : 'fas fa-globe-americas';
    $loadingModel = $lang === 'ar' ? 'loading.syncAr' : 'loading.syncEn';
    $selectedModel = $lang === 'ar' ? 'selectedKeywordsAr' : 'selectedKeywordsEn';
    $userCompetitors = auth()->user()->settings['keywords_competitors' . ($lang === 'en' ? '_en' : '')] ?? '';
    $hasCompetitors = !empty(trim($userCompetitors));
    $isAr = $lang === 'ar';
@endphp
<div class="glass-card flex flex-col h-[75vh] lg:h-[calc(100vh-120px)] overflow-hidden relative border border-white/5 glass-border-light" style="border-top-color: {{ $colorVar }}40;" dir="{{ $isAr ? 'rtl' : 'ltr' }}">
    <!-- Full-Box Loading Overlay -->
    <div id="sync-loading-{{ $lang }}" x-show="{{ $loadingModel }}" class="absolute inset-0 z-50 flex items-center justify-center backdrop-blur-md" style="display: none; background: var(--glass-bg);">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full border-t-2 border-r-2 animate-spin mb-4" style="border-color: {{ $colorVar }}; box-shadow: 0 0 15px {{ $colorVar }}80;"></div>
            <span class="text-sm font-bold" style="color: var(--text-main);">Extracting keywords using AI...</span>
            <span class="text-[10px] mt-2" style="color: var(--text-muted);">This may take a few minutes depending on the provider's speed</span>
        </div>
    </div>

    <div class="absolute top-0 {{ $isAr ? 'right-0' : 'left-0' }} w-32 h-32 blur-3xl rounded-full opacity-10" style="background: {{ $colorVar }}; margin-{{ $isAr ? 'right' : 'left' }}: -4rem; margin-top: -4rem;"></div>
    
    <div class="flex items-center justify-end gap-2 px-4 pt-4 pb-1 relative z-10 {{ $isAr ? '' : 'flex-row-reverse' }}">
        <a href="{{ route('dashboard.ai-keyword-radar.settings') }}" class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5 border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 hover:border-purple-500/50 shadow-sm" style="background: rgba(168, 85, 247, 0.05);">
            <i class="fas fa-cog text-[10px]"></i> Settings
        </a>
        
        <div class="relative" x-data="{ sortOpen: false, currentSort: 'Newest Publish' }" @click.away="sortOpen = false">
            <button @click="sortOpen = !sortOpen" class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1.5 border shadow-sm" style="background: rgba(15, 23, 42, 0.9) !important; border-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; backdrop-filter: blur(10px);">
                <i class="fas fa-sort-amount-down text-[10px]" style="color: {{ $colorVar }};"></i> 
                <span x-text="currentSort">Newest Publish</span>
                <i class="fas fa-chevron-down text-[8px] opacity-60 ml-0.5 transition-transform" :class="{'rotate-180': sortOpen}"></i>
            </button>
            <div x-show="sortOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 style="display: none; background: rgba(15, 23, 42, 0.98) !important; backdrop-filter: blur(15px); border-color: rgba(255,255,255,0.2) !important;" 
                 class="absolute top-full mt-2 {{ $isAr ? 'left-0' : 'right-0' }} w-44 rounded-xl shadow-2xl border overflow-hidden z-20">
                <button @click="window.executeKeywordSort('{{ $lang }}', 'pulldate'); currentSort = 'Newest Sync'; sortOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold transition flex items-center justify-between group" style="color: #ffffff !important; background: transparent;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-satellite-dish text-[10px] text-amber-500 group-hover:scale-125 transition-transform"></i> 
                        <span>Newest Sync</span>
                    </div>
                </button>
                <button @click="window.executeKeywordSort('{{ $lang }}', 'pubdate'); currentSort = 'Newest Publish'; sortOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold transition flex items-center justify-between border-t group" style="border-top-color: rgba(255,255,255,0.1) !important; color: #ffffff !important; background: transparent;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-history text-[10px] text-cyan-500 group-hover:scale-125 transition-transform"></i> 
                        <span>Newest Publish</span>
                    </div>
                </button>
                <button @click="window.executeKeywordSort('{{ $lang }}', 'alphabetical'); currentSort = 'Alphabetical (A-Z)'; sortOpen = false" class="w-full text-left px-4 py-3 text-xs font-bold transition flex items-center justify-between border-t group" style="border-top-color: rgba(255,255,255,0.1) !important; color: #ffffff !important; background: transparent;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-sort-alpha-down text-[10px] text-purple-500 group-hover:scale-125 transition-transform"></i> 
                        <span>Alphabetical (A-Z)</span>
                    </div>
                </button>
            </div>
        </div>
        <button @click="syncCompetitors('{{ $lang }}')" :disabled="{{ $loadingModel }}" class="px-4 py-1.5 text-black rounded-xl text-[11px] font-black tracking-tight shadow-md transition-all flex items-center gap-1.5 border border-white/10 hover:scale-105 active:scale-95" style="background: {{ $colorVar }}; box-shadow: 0 4px 15px {{ $colorVar }}30;">
            <i class="fas fa-sync-alt text-[10px]" :class="{ 'fa-spin': {{ $loadingModel }} }"></i> 
            <span x-text="{{ $loadingModel }} ? 'Syncing...' : 'Sync {{ strtoupper($lang) }}'"></span>
        </button>
    </div>

    <div class="px-4 py-3 sm:px-5 border-b border-white/5 bg-white/5 glass-bg-light glass-border-light flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 relative z-10 flex-shrink-0 w-full {{ $isAr ? '' : 'flex-row-reverse' }}">
        <h2 class="text-lg sm:text-xl font-black flex items-center gap-2 sm:gap-3 whitespace-nowrap" style="color: var(--text-main);">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg hidden sm:flex items-center justify-center shadow-sm flex-shrink-0 border" style="background: {{ $colorVar }}15; color: {{ $colorVar }}; border-color: {{ $colorVar }}30;">
                <i class="{{ $icon }} animate-pulse text-xs sm:text-sm"></i>
            </div>
            <span>{{ $title }}</span>
        </h2>
        
        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap {{ $isAr ? '' : 'flex-row-reverse' }}">
            @if(!empty($targetKeywords))
                <div class="flex items-center gap-2 bg-white/5 px-2 py-1 rounded-lg border border-white/5 whitespace-nowrap">
                    <input type="checkbox" 
                           @click="toggleSelectAll('{{ $lang }}', {{ json_encode(array_map(fn($kw) => is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw, $targetKeywords)) }})"
                           :checked="{{ $selectedModel }}.length === {{ count($targetKeywords) }} && {{ count($targetKeywords) }} > 0"
                           class="w-4 h-4 rounded border-gray-300 dark:border-white/10 bg-white/5" style="color: {{ $colorVar }};">
                    <span class="text-[10px] font-bold text-gray-400">All</span>
                </div>

                <template x-if="{{ $selectedModel }}.length > 0">
                    <button @click="copySelectedKeywords('{{ $lang }}')" class="px-2.5 py-1.5 text-black rounded-lg text-[9px] font-black shadow-lg transition-all flex items-center gap-1.5 whitespace-nowrap hover:scale-105 active:scale-95" style="background: {{ $colorVar }}; box-shadow: 0 4px 10px {{ $colorVar }}30;">
                        <i class="far fa-copy"></i>
                        <span>Copy (<span x-text="{{ $selectedModel }}.length"></span>)</span>
                    </button>
                </template>

                <form action="{{ route('dashboard.ai-keyword-radar.delete-all', ['lang' => $lang]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete all keywords?')" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-1 px-2.5 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-lg text-[10px] font-bold transition-all border border-red-500/20 whitespace-nowrap">
                        <i class="fas fa-trash-alt mr-1"></i> Delete 
                    </button>
                </form>
            @endif
            <span class="text-[10px] sm:text-xs font-bold px-2 py-1 rounded bg-white/5 text-gray-300 whitespace-nowrap">{{ count($targetKeywords ?? []) }} Keywords</span>
        </div>
    </div>

    <div class="p-3 sm:p-5 flex-1 relative z-10 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: {{ $colorVar }} transparent;">
        
        <div id="sync-notification-{{ $lang }}" style="display: none;" class="mb-4"></div>

        @if(!empty($targetKeywords))
            <div class="space-y-3 keyword-container-{{ $lang }}">
                @foreach($targetKeywords as $kw)
                    @php
                        $text = is_array($kw) ? ($kw['text'] ?? $kw['keyword'] ?? '') : $kw;
                        $source = is_array($kw) ? ($kw['source'] ?? 'AI') : 'AI';
                        $createdAt = is_array($kw) && isset($kw['created_at']) ? \Carbon\Carbon::parse($kw['created_at']) : null;
                    @endphp
                    @if(!empty($text))
                    <div data-pulldate="{{ $createdAt ? $createdAt->timestamp : 0 }}" data-pubdate="{{ isset($kw['published_at']) ? \Carbon\Carbon::parse($kw['published_at'])->timestamp : 0 }}" class="flex items-start justify-between p-3 sm:p-4 rounded-2xl border transition-all duration-300 group gap-2.5 keyword-row relative overflow-hidden" style="background: var(--glass-bg); border-color: var(--glass-border);">
                        <div class="flex items-start gap-2.5 flex-1 min-w-0">
                            <!-- Selection & Index -->
                            <div class="flex flex-col items-center gap-2 mt-1">
                                <input type="checkbox" 
                                       x-model="{{ $selectedModel }}" 
                                       value="{{ $text }}"
                                       class="w-4 h-4 rounded border-gray-300 dark:border-white/10 bg-white/5 flex-shrink-0" style="color: {{ $colorVar }};">
                                <span class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg font-black text-[10px] shadow-sm keyword-num" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: {{ $colorVar }};">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <!-- Content Area -->
                            <div class="flex flex-col min-w-0 flex-1 pt-0.5">
                                <span class="font-bold text-sm sm:text-base break-words leading-tight mb-1.5" style="color: var(--text-main);">{{ $text }}</span>
                                
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="text-[8px] text-red-400 bg-red-400/10 px-2 py-0.5 rounded-lg font-bold uppercase border border-red-400/20">{{ $source }}</span>
                                </div>

                                <!-- Metadata / Timestamps -->
                                <div class="grid grid-cols-1 gap-1.5" style="border-{{ $isAr ? 'right' : 'left' }}: 2px solid {{ $colorVar }}20; padding-{{ $isAr ? 'right' : 'left' }}: 0.75rem;">
                                    @if(isset($kw['published_at']))
                                        @php
                                            $pubDate = \Carbon\Carbon::parse($kw['published_at']);
                                            $pubTime = $pubDate->isFuture() 
                                                ? ($lang === 'ar' ? 'الآن' : 'Now') 
                                                : $pubDate->locale($lang)->diffForHumans();
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-history text-[9px] opacity-50" style="color: var(--primary-cyan);"></i>
                                            <span class="text-[9px] font-bold" style="color: var(--text-muted);">
                                                <span class="opacity-50 font-normal">{{ $lang === 'ar' ? 'نُشر:' : 'Pub:' }}</span>
                                                <span style="color: var(--primary-cyan);">{{ $pubTime }}</span>
                                            </span>
                                        </div>
                                    @endif
                                    @if($createdAt)
                                        @php
                                            $extTime = $createdAt->isFuture() 
                                                ? ($lang === 'ar' ? 'الآن' : 'Now') 
                                                : $createdAt->locale($lang)->diffForHumans();
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-satellite-dish text-[9px] opacity-50" style="color: #f59e0b;"></i>
                                            <span class="text-[9px] font-bold" style="color: var(--text-muted);">
                                                <span class="opacity-50 font-normal">{{ $lang === 'ar' ? 'سُحب:' : 'Sync:' }}</span>
                                                <span style="color: #f59e0b;">{{ $extTime }}</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions Sidebar (Vertical on mobile, horizontal on lg) -->
                        <div class="flex flex-col sm:flex-row lg:flex-row items-center gap-1.5 flex-shrink-0 pt-1">
                            <div class="flex flex-col gap-1.5">
                                <button onclick="copyToClipboard('{{ addslashes($text) }}')" class="w-8 h-8 flex items-center justify-center rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 keyword-action-btn" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-muted);" title="Copy">
                                    <i class="far fa-copy text-[10px]"></i>
                                </button>
                                <a href="https://www.google.com/search?q={{ urlencode($text) }}&gl={{ $lang === 'en' ? 'US' : 'EG' }}" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 keyword-action-btn" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-muted);" title="Google Search">
                                    <i class="fab fa-google text-[10px]"></i>
                                </a>
                            </div>
                            <a href="{{ route('headlines.index', ['keyword' => $text]) }}" target="_blank" class="w-8 h-8 sm:w-auto sm:h-auto vn-btn vn-btn-primary flex items-center justify-center gap-2" style="font-size: 0.65rem; padding: 0.4rem 0.5rem; min-width: 32px;" title="Discover">
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
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6 border shadow-xl" style="background: var(--card-bg); color: {{ $colorVar }}; border-color: {{ $colorVar }}30; box-shadow: 0 0 20px {{ $colorVar }}20;">
                        <i class="fas fa-robot text-3xl animate-bounce"></i>
                    </div>
                    <h4 class="font-black text-xl mb-3" style="color: var(--text-main);">Ready to extract smart keywords</h4>
                    <p class="text-sm mb-8 max-w-sm mx-auto leading-relaxed" style="color: var(--text-muted);">Competitors added successfully. Click Sync to extract hidden trends.</p>
                    <button @click="syncCompetitors('{{ $lang }}')" :disabled="{{ $loadingModel }}" class="px-10 py-4 text-white rounded-2xl font-black transition-all flex items-center gap-3 mx-auto hover:scale-105 active:scale-95" style="background: {{ $colorVar }}; box-shadow: 0 8px 25px {{ $colorVar }}40; color: #000;">
                        <i class="fas fa-sync-alt" :class="{ 'fa-spin': {{ $loadingModel }} }"></i>
                        <span x-text="{{ $loadingModel }} ? 'Extracting data...' : 'Start Sync and Analysis Now'"></span>
                    </button>
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
