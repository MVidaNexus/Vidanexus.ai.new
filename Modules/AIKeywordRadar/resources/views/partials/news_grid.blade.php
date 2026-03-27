@if(count($googleNews) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($googleNews as $index => $item)
            <div class="news-card glass-card p-5 flex flex-col group/news hover:bg-white/5 transition-all">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-primary-cyan/10 text-primary-cyan font-bold uppercase tracking-wider">
                        {{ $item['source'] }}
                    </span>
                    <span class="text-[10px] text-gray-500 font-medium">
                        <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($item['pubDate'])->diffForHumans() }}
                    </span>
                </div>
                
                <h3 class="text-white font-bold leading-relaxed mb-4 text-sm line-clamp-2 min-h-[3rem]">
                    {{ $item['title'] }}
                </h3>

                <div class="mt-auto flex items-center justify-between pt-4 border-t border-white/5">
                    <a href="{{ $item['link'] }}" target="_blank" class="text-[11px] text-primary-cyan hover:text-white transition-colors flex items-center gap-1 font-bold no-underline">
                        <span>Read Source</span>
                        <i class="fas fa-external-link-alt text-[9px]"></i>
                    </a>
                    
                    <div class="flex items-center gap-2">
                        <button onclick="copyToClipboard('{{ addslashes($item['title']) }}')" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs">
                            <i class="fas fa-copy"></i>
                        </button>
                        <a href="{{ route('headlines.index') }}?keyword={{ urlencode($item['title']) }}" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-primary-cyan hover:text-black flex items-center justify-center transition-all text-xs" title="Discover AI">
                            <i class="fas fa-bolt"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="glass-card p-16 text-center border-dashed border-white/10">
        <i class="fas fa-newspaper text-4xl text-gray-700 mb-4 block"></i>
        <p class="text-gray-500 font-bold">No news currently available</p>
    </div>
@endif
