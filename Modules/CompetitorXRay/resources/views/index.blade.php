@extends('competitorxray::layouts.master')

@section('title', 'Competitor X-Ray & Niche Dominator')

@section('content')
<div x-data="competitorXRay()" class="max-w-7xl mx-auto pb-12 relative">
    
    <!-- Background Effects -->
    <div class="fixed top-0 left-0 w-full h-full pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] rounded-full bg-rose-500/10 blur-[120px]"></div>
        <div class="absolute bottom-[10%] left-[-10%] w-[600px] h-[600px] rounded-full bg-indigo-500/10 blur-[150px]"></div>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs font-bold mb-4">
                <i class="fas fa-radar animate-spin-slow"></i> X-RAY MODE ACTIVE
            </div>
            <h1 class="text-4xl md:text-5xl font-black mb-2 flex items-center gap-4" style="color: var(--text-main);">
                <i class="fas fa-crosshairs text-rose-500"></i>
                <span>Competitor X-Ray</span>
            </h1>
            <p class="text-lg font-medium" style="color: var(--text-muted);">Steal their traffic. Dominate your niche.</p>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'competitor-xray'])

    <!-- ============ SETTINGS PANEL ============ -->
    <div class="glass-card p-6 md:p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-full h-1 bg-gradient-to-r from-transparent via-rose-500 to-transparent"></div>
        
        <div class="flex items-center justify-between mb-6 cursor-pointer" @click="showSettings = !showSettings">
            <h2 class="text-xl font-black flex items-center gap-3">
                <i class="fas fa-cog text-rose-500"></i> X-Ray Settings
                <span x-show="savedDomain" class="text-xs font-normal text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full">
                    <i class="fas fa-check-circle"></i> <span x-text="savedDomain"></span>
                </span>
            </h2>
            <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="showSettings ? 'rotate-180' : ''"></i>
        </div>

        <div x-show="showSettings" x-transition class="space-y-6">
            <!-- Domain Input -->
            <div>
                <label class="block text-sm font-bold mb-2 text-gray-300">Your Website <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-globe text-gray-500"></i>
                    </div>
                    <input type="text" x-model="settingsForm.domain" placeholder="e.g., mysite.com" 
                           class="w-full bg-black/40 border border-white/10 rounded-xl py-4 pl-12 pr-4 text-white focus:outline-none focus:border-rose-500/50 focus:ring-1 focus:ring-rose-500/50 transition-all placeholder-gray-600">
                </div>
            </div>

            <!-- Competitor Mode Toggle -->
            <div>
                <label class="block text-sm font-bold mb-3 text-gray-300">Competitor Discovery</label>
                <div class="flex gap-4">
                    <button @click="settingsForm.competitor_mode = 'auto'" 
                            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all border"
                            :class="settingsForm.competitor_mode === 'auto' ? 'bg-rose-500/20 border-rose-500/50 text-rose-400' : 'bg-black/20 border-white/10 text-gray-400 hover:border-white/20'">
                        <i class="fas fa-magic mr-2"></i> Auto-Detect
                        <p class="text-xs font-normal mt-1 opacity-70">AI will find your top 3 competitors</p>
                    </button>
                    <button @click="settingsForm.competitor_mode = 'manual'" 
                            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all border"
                            :class="settingsForm.competitor_mode === 'manual' ? 'bg-indigo-500/20 border-indigo-500/50 text-indigo-400' : 'bg-black/20 border-white/10 text-gray-400 hover:border-white/20'">
                        <i class="fas fa-pen mr-2"></i> Manual
                        <p class="text-xs font-normal mt-1 opacity-70">Add competitors yourself</p>
                    </button>
                </div>
            </div>

            <!-- Manual Competitors (conditional) -->
            <div x-show="settingsForm.competitor_mode === 'manual'" x-transition>
                <label class="block text-sm font-bold mb-2 text-gray-300">Competitor Domains <span class="text-xs text-gray-500">(one per line or comma separated)</span></label>
                <textarea x-model="settingsForm.manual_competitors" rows="3" placeholder="competitor1.com&#10;competitor2.com&#10;competitor3.com"
                          class="w-full bg-black/40 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/50 transition-all placeholder-gray-600 text-sm"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button @click="saveSettings()" :disabled="savingSettings" 
                        class="flex-1 py-3 rounded-xl font-bold text-white transition-all flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #e11d48, #be123c);">
                    <span x-show="!savingSettings"><i class="fas fa-save"></i> Save & Start Analysis</span>
                    <span x-show="savingSettings"><i class="fas fa-circle-notch fa-spin"></i> Saving...</span>
                </button>
                <button x-show="savedDomain" @click="deleteSettings()" 
                        class="py-3 px-6 rounded-xl font-bold text-red-400 bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 transition-all flex items-center gap-2">
                    <i class="fas fa-trash"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- ============ LOADING STATE ============ -->
    <div x-show="loading" class="glass-card p-12 text-center" x-transition>
        <div class="mb-6">
            <i class="fas fa-satellite-dish text-5xl text-rose-500 animate-pulse"></i>
        </div>
        <h3 class="text-2xl font-black mb-2">Scanning the Competitive Landscape...</h3>
        <p class="text-gray-400 text-sm mb-6">Extracting sitemaps, analyzing keywords, discovering competitors</p>
        <div class="w-64 mx-auto h-2 bg-white/10 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-rose-500 to-indigo-500 rounded-full animate-progress"></div>
        </div>
    </div>

    <!-- ============ RESULTS DASHBOARD ============ -->
    <div x-show="results && !loading" style="display: none;" class="space-y-8" x-transition.opacity.duration.800ms>
        
        <!-- Action Bar -->
        <div class="flex justify-between items-center bg-black/20 border border-white/5 rounded-2xl p-4 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-500/20 flex items-center justify-center text-rose-500 border border-rose-500/30">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Scan Complete</h3>
                    <p class="text-xs text-gray-400">Comparing <span x-text="results?.my_domain" class="text-white"></span> vs <span x-text="results?.competitors?.length" class="text-white"></span> competitors</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <!-- Show discovered competitors -->
                <template x-for="comp in results?.competitors || []" :key="comp">
                    <span class="text-xs bg-white/5 border border-white/10 rounded-full px-3 py-1 text-gray-300" x-text="comp"></span>
                </template>
                <button @click="rerun()" class="ml-3 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-sm font-bold border border-white/10 transition-all flex items-center gap-2">
                    <i class="fas fa-redo"></i> Re-scan
                </button>
            </div>
        </div>

        <!-- ===== NICHE STRENGTH + 4 STATS ===== -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Niche Strength Score (Large) -->
            <div class="md:col-span-1 glass-card p-6 flex flex-col items-center justify-center relative overflow-hidden" style="border-color: rgba(124, 58, 237, 0.3);">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent"></div>
                <p class="text-xs font-bold text-purple-400 mb-2 uppercase tracking-wider relative z-10">Niche Strength</p>
                <div class="relative w-24 h-24 mb-2 z-10">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" :stroke="results?.niche_score > 60 ? '#10b981' : (results?.niche_score > 30 ? '#f59e0b' : '#ef4444')" stroke-width="10" stroke-linecap="round"
                                :stroke-dasharray="(results?.niche_score / 100 * 314) + ' 314'"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-2xl font-black text-white" x-text="results?.niche_score + '%'"></span>
                </div>
                <p class="text-xs text-gray-500 text-center relative z-10" x-text="results?.niche_score > 60 ? 'Strong Position' : (results?.niche_score > 30 ? 'Room to Grow' : 'High Opportunity')"></p>
            </div>
            
            <!-- 4 Stats -->
            <div class="glass-card p-6 flex flex-col gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-400 border border-blue-500/20 group-hover:scale-110 transition-transform">
                    <i class="fas fa-database"></i>
                </div>
                <p class="text-xs font-bold text-gray-400">Your Keywords</p>
                <h3 class="text-3xl font-black text-white" x-text="formatNumber(results?.stats?.my_total)">0</h3>
            </div>
            
            <div class="glass-card p-6 flex flex-col gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 border border-purple-500/20 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users"></i>
                </div>
                <p class="text-xs font-bold text-gray-400">Competitors KWs</p>
                <h3 class="text-3xl font-black text-white" x-text="formatNumber(results?.stats?.comp_total)">0</h3>
            </div>

            <div class="glass-card p-6 flex flex-col gap-2 group" style="border-color: rgba(244, 63, 94, 0.3); background: linear-gradient(135deg, rgba(244,63,94,0.05), transparent);">
                <div class="w-10 h-10 rounded-xl bg-rose-500 flex items-center justify-center text-white shadow-[0_0_15px_rgba(244,63,94,0.5)] group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <p class="text-xs font-bold text-gray-400">Content Gaps <span class="text-rose-500">(Opportunities)</span></p>
                <h3 class="text-3xl font-black text-white" x-text="formatNumber(results?.stats?.gaps_count)">0</h3>
            </div>

            <div class="glass-card p-6 flex flex-col gap-2 group">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                    <i class="fas fa-handshake"></i>
                </div>
                <p class="text-xs font-bold text-gray-400">Shared Battleground</p>
                <h3 class="text-3xl font-black text-white" x-text="formatNumber(results?.stats?.shared_count)">0</h3>
            </div>
        </div>

        <!-- ===== YOUR KEYWORDS ARSENAL ===== -->
        <div class="glass-card p-0 overflow-hidden">
            <div class="p-5 border-b border-white/5 flex justify-between items-center bg-black/20 cursor-pointer" @click="showArsenal = !showArsenal">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="fas fa-shield-halved text-blue-400"></i> Your Keywords Arsenal
                    <span class="text-xs font-normal text-gray-500 ml-2" x-text="'(' + (results?.my_keywords?.length || 0) + ' keywords detected)'"></span>
                </h3>
                <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="showArsenal ? 'rotate-180' : ''"></i>
            </div>
            <div x-show="showArsenal" x-transition class="p-5">
                <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="kw in results?.my_keywords || []" :key="kw">
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-medium hover:bg-blue-500/20 transition-all cursor-default">
                            <i class="fas fa-circle text-[5px] text-blue-500"></i>
                            <span x-text="kw"></span>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- ===== QUICK WINS (LOW-HANGING FRUIT) ===== -->
        <div x-show="results?.quick_wins?.length > 0" class="glass-card p-0 overflow-hidden" style="border-color: rgba(16, 185, 129, 0.3);">
            <div class="p-6 border-b border-white/5 bg-gradient-to-r from-emerald-500/5 to-transparent">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-trophy text-emerald-400"></i> Quick Wins — Low-Hanging Fruit
                </h3>
                <p class="text-xs text-gray-500 mt-1">Keywords with KD &lt; 30 and high volume — you can rank for these FAST!</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-emerald-500/5 text-xs uppercase tracking-wider text-gray-400">
                            <th class="p-4 font-bold">Keyword</th>
                            <th class="p-4 font-bold text-right">Volume</th>
                            <th class="p-4 font-bold text-center">KD</th>
                            <th class="p-4 font-bold text-right">Est. Traffic Value</th>
                            <th class="p-4 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(win, index) in results?.quick_wins" :key="'qw'+index">
                            <tr class="border-b border-white/5 hover:bg-emerald-500/5 transition-colors">
                                <td class="p-4 font-bold text-white flex items-center gap-2">
                                    <i class="fas fa-star text-yellow-500 text-xs"></i>
                                    <span x-text="win.keyword"></span>
                                </td>
                                <td class="p-4 text-right text-emerald-400 font-bold" x-text="formatNumber(win.volume)"></td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold bg-emerald-500/20 text-emerald-400" x-text="win.kd"></span>
                                </td>
                                <td class="p-4 text-right text-amber-400 font-bold">$<span x-text="formatNumber(win.traffic_value)"></span></td>
                                <td class="p-4 text-right">
                                    <button @click="sendToArticleWriter(win.keyword)" 
                                            class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition-all shadow-[0_0_10px_rgba(16,185,129,0.3)] flex items-center gap-2 ml-auto">
                                        <i class="fas fa-pen-nib"></i> Write Now
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== CONTENT VELOCITY CHART ===== -->
        <div class="glass-card p-6">
            <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-chart-area text-rose-500"></i> Competitor Content Velocity 
                <span class="text-xs font-normal text-gray-500 ml-2">(Est. Articles Published / Month)</span>
            </h3>
            <div class="w-full h-[300px] relative">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>

        <!-- ===== MAIN TABLE: CONTENT GAPS ===== -->
        <div class="glass-card p-0 overflow-hidden">
            <div class="p-6 border-b border-white/5 flex justify-between items-center bg-black/20">
                <h3 class="text-xl font-bold flex items-center gap-2">
                    <i class="fas fa-meteor text-rose-500"></i> Actionable Content Gaps
                    <span class="text-xs font-normal text-gray-500 ml-2" x-text="'(' + (results?.gaps?.length || 0) + ' opportunities)'"></span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 text-xs uppercase tracking-wider text-gray-400">
                            <th class="p-4 font-bold">Keyword Opportunity</th>
                            <th class="p-4 font-bold text-right">Search Vol</th>
                            <th class="p-4 font-bold text-center">KD %</th>
                            <th class="p-4 font-bold">Intent</th>
                            <th class="p-4 font-bold text-center">Top Comp Rank</th>
                            <th class="p-4 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(gap, index) in results?.gaps" :key="'gap'+index">
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="p-4 font-bold text-white flex items-center gap-2">
                                    <i class="fas fa-search text-gray-500 text-xs"></i>
                                    <span x-text="gap.keyword"></span>
                                </td>
                                <td class="p-4 text-right text-emerald-400 font-bold" x-text="formatNumber(gap.volume)"></td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-1 rounded-md text-xs font-bold"
                                          :class="gap.kd < 30 ? 'bg-emerald-500/20 text-emerald-400' : (gap.kd < 60 ? 'bg-orange-500/20 text-orange-400' : 'bg-red-500/20 text-red-400')"
                                          x-text="gap.kd"></span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 bg-white/10 rounded-full text-xs text-gray-300" x-text="gap.intent"></span>
                                </td>
                                <td class="p-4 text-center font-bold text-gray-300">
                                    #<span x-text="gap.competitor_rank"></span>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="sendToArticleWriter(gap.keyword)" 
                                            class="px-4 py-2 rounded-lg bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold transition-all shadow-[0_0_10px_rgba(244,63,94,0.3)] hover:shadow-[0_0_20px_rgba(244,63,94,0.5)] flex items-center gap-2 ml-auto">
                                        <i class="fas fa-pen-nib"></i> Steal Traffic
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
    </div>

    <!-- ============ PEOPLE ALSO ASKED (PAA) EXPLORER ============ -->
    <div class="mt-12 pt-12 border-t border-white/5">
        <div class="glass-card p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-blue-500 to-indigo-600"></div>
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black flex items-center gap-3" style="color: var(--text-main);">
                        <i class="fas fa-question-circle text-blue-500"></i> People Also Asked Explorer
                    </h2>
                    <p class="text-gray-400 mt-1 font-medium italic">"Built on SerpAPI — Discover what the world is asking."</p>
                </div>
                <div x-show="paa.results.length > 0" class="flex gap-2">
                    <button @click="resetPaa()" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-white text-xs font-bold border border-white/10 transition-all flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Reset
                    </button>
                </div>
            </div>

            <!-- PAA Setup Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">SerpAPI Key</label>
                    <input type="password" x-model="paa.apiKey" placeholder="Paste your SerpAPI key here..." 
                           class="w-full bg-black/40 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Search Query</label>
                    <input type="text" x-model="paa.query" placeholder="e.g., learn seo" 
                           class="w-full bg-black/40 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Location</label>
                    <select x-model="paa.location" class="w-full bg-black/40 border border-white/10 rounded-xl py-3 px-4 text-white focus:outline-none focus:border-blue-500/50 transition-all text-sm appearance-none">
                        <option value="United States">United States</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Canada">Canada</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                        <option value="Egypt">Egypt</option>
                        <option value="United Arab Emirates">United Arab Emirates</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <div class="flex justify-between mb-2">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500">Max Depth: <span class="text-blue-400" x-text="paa.maxDepth"></span></label>
                    </div>
                    <input type="range" min="1" max="5" x-model="paa.maxDepth" 
                           class="w-full h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-500">
                </div>
            </div>

            <!-- Controls -->
            <div class="flex gap-4 mb-8">
                <button @click="startPaaSearch()" :disabled="paa.loading || !paa.apiKey || !paa.query" 
                        class="flex-1 py-4 rounded-xl font-black text-white transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background: linear-gradient(135deg, #3b82f6, #4f46e5);">
                    <span x-show="!paa.loading"><i class="fas fa-search-plus"></i> Start Exploring Questions</span>
                    <span x-show="paa.loading"><i class="fas fa-circle-notch fa-spin"></i> Searching...</span>
                </button>
                <button x-show="paa.loading" @click="stopPaaSearch()" 
                        class="px-8 py-4 rounded-xl font-black text-white bg-red-500 hover:bg-red-600 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-stop"></i> Stop
                </button>
            </div>

            <!-- Progress Indicator -->
            <div x-show="paa.loading || paa.results.length > 0" x-transition class="bg-black/20 border border-white/5 rounded-2xl p-4 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <template x-if="paa.loading">
                            <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                        </template>
                        <span class="text-xs font-bold" x-text="paa.loading ? 'Exploring deeper questions...' : 'Exploration complete'"></span>
                    </div>
                    <div class="flex gap-4 text-[10px] uppercase font-bold text-gray-500">
                        <span>Questions: <span class="text-white" x-text="paa.results.length"></span></span>
                        <span>API Calls: <span class="text-white" x-text="paa.apiCalls"></span></span>
                    </div>
                </div>
                <div x-show="paa.currentQuestion" class="text-xs text-blue-400 italic mb-2">
                    <i class="fas fa-eye mr-1"></i> Reading: <span x-text="paa.currentQuestion"></span>
                </div>
                <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-500" :style="'width: ' + (paa.loading ? (paa.apiCalls * 20) % 100 : 100) + '%'"></div>
                </div>
            </div>

            <!-- PAA Results Table -->
            <div x-show="paa.results.length > 0" class="overflow-hidden rounded-xl border border-white/5">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 text-[10px] uppercase tracking-widest text-gray-400">
                            <th class="p-4 font-black">Level</th>
                            <th class="p-4 font-black">Question</th>
                            <th class="p-4 font-black text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in paa.results" :key="index">
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors group">
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-[10px] font-black" 
                                          :class="item.depth == 0 ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/10 text-gray-500'"
                                          x-text="'Depth ' + item.depth"></span>
                                </td>
                                <td class="p-4 font-bold text-white group-hover:text-blue-400 transition-colors" x-text="item.question"></td>
                                <td class="p-4 text-right">
                                    <button @click="sendToArticleWriter(item.question)" 
                                            class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-blue-500 text-white text-[10px] font-black transition-all border border-white/10 flex items-center gap-2 ml-auto">
                                        <i class="fas fa-pen-nib"></i> Steal
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function competitorXRay() {
    return {
        loading: false,
        results: null,
        chartInstance: null,
        showSettings: {{ empty($savedDomain) ? 'true' : 'false' }},
        showArsenal: false,
        savedDomain: '{{ $savedDomain ?? '' }}',
        savingSettings: false,
        settingsForm: {
            domain: '{{ $savedDomain ?? '' }}',
            competitor_mode: '{{ $competitorMode ?? 'auto' }}',
            manual_competitors: '{{ $manualCompetitors ?? '' }}'
        },
        
        init() {
            // Auto-trigger analysis if domain is already saved
            if (this.savedDomain) {
                this.runAnalysis(this.savedDomain);
            }
        },

        formatNumber(num) {
            if (!num) return '0';
            return new Intl.NumberFormat().format(num);
        },

        saveSettings() {
            if (!this.settingsForm.domain) {
                Swal.fire('Error', 'Please enter your website domain.', 'warning');
                return;
            }
            
            this.savingSettings = true;
            
            fetch('{{ route("dashboard.competitor-xray.settings") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.settingsForm)
            })
            .then(res => res.json())
            .then(data => {
                this.savingSettings = false;
                if (data.status === 'success') {
                    this.savedDomain = this.settingsForm.domain;
                    this.showSettings = false;
                    this.runAnalysis(this.settingsForm.domain);
                }
            })
            .catch(() => {
                this.savingSettings = false;
                Swal.fire('Error', 'Could not save settings.', 'error');
            });
        },

        deleteSettings() {
            Swal.fire({
                title: 'Clear Settings?',
                text: 'This will remove your saved domain and all cached data.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Yes, clear it'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route("dashboard.competitor-xray.settings.delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.savedDomain = '';
                            this.settingsForm.domain = '';
                            this.settingsForm.manual_competitors = '';
                            this.results = null;
                            this.showSettings = true;
                            if (this.chartInstance) this.chartInstance.destroy();
                        }
                    });
                }
            });
        },

        runAnalysis(domain) {
            this.loading = true;
            this.results = null;
            
            fetch('{{ route("dashboard.competitor-xray.analyze") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ my_domain: domain })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.status === 'success') {
                    this.results = data.data;
                    setTimeout(() => {
                        this.renderChart(this.results.velocity_labels, this.results.velocity_data);
                    }, 200);
                } else {
                    Swal.fire('Error', data.message || 'Something went wrong', 'error');
                }
            })
            .catch(err => {
                this.loading = false;
                Swal.fire('Error', 'Failed to connect to server', 'error');
            });
        },

        rerun() {
            if (this.savedDomain) {
                this.runAnalysis(this.savedDomain);
            }
        },

        renderChart(labels, datasetsObj) {
            const ctx = document.getElementById('velocityChart')?.getContext('2d');
            if (!ctx) return;
            
            if (this.chartInstance) this.chartInstance.destroy();
            
            const colors = [
                { border: '#f43f5e', bg: 'rgba(244, 63, 94, 0.1)' },
                { border: '#3b82f6', bg: 'rgba(59, 130, 246, 0.1)' },
                { border: '#10b981', bg: 'rgba(16, 185, 129, 0.1)' }
            ];

            let datasets = [];
            let i = 0;
            for (const [competitor, dataArray] of Object.entries(datasetsObj || {})) {
                let color = colors[i % colors.length];
                datasets.push({
                    label: competitor,
                    data: dataArray,
                    borderColor: color.border,
                    backgroundColor: color.bg,
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: color.border,
                    pointRadius: 4,
                });
                i++;
            }

            Chart.defaults.color = '#9ca3af';
            Chart.defaults.font.family = 'Inter, sans-serif';

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', align: 'end' },
                        tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(0,0,0,0.8)' }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false }, ticks: { precision: 0 } },
                        x: { grid: { display: false, drawBorder: false } }
                    },
                    interaction: { mode: 'nearest', axis: 'x', intersect: false }
                }
            });
        },

        sendToArticleWriter(keyword) {
            Swal.fire({
                title: 'Executing Hostile Takeover',
                text: `Sending "${keyword}" to AI Article Writer...`,
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '/dashboard/pro-article-writer?keyword=' + encodeURIComponent(keyword) + '&intent=outrank_competitor';
            });
        },

        // PAA Explorer Data & Logic
        paa: {
            loading: false,
            query: '',
            location: 'United States',
            maxDepth: 3,
            results: [],
            apiCalls: 0,
            currentQuestion: '',
            stopRequested: false,
            seenQuestions: new Set()
        },

        resetPaa() {
            this.paa.results = [];
            this.paa.apiCalls = 0;
            this.paa.currentQuestion = '';
            this.paa.seenQuestions.clear();
            this.paa.query = '';
        },

        stopPaaSearch() {
            this.paa.stopRequested = true;
            this.paa.loading = false;
        },

        async startPaaSearch() {
            if (!this.paa.query) return;
            
            this.paa.loading = true;
            this.paa.stopRequested = false;
            this.paa.results = [];
            this.paa.apiCalls = 0;
            this.paa.seenQuestions = new Set();
            
            try {
                await this.explorePaa(this.paa.query, 0);
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Failed to fetch PAA questions.', 'error');
            } finally {
                this.paa.loading = false;
                this.paa.currentQuestion = '';
            }
        },

        async explorePaa(query, depth) {
            if (this.paa.stopRequested || depth >= this.paa.maxDepth) return;
            
            this.paa.currentQuestion = query;
            this.paa.apiCalls++;
            
            const response = await fetch('{{ route("dashboard.competitor-xray.paa.fetch") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    query: query,
                    location: this.paa.location
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                for (const qObj of result.data) {
                    if (this.paa.stopRequested) break;
                    
                    const question = qObj.question;
                    if (!this.paa.seenQuestions.has(question)) {
                        this.paa.seenQuestions.add(question);
                        this.paa.results.push({
                            question: question,
                            depth: depth
                        });
                        
                        // Recursive depth-first exploration
                        await this.explorePaa(question, depth + 1);
                    }
                }
            } else if (result.status === 'error') {
                throw new Error(result.message);
            }
        }
    }
}
</script>
<style>
@keyframes shine {
    100% { transform: translateX(100%) skewX(-12deg); }
}
.animate-shine { animation: shine 1.5s infinite; }
.animate-spin-slow { animation: spin 3s linear infinite; }
@keyframes progress {
    0% { width: 0; }
    50% { width: 70%; }
    100% { width: 100%; }
}
.animate-progress { animation: progress 3s ease-in-out infinite; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(244,63,94,0.3); border-radius: 4px; }
</style>
@endpush
@endsection
