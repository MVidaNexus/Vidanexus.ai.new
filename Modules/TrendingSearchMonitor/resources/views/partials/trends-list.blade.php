@if(count($trends) > 0)
    @foreach($trends as $index => $trend)
        @php
            $rankClass = $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-default'));
        @endphp
        <div class="trend-card glass-card p-5 group/card">
            <div class="flex items-center gap-5">
                <div class="rank-badge {{ $rankClass }} flex-shrink-0">
                    {{ $index + 1 }}
                </div>

                @if($trend['image'])
                    <img src="{{ $trend['image'] }}" class="w-14 h-14 rounded-2xl object-cover border border-white/5 shadow-2xl" alt="{{ $trend['title'] }}">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center text-gray-600 border border-white/5">
                        <i class="fas fa-trending-up"></i>
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <a href="https://www.google.com/search?q={{ urlencode($trend['title']) }}&gl={{ $region }}" 
                           target="_blank" 
                           class="text-lg font-black text-white hover:text-primary-cyan transition-colors block break-words no-underline">
                            {{ $trend['title'] }}
                        </a>
                        @if(!empty($trend['traffic']))
                            <span class="px-2 py-0.5 bg-primary-cyan/10 text-primary-cyan text-[10px] border border-primary-cyan/20 rounded-lg font-bold">
                                {{ $trend['traffic'] }} search
                            </span>
                        @endif
                        
                        {{-- V2.0 Opportunity Meter --}}
                        @php $oppScore = app(\Modules\TrendingSearchMonitor\Services\TrendIntelligenceService::class)->getEstimatedScore($trend['title']); @endphp
                        <div class="flex items-center gap-2 ml-auto md:ml-0">
                             <div class="w-16 h-1.5 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                 <div class="h-full bg-gradient-to-r from-primary-cyan to-primary-purple" style="width: {{ $oppScore }}%"></div>
                             </div>
                             <span class="text-[9px] font-black {{ $oppScore > 60 ? 'text-primary-cyan' : 'text-gray-500' }} uppercase tracking-tighter">{{ $oppScore }}% ROI</span>
                        </div>
                    </div>
                    
                    @if(!empty($trend['news']))
                        <div class="mt-2 space-y-1.5">
                            @foreach($trend['news'] as $ni)
                                <div class="flex items-start gap-2 group/news">
                                    <i class="fas fa-newspaper text-[9px] mt-1 text-gray-600 group-hover/news:text-primary-cyan transition-colors"></i>
                                    <a href="{{ $ni['url'] }}" target="_blank" class="text-[11px] text-gray-400 hover:text-white transition-colors line-clamp-1 no-underline">
                                        <span class="font-bold text-gray-500">[{{ $ni['source'] }}]</span> {{ $ni['title'] }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-500 font-medium break-words mt-1">{{ $trend['subtitle'] ?? '' }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 transition-all duration-300">
                    {{-- V2.0 AI Deep Intel Button --}}
                    <button onclick="analyzeTrend('{{ addslashes($trend['title']) }}', '{{ $region }}', '{{ $currentCountry['lang'] ?? 'ar' }}', this)" 
                            class="w-10 h-10 rounded-xl bg-primary-cyan/10 border border-primary-cyan/20 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-primary-cyan"
                            title="AI Deep Intelligence">
                        <i class="fas fa-brain text-sm"></i>
                    </button>

                    <button onclick="copyTrend('{{ addslashes($trend['title']) }}', this)" 
                            class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all text-gray-400"
                            title="Copy Keyword">
                        <i class="fas fa-copy text-sm"></i>
                    </button>

                    <a href="{{ route('headlines.index') }}?keyword={{ urlencode($trend['title']) }}" 
                       class="w-10 h-10 rounded-xl bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-gray-400"
                       title="Generate Discover Headlines">
                        <i class="fas fa-bolt text-sm"></i>
                    </a>

                    <a href="{{ route('dashboard.article-writer.index') }}?keyword={{ urlencode($trend['title']) }}" target="_blank"
                       class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#a855f7] to-[#6366f1] hover:scale-110 flex items-center justify-center transition-all text-white shadow-lg"
                       title="Write with AI">
                        <i class="fas fa-pen-fancy text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="glass-card p-16 text-center border-dashed border-white/5">
        <i class="fas fa-search-minus text-4xl text-gray-700 mb-4 block"></i>
        <h3 class="text-xl font-bold text-gray-500">No data available currently</h3>
        <p class="text-gray-600 text-sm mt-2">Try selecting another region or refreshing later</p>
    </div>
@endif
