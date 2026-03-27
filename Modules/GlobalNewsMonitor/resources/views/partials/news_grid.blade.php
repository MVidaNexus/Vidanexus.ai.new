@if(count($googleNews) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($googleNews as $index => $item)
            <div x-show="!showHighChanceOnly || {{ ($item['seo_score'] ?? 0) }} >= 70"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="news-card glass-card p-5 flex flex-col group/news hover:bg-white/5 transition-all border-l-4 {{ ($item['sentiment'] ?? 'neutral') === 'positive' ? 'border-primary-cyan' : (($item['sentiment'] ?? 'negative') ? 'border-red-500/50' : 'border-white/10') }}">
                
                <!-- Header: Source & SEO Score -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-primary-cyan/10 text-primary-cyan font-bold uppercase tracking-wider flex items-center gap-1">
                            @if($item['is_high_authorative'] ?? false) <i class="fas fa-check-circle text-[8px]"></i> @endif
                            {{ $item['source'] }}
                        </span>
                        
                        <!-- Sentiment Dot -->
                        <div class="w-1.5 h-1.5 rounded-full animate-pulse" 
                             style="background: {{ ($item['sentiment'] ?? 'neutral') === 'positive' ? '#0ea5e9' : (($item['sentiment'] ?? 'neutral') === 'negative' ? '#ff4d4d' : '#888') }}"
                             title="Sentiment: {{ ucfirst($item['sentiment'] ?? 'neutral') }}">
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 {{ ($item['seo_score'] ?? 0) >= 70 ? 'bg-emerald-500/10 border-emerald-500/20 shadow-[0_0_15px_rgba(52,211,153,0.1)]' : 'bg-white/5 border-white/5' }} px-2 py-1 rounded-lg border cursor-help transition-all" 
                         title="Ranking Opportunity: Calculated based on freshness, source authority, and current virality.">
                        <i class="fas fa-chart-line text-[9px] {{ ($item['seo_score'] ?? 0) >= 70 ? 'text-emerald-400' : 'text-primary-cyan' }}"></i>
                        <span class="text-[9px] font-bold opacity-70" style="color: {{ ($item['seo_score'] ?? 0) >= 70 ? 'rgba(52, 211, 153, 0.8)' : 'var(--text-muted)' }};">Score:</span>
                        <span class="text-[10px] font-black {{ ($item['seo_score'] ?? 0) >= 70 ? 'text-emerald-400 drop-shadow-[0_0_5px_rgba(52,211,153,0.5)]' : 'text-primary-blue' }}">
                            {{ $item['seo_score'] ?? 0 }}%
                        </span>
                    </div>
                </div>
                
                <h3 class="font-bold leading-relaxed mb-3 text-sm line-clamp-2 min-h-[3rem]" style="color: var(--text-main);">
                    {{ $item['title'] }}
                </h3>

                <!-- Entity/Keyword Tags -->
                @if(!empty($item['entities']))
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach($item['entities'] as $tag)
                        <span class="text-[9px] px-2 py-0.5 rounded-md bg-white/5 text-muted-foreground border border-white/5 hover:border-primary-cyan/30 transition-colors">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
                @endif

                <div class="mt-auto flex items-center justify-between pt-4 border-t" style="border-color: var(--glass-border);">
                    <div class="flex flex-col gap-1">
                        <a href="{{ $item['link'] }}" target="_blank" class="text-[11px] text-primary-cyan hover:text-primary-blue transition-colors flex items-center gap-1 font-bold no-underline mb-1">
                            <span>Read Source</span>
                            <i class="fas fa-external-link-alt text-[9px]"></i>
                        </a>
                        <span class="text-[10px] font-medium opacity-60 flex items-center gap-1" style="color: var(--text-muted);">
                            <i class="far fa-clock text-[8px]"></i> {{ \Carbon\Carbon::parse($item['pubDate'])->diffForHumans() }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="https://www.google.com/search?q={{ urlencode($item['title']) }}&gl={{ $region ?? 'US' }}&hl={{ $lang ?? 'en' }}" target="_blank" class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" title="Google Search">
                            <i class="fab fa-google"></i>
                        </a>
                        <button onclick="copyToClipboard('{{ addslashes($item['title']) }}')" class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" title="Copy Title">
                            <i class="fas fa-copy"></i>
                        </button>
                        <a href="{{ route('headlines.index') }}?keyword={{ urlencode($item['title']) }}" class="w-8 h-8 rounded-lg hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs shadow-lg shadow-primary-cyan/10" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-main);" title="Discover Headlines">
                            <i class="fas fa-bolt"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="glass-card p-16 text-center border-dashed" style="border-color: var(--glass-border);">
        <i class="fas fa-newspaper text-4xl mb-4 block" style="color: var(--text-muted); opacity: 0.5;"></i>
        <p class="font-bold" style="color: var(--text-muted);">No news available currently</p>
    </div>
@endif
