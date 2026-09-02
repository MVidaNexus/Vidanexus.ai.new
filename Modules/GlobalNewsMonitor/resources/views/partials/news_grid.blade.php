@if(count($googleNews) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($googleNews as $index => $item)
            @php
                $score = $item['seo_score'] ?? 0;
                $trend = $item['trend_direction'] ?? 'stable';
                $sentiment = $item['sentiment'] ?? 'neutral';
                $ageHours = $item['age_hours'] ?? 99;
                
                // Use dynamic thresholds passed from controller or defaults
                $tHigh = (int) ($thresholdHigh ?? 70);
                $tMod  = (int) ($thresholdModerate ?? 45);

                // RECALCULATE LEVEL LIVE (to avoid caching issues)
                $level = 'low';
                if ($score >= $tHigh) $level = 'high';
                elseif ($score >= $tMod) $level = 'moderate';

                // Opportunity badge config
                $badgeConfig = match($level) {
                    'high' => [
                        'label' => 'HIGH OPPORTUNITY',
                        'sublabel' => 'Write Now!',
                        'bg' => 'rgba(16, 185, 129, 0.12)',
                        'border' => 'rgba(16, 185, 129, 0.35)',
                        'color' => '#10b981',
                        'glow' => '0 0 25px rgba(16, 185, 129, 0.15)',
                        'icon' => 'fas fa-rocket',
                        'cardBorder' => 'border-emerald-500/60',
                    ],
                    'moderate' => [
                        'label' => 'MODERATE',
                        'sublabel' => 'Unique Angle Needed',
                        'bg' => 'rgba(245, 158, 11, 0.1)',
                        'border' => 'rgba(245, 158, 11, 0.3)',
                        'color' => '#f59e0b',
                        'glow' => '0 0 15px rgba(245, 158, 11, 0.1)',
                        'icon' => 'fas fa-bolt',
                        'cardBorder' => 'border-amber-500/40',
                    ],
                    default => [
                        'label' => 'LOW',
                        'sublabel' => 'High Competition',
                        'bg' => 'rgba(100, 116, 139, 0.08)',
                        'border' => 'rgba(100, 116, 139, 0.2)',
                        'color' => '#64748b',
                        'glow' => 'none',
                        'icon' => 'fas fa-shield-halved',
                        'cardBorder' => 'border-white/10',
                    ],
                };

                // Trend direction config
                $trendConfig = match($trend) {
                    'rising_fast' => ['icon' => 'fas fa-arrow-trend-up', 'color' => '#10b981', 'label' => 'Surging'],
                    'rising' => ['icon' => 'fas fa-arrow-up', 'color' => '#0ea5e9', 'label' => 'Rising'],
                    'declining' => ['icon' => 'fas fa-arrow-down', 'color' => '#ef4444', 'label' => 'Cooling'],
                    default => ['icon' => 'fas fa-minus', 'color' => '#64748b', 'label' => 'Stable'],
                };

                // Sentiment border color
                $sentimentBorder = match($sentiment) {
                    'positive' => 'border-emerald-500/50',
                    'negative' => 'border-red-500/40',
                    default => 'border-white/10',
                };
            @endphp

            <div x-show="!showHighChanceOnly || '{{ $level }}' === 'high'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="news-card glass-card flex flex-col group/news hover:bg-white/[0.04] transition-all duration-300 border-l-4 {{ $badgeConfig['cardBorder'] }} relative overflow-hidden"
                 style="box-shadow: {{ $badgeConfig['glow'] }};"
                 data-news-title="{{ $item['title'] }}">
                
                {{-- Multi-select checkbox --}}
                <label class="absolute top-3 left-3 z-20 flex items-center justify-center w-6 h-6 rounded-md cursor-pointer"
                       style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15);"
                       title="Select for analysis">
                    <input type="checkbox" class="news-select-checkbox" value="{{ $item['title'] }}"
                           style="accent-color:#0ea5e9;cursor:pointer;width:14px;height:14px;">
                </label>
                
                {{-- Glow Effect for HIGH opportunity --}}
                @if($level === 'high')
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/[0.06] blur-3xl -mr-16 -mt-16 rounded-full pointer-events-none"></div>
                @endif

                {{-- ═══ TOP BAR: Opportunity Badge + Trend + Age ═══ --}}
                <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-2">
                    {{-- Opportunity Badge --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-[10px] font-black tracking-wider uppercase"
                         style="background: {{ $badgeConfig['bg'] }}; border: 1px solid {{ $badgeConfig['border'] }}; color: {{ $badgeConfig['color'] }};">
                        <i class="{{ $badgeConfig['icon'] }} text-[9px]"></i>
                        <span>{{ $badgeConfig['label'] }}</span>
                        <span class="text-[8px] font-bold opacity-70">{{ $score }}%</span>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Trend Direction --}}
                        <div class="flex items-center gap-1 px-2 py-1 rounded-lg text-[9px] font-bold"
                             style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); color: {{ $trendConfig['color'] }};"
                             title="Trend: {{ $trendConfig['label'] }}">
                            <i class="{{ $trendConfig['icon'] }} text-[8px]"></i>
                            <span>{{ $trendConfig['label'] }}</span>
                        </div>

                        {{-- Age Badge --}}
                        @if($ageHours < 1)
                        <span class="px-2 py-1 bg-red-500/10 text-red-400 text-[8px] border border-red-500/20 rounded-lg font-black flex items-center gap-1 animate-pulse">
                            <div class="w-1.5 h-1.5 bg-red-400 rounded-full"></div> LIVE
                        </span>
                        @elseif($ageHours < 24)
                        <span class="px-2 py-1 text-[8px] rounded-lg font-bold" style="background: rgba(14, 165, 233, 0.08); color: #0ea5e9; border: 1px solid rgba(14, 165, 233, 0.15);">
                            {{ $ageHours < 2 ? round($ageHours * 60) . 'm ago' : round($ageHours) . 'h ago' }}
                        </span>
                        @else
                        <span class="px-2 py-1 text-[8px] rounded-lg font-bold" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); border: 1px solid var(--glass-border);">
                            {{ round($ageHours / 24) }}d ago
                        </span>
                        @endif
                    </div>
                </div>

                {{-- ═══ SOURCE + SENTIMENT ═══ --}}
                <div class="px-5 pb-2 flex items-center gap-2">
                    <span class="text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider flex items-center gap-1.5"
                          style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-muted);">
                        @if($item['is_high_authorative'] ?? false) 
                            <i class="fas fa-check-circle text-[8px]" style="color: #0ea5e9;"></i> 
                        @endif
                        {{ $item['source'] }}
                    </span>
                    
                    {{-- Sentiment Indicator --}}
                    <div class="flex items-center gap-1 text-[9px] font-bold" style="color: {{ $sentiment === 'positive' ? '#10b981' : ($sentiment === 'negative' ? '#ef4444' : '#64748b') }};">
                        <div class="w-1.5 h-1.5 rounded-full" style="background: {{ $sentiment === 'positive' ? '#10b981' : ($sentiment === 'negative' ? '#ef4444' : '#64748b') }};"></div>
                        {{ ucfirst($sentiment) }}
                    </div>

                    {{-- Authority Level --}}
                    @if(($item['authority_level'] ?? 'low') === 'low')
                    <span class="text-[8px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold border border-emerald-500/15" title="Small source = easier to outrank">
                        Low Authority
                    </span>
                    @endif
                </div>
                
                {{-- ═══ TITLE ═══ --}}
                <h3 class="px-5 pt-1 font-bold leading-relaxed text-sm line-clamp-2 min-h-[3rem] mb-1 pl-10" style="color: var(--text-main);">
                    <a href="{{ $item['link'] }}" target="_blank" class="hover:text-primary-cyan transition-colors no-underline">
                        {{ $item['title'] }}
                    </a>
                </h3>

                {{-- ═══ ENTITY TAGS ═══ --}}
                @if(!empty($item['entities']))
                <div class="px-5 flex flex-wrap gap-1 mb-3">
                    @foreach($item['entities'] as $tag)
                        <span class="text-[9px] px-2 py-0.5 rounded-md font-medium" 
                              style="background: rgba(14, 165, 233, 0.06); color: rgba(14, 165, 233, 0.7); border: 1px solid rgba(14, 165, 233, 0.1);">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
                @endif

                {{-- ═══ SCORING BREAKDOWN (mini bar) ═══ --}}
                <div class="px-5 mb-3">
                    <div class="flex items-center gap-3 text-[9px] font-medium" style="color: var(--text-muted);">
                        <span title="Virality Score">🔥 {{ $item['virality_score'] ?? 0 }}</span>
                        <span title="Freshness Score">⚡ {{ $item['freshness_score'] ?? 0 }}</span>
                        <span class="flex-1">
                            <div class="w-full h-1 rounded-full overflow-hidden" style="background: rgba(255,255,255,0.05);">
                                <div class="h-full rounded-full transition-all duration-500"
                                     style="width: {{ $score }}%; background: {{ $badgeConfig['color'] }};"></div>
                            </div>
                        </span>
                    </div>
                </div>

                {{-- ═══ AI ANALYSIS PANEL (expandable) ═══ --}}
                <div x-data="{ showAnalysis: false, analysisData: null, analyzing: false }" class="px-5 mb-3">
                    {{-- Analyze Button --}}
                    <button x-show="!showAnalysis && !analyzing" 
                            @click="analyzeArticle($el, '{{ addslashes($item['title']) }}', '{{ addslashes($item['description'] ?? '') }}', '{{ $region ?? 'EG' }}', '{{ $lang ?? 'ar' }}', '{{ $topic ?? 'WORLD' }}')"
                            class="w-full py-2 px-3 rounded-xl text-[11px] font-bold flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.08), rgba(14, 165, 233, 0.08)); border: 1px solid rgba(168, 85, 247, 0.15); color: #a855f7;">
                        <i class="fas fa-brain text-[10px]"></i>
                        <span>AI Deep Analysis</span>
                        <span class="text-[8px] opacity-60 ml-1">1 CRS</span>
                    </button>
                    
                    {{-- Loading State --}}
                    <div x-show="analyzing" class="w-full py-3 flex items-center justify-center gap-2 text-[11px] font-bold" style="color: #a855f7;">
                        <i class="fas fa-spinner animate-spin text-[10px]"></i>
                        <span>Analyzing with AI...</span>
                    </div>

                    {{-- Analysis Results --}}
                    <div x-show="showAnalysis && analysisData" x-transition
                         class="rounded-xl p-3 space-y-2"
                         style="background: rgba(168, 85, 247, 0.05); border: 1px solid rgba(168, 85, 247, 0.12);">
                        
                        <template x-if="analysisData">
                            <div class="space-y-2">
                                {{-- Recommendation --}}
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-wider" style="color: var(--text-muted);">Action:</span>
                                    <span class="text-[11px] font-black" 
                                          :class="{
                                              'text-emerald-400': analysisData.recommended_action?.includes('now') || analysisData.recommended_action?.includes('الآن'),
                                              'text-amber-400': analysisData.recommended_action?.includes('monitor') || analysisData.recommended_action?.includes('راقب'),
                                              'text-red-400': analysisData.recommended_action?.includes('skip') || analysisData.recommended_action?.includes('تجاوز')
                                          }"
                                          x-text="analysisData.recommended_action"></span>
                                </div>

                                {{-- Reason --}}
                                <p class="text-[10px] leading-relaxed text-slate-300" x-text="analysisData.ranking_reason"></p>

                                {{-- Suggested Angle --}}
                                <div>
                                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Content Angle:</span>
                                    <p class="text-[11px] font-semibold mt-0.5 text-slate-200" x-text="analysisData.suggested_angle"></p>
                                </div>

                                {{-- Keywords --}}
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="kw in (analysisData.suggested_keywords || [])" :key="kw">
                                        <span class="text-[9px] px-2 py-0.5 rounded-md font-bold text-slate-300"
                                              style="background: rgba(148, 163, 184, 0.12); border: 1px solid rgba(148, 163, 184, 0.2);"
                                              x-text="kw"></span>
                                    </template>
                                </div>

                                {{-- Meta Row --}}
                                <div class="flex items-center gap-3 pt-1 text-[9px] font-bold text-slate-400">
                                    <span>📊 <span x-text="analysisData.content_type"></span></span>
                                    <span>🔍 Vol: <span x-text="analysisData.estimated_search_volume"></span></span>
                                    <span>⚔️ Comp: <span x-text="analysisData.competition_level"></span></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ═══ FOOTER: Actions ═══ --}}
                <div class="mt-auto px-5 pb-5 flex items-center justify-between pt-3 border-t" style="border-color: var(--glass-border);">
                    <div class="flex flex-col gap-1">
                        <a href="{{ $item['link'] }}" target="_blank" class="text-[11px] text-primary-cyan hover:text-primary-blue transition-colors flex items-center gap-1 font-bold no-underline mb-1">
                            <span>Read Source</span>
                            <i class="fas fa-external-link-alt text-[9px]"></i>
                        </a>
                        <span class="text-[10px] font-medium opacity-70 flex items-center gap-1" style="color: var(--text-muted);">
                            <i class="far fa-clock text-[8px]"></i>
                            {{ !empty($item['time_ago']) ? $item['time_ago'] : \Carbon\Carbon::parse($item['pubDate'])->diffForHumans() }}
                            @if(!empty($item['scraped_at']))
                                <span class="mx-1">·</span>
                                <i class="fas fa-sync text-[8px]"></i>
                                Fetched {{ \Carbon\Carbon::parse($item['scraped_at'])->diffForHumans() }}
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="https://www.google.com/search?q={{ urlencode($item['title']) }}&gl={{ $region ?? 'US' }}&hl={{ $lang ?? 'en' }}" target="_blank" 
                           class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" 
                           style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" 
                           title="Check SERP">
                            <i class="fab fa-google"></i>
                        </a>
                        <button onclick="copyToClipboard('{{ addslashes($item['title']) }}')" 
                                class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" 
                                style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" 
                                title="Copy Title">
                            <i class="fas fa-copy"></i>
                        </button>
                        <a href="{{ route('headlines.index') }}?keyword={{ urlencode($item['title']) }}" target="_blank"
                           class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" 
                           style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" 
                           title="Discover Headlines">
                            <i class="fas fa-bolt"></i>
                        </a>
                        <a href="{{ route('dashboard.article-writer.index') }}?topic={{ urlencode($item['title']) }}" target="_blank"
                           class="w-8 h-8 rounded-lg flex items-center justify-center transition-all text-xs shadow-lg bg-gradient-to-br from-[#10b981] to-[#0ea5e9] hover:scale-110 text-white"
                           style="border: 1px solid rgba(16, 185, 129, 0.2);"
                           title="Write Article About This">
                            <i class="fas fa-pen-fancy"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="glass-card p-16 text-center border-dashed" style="border-color: var(--glass-border);">
        @if($isInitial ?? false)
            <div class="mb-6">
                <div class="w-20 h-20 bg-primary-cyan/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-primary-cyan/20">
                    <i class="fas fa-satellite-dish text-3xl text-primary-cyan animate-pulse"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Ready to Scan?</h3>
                <p class="text-sm max-w-md mx-auto" style="color: var(--text-muted); opacity: 0.8;">
                    Click the <strong>"Get News"</strong> button to start scanning for global ranking opportunities in real-time.
                </p>
                <button @click="refreshNews(true)" class="mt-6 vn-btn vn-btn-primary px-8 py-3 rounded-2xl flex items-center gap-2 mx-auto">
                    <i class="fas fa-sync-alt" :class="{ 'animate-spin': loading }"></i>
                    <span class="font-bold">Fetch Latest News</span>
                </button>
            </div>
        @else
            <i class="fas fa-newspaper text-4xl mb-4 block" style="color: var(--text-muted); opacity: 0.5;"></i>
            <p class="font-bold text-white">No news found for this search</p>
            <p class="text-sm mt-2" style="color: var(--text-muted); opacity: 0.6;">Try changing the region, topic, or click Get News again.</p>
        @endif
    </div>
@endif
