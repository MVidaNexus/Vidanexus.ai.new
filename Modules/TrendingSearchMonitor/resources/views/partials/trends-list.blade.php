@if(count($trends) > 0)
    @foreach($trends as $index => $trend)
        @php
            $rankClass = $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-default'));
            $newsItems = array_slice($trend['news'] ?? [], 0, 3);
            $featuredImg = $trend['image'] ?? ($newsItems[0]['image'] ?? null);
        @endphp
        <div class="trend-card glass-card p-5 group/card" data-trend-title="{{ $trend['title'] }}">
            <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <label class="trend-select-label flex-shrink-0" style="display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);cursor:pointer;margin-top:4px;">
                        <input type="checkbox" class="trend-select-checkbox" value="{{ $trend['title'] }}" style="accent-color:#0ea5e9;cursor:pointer;">
                    </label>
                    <div class="rank-badge {{ $rankClass }} flex-shrink-0">
                        {{ $index + 1 }}
                    </div>

                    <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0 border border-white/5 shadow-2xl relative bg-white/5">
                        @if($featuredImg)
                            <img src="{{ $featuredImg }}" alt="{{ $trend['title'] }}"
                                 class="trend-thumb-img w-full h-full object-cover"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        @endif
                        <div class="trend-img-fallback absolute inset-0 items-center justify-center {{ $featuredImg ? 'hidden' : 'flex' }}"
                             style="display:{{ $featuredImg ? 'none' : 'flex' }};">
                            <i class="fas fa-trending-up text-gray-600"></i>
                        </div>
                    </div>

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

                            @php $oppScore = app(\Modules\TrendingSearchMonitor\Services\TrendIntelligenceService::class)->getEstimatedScore($trend['title']); @endphp
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-white/5 rounded-full overflow-hidden border border-white/5">
                                    <div class="h-full bg-gradient-to-r from-primary-cyan to-primary-purple" style="width: {{ $oppScore }}%"></div>
                                </div>
                                <span class="text-[9px] font-black {{ $oppScore > 60 ? 'text-primary-cyan' : 'text-gray-500' }} uppercase tracking-tighter">{{ $oppScore }}% ROI</span>
                            </div>
                        </div>

                        @include('trendingsearchmonitor::partials.trend-news', ['trend' => $trend])
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0 self-start lg:self-center pl-9 lg:pl-0">
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
