{{-- Related news block: up to 3 articles with featured image for the top story --}}
@php
    $newsItems = array_slice($trend['news'] ?? [], 0, 3);
    $featured = $newsItems[0] ?? null;
    $rest = array_slice($newsItems, 1);
    $featuredImg = $featured['image'] ?? ($trend['image'] ?? null);
@endphp

@if(!empty($newsItems))
    <div class="trend-news-section mt-4 pt-4 border-t border-white/10" data-news-articles='@json($newsItems)'>
        <div class="text-[11px] font-black text-primary-cyan uppercase tracking-widest mb-3 flex items-center gap-2">
            <i class="fas fa-newspaper"></i> Related News ({{ count($newsItems) }})
        </div>

        @if($featured)
            <a href="{{ $featured['url'] }}" target="_blank" rel="noopener"
               class="news-featured block mb-3 rounded-xl overflow-hidden border border-white/10 bg-white/[0.04] hover:border-primary-cyan/40 transition-all group/feat no-underline">
                <div class="flex flex-col sm:flex-row">
                    <div class="sm:w-36 h-28 sm:h-auto flex-shrink-0 relative bg-white/10">
                        @if($featuredImg)
                            <img src="{{ $featuredImg }}" alt=""
                                 class="trend-news-img w-full h-full object-cover min-h-[7rem]"
                                 loading="lazy"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        @endif
                        <div class="trend-img-fallback absolute inset-0 items-center justify-center {{ $featuredImg ? 'hidden' : 'flex' }}"
                             style="display:{{ $featuredImg ? 'none' : 'flex' }};">
                            <i class="fas fa-newspaper text-2xl text-slate-400"></i>
                        </div>
                    </div>
                    <div class="p-3.5 flex-1 min-w-0">
                        <span class="news-title-text text-sm font-bold text-white group-hover/feat:text-primary-cyan transition-colors line-clamp-2 block leading-snug">
                            {{ $featured['title'] }}
                        </span>
                        @if(!empty($featured['snippet']))
                            <p class="text-[12px] text-slate-300 mt-1.5 line-clamp-2 leading-relaxed">{{ $featured['snippet'] }}</p>
                        @endif
                        <div class="text-[11px] text-primary-cyan font-bold mt-2 flex flex-wrap items-center gap-2">
                            @if(!empty($featured['source']))
                                <span class="uppercase px-2 py-0.5 rounded bg-primary-cyan/10 border border-primary-cyan/20">{{ $featured['source'] }}</span>
                            @endif
                            @if(!empty($featured['date']))
                                <span class="text-slate-400 font-normal">{{ \Carbon\Carbon::parse($featured['date'])->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @endif

        @if(count($rest) > 0)
            <ul class="space-y-2.5">
                @foreach($rest as $ni)
                    <li>
                        <a href="{{ $ni['url'] }}" target="_blank" rel="noopener"
                           class="news-link-item flex items-start gap-3 p-2 rounded-xl bg-white/[0.02] hover:bg-white/[0.06] border border-white/5 hover:border-white/15 transition-all group/news no-underline">
                            <div class="w-11 h-11 rounded-lg overflow-hidden flex-shrink-0 bg-white/10 relative">
                                @if(!empty($ni['image']))
                                    <img src="{{ $ni['image'] }}" alt=""
                                         class="trend-news-img w-full h-full object-cover"
                                         loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                @endif
                                <div class="trend-img-fallback absolute inset-0 items-center justify-center {{ !empty($ni['image']) ? 'hidden' : 'flex' }}"
                                     style="display:{{ !empty($ni['image']) ? 'none' : 'flex' }};">
                                    <i class="fas fa-file-alt text-xs text-slate-400"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="news-title-text text-[13px] text-slate-100 font-medium group-hover/news:text-primary-cyan transition-colors line-clamp-2 block leading-snug">
                                    {{ $ni['title'] }}
                                </span>
                                <div class="text-[11px] text-primary-cyan font-bold flex flex-wrap items-center gap-2 mt-1">
                                    @if(!empty($ni['source']))
                                        <span class="uppercase text-[10px] px-1.5 py-0.5 rounded bg-primary-cyan/10 border border-primary-cyan/20">{{ $ni['source'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <i class="fas fa-external-link-alt text-[10px] text-slate-400 group-hover/news:text-primary-cyan opacity-0 group-hover/news:opacity-100 transition-all mt-1"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@else
    <p class="text-xs text-slate-300 font-medium break-words mt-1">{{ $trend['subtitle'] ?? '' }}</p>
@endif
