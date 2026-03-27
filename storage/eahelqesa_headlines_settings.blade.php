@extends('dashboard.layouts.app')

@section('title', 'إعدادات عناوين Google Discover')
@section('page-title', 'إعدادات عناوين Google Discover')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 mb-8 bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 w-fit">
        <a href="{{ route('dashboard.headlines.index') }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 text-gray-500 hover:bg-gray-50">
            <i class="fas fa-magic"></i> المولد الذكي
        </a>
        <a href="{{ route('dashboard.headlines.settings') }}" 
           class="px-6 py-2.5 rounded-xl text-sm font-bold transition flex items-center gap-2 bg-blue-600 text-white shadow-lg shadow-blue-200">
            <i class="fas fa-cog"></i> الإعدادات
        </a>
    </div>

    <form action="{{ route('dashboard.headlines.settings.update') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            {{-- AI Provider & Keys Card --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3 font-cairo">
                        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-robot"></i>
                        </div>
                        إعدادات الذكاء الاصطناعي لهذه الأداة
                    </h3>
                    <p class="text-sm text-gray-500 mt-2">تأثير هذه الإعدادات يقتصر فقط على أداة توليد عناوين Google Discover.</p>
                </div>

                <div class="p-8 space-y-8">
                    {{-- Provider Choice --}}
                    <div>
                        <label for="headlines_ai_provider" class="block text-sm font-bold text-gray-700 mb-3 font-cairo">مزود AI الأساسي للعناوين</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="relative flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition"
                                   :class="provider === 'gemini' ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-blue-200'">
                                <input type="radio" name="headlines_ai_provider" value="gemini" class="w-5 h-5 text-blue-600"
                                       {{ ($settings['headlines_ai_provider'] ?? 'gemini') == 'gemini' ? 'checked' : '' }}>
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    <div>
                                        <p class="font-bold text-gray-800">Advanced AI Engine</p>
                                        <p class="text-xs text-gray-500">سريع، دقيق، ويدعم العربية بامتياز</p>
                                    </div>
                                </div>
                            </label>

                            <label class="relative flex items-center gap-4 p-4 border-2 rounded-2xl cursor-pointer transition"
                                   :class="provider === 'openrouter' ? 'border-purple-500 bg-purple-50' : 'border-gray-100 hover:border-purple-200'">
                                <input type="radio" name="headlines_ai_provider" value="openrouter" class="w-5 h-5 text-purple-600"
                                       {{ ($settings['headlines_ai_provider'] ?? '') == 'openrouter' ? 'checked' : '' }}>
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                    <div>
                                        <p class="font-bold text-gray-800">OpenRouter</p>
                                        <p class="text-xs text-gray-500">للوصول لنماذج أخرى مخصصة</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Override Keys & Models --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-gray-50">
                        {{-- Gemini Specific --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                                <i class="fas fa-microchip text-blue-500"></i> إعدادات المحرك الخاصة
                            </h4>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1 uppercase tracking-wider">النموذج (Model)</label>
                                <input type="text" name="headlines_gemini_model" value="{{ $settings['headlines_gemini_model'] ?? 'gemini-2.0-flash' }}"
                                       class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono"
                                       placeholder="gemini-2.0-flash">
                                <a href="#" onclick="return false;" 
                                   class="inline-flex items-center gap-1 text-[10px] text-blue-500 hover:text-blue-600 mt-1">
                                    <i class="fas fa-external-link-alt"></i> دليل النماذج المتاحة
                                </a>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1 uppercase tracking-wider">API KEY</label>
                                <input type="password" name="headlines_gemini_key" value="{{ $settings['headlines_gemini_key'] ?? '' }}"
                                       class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono"
                                       placeholder="أدخل مفتاح الـ API الخاص بالعناوين">
                            </div>
                        </div>

                        {{-- OpenRouter Specific --}}
                        <div class="space-y-4">
                            <h4 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                                <i class="fas fa-network-wired text-purple-500"></i> إعدادات OpenRouter الخاصة
                            </h4>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1 uppercase tracking-wider">النموذج (Model)</label>
                                <input type="text" name="headlines_openrouter_model" value="{{ $settings['headlines_openrouter_model'] ?? '' }}"
                                       class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-sm font-mono"
                                       placeholder="المعرف التلقائي للنموذج">
                                <a href="https://openrouter.ai/models" target="_blank" 
                                   class="inline-flex items-center gap-1 text-[10px] text-purple-500 hover:text-purple-600 mt-1">
                                    <i class="fas fa-external-link-alt"></i> دليل موديلات OpenRouter
                                </a>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 mb-1 uppercase tracking-wider">API KEY</label>
                                <input type="password" name="headlines_openrouter_key" value="{{ $settings['headlines_openrouter_key'] ?? '' }}"
                                       class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none text-sm font-mono"
                                       placeholder="أدخل مفتاح الـ API الخاص بالعناوين">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prompt Card 1: Headlines Style --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3 font-cairo">
                        <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        استراتيجية وأسلوب العناوين (Strategy & Style)
                    </h3>
                    <div class="px-3 py-1 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-full uppercase">Step 1: Suggestion Logic</div>
                </div>
                <div class="p-8">
                    <p class="text-xs text-gray-500 mb-4 font-bold leading-relaxed">
                        <i class="fas fa-info-circle ml-1 text-orange-400"></i>
                        هنا تضع فقط "خطة" العمل (مثل: "ركز على الجانب العاطفي" أو "اجعل العبارات قصيرة"). 
                        <span class="text-orange-600">التعليمات التقنية (مثل تنسيق الأسطر واللغة) أصبحت مخفية ومبرمجة تلقائياً لضمان عدم تعطل الأداة.</span>
                    </p>
                    <textarea name="discover_headlines_prompt" rows="8"
                              class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition font-ibm-plex text-sm leading-relaxed"
                              placeholder="مثال: ركز على صياغة عناوين إخبارية قوية، تجنب الكلمات المكررة، اجعل العناوين متوافقة مع اهتمامات القراء المحبين للتريندات... ">{{ $settings['discover_headlines_prompt'] ?? '' }}</textarea>
                    
                    <div class="mt-4 p-4 bg-orange-50 rounded-xl border border-orange-100 flex items-start gap-4">
                        <div class="text-orange-500 mt-1"><i class="fas fa-lightbulb"></i></div>
                        <div>
                            <p class="text-xs text-orange-800 font-bold mb-1">نصيحة:</p>
                            <p class="text-xs text-orange-600 leading-relaxed">
                                • يمكنك تغيير "شخصية" العناوين هنا دون الخوف من تخريب تنسيق البرنامج.<br>
                                • استخدم <code class="bg-orange-100 px-1 rounded">[Keyword]</code> و <code class="bg-orange-100 px-1 rounded">[NewsContext]</code> دائماً.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prompt Card 2: Content --}}
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3 font-cairo">
                        <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center text-white shadow-md">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        تعليمات توليد المحتوى (Article Content)
                    </h3>
                    <div class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase">Step 2: Content</div>
                </div>
                <div class="p-8">
                    <textarea name="discover_content_prompt" rows="12"
                              class="w-full px-6 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:bg-white outline-none transition font-ibm-plex text-sm leading-relaxed"
                              placeholder="أدخل تعليمات إعادة صياغة المحتوى هنا...">{{ $settings['discover_content_prompt'] ?? '' }}</textarea>
                    
                    <div class="mt-4 p-4 bg-green-50 rounded-xl border border-green-100 flex items-start gap-4">
                        <div class="text-green-500 mt-1"><i class="fas fa-magic"></i></div>
                        <div>
                            <p class="text-xs text-green-800 font-bold mb-1">تلميحات للمحتوى:</p>
                            <p class="text-xs text-green-600 leading-relaxed">
                                • هذا البرومبت مسؤول فقط عن إعادة صياغة محتوى المقال المستخرج.<br>
                                • سيتم إرسال العنوان الذي اخترته مع المحتوى الأصلي للذكاء الاصطناعي كمرجع.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Form --}}
            <div class="flex justify-end pt-4 mb-20">
                <button type="submit" class="px-12 py-4 bg-blue-600 text-white rounded-2xl font-bold font-cairo shadow-xl shadow-blue-200 hover:bg-blue-700 transform transition hover:-translate-y-1">
                    <i class="fas fa-save ml-2"></i> حفظ الإعدادات
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
