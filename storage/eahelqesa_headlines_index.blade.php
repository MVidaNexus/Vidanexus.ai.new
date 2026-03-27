@extends('dashboard.layouts.app')

@section('title', 'عناوين Google Discover')
@section('page-title', 'عناوين Google Discover')

@section('content')
{{-- Progress Overlay --}}
<div id="generation-progress-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300" id="progress-card">
        <div class="text-center space-y-6">
            <div class="relative w-24 h-24 mx-auto">
                {{-- Circular Progress SVG --}}
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100" />
                    <circle id="progress-circle" cx="48" cy="48" r="44" stroke="currentColor" stroke-width="8" fill="transparent" 
                            class="text-blue-600 transition-all duration-700 ease-out" 
                            stroke-dasharray="276.46" stroke-dashoffset="276.46" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fas fa-rocket text-3xl text-blue-600 animate-pulse"></i>
                </div>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-800" id="progress-title">جاري البدء...</h3>
                <p class="text-gray-500 text-sm font-medium h-4" id="progress-message">يتم الآن التحضير...</p>
            </div>

            <div class="pt-4">
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">
                        <span>Progress</span>
                        <span id="progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 w-0 transition-all duration-700 ease-out"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-2 sm:px-4 font-tajawal" dir="rtl" x-data="headlineGenerator()">
    {{-- Tabs Navigation --}}
    <div class="flex flex-wrap items-center gap-2 mb-6 sm:mb-8 bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 w-full sm:w-fit">
        <a href="{{ route('dashboard.headlines.index') }}" 
           class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 bg-blue-600 text-white shadow-lg shadow-blue-200">
            <i class="fas fa-magic"></i> المولد الذكي
        </a>
        @if(auth('admin')->user()->hasPermission('manage_headline_settings'))
        <a href="{{ route('dashboard.headlines.settings') }}" 
           class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl text-sm font-bold transition flex items-center justify-center gap-2 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-cog"></i> الإعدادات
        </a>
        @endif
    </div>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden mb-8">
        @if(!$hasKey && auth('admin')->user()->hasPermission('manage_headline_settings'))
        <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3 text-red-700 text-sm font-bold font-cairo">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span>تنبيه: مفتاح الـ API غير مضبوط. لن تتمكن من التوليد.</span>
            </div>
            <a href="{{ route('dashboard.headlines.settings') }}" class="text-xs bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition text-center">
                ضبط الآن
            </a>
        </div>
        @endif

        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6 sm:mb-8">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200 shrink-0">
                    <i class="fas fa-magic text-xl"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 font-cairo">مولد عناوين Discover الذكي</h2>
                    <p class="text-gray-500 text-xs sm:text-sm mt-1">أنشئ عناوين إبداعية متوافقة مع معايير Google Discover باستخدام قوة الذكاء الاصطناعي وأحدث الأخبار.</p>
                </div>
            </div>

            <div class="flex items-center gap-1 mb-6 bg-gray-50 p-1 rounded-xl w-fit">
                <button @click="mode = 'keyword'; results = null; emptyState = false;" 
                        :class="mode === 'keyword' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 rounded-lg text-xs font-black transition-all duration-200">
                    📰 بحث بالكلمات الإخبارية
                </button>
                <button @click="mode = 'content'; results = null; emptyState = false;" 
                        :class="mode === 'content' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 rounded-lg text-xs font-black transition-all duration-200">
                    📝 من عنوان أو محتوى
                </button>
            </div>

            <div class="space-y-4 sm:space-y-6">
                {{-- Keyword Mode Input --}}
                <div class="relative group" x-show="mode === 'keyword'">
                    <input type="text" x-model="keyword" @keydown.enter="generate()"
                           class="w-full px-5 sm:px-6 py-4 bg-gray-50 border-2 border-transparent focus:border-blue-500 focus:bg-white rounded-2xl outline-none transition font-cairo text-base sm:text-lg shadow-inner text-gray-800"
                           placeholder="مثال: أسعار الذهب اليوم، أخبار التعليم...">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-6 pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                {{-- Trending Suggestions Chips --}}
                @if(!empty($trendingSuggestions ?? []))
                <div x-show="mode === 'keyword'" class="space-y-2">
                    <p class="text-[11px] font-bold text-gray-400 flex items-center gap-1.5 px-1">
                        <i class="fas fa-fire text-orange-500"></i> المقترحات الشائعة في {{ $currentCountry['name'] ?? 'هذه المنطقة' }}:
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($trendingSuggestions as $suggestion)
                        <button @click="keyword = @js($suggestion['keyword']); generate()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 border border-gray-100 text-gray-600 rounded-xl text-[11px] font-bold hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100 transition-all duration-200">
                            <span>{{ $suggestion['keyword'] }}</span>
                            @if(!empty($suggestion['traffic']))
                            <span class="text-gray-400 text-[9px]">{{ $suggestion['traffic'] }}</span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Content Mode Input --}}
                <div class="space-y-2" x-show="mode === 'content'" x-cloak>
                    <textarea x-model="content" 
                              class="w-full px-5 sm:px-6 py-4 bg-gray-50 border-2 border-transparent focus:border-blue-500 focus:bg-white rounded-2xl outline-none transition font-cairo text-base shadow-inner min-h-[180px] text-gray-800"
                              placeholder="أدخل النص أو المقال هنا ليتم استخراج العناوين منه..."></textarea>
                </div>

                <div class="flex justify-end pt-2">
                    <button @click="generate()" :disabled="loading || (mode === 'keyword' ? !keyword : !content)"
                            class="w-full sm:w-auto px-10 py-4 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 transition shadow-xl shadow-blue-100 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed min-w-[180px]">
                        <template x-if="!loading">
                            <i class="fas fa-rocket"></i>
                        </template>
                        <template x-if="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                        </template>
                        <span x-text="loading ? 'جاري التحليل...' : (mode === 'keyword' ? 'توليد العناوين' : 'استخراج العناوين')"></span>
                    </button>
                </div>
            </div>

        </div>
        
        {{-- Guidelines Footer --}}
        <div class="px-6 sm:px-8 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between text-[10px] sm:text-xs text-gray-500 gap-3">
            <div class="flex flex-wrap justify-center gap-4">
                <span class="flex items-center gap-1.5"><i class="fas fa-check text-green-500"></i> متوافق مع سياسات جوجل</span>
                <span class="flex items-center gap-1.5"><i class="fas fa-check text-green-500"></i> خالي من النقرات المضللة</span>
            </div>
            <div class="font-bold text-gray-400">
                يعتمد على أحدث الأخبار الحقيقية
            </div>
        </div>
    </div>

    {{-- Results Section --}}
    <div id="results-section-anchor" x-show="results" x-cloak class="space-y-4 sm:space-y-6 animate-fade-in pb-12">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-sm sm:text-lg font-bold text-gray-800 font-cairo flex items-center gap-2">
                <i class="fas fa-list-ul text-blue-600"></i>
                المقترحات لـ: <span class="text-blue-600 font-black" x-text="mode === 'keyword' ? '«' + resultKeyword + '»' : 'المحتوى المقدم'"></span>
            </h3>
            <div class="flex items-center gap-2">
                <button @click="generate()" class="p-2 text-gray-400 hover:text-blue-600 transition rounded-lg hover:bg-blue-50" title="إعادة التوليد">
                    <i class="fas fa-redo"></i>
                </button>
                <button @click="results = null;" class="p-2 text-gray-400 hover:text-red-500 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <template x-for="(headline, index) in parsedHeadlines" :key="index">
                <div class="bg-white p-5 sm:p-6 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-gray-100 transition-all duration-300 group">
                    <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
                        <div class="flex-1 w-full">
                            {{-- Tags Row --}}
                            <div class="flex items-center gap-2 mb-3 flex-wrap">
                                <span class="text-[9px] font-black px-2.5 py-1 rounded-lg border"
                                      :class="getBadgeClass(headline.tag)" 
                                      x-show="headline.tag" 
                                      x-text="headline.tag"></span>
                                <span class="text-[9px] font-black px-2.5 py-1 bg-gray-100 text-gray-500 rounded-lg border border-gray-200" x-text="'الخيار ' + (index + 1)"></span>
                                
                                {{-- Score Badge --}}
                                <template x-if="headline.score !== undefined">
                                    <span class="text-[9px] font-black px-2.5 py-1 rounded-lg border inline-flex items-center gap-1"
                                          :class="{
                                              'bg-green-100 text-green-700 border-green-200': headline.grade?.color === 'green',
                                              'bg-blue-100 text-blue-700 border-blue-200': headline.grade?.color === 'blue',
                                              'bg-yellow-100 text-yellow-700 border-yellow-200': headline.grade?.color === 'yellow',
                                              'bg-red-100 text-red-700 border-red-200': headline.grade?.color === 'red',
                                          }">
                                        <span x-text="headline.grade?.emoji"></span>
                                        <span x-text="headline.grade?.label"></span>
                                        <span x-text="headline.score + '%'"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Headline Text --}}
                            <p class="text-gray-900 font-bold text-base sm:text-lg leading-relaxed mb-3 group-hover:text-blue-700 transition-colors" x-text="headline.text"></p>
                            
                            {{-- Score Progress Bar --}}
                            <template x-if="headline.score !== undefined">
                                <div class="mb-3">
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-700 ease-out rounded-full"
                                             :class="{
                                                 'bg-green-500': headline.score >= 80,
                                                 'bg-blue-500': headline.score >= 60 && headline.score < 80,
                                                 'bg-yellow-500': headline.score >= 40 && headline.score < 60,
                                                 'bg-red-500': headline.score < 40,
                                             }"
                                             :style="'width: ' + headline.score + '%'"></div>
                                    </div>
                                </div>
                            </template>

                            {{-- Feedback Tags --}}
                            <template x-if="headline.feedback && headline.feedback.length > 0">
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    <template x-for="(fb, fbIdx) in headline.feedback" :key="fbIdx">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg inline-flex items-center gap-0.5"
                                              :class="{
                                                  'bg-green-50 text-green-700': fb.type === 'success',
                                                  'bg-blue-50 text-blue-600': fb.type === 'info',
                                                  'bg-yellow-50 text-yellow-700': fb.type === 'warning',
                                                  'bg-red-50 text-red-600': fb.type === 'danger',
                                              }"
                                              x-text="(fb.type === 'success' ? '✓ ' : fb.type === 'danger' ? '✗ ' : fb.type === 'warning' ? '⚠ ' : 'ℹ ') + fb.text"></span>
                                    </template>
                                </div>
                            </template>

                            {{-- Generation Actions --}}
                            <div class="mt-4 pt-4 border-t border-dashed border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <select @change="headline.categoryId = $event.target.value" 
                                        class="bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer flex-1 sm:max-w-[220px] shadow-sm">
                                    <option value="">اختر القسم للمقال...</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                 <button @click="generateArticle(headline, $event.target)" 
                                         :disabled="!headline.categoryId || headline.generating"
                                         class="px-6 py-2.5 bg-green-600 text-white text-xs font-black rounded-xl hover:bg-green-700 transition shadow-lg shadow-green-100 flex items-center justify-center gap-2 disabled:opacity-50 disabled:bg-gray-400 animate-fade-in">
                                    <i class="fas fa-rocket" x-show="!headline.generating"></i>
                                    <i class="fas fa-sync fa-spin" x-show="headline.generating"></i>
                                    <span>توليد المقال الآن</span>
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-row sm:flex-col gap-2 w-full sm:w-auto mt-4 sm:mt-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                            <button @click="copyToClipboard(headline.text)" 
                                    class="flex-1 sm:flex-none p-4 bg-gray-50 text-gray-400 rounded-2xl hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:scale-105 active:scale-95" title="نسخ العنوان">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a :href="'https://www.google.com/search?q=' + encodeURIComponent(headline.text)" 
                               target="_blank"
                               class="flex-1 sm:flex-none p-4 bg-gray-50 text-gray-400 rounded-2xl hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:scale-105 active:scale-95 text-center" title="بحث في جوجل">
                                <i class="fab fa-google"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Fallback: Show raw text if parsing failed but we have data -->
            <div x-show="results && (parsedHeadlines.length === 0)" class="bg-amber-50 border-2 border-dashed border-amber-200 rounded-3xl p-8 text-center animate-fade-in">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-amber-800 mb-2 font-cairo">تم التوليد ولكن بتنسيق مختلف</h3>
                <p class="text-amber-700 mb-6">لقد نجح النظام في إنتاج النتائج، لكنها تتطلب مراجعة يدوية:</p>
                <div class="bg-white rounded-2xl p-6 text-right font-mono text-sm leading-relaxed border border-amber-100 whitespace-pre-wrap mb-6" x-text="results"></div>
                <button @click="location.reload()" class="px-6 py-3 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-colors font-bold">
                    <i class="fas fa-sync-alt ml-1"></i> تحديث الصفحة
                </button>
            </div>

        </div>

    </div>

    {{-- Empty State --}}
    <div x-show="emptyState" x-cloak class="bg-orange-50 border border-orange-200 rounded-2xl p-8 text-center space-y-4">
        <div class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mx-auto text-2xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="text-xl font-bold text-orange-800 font-cairo">لا توجد أخبار كافية</h3>
        <p class="text-orange-700" x-text="emptyMessage"></p>
        <div class="flex flex-wrap gap-2 justify-center mt-4">
            <button @click="emptyState = false; keyword = ''" class="text-orange-600 font-bold hover:underline text-sm">حاول بكلمة أخرى</button>
            <span class="text-orange-300">|</span>
            <button @click="mode = 'content'; emptyState = false;" class="text-blue-600 font-bold hover:underline text-sm">جرّب وضع المحتوى</button>
        </div>
    </div>

    {{-- Error State --}}
    <div x-show="error" x-cloak class="bg-red-50/50 border border-red-100 rounded-[2.5rem] p-8 sm:p-12 text-center space-y-6 animate-fade-in">
        <div class="w-20 h-20 bg-red-100 text-red-600 rounded-3xl flex items-center justify-center mx-auto text-3xl rotate-12 group-hover:rotate-0 transition-transform">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="max-w-md mx-auto">
            <h3 class="text-xl sm:text-2xl font-black text-red-900 font-cairo mb-2">عذراً، فشل التوليد الذكي</h3>
            <p class="text-red-700 font-medium leading-relaxed" x-text="errorMessage"></p>
        </div>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button @click="generate()" class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-red-200 flex items-center gap-2">
                <i class="fas fa-redo"></i>
                إعادة المحاولة
            </button>
            <button @click="error = false" class="px-8 py-3 bg-white border border-red-200 text-red-600 rounded-2xl font-bold hover:bg-red-50 transition-all">
                إغلاق
            </button>
        </div>
    </div>
