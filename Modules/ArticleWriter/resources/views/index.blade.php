@extends('dashboard.layouts.app')

@section('title', 'Pro AI Article Writer | Vidanexus')

@section('content')
<div x-data="articleWriter()" x-init="init()" class="container-fluid py-4 pb-10" :dir="form.language === 'ar' ? 'rtl' : 'ltr'">
    
    <!-- Compact Header -->
    <div class="tool-header-compact mb-6">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="tool-icon-box">
                    <i class="fas fa-pen-nib"></i>
                </div>
                <div>
                    <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 900; letter-spacing: -0.03em; color: #fff;" x-text="t('title')">Pro AI Article Writer</h1>
                    <p class="mb-0" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;" x-text="t('subtitle')">SEO-optimized content engine with E-E-A-T compliance</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="header-stat">
                    <div class="header-stat-label" x-text="t('engine_label')">ENGINE</div>
                    <div class="header-stat-value" style="color: #10b981;" x-text="t('engine_val')">AI POWERED</div>
                </div>
                <div class="header-stat">
                    <div class="header-stat-label" x-text="t('output_label')">OUTPUT</div>
                    <div class="header-stat-value" x-text="t('output_val')">HTML + SEO</div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'article-writer'])

    <div class="row g-4">
        <!-- Sidebar: History -->
        <div class="col-lg-3">
            <div class="aw-sidebar h-100">
                <div class="aw-sidebar-header">
                    <span class="aw-sidebar-title" x-text="t('saved_content')">Saved Content</span>
                    <i class="fas fa-history" style="color: var(--aw-text-dim); font-size: 0.85rem;"></i>
                </div>
                <div class="custom-scrollbar" style="max-height: 700px; overflow-y: auto;">
                    <template x-for="item in history" :key="item.id">
                        <div class="history-item-container" style="position: relative; group;">
                            <div @click="loadArticle(item.id)" 
                                 class="history-item"
                                 :class="currentArticle && currentArticle.id == item.id ? 'history-item-active' : ''">
                                <div style="font-size: 0.7rem; color: var(--aw-text-dim); margin-bottom: 4px; display: flex; justify-content: space-between;">
                                    <span x-text="formatDate(item.created_at)"></span>
                                    <span style="font-size: 0.6rem; font-weight: 800; color: var(--aw-cyan); text-transform: uppercase;" x-text="item.language"></span>
                                </div>
                                <div style="font-weight: 800; font-size: 0.85rem; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="item.title || item.topic"></div>
                                <div style="font-size: 0.65rem; color: var(--aw-text-dim); margin-top: 4px; display: flex; gap: 0.5rem;">
                                    <span x-text="item.word_count + ' ' + t('words')"></span>
                                    <span>•</span>
                                    <span x-text="item.language?.toUpperCase() || 'EN'"></span>
                                </div>
                            </div>
                            <!-- Delete Button -->
                            <button @click.stop="deleteArticle(item.id)" 
                                    class="delete-btn"
                                    :title="t('alert_del_btn')"
                                    style="position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,75,75,0.1); border: 1px solid rgba(255,75,75,0.2); color: #ff4b4b; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; opacity: 0.4;">
                                <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="history.length === 0" style="padding: 3rem 1.5rem; text-align: center; color: var(--aw-text-dim); font-size: 0.85rem; font-style: italic;" x-text="t('no_saved')">
                        No articles generated yet. Create your first masterpiece!
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Column -->
        <div class="col-lg-9">
            <!-- Writer Interface -->
            <div x-show="view === 'form'" class="aw-card animate-in">
                <div style="padding: 2rem 2.5rem;">
                    
                    <!-- Primary Keyword -->
                    <div style="margin-bottom: 1.5rem;">
                        <div class="aw-label">
                            <i class="fas fa-search"></i> <span x-text="t('primary_keyword')"></span>
                        </div>
                        <div class="aw-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" x-model="form.keyword" 
                                   class="form-control"
                                   :placeholder="t('keyword_placeholder')">
                        </div>
                    </div>

                    <!-- Language Selection (Full Width) -->
                    <div style="margin-bottom: 1.5rem;">
                        <div class="aw-label">
                            <i class="fas fa-globe"></i> <span x-text="t('language')"></span>
                        </div>
                        <div class="lang-grid">
                            <template x-for="lang in settings.languages" :key="lang.value">
                                <button @click="form.language = lang.value" type="button"
                                        class="lang-btn"
                                        :class="form.language === lang.value ? 'lang-btn-active' : ''">
                                    <span x-text="lang.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Tone + Audience -->
                    <div class="row g-3" style="margin-bottom: 1rem;">
                        <div class="col-md-6">
                            <div class="aw-label">
                                <i class="fas fa-palette"></i> <span x-text="t('editorial_tone')"></span>
                            </div>
                            <select x-model="form.tone" class="aw-select">
                                <template x-for="tone in settings.tones">
                                    <option :value="tone.value" x-text="getToneLabel(tone)"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="aw-label">
                                <i class="fas fa-users"></i> <span x-text="t('target_audience')"></span>
                            </div>
                            <select x-model="form.audience" class="aw-select">
                                <template x-for="audience in settings.audiences">
                                    <option :value="audience.value" x-text="getAudienceLabel(audience)"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Persona Mastery Guide (Interactive Presets) -->
                    <div style="margin-bottom: 2rem; padding: 1.25rem; background: var(--aw-cyan-10); border: 1px solid var(--aw-cyan-20); border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -10px; top: -10px; font-size: 4rem; color: var(--aw-cyan-10); opacity: 0.3; transform: rotate(-15deg);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-magic" style="color: var(--aw-cyan);"></i>
                                <h4 style="margin: 0; font-size: 0.85rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px;" x-text="t('persona_guide')">Persona Mastery Guide</h4>
                            </div>
                            <span style="font-size: 0.7rem; color: var(--aw-cyan); font-weight: 700;" x-text="t('persona_guide_hint')">Click any preset to auto-apply tone & audience</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem;">
                            <!-- Preset 1: News & Trends -->
                            <div @click="applyPersonaPreset('informative', 'general')"
                                 role="button"
                                 tabindex="0"
                                 style="cursor: pointer; padding: 0.75rem; border-radius: 12px; transition: all 0.2s ease;"
                                 :style="form.tone === 'informative' && form.audience === 'general' ? 'background: rgba(6,182,212,0.2); border: 1px solid var(--aw-cyan);' : 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);'">
                                <div style="font-size: 0.7rem; font-weight: 900; color: var(--aw-cyan); margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fas fa-newspaper"></i> <span x-text="t('news_trends')">NEWS & TRENDS</span></span>
                                    <i x-show="form.tone === 'informative' && form.audience === 'general'" class="fas fa-check-circle" style="color: var(--aw-cyan);"></i>
                                </div>
                                <div style="font-size: 0.73rem; color: var(--aw-text-dim);" x-text="t('news_trends_desc')"></div>
                            </div>

                            <!-- Preset 2: Markets & Gold & Economy -->
                            <div @click="applyPersonaPreset('professional', 'general')"
                                 role="button"
                                 tabindex="0"
                                 style="cursor: pointer; padding: 0.75rem; border-radius: 12px; transition: all 0.2s ease;"
                                 :style="form.tone === 'professional' && form.audience === 'general' ? 'background: rgba(245,158,11,0.2); border: 1px solid #f59e0b;' : 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);'">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #f59e0b; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fas fa-coins"></i> <span x-text="t('gold_markets')">GOLD & MARKETS</span></span>
                                    <i x-show="form.tone === 'professional' && form.audience === 'general'" class="fas fa-check-circle" style="color: #f59e0b;"></i>
                                </div>
                                <div style="font-size: 0.73rem; color: var(--aw-text-dim);" x-text="t('gold_markets_desc')"></div>
                            </div>

                            <!-- Preset 3: Shopping & Products & Real Estate -->
                            <div @click="applyPersonaPreset('creative', 'shoppers')"
                                 role="button"
                                 tabindex="0"
                                 style="cursor: pointer; padding: 0.75rem; border-radius: 12px; transition: all 0.2s ease;"
                                 :style="form.tone === 'creative' && form.audience === 'shoppers' ? 'background: rgba(168,85,247,0.2); border: 1px solid #a855f7;' : 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);'">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #a855f7; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fas fa-shopping-cart"></i> <span x-text="t('real_estate')">REVIEWS & SHOPPING</span></span>
                                    <i x-show="form.tone === 'creative' && form.audience === 'shoppers'" class="fas fa-check-circle" style="color: #a855f7;"></i>
                                </div>
                                <div style="font-size: 0.73rem; color: var(--aw-text-dim);" x-text="t('real_estate_desc')"></div>
                            </div>

                            <!-- Preset 4: Business B2B -->
                            <div @click="applyPersonaPreset('professional', 'professionals')"
                                 role="button"
                                 tabindex="0"
                                 style="cursor: pointer; padding: 0.75rem; border-radius: 12px; transition: all 0.2s ease;"
                                 :style="form.tone === 'professional' && form.audience === 'professionals' ? 'background: rgba(16,185,129,0.2); border: 1px solid #10b981;' : 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);'">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #10b981; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fas fa-briefcase"></i> <span x-text="t('business_b2b')">BUSINESS B2B</span></span>
                                    <i x-show="form.tone === 'professional' && form.audience === 'professionals'" class="fas fa-check-circle" style="color: #10b981;"></i>
                                </div>
                                <div style="font-size: 0.73rem; color: var(--aw-text-dim);" x-text="t('business_b2b_desc')"></div>
                            </div>

                            <!-- Preset 5: Educational / Blog -->
                            <div @click="applyPersonaPreset('informative', 'beginners')"
                                 role="button"
                                 tabindex="0"
                                 style="cursor: pointer; padding: 0.75rem; border-radius: 12px; transition: all 0.2s ease;"
                                 :style="form.tone === 'informative' && form.audience === 'beginners' ? 'background: rgba(59,130,246,0.2); border: 1px solid #3b82f6;' : 'background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);'">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #3b82f6; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                                    <span><i class="fas fa-graduation-cap"></i> <span x-text="t('edu_blog')">HOW-TO & GUIDES</span></span>
                                    <i x-show="form.tone === 'informative' && form.audience === 'beginners'" class="fas fa-check-circle" style="color: #3b82f6;"></i>
                                </div>
                                <div style="font-size: 0.73rem; color: var(--aw-text-dim);" x-text="t('edu_blog_desc')"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Article Length -->
                    <div style="margin-bottom: 1.5rem;">
                        <div class="aw-label">
                            <i class="fas fa-ruler-horizontal"></i> <span x-text="t('article_length')"></span>
                        </div>
                        <div class="length-container">
                            <button @click="form.word_count = 300" type="button"
                                    class="length-card"
                                    :class="form.word_count === 300 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_mini')">MINI</div>
                                <div class="length-card-words" x-text="t('words_300')">~300</div>
                            </button>
                            <button @click="form.word_count = 500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_micro')">MICRO</div>
                                <div class="length-card-words" x-text="t('words_500')">~500</div>
                            </button>
                            <button @click="form.word_count = 800" type="button"
                                    class="length-card"
                                    :class="form.word_count === 800 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_short')">SHORT</div>
                                <div class="length-card-words" x-text="t('words_800')">~800</div>
                            </button>
                            <button @click="form.word_count = 1500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 1500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_medium')">MEDIUM</div>
                                <div class="length-card-words" x-text="t('words_1500')">~1.5k</div>
                            </button>
                            <button @click="form.word_count = 2500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 2500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_long')">LONG</div>
                                <div class="length-card-words" x-text="t('words_2500')">~2.5k</div>
                            </button>
                        </div>
                    </div>

                    <!-- Output Components (Checkboxes) -->
                    <div style="margin-bottom: 2rem;">
                        <div class="aw-label">
                            <i class="fas fa-puzzle-piece"></i> <span x-text="t('include_in_article')"></span>
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <template x-for="comp in settings.components" :key="comp.value">
                                <button type="button"
                                        class="comp-card"
                                        :class="form.components.includes(comp.value) ? 'comp-card-active' : ''"
                                        @click="toggleComponent(comp.value)">
                                    <div class="comp-check">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span class="comp-label" x-text="getComponentLabel(comp)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div>
                        <button @click="generate()" 
                                :disabled="isProcessing || !form.keyword"
                                class="aw-generate-btn">
                            <span x-show="!isProcessing">
                                <i class="fas fa-wand-magic-sparkles"></i> <span x-text="t('btn_generate')"></span>
                                <span class="aw-generate-cost" x-text="settings.credit_cost + (form.language === 'ar' ? ' كريديت' : ' CRS')"></span>
                            </span>
                            <span x-show="isProcessing">
                                <i class="fas fa-circle-notch fa-spin"></i> <span x-text="t('btn_generating')"></span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loader / Processing View -->
            <div x-show="isProcessing" class="aw-card text-center animate-in" style="padding: 4rem 2rem;">
                <div class="loader-container" style="margin-bottom: 2rem;">
                    <div class="loader-ring"></div>
                    <i class="fas fa-pen-fancy" style="color: var(--aw-cyan); font-size: 2rem;"></i>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 900; color: #fff; margin-bottom: 0.5rem;" x-text="t('engine_active')">Content Engine Active</h2>
                <p style="color: var(--aw-text-dim); font-style: italic; margin-bottom: 0.25rem; font-size: 0.9rem;" x-text="t('engine_simulating')">Simulating SERP analysis, applying E-E-A-T signals, building heading hierarchy...</p>
                <p style="color: var(--aw-text-label); font-size: 0.75rem;" x-text="t('engine_time')">This may take 30-60 seconds for comprehensive articles.</p>
                <div style="max-width: 300px; margin: 2rem auto 0;">
                    <div style="height: 4px; background: var(--aw-surface); border-radius: 10px; overflow: hidden;">
                        <div class="animate-shimmer" style="height: 100%; width: 100%; background: linear-gradient(90deg, transparent, var(--aw-cyan), transparent);"></div>
                    </div>
                </div>
            </div>

            <!-- Result Viewer -->
            <div x-show="view === 'result' && currentArticle" class="animate-in">
                <div class="aw-card" style="overflow: hidden;">
                    <!-- Result Header -->
                    <div style="padding: 1.25rem 2rem; border-bottom: 1px solid var(--aw-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; min-width: 0;">
                            <div style="width: 42px; height: 42px; border-radius: 12px; background: var(--aw-cyan-10); border: 1px solid var(--aw-cyan-20); display: flex; align-items: center; justify-content: center; color: var(--aw-cyan); flex-shrink: 0;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <h3 style="font-size: 1.05rem; font-weight: 800; color: #fff; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 550px;" x-text="currentArticle.title"></h3>
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; display: flex; gap: 0.75rem;">
                                    <span x-text="currentArticle.word_count + ' ' + t('words')"></span>
                                    <span>•</span>
                                    <span x-text="currentArticle.language.toUpperCase()"></span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                            <button @click="view = 'form'" style="background: var(--aw-surface); border: 1px solid var(--aw-border); color: #fff; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-plus" style="margin-right: 4px;"></i> <span x-text="t('btn_new')"></span>
                            </button>
                            <button @click="copyContent()" style="background: var(--aw-cyan); border: none; color: #000; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-copy" style="margin-right: 4px;"></i> <span x-text="t('btn_copy_html')"></span>
                            </button>
                            <button @click="copyPlainText()" style="background: var(--aw-surface); border: 1px solid var(--aw-border); color: #fff; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-align-left" style="margin-right: 4px;"></i> <span x-text="t('btn_copy_text')"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div style="padding: 0 2rem; border-bottom: 1px solid var(--aw-border); display: flex; gap: 0.25rem; background: rgba(0,0,0,0.15);">
                        <button @click="articleTab = 'read'" class="result-tab" :class="articleTab === 'read' ? 'result-tab-active' : ''">
                            <i class="fas fa-book-reader" style="margin-right: 4px;"></i> <span x-text="t('tab_article')"></span>
                        </button>
                        <button @click="articleTab = 'seo'" class="result-tab" :class="articleTab === 'seo' ? 'result-tab-active' : ''">
                            <i class="fas fa-search" style="margin-right: 4px;"></i> <span x-text="t('tab_seo')"></span>
                        </button>
                        <button @click="articleTab = 'raw'" class="result-tab" :class="articleTab === 'raw' ? 'result-tab-active' : ''">
                            <i class="fas fa-code" style="margin-right: 4px;"></i> <span x-text="t('tab_raw')"></span>
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="custom-scrollbar" style="padding: 2rem 2.5rem; max-height: 800px; overflow-y: auto;">
                        
                        <!-- Read View -->
                        <div x-show="articleTab === 'read'" class="article-render-container">
                            <div x-html="currentArticle.content"></div>
                        </div>

                        <!-- SEO View -->
                        <div x-show="articleTab === 'seo'" class="animate-in">
                            <!-- Title Tag -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 16px;">
                                <div class="aw-label" style="margin-bottom: 0.75rem;">
                                    <i class="fas fa-heading"></i> <span x-text="t('seo_title_tag')"></span>
                                </div>
                                <div style="font-size: 1.15rem; font-weight: 700; color: #fff; padding: 1rem; background: rgba(0,0,0,0.25); border-radius: 12px; border: 1px solid var(--aw-border);" x-text="currentArticle.title"></div>
                                <div style="text-align: right; font-size: 0.65rem; font-weight: 700; margin-top: 0.5rem;"
                                     :style="(currentArticle.title || '').length <= 60 ? 'color: #10b981;' : 'color: #f59e0b;'"
                                     x-text="(currentArticle.title || '').length + '/60 chars'"></div>
                            </div>
                            
                            <!-- Meta Description -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 16px;">
                                <div class="aw-label" style="margin-bottom: 0.75rem;">
                                    <i class="fas fa-align-left"></i> <span x-text="t('meta_desc')"></span>
                                </div>
                                <div style="font-size: 1rem; line-height: 1.6; color: var(--aw-text); padding: 1rem; background: rgba(0,0,0,0.25); border-radius: 12px; border: 1px solid var(--aw-border);" x-text="currentArticle.meta_description || t('meta_desc_missing')"></div>
                                <div style="text-align: right; font-size: 0.65rem; font-weight: 700; margin-top: 0.5rem;"
                                     :style="(currentArticle.meta_description || '').length <= 155 ? 'color: #10b981;' : 'color: #f59e0b;'"
                                     x-text="(currentArticle.meta_description || '').length + '/155 chars'"></div>
                            </div>

                            <!-- Suggested URL / Slug (English + Arabic) -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 16px;">
                                <div class="aw-label" style="margin-bottom: 0.75rem;">
                                    <i class="fas fa-link"></i> <span x-text="t('suggested_url')"></span>
                                </div>

                                <!-- English slug -->
                                <div style="margin-bottom: 0.85rem;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                                        <span style="background: var(--aw-cyan-10); color: var(--aw-cyan); padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.6rem;">EN</span>
                                        <span x-text="t('en_url')"></span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                                        <div style="flex: 1; padding: 0.85rem 1rem; background: rgba(0,0,0,0.25); border: 1px solid var(--aw-border); border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #fff; direction: ltr; word-break: break-all; line-height: 1.5;">
                                            <span style="color: var(--aw-text-dim);" x-text="suggestedDomain() + '/'"></span><span style="color: var(--aw-cyan); font-weight: 700;" x-text="slugEn() || '—'"></span>
                                        </div>
                                        <button @click="copyValue(slugEn(), 'English slug')"
                                                :disabled="!slugEn()"
                                                style="background: var(--aw-cyan-10); border: 1px solid var(--aw-cyan-20); color: var(--aw-cyan); padding: 0 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 800; cursor: pointer; transition: all 0.2s;"
                                                :style="!slugEn() ? 'opacity: 0.4; cursor: not-allowed;' : ''">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Arabic slug -->
                                <div>
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                                        <span style="background: rgba(168,85,247,0.1); color: #a855f7; padding: 0.15rem 0.5rem; border-radius: 6px; font-size: 0.6rem;">AR</span>
                                        <span x-text="t('ar_url')"></span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; align-items: stretch;">
                                        <div style="flex: 1; padding: 0.85rem 1rem; background: rgba(0,0,0,0.25); border: 1px solid var(--aw-border); border-radius: 10px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #fff; word-break: break-all; line-height: 1.5;">
                                            <span style="color: var(--aw-text-dim); direction: ltr; display: inline-block;" x-text="suggestedDomain() + '/'"></span><span style="color: #a855f7; font-weight: 700;" x-text="slugAr() || '—'"></span>
                                        </div>
                                        <button @click="copyValue(slugAr(), 'Arabic slug')"
                                                :disabled="!slugAr()"
                                                style="background: rgba(168,85,247,0.1); border: 1px solid rgba(168,85,247,0.2); color: #a855f7; padding: 0 1rem; border-radius: 10px; font-size: 0.75rem; font-weight: 800; cursor: pointer; transition: all 0.2s;"
                                                :style="!slugAr() ? 'opacity: 0.4; cursor: not-allowed;' : ''">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <p style="font-size: 0.7rem; color: var(--aw-text-dim); margin-top: 0.85rem; margin-bottom: 0; line-height: 1.5;" x-text="t('url_note')">
                                    Auto-generated from the article title. Both slugs are sanitized for SEO — English is lowercase ASCII, Arabic preserves native letters with hyphens.
                                </p>
                            </div>

                            <!-- Google Preview -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #fff; border-radius: 16px; color: #000;">
                                <div class="aw-label" style="margin-bottom: 0.75rem; color: rgba(0,0,0,0.4);">
                                    <i class="fab fa-google" style="color: #4285f4;"></i> <span x-text="t('serp_preview')"></span>
                                </div>
                                <div style="font-size: 1.15rem; color: #1a0dab; font-weight: 400; margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="currentArticle.title || 'Untitled'"></div>
                                <div style="font-size: 0.8rem; color: #006621; margin-bottom: 4px;" x-text="'https://' + suggestedDomain() + '/' + (currentArticle.language === 'ar' ? (slugAr() || slugEn() || '...') : (slugEn() || slugAr() || '...'))"></div>
                                <div style="font-size: 0.85rem; color: #545454; line-height: 1.5;" x-text="currentArticle.meta_description || 'No meta description available'"></div>
                            </div>

                            <!-- SEO Stats Grid -->
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;" x-text="t('stat_status')">Status</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #10b981;" x-text="t('stat_status_val')">INDEX READY</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;" x-text="t('stat_hierarchy')">Hierarchy</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #fff;" x-text="t('stat_hierarchy_val')">STRICT H1-H3</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;" x-text="t('stat_eeat')">E-E-A-T</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #10b981;" x-text="t('stat_eeat_val')">VERIFIED</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;" x-text="t('stat_words')">Word Count</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: var(--aw-cyan);" x-text="currentArticle.word_count"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Raw View -->
                        <div x-show="articleTab === 'raw'" class="animate-in">
                            <pre style="background: rgba(0,0,0,0.3); padding: 1.5rem; border-radius: 16px; color: #7dd3fc; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.7; overflow-x: auto; white-space: pre-wrap; user-select: all; border: 1px solid var(--aw-border);" 
                                 x-text="currentArticle.content"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* ===== DESIGN TOKENS ===== */
    :root {
        --aw-cyan: #0ea5e9;
        --aw-cyan-10: rgba(14, 165, 233, 0.1);
        --aw-cyan-20: rgba(14, 165, 233, 0.2);
        --aw-cyan-30: rgba(14, 165, 233, 0.3);
        --aw-surface: rgba(255,255,255,0.04);
        --aw-surface-hover: rgba(255,255,255,0.07);
        --aw-border: rgba(255,255,255,0.08);
        --aw-border-hover: rgba(255,255,255,0.15);
        --aw-text: #e2e8f0;
        --aw-text-dim: rgba(255,255,255,0.5);
        --aw-text-label: rgba(255,255,255,0.4);
        --aw-input-bg: rgba(255,255,255,0.05);
    }

    /* ===== COMPACT HEADER ===== */
    .tool-header-compact {
        background: var(--aw-surface);
        border: 1px solid var(--aw-border);
        border-radius: 20px;
        padding: 1.25rem 1.75rem;
        backdrop-filter: blur(12px);
    }
    .tool-icon-box {
        width: 50px; height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--aw-cyan-20), var(--aw-cyan-10));
        border: 1px solid var(--aw-cyan-30);
        display: flex; align-items: center; justify-content: center;
        color: var(--aw-cyan);
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .header-stat {
        text-align: center;
        padding: 0.5rem 1rem;
        background: var(--aw-surface);
        border: 1px solid var(--aw-border);
        border-radius: 12px;
        min-width: 90px;
    }
    .header-stat-label {
        font-size: 0.6rem; font-weight: 800;
        letter-spacing: 1.5px; text-transform: uppercase;
        color: var(--aw-text-label);
    }
    .header-stat-value {
        font-size: 0.8rem; font-weight: 800;
        color: #fff; letter-spacing: 0.5px;
    }

    /* ===== FORM CONTROLS — FORCE DARK COLORS ===== */
    .aw-input,
    .aw-card .form-control,
    .aw-card input[type="text"] {
        width: 100%;
        background: var(--aw-input-bg) !important;
        border: 1px solid var(--aw-border) !important;
        color: #ffffff !important;
        padding: 1rem 1rem 1rem 3rem;
        border-radius: 14px;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.25s ease;
        outline: none;
    }
    .aw-card .form-control:focus,
    .aw-card input[type="text"]:focus {
        border-color: var(--aw-cyan) !important;
        box-shadow: 0 0 0 3px var(--aw-cyan-10) !important;
        background: rgba(255,255,255,0.06) !important;
    }
    .aw-card .form-control::placeholder {
        color: var(--aw-text-dim) !important;
    }

    /* SELECT DROPDOWNS */
    .aw-select {
        width: 100%;
        background: var(--aw-input-bg) !important;
        border: 1px solid var(--aw-border) !important;
        color: #ffffff !important;
        padding: 0.85rem 1.25rem;
        border-radius: 14px;
        font-size: 0.9rem;
        font-weight: 700;
        outline: none;
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%230ea5e9' d='M6 8L1 3h10z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 12px !important;
        transition: all 0.25s ease;
    }
    .aw-select:focus {
        border-color: var(--aw-cyan) !important;
        box-shadow: 0 0 0 3px var(--aw-cyan-10) !important;
    }
    .aw-select option {
        background: #1a1b20 !important;
        color: #ffffff !important;
        padding: 0.5rem;
    }

    /* ===== LANGUAGE GRID ===== */
    .lang-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .lang-btn {
        background: var(--aw-surface);
        border: 1px solid var(--aw-border);
        color: var(--aw-text-dim);
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        outline: none;
    }
    .lang-btn:hover {
        border-color: var(--aw-cyan-30);
        color: #fff;
        background: var(--aw-surface-hover);
        transform: translateY(-1px);
    }
    .lang-btn-active {
        background: var(--aw-cyan) !important;
        color: #000 !important;
        border-color: var(--aw-cyan) !important;
        box-shadow: 0 4px 16px rgba(14, 165, 233, 0.35);
        transform: translateY(-1px);
    }

    .length-container {
        display: flex;
        gap: 0.5rem;
        background: rgba(0,0,0,0.15);
        padding: 0.4rem;
        border-radius: 18px;
        border: 1px solid var(--aw-border);
    }
    .length-card {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 0.6rem 0.4rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        flex: 1;
        outline: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 0;
    }
    .length-card:hover {
        background: rgba(255,255,255,0.03);
    }
    .length-card-active {
        background: var(--aw-cyan-10) !important;
        border-color: var(--aw-cyan-30) !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .length-card-title {
        font-size: 0.65rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        color: var(--aw-text-label);
        text-transform: uppercase;
    }
    .length-card-active .length-card-title {
        color: var(--aw-cyan);
    }
    .length-card-words {
        font-size: 0.75rem;
        font-weight: 800;
        color: #fff;
        margin-top: 2px;
    }
    .length-card-active .length-card-words {
        color: #fff;
    }
    .length-card-desc {
        display: none;
    }

    /* ===== COMPONENT CHECKBOXES ===== */
    .comp-card {
        background: var(--aw-surface);
        border: 1px solid var(--aw-border);
        border-radius: 12px;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        user-select: none;
        flex: 1;
        min-width: 0;
        justify-content: center;
    }
    .comp-card:hover {
        border-color: var(--aw-border-hover);
        background: var(--aw-surface-hover);
    }
    .comp-card-active {
        background: var(--aw-cyan-10) !important;
        border-color: var(--aw-cyan-30) !important;
    }
    .comp-check {
        width: 20px; height: 20px;
        border-radius: 6px;
        border: 2px solid var(--aw-border-hover);
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
        font-size: 10px;
        color: transparent;
    }
    .comp-card-active .comp-check {
        background: var(--aw-cyan);
        border-color: var(--aw-cyan);
        color: #000;
    }
    .comp-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--aw-text-dim);
    }
    .comp-card-active .comp-label {
        color: var(--aw-cyan);
    }

    /* ===== GENERATE BUTTON ===== */
    .aw-generate-btn {
        width: 100%;
        padding: 1rem 2rem;
        border-radius: 16px;
        border: none;
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: #000;
        font-size: 1rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    .aw-generate-btn:hover:not(:disabled) {
        background: linear-gradient(135deg, #38bdf8, #0ea5e9);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(14, 165, 233, 0.35);
    }
    .aw-generate-btn:active:not(:disabled) {
        transform: scale(0.98);
    }
    .aw-generate-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .aw-generate-cost {
        background: rgba(0,0,0,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 800;
    }

    /* ===== SECTION LABELS ===== */
    .aw-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--aw-text-label);
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .aw-label i {
        color: var(--aw-cyan);
        font-size: 0.75rem;
    }

    /* ===== SEARCH ICON ===== */
    .aw-search-wrap {
        position: relative;
    }
    .aw-search-wrap i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--aw-text-dim);
        font-size: 0.9rem;
        z-index: 2;
    }

    /* ===== CARD & SIDEBAR ===== */
    .aw-card {
        background: var(--glass-bg, rgba(255,255,255,0.03));
        border: 1px solid var(--glass-border, rgba(255,255,255,0.08));
        border-radius: 20px;
        backdrop-filter: blur(12px);
    }
    .aw-sidebar {
        background: var(--glass-bg, rgba(255,255,255,0.03));
        border: 1px solid var(--glass-border, rgba(255,255,255,0.08));
        border-radius: 20px;
        overflow: hidden;
        backdrop-filter: blur(12px);
    }
    .aw-sidebar-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--aw-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .aw-sidebar-title {
        font-size: 0.75rem;
        font-weight: 900;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--aw-cyan);
    }

    /* ===== SCROLLBAR ===== */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--aw-border); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--aw-cyan); }

    /* ===== LOADER ===== */
    .loader-container { position: relative; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
    .loader-ring { position: absolute; inset: 0; border: 4px solid var(--aw-border); border-top-color: var(--aw-cyan); border-radius: 50%; animation: aw-spin 1s linear infinite; }
    @keyframes aw-spin { to { transform: rotate(360deg); } }
    @keyframes aw-shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .animate-shimmer { animation: aw-shimmer 2s infinite ease-in-out; }

    /* ===== ARTICLE RENDERING ===== */
    .article-render-container { color: var(--aw-text); line-height: 1.85; }
    .article-render-container h1 { font-weight: 900; color: #fff; margin-bottom: 2rem; border-bottom: 2px solid var(--aw-cyan-20); padding-bottom: 1rem; font-size: 2rem; }
    .article-render-container h2 { font-weight: 800; color: #fff; margin-top: 3rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem; }
    .article-render-container h2::before { content: ''; width: 4px; height: 24px; background: var(--aw-cyan); border-radius: 4px; flex-shrink: 0; }
    .article-render-container h3 { font-weight: 700; color: var(--aw-cyan); margin-top: 2rem; font-size: 1.2rem; }
    .article-render-container p { margin-bottom: 1.5rem; font-size: 1.05rem; opacity: 0.9; }
    .article-render-container ul, .article-render-container ol { margin-bottom: 2rem; padding-inline-start: 1.5rem; }
    .article-render-container li { margin-bottom: 0.75rem; }
    .article-render-container strong { color: var(--aw-cyan); font-weight: 800; }
    .article-render-container blockquote { border-left: 4px solid var(--aw-cyan); padding: 1rem 1.5rem; background: var(--aw-cyan-10); border-radius: 0 12px 12px 0; margin: 2rem 0; font-style: italic; }
    .article-render-container .quick-summary { background: var(--aw-cyan-10); border-left: 4px solid var(--aw-cyan); padding: 2rem; border-radius: 16px; margin-bottom: 3rem; }
    .article-render-container .quick-summary::before { content: 'QUICK SUMMARY'; display: block; font-size: 0.7rem; font-weight: 900; letter-spacing: 2px; color: var(--aw-cyan); margin-bottom: 1rem; }

    /* ===== ANIMATIONS ===== */
    .animate-in { animation: aw-enter 0.3s ease-out; }
    @keyframes aw-enter { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* ===== HISTORY ITEMS ===== */
    .history-item {
        padding: 1rem 1.25rem;
        padding-right: 3rem;
        border-bottom: 1px solid var(--aw-border);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .history-item:hover { background: var(--aw-surface-hover); }
    .history-item-active {
        background: var(--aw-cyan-10) !important;
        border-right: 3px solid var(--aw-cyan);
    }

    /* ===== RTL ADAPTATIONS ===== */
    [dir="rtl"] .aw-card .form-control,
    [dir="rtl"] .aw-card input[type="text"] {
        padding: 1rem 3rem 1rem 1rem !important;
    }
    [dir="rtl"] .aw-search-wrap i {
        left: auto !important;
        right: 1.25rem !important;
    }
    [dir="rtl"] .aw-select {
        background-position: left 1.25rem center !important;
        padding-left: 2.5rem !important;
        padding-right: 1.25rem !important;
    }
    [dir="rtl"] .history-item {
        padding-right: 1.25rem !important;
        padding-left: 3rem !important;
    }
    [dir="rtl"] .delete-btn {
        right: auto !important;
        left: 1rem !important;
    }
    [dir="rtl"] .history-item-active {
        border-right: none !important;
        border-left: 3px solid var(--aw-cyan) !important;
    }
    [dir="rtl"] .article-render-container blockquote {
        border-left: none !important;
        border-right: 4px solid var(--aw-cyan) !important;
        border-radius: 12px 0 0 12px !important;
    }
    [dir="rtl"] .article-render-container .quick-summary {
        border-left: none !important;
        border-right: 4px solid var(--aw-cyan) !important;
    }

    /* ===== RESULT TABS ===== */
    .result-tab {
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.2s ease;
        color: var(--aw-text-dim);
        background: transparent;
        border: 1px solid transparent;
    }
    .result-tab:hover { color: #fff; }
    .result-tab-active {
        background: var(--aw-cyan-10) !important;
        color: var(--aw-cyan) !important;
        border-color: var(--aw-cyan-20) !important;
    }
    .delete-btn:hover {
        opacity: 1 !important;
        background: #ff4b4b !important;
        color: #fff !important;
        transform: translateY(-50%) scale(1.1);
    }
    .history-item-container:hover .delete-btn {
        opacity: 0.7 !important;
    }
</style>
@endpush

@push('scripts')
<script>
function articleWriter() {
    return {
        view: 'form',
        articleTab: 'read',
        isProcessing: false,
        history: @json($history),
        settings: @json($settings),
        currentArticle: null,
        form: {
            keyword: '',
            language: 'en',
            tone: 'professional',
            audience: 'general',
            word_count: {{ $settings['default_word_count'] ?? 1500 }},
            components: ['faq', 'summary', 'takeaways', 'meta']
        },

        i18n: {
            en: {
                title: 'Pro AI Article Writer',
                subtitle: 'SEO-optimized content engine with E-E-A-T compliance',
                engine_label: 'ENGINE',
                engine_val: 'AI POWERED',
                output_label: 'OUTPUT',
                output_val: 'HTML + SEO',
                saved_content: 'Saved Content',
                no_saved: 'No articles generated yet. Create your first masterpiece!',
                words: 'words',
                primary_keyword: 'Primary Keyword or Topic',
                keyword_placeholder: 'e.g., Best AI Tools for Digital Marketing in 2026...',
                language: 'Article Language',
                editorial_tone: 'Editorial Tone',
                target_audience: 'Target Audience',
                persona_guide: 'Persona Mastery Guide',
                persona_guide_hint: 'Click any preset to auto-apply tone & audience',
                news_trends: 'NEWS & TRENDS',
                news_trends_desc: 'Informative + General Audience',
                gold_markets: 'MARKETS & GOLD',
                gold_markets_desc: 'Professional + General Audience',
                real_estate: 'SHOPPING & REVIEWS',
                real_estate_desc: 'Creative & Engaging + Shoppers',
                business_b2b: 'BUSINESS & TECH',
                business_b2b_desc: 'Professional + Industry Experts',
                edu_blog: 'HOW-TO & GUIDES',
                edu_blog_desc: 'Informative + Beginners',
                article_length: 'Article Length',
                length_mini: 'MINI',
                length_micro: 'MICRO',
                length_short: 'SHORT',
                length_medium: 'MEDIUM',
                length_long: 'LONG',
                include_in_article: 'Include in Article',
                btn_generate: 'GENERATE ARTICLE',
                btn_generating: 'CRAFTING CONTENT...',
                engine_active: 'Content Engine Active',
                engine_simulating: 'Simulating SERP analysis, applying E-E-A-T signals, building heading hierarchy...',
                engine_time: 'This may take 30-60 seconds for comprehensive articles.',
                btn_new: 'New Article',
                btn_copy_html: 'Copy HTML',
                btn_copy_text: 'Copy Text',
                tab_article: 'Article View',
                tab_seo: 'SEO & Meta',
                tab_raw: 'Raw Code',
                seo_title_tag: 'SEO Title Tag',
                meta_desc: 'Meta Description',
                meta_desc_missing: 'Not generated — check Raw Code tab',
                suggested_url: 'Suggested URL / Slug',
                en_url: 'English URL',
                ar_url: 'Arabic URL',
                url_note: 'Auto-generated from the article title. Both slugs are sanitized for SEO — English is lowercase ASCII, Arabic preserves native letters with hyphens.',
                serp_preview: 'Google SERP Preview',
                stat_status: 'Status',
                stat_status_val: 'INDEX READY',
                stat_hierarchy: 'Hierarchy',
                stat_hierarchy_val: 'STRICT H1-H3',
                stat_eeat: 'E-E-A-T',
                stat_eeat_val: 'VERIFIED',
                stat_words: 'Word Count',
                alert_generated: 'Article Generated Successfully!',
                alert_html_copied: 'HTML Copied to Clipboard!',
                alert_text_copied: 'Plain Text Copied!',
                alert_val_copied: 'Value copied',
                alert_confirm_del: 'Are you sure?',
                alert_del_text: 'This article will be permanently removed from your history.',
                alert_del_btn: 'Yes, Delete',
                alert_cancel: 'Cancel',
                alert_deleted: 'Article Deleted',
                words_300: '~300',
                words_500: '~500',
                words_800: '~800',
                words_1500: '~1.5k',
                words_2500: '~2.5k',
                tones: {
                    professional: 'Professional',
                    informative: 'Informative',
                    casual: 'Casual & Friendly',
                    authoritative: 'Authoritative Expert',
                    creative: 'Creative & Engaging',
                    marketers: 'Marketing & Sales',
                    academic: 'Academic & Research',
                    journalistic: 'Journalistic'
                },
                audiences: {
                    general: 'General Audience',
                    professionals: 'Industry Professionals',
                    beginners: 'Beginners & Learners',
                    shoppers: 'Online Shoppers',
                    marketers: 'Marketers & Growth',
                    developers: 'Developers & Technical',
                    investors: 'Investors & Executives'
                },
                components: {
                    faq: 'FAQ Section',
                    summary: 'Quick Summary',
                    takeaways: 'Key Takeaways',
                    meta: 'SEO Meta Tags',
                    internal_links: 'Internal Link Suggestions'
                }
            },
            ar: {
                title: 'كاتب المقالات الاحترافي بالذكاء الاصطناعي',
                subtitle: 'محرك محتوى متوافق مع معايير SEO و E-E-A-T وخلاصات Google Discover',
                engine_label: 'المحرك',
                engine_val: 'ذكاء اصطناعي',
                output_label: 'المخرجات',
                output_val: 'HTML + SEO جاهز',
                saved_content: 'المقالات المحفوظة',
                no_saved: 'لم يتم إنشاء أي مقالات بعد. ابدأ بصناعة مقالك الأول الآن!',
                words: 'كلمة',
                primary_keyword: 'الكلمة المفتاحية أو موضوع المقال',
                keyword_placeholder: 'مثال: أفضل أدوات الذكاء الاصطناعي لكتابة المقالات في 2026...',
                language: 'لغة المقال',
                editorial_tone: 'أسلوب ونبرة الصياغة',
                target_audience: 'الجمهور المستهدف',
                persona_guide: 'دليل اختيار نبرة وأسلوب المقال',
                persona_guide_hint: 'اضغط على أي نمط لتطبيقه تلقائياً بنقرة واحدة',
                news_trends: 'الأخبار والترندات',
                news_trends_desc: 'إخباري وتثقيفي + الجمهور العام',
                gold_markets: 'الذهب والأسواق والأسعار',
                gold_markets_desc: 'احترافي وموثوق + الجمهور العام',
                real_estate: 'المراجعات والمشتريات',
                real_estate_desc: 'إبداعي وشيق + المتسوقين والمهتمين بالشراء',
                business_b2b: 'الأعمال والتقنية',
                business_b2b_desc: 'احترافي وموثوق + المتخصصين والخبراء',
                edu_blog: 'الشروحات والأدلة',
                edu_blog_desc: 'إخباري وتثقيفي + المبتدئين والباحثين عن تعلم',
                article_length: 'طول المقال المطلوب',
                length_mini: 'موجز',
                length_micro: 'قصير جداً',
                length_short: 'قصير',
                length_medium: 'متوسط',
                length_long: 'شامل ومفصل',
                words_300: '~300 كلمة',
                words_500: '~500 كلمة',
                words_800: '~800 كلمة',
                words_1500: '~1.5 ألف كلمة',
                words_2500: '~2.5 ألف كلمة',
                include_in_article: 'عناصر وتنسيقات المقال الإضافية',
                btn_generate: 'توليد المقال بالذكاء الاصطناعي',
                btn_generating: 'جاري صياغة المقال وتوليد السيو...',
                engine_active: 'محرك الذكاء الاصطناعي يعمل الآن',
                engine_simulating: 'تحليل نتائج البحث، تطبيق إشارات E-E-A-T، وهيكلة العناوين H1-H3...',
                engine_time: 'قد يستغرق توليد المقال الشامل بين 30 إلى 60 ثانية.',
                btn_new: 'مقال جديد',
                btn_copy_html: 'نسخ كود HTML',
                btn_copy_text: 'نسخ النص',
                tab_article: 'معاينة المقال',
                tab_seo: 'بيانات السيو والميتا',
                tab_raw: 'الكود المصدري الخام',
                seo_title_tag: 'عنوان السيو المخصص (Title Tag)',
                meta_desc: 'الوصف التعريفي (Meta Description)',
                meta_desc_missing: 'لم يتم التوليد — تحقق من تبويب الكود الخام',
                suggested_url: 'الرابط الموصى به للمقال (Slug)',
                en_url: 'رابط إنجليزي',
                ar_url: 'رابط عربي',
                url_note: 'تم التوليد تلقائياً من عنوان المقال ومطابقته لمعايير السيو (SEO-Friendly). الرابط الإنجليزي بأحرف ASCII والرابط العربي يحتفظ بالأحرف الأصلية مفصولة بشرطات.',
                serp_preview: 'معاينة النتيجة في بحث جوجل (SERP Preview)',
                stat_status: 'الحالة',
                stat_status_val: 'جاهز للأرشفة',
                stat_hierarchy: 'التسلسل',
                stat_hierarchy_val: 'منظم H1-H3',
                stat_eeat: 'معايير E-E-A-T',
                stat_eeat_val: 'موثوق ومعتمد',
                stat_words: 'عدد الكلمات',
                alert_generated: 'تم إنشاء المقال بنجاح!',
                alert_html_copied: 'تم نسخ كود HTML إلى الحافظة!',
                alert_text_copied: 'تم نسخ النص إلى الحافظة!',
                alert_val_copied: 'تم النسخ بنجاح',
                alert_confirm_del: 'هل أنت متأكد؟',
                alert_del_text: 'سيتم حذف هذا المقال نهائياً من سجل المقالات لديك.',
                alert_del_btn: 'نعم، احذف',
                alert_cancel: 'إلغاء',
                alert_deleted: 'تم حذف المقال',
                tones: {
                    professional: 'احترافي وموثوق (Professional)',
                    informative: 'إخباري وتثقيفي (Informative)',
                    casual: 'ودي وجذاب (Casual & Friendly)',
                    authoritative: 'خبير متخصص ومعتمد (Authoritative)',
                    creative: 'إبداعي وشيق (Creative)',
                    marketers: 'تسويقي وترويجي (Marketing)',
                    academic: 'أكاديمي وبحثي (Academic)',
                    journalistic: 'صحفي واستقصائي (Journalistic)'
                },
                audiences: {
                    general: 'الجمهور العام (General)',
                    professionals: 'المتخصصين والخبراء (Professionals)',
                    beginners: 'المبتدئين والباحثين عن تعلم (Beginners)',
                    shoppers: 'المتسوقين والمهتمين بالشراء (Shoppers)',
                    marketers: 'المسوقين ورواد الأعمال (Marketers)',
                    developers: 'المطورين والتقنيين (Developers)',
                    investors: 'المستثمرين ورجال الأعمال (Investors)'
                },
                components: {
                    faq: 'قسم الأسئلة الشائعة (FAQ)',
                    summary: 'الموجز التنفيذي السريع (Summary)',
                    takeaways: 'أهم النقاط المستخلصة (Takeaways)',
                    meta: 'بيانات السيو والعنوان الميتا (Meta)',
                    internal_links: 'اقتراحات الروابط الداخلية (Links)'
                }
            }
        },

        applyPersonaPreset(tone, audience) {
            this.form.tone = tone;
            this.form.audience = audience;
        },

        t(key) {
            const lang = this.form.language === 'ar' ? 'ar' : 'en';
            return this.i18n[lang]?.[key] || this.i18n['en']?.[key] || key;
        },

        getToneLabel(tone) {
            const key = typeof tone === 'object' ? tone.value : tone;
            const lang = this.form.language === 'ar' ? 'ar' : 'en';
            return this.i18n[lang]?.tones?.[key] || (typeof tone === 'object' ? tone.label : tone);
        },

        getAudienceLabel(audience) {
            const key = typeof audience === 'object' ? audience.value : audience;
            const lang = this.form.language === 'ar' ? 'ar' : 'en';
            return this.i18n[lang]?.audiences?.[key] || (typeof audience === 'object' ? audience.label : audience);
        },

        getComponentLabel(comp) {
            const key = typeof comp === 'object' ? comp.value : comp;
            const lang = this.form.language === 'ar' ? 'ar' : 'en';
            return this.i18n[lang]?.components?.[key] || (typeof comp === 'object' ? comp.label : comp);
        },

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('keyword')) {
                this.form.keyword = urlParams.get('keyword');
            } else if (urlParams.has('topic')) {
                this.form.keyword = urlParams.get('topic');
            }
            
            // Auto-detect language from prefilled keyword if Arabic characters are present
            if (this.form.keyword && /[\u0600-\u06FF]/.test(this.form.keyword)) {
                this.form.language = 'ar';
            } else if (this.settings.languages && this.settings.languages.length > 0) {
                this.form.language = this.settings.languages[0].value;
            }
        },

        toggleComponent(value) {
            const idx = this.form.components.indexOf(value);
            if (idx > -1) {
                this.form.components.splice(idx, 1);
            } else {
                this.form.components.push(value);
            }
        },

        async generate() {
            if (!this.form.keyword) return;
            
            this.isProcessing = true;
            this.currentArticle = null;

            try {
                const response = await fetch('{{ route("dashboard.article-writer.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.currentArticle = data.article;
                    this.history.unshift(data.article);
                    this.view = 'result';
                    this.articleTab = 'read';

                    if (window.VidaCredits) {
                        if (typeof data.balance !== 'undefined') {
                            window.VidaCredits.updateAll(data.balance);
                        } else {
                            window.VidaCredits.refresh();
                        }
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: this.t('alert_generated'),
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#17181c',
                        color: '#fff'
                    });
                } else {
                    this.showError(data.message || (this.form.language === 'ar' ? 'فشل التوليد. يرجى المحاولة مرة أخرى.' : 'Generation failed. Please try again.'));
                }
            } catch (err) {
                this.showError(this.form.language === 'ar' ? 'خطأ في الاتصال. يرجى التحقق من الشبكة والمحاولة مجدداً.' : 'Connection error. Please check your network and try again.');
            } finally {
                this.isProcessing = false;
            }
        },

        async loadArticle(id) {
            this.isProcessing = true;
            try {
                const res = await fetch(`{{ url('dashboard/article-writer') }}/${id}`);
                const data = await res.json();
                this.currentArticle = data;
                if (data.language) {
                    this.form.language = data.language;
                }
                this.view = 'result';
                this.articleTab = 'read';
            } catch (err) {
                this.showError(this.form.language === 'ar' ? 'فشل تحميل المقال' : 'Failed to load article');
            } finally {
                this.isProcessing = false;
            }
        },

        async deleteArticle(id) {
            const isAr = this.form.language === 'ar';
            const result = await Swal.fire({
                title: this.t('alert_confirm_del'),
                text: this.t('alert_del_text'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4b4b',
                cancelButtonColor: 'var(--aw-surface)',
                confirmButtonText: this.t('alert_del_btn'),
                cancelButtonText: this.t('alert_cancel'),
                background: '#17181c',
                color: '#fff'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`{{ url('dashboard/article-writer') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (data.status === 'success') {
                        this.history = this.history.filter(item => item.id !== id);
                        if (this.currentArticle && this.currentArticle.id === id) {
                            this.currentArticle = null;
                            this.view = 'form';
                        }
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: this.t('alert_deleted'),
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#17181c',
                            color: '#fff'
                        });
                    }
                } catch (err) {
                    this.showError(isAr ? 'فشل حذف المقال' : 'Finalizing delete failed');
                }
            }
        },

        copyContent() {
            if (!this.currentArticle) return;
            navigator.clipboard.writeText(this.currentArticle.content);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: this.t('alert_html_copied'),
                showConfirmButton: false,
                timer: 1500,
                background: '#17181c',
                color: '#fff'
            });
        },

        copyPlainText() {
            if (!this.currentArticle) return;
            const tmp = document.createElement('div');
            tmp.innerHTML = this.currentArticle.content;
            const plainText = tmp.textContent || tmp.innerText;
            navigator.clipboard.writeText(plainText);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: this.t('alert_text_copied'),
                showConfirmButton: false,
                timer: 1500,
                background: '#17181c',
                color: '#fff'
            });
        },

        seoData() {
            if (!this.currentArticle) return {};
            const raw = this.currentArticle.seo_data;
            if (!raw) return {};
            if (typeof raw === 'string') {
                try { return JSON.parse(raw) || {}; } catch (e) { return {}; }
            }
            return raw;
        },
        slugEn() {
            return (this.seoData().slug_en || '').toString();
        },
        slugAr() {
            return (this.seoData().slug_ar || '').toString();
        },
        suggestedDomain() {
            return this.seoData().site_domain || '{{ parse_url(config('app.url', 'https://yoursite.com'), PHP_URL_HOST) ?: 'yoursite.com' }}';
        },
        copyValue(value, label) {
            if (!value) return;
            navigator.clipboard.writeText(value).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: (label || this.t('alert_val_copied')) + (this.form.language === 'ar' ? ' (تم النسخ)' : ' copied'),
                    showConfirmButton: false,
                    timer: 1500,
                    background: '#17181c',
                    color: '#fff'
                });
            });
        },

        showError(msg) {
            Swal.fire({
                icon: 'error',
                title: this.form.language === 'ar' ? 'تنبيه' : 'Error',
                text: msg,
                background: '#17181c',
                color: '#fff',
                confirmButtonColor: '#0ea5e9'
            });
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            const locale = this.form.language === 'ar' ? 'ar-EG' : 'en-US';
            return date.toLocaleDateString(locale, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
@endpush
@endsection
