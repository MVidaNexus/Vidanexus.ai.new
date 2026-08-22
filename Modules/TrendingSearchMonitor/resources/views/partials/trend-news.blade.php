{{-- Related news block: up to 3 articles with featured image for the top story --}}
@php
    $newsItems = array_slice($trend['news'] ?? [], 0, 3);
    $featured = $newsItems[0] ?? null;
    $rest = array_slice($newsItems, 1);
    $featuredImg = $featured['image'] ?? ($trend['image'] ?? null);
@endphp

@if(!empty($newsItems))
    <div class="trend-news-section mt-4 pt-4 border-t border-white/5" data-news-articles='@json($newsItems)'>
        <div class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-3 flex items-center gap-2">
            <i class="fas fa-newspaper"></i> Related News ({{ count($newsItems) }})
        </div>

        @if($featured)
            <a href="{{ $featured['url'] }}" target="_blank" rel="noopener"
               class="news-featured block mb-3 rounded-xl overflow-hidden border border-white/5 bg-white/[0.02] hover:border-primary-cyan/30 transition-all group/feat no-underline">
                <div class="flex flex-col sm:flex-row">
                    <div class="sm:w-36 h-28 sm:h-auto flex-shrink-0 relative bg-white/5">
                        @if($featuredImg)
                            <img src="{{ $featuredImg }}" alt=""
                                 class="trend-news-img w-full h-full object-cover min-h-[7rem]"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        @endif
                        <div class="trend-img-fallback absolute inset-0 items-center justify-center {{ $featuredImg ? 'hidden' : 'flex' }}"
                             style="display:{{ $featuredImg ? 'none' : 'flex' }};">
                            <i class="fas fa-newspaper text-2xl text-gray-600"></i>
                        </div>
                    </div>
                    <div class="p-3 flex-1 min-w-0">
                        <span class="news-title-text text-sm font-bold text-white group-hover/feat:text-primary-cyan transition-colors line-clamp-2 block">
                            {{ $featured['title'] }}
                        </span>
                        @if(!empty($featured['snippet']))
                            <p class="text-[11px] text-gray-500 mt-1 line-clamp-2">{{ $featured['snippet'] }}</p>
                        @endif
                        <div class="text-[10px] text-gray-600 mt-2 flex flex-wrap items-center gap-2">
                            @if(!empty($featured['source']))
                                <span class="font-black uppercase">{{ $featured['source'] }}</span>
                            @endif
                            @if(!empty($featured['date']))
                                <span>{{ \Carbon\Carbon::parse($featured['date'])->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endif

        @if(count($rest) > 0)
            <ul class="space-y-2">
                @foreach($rest as $ni)
                    <li>
                        <a href="{{ $ni['url'] }}" target="_blank" rel="noopener"
                           class="news-link-item flex items-start gap-3 group/news no-underline">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-white/5 relative">
                                @if(!empty($ni['image']))
                                    <img src="{{ $ni['image'] }}" alt=""
                                         class="trend-news-img w-full h-full object-cover"
                                         loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="trend-img-fallback absolute inset-0 items-center justify-center {{ !empty($ni['image']) ? 'hidden' : 'flex' }}"
                                     style="display:{{ !empty($ni['image']) ? 'none' : 'flex' }};">
                                    <i class="fas fa-file-alt text-[10px] text-gray-600"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="news-title-text text-[11px] text-gray-400 font-medium group-hover/news:text-primary-cyan transition-colors line-clamp-2 block">
                                    {{ $ni['title'] }}
                                </span>
                                <div class="text-[10px] text-gray-600 flex flex-wrap items-center gap-2 mt-0.5">
                                    @if(!empty($ni['source']))
                                        <span class="font-black uppercase">{{ $ni['source'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-external-link-alt text-[9px] text-gray-700 group-hover/news:text-primary-cyan opacity-0 group-hover/news:opacity-100 transition-all mt-1"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@else
    <p class="text-xs text-gray-500 font-medium break-words mt-1">{{ $trend['subtitle'] ?? '' }}</p>
@endif