</div>


{{-- Hidden Form (direct POST to _self - bypasses ALL AJAX/WAF/Fetch/iframe issues) --}}
<div style="display:none">
    <form id="headline-trigger-form" method="POST" action="{{ route('dashboard.headlines.generate') }}">
        @csrf
        <input type="hidden" name="type" id="form-trigger-type">
        <input type="hidden" name="keyword" id="form-trigger-keyword">
        <input type="hidden" name="content" id="form-trigger-content">
        <input type="hidden" name="progress_id" id="form-trigger-progress-id">
    </form>
</div>

{{-- Safe data: base64-encoded to survive HTML minification --}}
<meta id="hl-server-data" content="{{ base64_encode(json_encode([
    'results'         => $headlineResults ?? null,
    'keyword'         => $headlineKeyword ?? '',
    'scored'          => $scoredHeadlines ?? [],
    'emptyState'      => !empty($headlineEmpty ?? null),
    'emptyMessage'    => $headlineEmpty ?? '',
    'hasError'        => !empty($headlineError ?? null),
    'errorMessage'    => $headlineError ?? '',
    'activeProgressId'=> $activeProgressId ?? null,
    'prefilledKeyword'=> $prefilledKeyword ?? '',
    'mode'            => request()->input('type', 'keyword'),
], JSON_UNESCAPED_UNICODE) ) }}">

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    function headlineGenerator() {
        // Read server data from base64-encoded meta tag (survives HTML minification)
        // Uses TextDecoder to correctly handle UTF-8 Arabic characters (atob alone is Latin-1 only)
        const _sd = (() => {
            try {
                const meta = document.getElementById('hl-server-data');
                if (!meta) return {};
                const b64 = meta.getAttribute('content');
                if (!b64) return {};
                // UTF-8 safe base64 decode
                const binary = atob(b64);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                const jsonStr = new TextDecoder('utf-8').decode(bytes);
                return JSON.parse(jsonStr);
            } catch(e) { console.error('[Headlines] Failed to parse server data:', e); return {}; }
        })();

        return {
            keyword: _sd.prefilledKeyword || '',
            mode: _sd.mode || 'keyword',
            content: '',
            originalContent: '',
            loading: false,
            results: _sd.results || null,
            resultKeyword: _sd.keyword || '',
            parsedHeadlines: [],
            emptyState: _sd.emptyState || false,
            emptyMessage: _sd.emptyMessage || '',
            error: _sd.hasError || false,
            errorMessage: _sd.errorMessage || '',
            _serverScored: _sd.scored || [],
            progressId: null,
            pInterval: null,

            init() {
                // Auto-display results from session if available
                if (this.results) {
                    this.loading = false;
                    if (this._serverScored && this._serverScored.length > 0) {
                        this.parsedHeadlines = this._serverScored.map((s) => ({
                            text: s.headline,
                            tag: '',
                            generating: false,
                            categoryId: '',
                            score: s.score,
                            grade: s.grade,
                            feedback: s.feedback || [],
                        }));
                    } else {
                        this.parseHeadlines(this.results);
                    }
                    setTimeout(() => {
                        const el = document.getElementById('results-section-anchor');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 300);
                }

                // Resume polling if generation was in progress
                const activeId = _sd.activeProgressId;
                if (activeId && !this.results) {
                    this.progressId = activeId;
                    this.loading = true;
                    this.startPolling(activeId);
                }
            },

            startPolling(pid) {
                if (this.pInterval) clearInterval(this.pInterval);

                this.pInterval = setInterval(() => {
                    const pollUrl = "{{ route('dashboard.headlines.generate-progress', ['id' => ':id']) }}".replace(':id', pid);
                    fetch(pollUrl)
                        .then(r => r.json())
                        .then(data => {
                            if (!data || !data.stage) return;
                            this.updateProgressUI(data);

                            if (data.stage === 'completed') {
                                clearInterval(this.pInterval);
                                if (data.headlines) {
                                    this.handleResults({
                                        status: 'success',
                                        headlines: data.headlines,
                                        scored: data.scored,
                                        keyword: data.keyword
                                    });
                                } else {
                                    location.reload();
                                }
                                return;
                            }

                            if (data.stage === 'error') {
                                clearInterval(this.pInterval);
                                this.loading = false;
                                this.error = true;
                                this.errorMessage = data.message || "حدث خطأ أثناء التوليد.";
                                this.hideProgressOverlay();
                            }
                        }).catch(e => {
                            console.error('[Headlines] Polling error:', e.message);
                        });
                }, 1500);
            },

            updateProgressUI(data) {
                const stages = {
                    'starting': { p: 10, t: 'جاري البدء...' },
                    'searching': { p: 30, t: 'جاري البحث...' },
                    'ai_processing': { p: 60, t: 'جاري التوليد...' },
                    'scoring': { p: 85, t: 'جاري التحليل...' },
                    'completed': { p: 100, t: 'تم بنجاح!' }
                };

                const info = stages[data.stage] || { p: 5, t: 'جاري التوليد...' };
                const titleEl = document.getElementById('progress-title');
                const msgEl = document.getElementById('progress-message');
                const barEl = document.getElementById('progress-bar');
                const percentEl = document.getElementById('progress-percent');
                const circleEl = document.getElementById('progress-circle');

                if (titleEl) titleEl.innerText = info.t;
                if (msgEl) msgEl.innerText = data.message;
                if (barEl) barEl.style.width = info.p + '%';
                if (percentEl) percentEl.innerText = info.p + '%';
                
                if (circleEl) {
                    const offset = 276.46 - (276.46 * info.p / 100);
                    circleEl.style.strokeDashoffset = offset;
                }
            },

            hideProgressOverlay() {
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.replace('opacity-100', 'opacity-0');
                    setTimeout(() => overlay.classList.add('hidden'), 300);
                }
            },

            handleResults(data) {
                this.results = data.headlines || data.results;
                this.resultKeyword = data.keyword || this.keyword;
                this._serverScored = data.scored || [];

                if (this._serverScored && this._serverScored.length > 0) {
                    this.parsedHeadlines = this._serverScored.map((s) => ({
                        text: s.headline,
                        tag: '',
                        generating: false,
                        categoryId: '',
                        score: s.score,
                        grade: s.grade,
                        feedback: s.feedback || [],
                    }));
                } else {
                    this.parseHeadlines(this.results);
                }

                const count = this.parsedHeadlines.length;
                this.loading = false;
                this.emptyState = (data.status === 'empty' || count === 0);
                if (this.emptyState) {
                    this.emptyMessage = data.message || "لم نتمكن من العثور على عناوين صالحة.";
                }

                if (this.pInterval) clearInterval(this.pInterval);
                this.hideProgressOverlay();

                // Sync URL with the result keyword
                if (this.resultKeyword) {
                    const url = new URL(window.location);
                    url.searchParams.set('keyword', this.resultKeyword);
                    window.history.pushState({}, '', url);
                }

                setTimeout(() => {
                    const el = document.getElementById('results-section-anchor');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 400);
            },

            async generate() {
                if (this.mode === 'keyword' && !this.keyword) return;
                if (this.mode === 'content' && !this.content) return;
                if (this.loading) return;
                
                const pid = 'hl_gen_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.progressId = pid;
                this.loading = true;
                this.error = false;
                this.results = null;
                this.parsedHeadlines = [];

                // Show Overlay
                const overlay = document.getElementById('generation-progress-overlay');
                if (overlay) {
                    overlay.classList.remove('hidden');
                    setTimeout(() => { overlay.classList.replace('opacity-0', 'opacity-100'); }, 10);
                }

                // Start polling for progress UI updates only
                this.startPolling(pid);

                // Main AJAX request
                try {
                    const formData = new URLSearchParams();
                    formData.append('type', this.mode);
                    formData.append('keyword', this.keyword || '');
                    formData.append('content', this.content || '');
                    formData.append('progress_id', pid);

                    const response = await fetch("{{ route('dashboard.headlines.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    // Stop polling — AJAX is done
                    if (this.pInterval) { clearInterval(this.pInterval); this.pInterval = null; }

                    if (!response.ok) {
                        throw new Error('Server Error ' + response.status);
                    }

                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        // Server returned HTML (redirect/WAF) — fall through to form fallback
                        throw new Error('Non-JSON response received');
                    }

                    const data = await response.json();
                    if (data.status === 'success') {
                        this.handleResults(data);
                    } else {
                        this.loading = false;
                        this.error = true;
                        this.errorMessage = data.message || 'حدث خطأ أثناء التوليد.';
                        this.hideProgressOverlay();
                    }

                } catch (e) {
                    console.error('[Headlines] AJAX failed:', e.message);
                    if (this.pInterval) { clearInterval(this.pInterval); this.pInterval = null; }

                    // FALLBACK: Form submission (page reload with session results)
                    const form = document.getElementById('headline-trigger-form');
                    if (form) {
                        document.getElementById('form-trigger-type').value = this.mode;
                        document.getElementById('form-trigger-keyword').value = this.keyword;
                        document.getElementById('form-trigger-content').value = this.content;
                        document.getElementById('form-trigger-progress-id').value = pid;
                        form.submit();
                    } else {
                        this.loading = false;
                        this.error = true;
                        this.errorMessage = 'فشل في الاتصال بالخادم: ' + e.message;
                        this.hideProgressOverlay();
                    }
                }
            },

            parseHeadlines(rawText) {
                if (!rawText) return;
                
                // CRITICAL: Strip Unicode line/paragraph separators (U+2028, U+2029) that cause splits inside Arabic words
                rawText = rawText.replace(/[\u2028\u2029]/g, '');

                const lines = rawText.split(/\r?\n/).map(l => l.trim()).filter(line => {
                    if (line.length < 5) return false;
                    const metadataPrefixes = ['ملاحظة:', 'تنبيه:', 'ملخص:', 'المصدر:', 'note:', 'summary:', 'source:'];
                    if (metadataPrefixes.some(p => line.toLowerCase().startsWith(p))) return false;
                    if (line.match(/^[\*\-\s]+$/)) return false;
                    return true;
                });

                this.parsedHeadlines = lines.map(line => {
                    // SAFE cleaning: Only strip CLEARLY numbered prefixes and markdown
                    // Step 1: Remove numbered prefixes like "1. ", "1) ", "- ", "* "
                    let text = line.replace(/^\s*\d{1,2}[\.\)]\s+/, '')  // "1. " or "2) "
                                   .replace(/^\s*[\-\*•]\s+/, '')         // "- " or "* " or "• "
                                   .trim();
                    
                    // Step 2: Remove markdown bold/backticks only (NOT dots, colons, or brackets)
                    text = text.replace(/\*\*/g, '').replace(/`/g, '').trim();

                    let tag = '';
                    const tags = ['[Discover Best]', '[Neutral]', '[News-Focused]', '[أفضل للاكتشاف]', '[محايد]', '[متمركز حول الخبر]', '[تقرير]', '[تحليل]', '[حصري]', '[فيديو]'];
                    for (const t of tags) {
                        if (text.includes(t)) {
                            tag = t.replace(/[\[\]]/g, '');
                            text = text.replace(t, '').trim();
                            break;
                        }
                    }
                    
                    return { text, tag, generating: false, categoryId: '', score: undefined, grade: null, feedback: [] };
                });
            },

            async generateArticle(headline, btn) {
                if (!headline.categoryId || headline.generating) return;

                const progressId = 'gen_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                headline.generating = true;

                // UI Elements for Progress
                const overlay = document.getElementById('generation-progress-overlay');
                const cardEl = document.getElementById('progress-card');
                const titleEl = document.getElementById('progress-title');
                const msgEl = document.getElementById('progress-message');
                const barEl = document.getElementById('progress-bar');
                const percentEl = document.getElementById('progress-percent');
                const circleEl = document.getElementById('progress-circle');

                // Reset overlay defaults for Article generation
                titleEl.innerText = 'جاري التوليد...';
                msgEl.innerText = 'يتم الآن تحضير المقال بالكامل...';

                // Reset UI
                circleEl.classList.remove('text-green-500');
                circleEl.classList.add('text-blue-600');
                barEl.classList.remove('bg-green-500');
                barEl.classList.add('bg-gradient-to-r');
                barEl.style.width = '0%';
                percentEl.innerText = '0%';
                circleEl.style.strokeDashoffset = '276.46';

                // Show Overlay
                overlay.classList.remove('hidden');
                setTimeout(() => {
                    overlay.classList.replace('opacity-0', 'opacity-100');
                    cardEl.classList.replace('scale-95', 'scale-100');
                }, 10);

                const stages = {
                    'starting': { p: 10, t: 'جاري التحضير...' },
                    'searching': { p: 25, t: 'جاري البحث...' },
                    'extracting': { p: 45, t: 'جاري السحب...' },
                    'ai_processing': { p: 70, t: 'جاري الصياغة...' },
                    'image_optimizing': { p: 85, t: 'جاري التحسين...' },
                    'saving': { p: 95, t: 'جاري الحفظ...' },
                    'completed': { p: 100, t: 'تم بنجاح!' }
                };

                const updateUI = (data) => {
                    const info = stages[data.stage] || { p: 5, t: 'جاري البدء...' };
                    titleEl.innerText = info.t;
                    msgEl.innerText = data.message;
                    barEl.style.width = info.p + '%';
                    percentEl.innerText = info.p + '%';
                    
                    // Circular progress
                    const offset = 276.46 - (276.46 * info.p / 100);
                    circleEl.style.strokeDashoffset = offset;

                    if (data.stage === 'completed') {
                        circleEl.classList.replace('text-blue-600', 'text-green-500');
                        barEl.classList.replace('bg-gradient-to-r', 'bg-green-500');
                    }
                };

                let pollingInterval = setInterval(() => {
                    const pollUrl = "{{ route('dashboard.headlines.generate-progress', ['id' => ':id']) }}".replace(':id', progressId);
                    fetch(pollUrl)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.stage) {
                                updateUI(data);
                                if (data.stage === 'completed') {
                                    clearInterval(pollingInterval);
                                    setTimeout(() => {
                                        overlay.classList.replace('opacity-100', 'opacity-0');
                                        cardEl.classList.replace('scale-100', 'scale-95');
                                        setTimeout(() => overlay.classList.add('hidden'), 300);
                                    }, 1000);
                                }
                            }
                        });
                }, 1200);

                try {
                    const formData = new URLSearchParams();
                    formData.append('headline', headline.text);
                    formData.append('keyword', this.resultKeyword);
                    formData.append('category_id', headline.categoryId);
                    formData.append('progress_id', progressId);
                    if (this.mode === 'content' && this.originalContent) {
                        formData.append('source_content', this.originalContent);
                    }

                    const response = await fetch("{{ route('dashboard.headlines.generate-article') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    let data;
                    const text = await response.text();
                    
                    clearInterval(pollingInterval);

                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        overlay.classList.add('hidden');
                        console.error('Raw Server Response:', text);
                        Swal.fire({
                            title: 'خطأ في الاستجابة',
                            html: `<p class="text-sm">حدث خطأ تقني في السيرفر (الحالة: ${response.status}).</p>`,
                            icon: 'error'
                        });
                        headline.generating = false;
                        return;
                    }

                    if (data.success) {
                        updateUI({ stage: 'completed', message: 'تم الانتهاء من كل شيء!' });

                        setTimeout(() => {
                            overlay.classList.replace('opacity-100', 'opacity-0');
                            cardEl.classList.replace('scale-100', 'scale-95');
                            setTimeout(() => overlay.classList.add('hidden'), 300);

                            Swal.fire({
                                title: 'تم التوليد بنجاح!',
                                text: data.message + '\n\nهل تريد الانتقال لتعديل المقال؟',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'نعم، انقلني للمقال',
                                cancelButtonText: 'إغلاق',
                                confirmButtonColor: '#10b981',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = data.edit_url;
                                }
                            });
                        }, 800);
                    } else {
                        overlay.classList.add('hidden');
                        throw new Error(data.message || 'فشل في توليد المقال');
                    }
                } catch (e) {
                    clearInterval(pollingInterval);
                    overlay.classList.add('hidden');
                    Swal.fire({
                        icon: 'error',
                        title: 'خطأ',
                        text: e.message
                    });
                } finally {
                    headline.generating = false;
                }
            },
            getBadgeClass(tag) {
                tag = (tag || '').toLowerCase();
                if (tag.includes('discover') || tag.includes('أفضل')) return 'bg-green-100 text-green-700 ring-1 ring-green-200';
                if (tag.includes('focused') || tag.includes('متمركز')) return 'bg-blue-100 text-blue-700 ring-1 ring-blue-200';
                return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200';
            },

            copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'تم النسخ بنجاح'
                    });
                });
            }
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .font-tajawal { font-family: 'Tajawal', sans-serif !important; }
    .animate-fade-in { animation: fadeIn 0.5s ease-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [x-cloak] { display: none !important; }
    /* Smooth scrollbar for lists */
    .overflow-y-auto::-webkit-scrollbar { width: 4px; }
    .overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush
@endsection
