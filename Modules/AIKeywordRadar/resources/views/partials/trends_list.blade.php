@if(count($googleTrends) > 0)
    <div class="grid grid-cols-1 gap-3">
        @foreach($googleTrends as $index => $trend)
            <div class="flex items-center justify-between p-3.5 rounded-2xl border transition-all duration-300 group gap-3 keyword-row" style="background: var(--glass-bg); border-color: var(--glass-border);">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <input type="checkbox" 
                           x-model="selectedTrends" 
                           value="{{ $trend['title'] }}"
                           class="w-4 h-4 rounded border-white/10 bg-white/5 text-primary-cyan focus:ring-primary-cyan flex-shrink-0">
                           
                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-xl font-black text-xs shadow-sm keyword-num" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--primary-cyan);">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    
                    @if(!empty($trend['picture']))
                        <img src="{{ $trend['picture'] }}" class="w-10 h-10 rounded-xl object-cover border border-white/10" alt="{{ $trend['title'] }}">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center text-gray-600 border border-white/5">
                            <i class="fas fa-chart-line text-xs"></i>
                        </div>
                    @endif
                    
                    <div class="flex flex-col min-w-0">
                        <span class="font-bold text-xs sm:text-sm break-words" style="color: var(--text-main);">{{ $trend['title'] }}</span>
                        <div class="flex items-center gap-2.5 mt-0.5">
                            <span class="text-[8px] font-bold flex items-center gap-1" style="color: var(--text-main);">
                                <i class="fas fa-fire text-orange-500"></i>
                                {{ $trend['traffic'] }}
                            </span>
                            <span class="text-[8px] font-bold flex items-center gap-1" style="color: var(--text-muted);">
                                <i class="far fa-clock"></i>
                                @php
                                    $trendDate = \Carbon\Carbon::parse($trend['pubDate']);
                                @endphp
                                {{ $trendDate->isFuture() ? 'Now' : $trendDate->diffForHumans(null, true, false, 2) . ' ago' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <button onclick="copyToClipboard('{{ addslashes($trend['title']) }}')" class="w-8 h-8 flex items-center justify-center rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 keyword-action-btn" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-muted);" title="Copy">
                        <i class="far fa-copy text-[10px]"></i>
                    </button>
                    <a href="https://www.google.com/search?q={{ urlencode($trend['title']) }}&gl={{ $region ?? 'EG' }}" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-xl shadow-sm transition-all hover:scale-105 active:scale-95 keyword-action-btn" style="background: var(--card-bg); border: 1px solid var(--glass-border); color: var(--text-muted);" title="Google Search">
                        <i class="fab fa-google text-[10px]"></i>
                    </a>
                    <a href="{{ route('headlines.index') }}?keyword={{ urlencode($trend['title']) }}" class="vn-btn vn-btn-primary flex items-center gap-2" style="font-size: 0.65rem; padding: 0.4rem 0.8rem;" title="Discover AI">
                        <i class="fas fa-magic text-[10px]"></i>
                        <span class="hidden md:inline">Discover AI</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12">
        <i class="fas fa-search text-3xl text-gray-700 mb-3 block"></i>
        <p class="text-gray-500 text-sm">No current search trends for this region</p>
    </div>
@endif
