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
                <button @click="openWpSettingsModal()" type="button" 
                        class="btn-wp-header"
                        :title="form.language === 'ar' ? 'إعدادات ربط ووردبريس والمواقع' : 'WordPress & CMS Integration Settings'">
                    <div class="wp-icon-badge">
                        <i class="fab fa-wordpress"></i>
                    </div>
                    <div class="d-flex flex-column text-start">
                        <span style="font-size: 0.85rem; font-weight: 800; color: #fff; line-height: 1.2;" x-text="form.language === 'ar' ? 'إعدادات ووردبريس' : 'WordPress Settings'"></span>
                        <span style="font-size: 0.68rem; color: #38bdf8; font-weight: 600;" x-text="cmsConnections.length > 0 ? (cmsConnections.length + (form.language === 'ar' ? ' موقع متصل' : ' sites connected')) : (form.language === 'ar' ? 'ربط موقعك الآن' : 'Connect Site')"></span>
                    </div>
                </button>
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
                <div class="aw-sidebar-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="aw-sidebar-title" x-text="t('saved_content')">Saved Content</span>
                        <i class="fas fa-history" style="color: var(--aw-text-dim); font-size: 0.85rem;"></i>
                    </div>
                    <button @click="openWpSettingsModal()" type="button" 
                            class="btn p-0 border-0 d-inline-flex align-items-center gap-1" 
                            style="background: rgba(0, 160, 210, 0.15); border: 1px solid rgba(0, 160, 210, 0.3) !important; color: #00d2ff; border-radius: 6px; font-size: 0.72rem; padding: 2px 7px; cursor: pointer;"
                            :title="form.language === 'ar' ? 'إدارة مواقع ووردبريس' : 'Manage WordPress'">
                        <i class="fab fa-wordpress"></i>
                        <span x-text="form.language === 'ar' ? 'المواقع' : 'Sites'"></span>
                    </button>
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
                            <!-- WordPress Quick Send Button -->
                            <button @click.stop="openWpPublishModal(item)" 
                                    class="wp-quick-send-btn"
                                    :title="form.language === 'ar' ? 'إرسال إلى ووردبريس كمسودة' : 'Send to WordPress as Draft'"
                                    style="position: absolute; top: 50%; inset-inline-end: 42px; transform: translateY(-50%); background: rgba(0, 115, 170, 0.15); border: 1px solid rgba(0, 160, 210, 0.3); color: #00a0d2; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; opacity: 0.5;">
                                <i class="fab fa-wordpress" style="font-size: 0.75rem;"></i>
                            </button>
                            <!-- Delete Button -->
                            <button @click.stop="deleteArticle(item.id)" 
                                    class="delete-btn"
                                    :title="t('alert_del_btn')"
                                    style="position: absolute; top: 50%; inset-inline-end: 10px; transform: translateY(-50%); background: rgba(255,75,75,0.1); border: 1px solid rgba(255,75,75,0.2); color: #ff4b4b; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; opacity: 0.4;">
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
                    
                    <!-- WordPress Fast Link Banner -->
                    <!-- WordPress Fast Link Banner -->
                    <div class="mb-4 p-3 d-flex align-items-center justify-content-between flex-wrap gap-3"
                         style="background: radial-gradient(circle at 0% 50%, rgba(0, 160, 210, 0.15) 0%, rgba(14, 165, 233, 0.05) 100%); border: 1px solid rgba(0, 160, 210, 0.35); border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="wp-icon-badge" style="width: 42px !important; height: 42px !important; font-size: 1.3rem !important;">
                                <i class="fab fa-wordpress"></i>
                            </div>
                            <div>
                                <div style="font-size: 0.88rem; font-weight: 800; color: #ffffff;">
                                    <span x-show="cmsConnections.length > 0" x-text="form.language === 'ar' ? ('متصل بـ ' + cmsConnections.length + ' موقع ووردبريس — سيتم إرسال المقال كمسودة بنقرة واحدة') : ('Connected to ' + cmsConnections.length + ' WordPress sites — One-click draft sending ready')"></span>
                                    <span x-show="cmsConnections.length === 0" x-text="form.language === 'ar' ? 'ربط ووردبريس: أرسل مقالاتك تلقائياً كمسودة مع العناوين والوسوم والتصنيف' : 'Connect WordPress: Send articles directly as drafts with tags and categories'"></span>
                                </div>
                                <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 2px;" x-text="form.language === 'ar' ? 'ربط رسمي آمن بدون أي إضافات عبر WordPress Application Passwords' : 'Secure official connection without plugins via Application Passwords'"></div>
                            </div>
                        </div>
                        <button @click="openWpSettingsModal()" type="button"
                                class="btn-wp-pill-test"
                                style="font-size: 0.82rem !important; padding: 0.55rem 1.25rem !important; border-radius: 12px !important;">
                            <i class="fas fa-cog"></i>
                            <span x-text="cmsConnections.length > 0 ? (form.language === 'ar' ? 'إدارة مواقع ووردبريس' : 'Manage Sites') : (form.language === 'ar' ? 'اضغط لربط موقعك الآن' : 'Connect Site Now')"></span>
                        </button>
                    </div>

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
                        <div style="position: absolute; right: -10px; top: -10px; font-size: 4.5rem; color: var(--aw-cyan-10); opacity: 0.2; transform: rotate(-15deg); pointer-events: none;">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fas fa-magic" style="color: var(--aw-cyan);"></i>
                                <h4 style="margin: 0; font-size: 0.88rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px;" x-text="t('persona_guide')">Persona Mastery Guide</h4>
                            </div>
                            <span style="font-size: 0.72rem; color: var(--aw-cyan); font-weight: 700; background: rgba(6,182,212,0.12); padding: 0.25rem 0.75rem; border-radius: 20px; border: 1px solid var(--aw-cyan-20);" x-text="t('persona_guide_hint')">Click any preset to auto-apply tone & audience</span>
                        </div>
                        
                        <div class="persona-grid">
                            <!-- Preset 1: News & Trends -->
                            <div @click="applyPersonaPreset('informative', 'general')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'informative' && form.audience === 'general' ? 'background: rgba(6,182,212,0.2); border-color: var(--aw-cyan); box-shadow: 0 0 16px -2px rgba(6,182,212,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: var(--aw-cyan);">
                                            <i class="fas fa-newspaper"></i> <span x-text="t('news_trends')"></span>
                                        </span>
                                        <i x-show="form.tone === 'informative' && form.audience === 'general'" class="fas fa-check-circle" style="color: var(--aw-cyan); font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('news_trends_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('news_trends_hint')"></div>
                            </div>

                            <!-- Preset 2: Markets & Gold & Economy -->
                            <div @click="applyPersonaPreset('professional', 'general')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'professional' && form.audience === 'general' ? 'background: rgba(245,158,11,0.2); border-color: #f59e0b; box-shadow: 0 0 16px -2px rgba(245,158,11,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #f59e0b;">
                                            <i class="fas fa-coins"></i> <span x-text="t('gold_markets')"></span>
                                        </span>
                                        <i x-show="form.tone === 'professional' && form.audience === 'general'" class="fas fa-check-circle" style="color: #f59e0b; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('gold_markets_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('gold_markets_hint')"></div>
                            </div>

                            <!-- Preset 3: E-Commerce & WooCommerce Stores -->
                            <div @click="applyPersonaPreset('marketers', 'shoppers')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'marketers' && form.audience === 'shoppers' ? 'background: rgba(16,185,129,0.2); border-color: #10b981; box-shadow: 0 0 16px -2px rgba(16,185,129,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #10b981;">
                                            <i class="fas fa-store"></i> <span x-text="t('ecommerce_store')"></span>
                                        </span>
                                        <i x-show="form.tone === 'marketers' && form.audience === 'shoppers'" class="fas fa-check-circle" style="color: #10b981; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('ecommerce_store_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('ecommerce_store_hint')"></div>
                            </div>

                            <!-- Preset 4: Reviews & Product Comparisons -->
                            <div @click="applyPersonaPreset('creative', 'shoppers')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'creative' && form.audience === 'shoppers' ? 'background: rgba(168,85,247,0.2); border-color: #a855f7; box-shadow: 0 0 16px -2px rgba(168,85,247,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #a855f7;">
                                            <i class="fas fa-balance-scale"></i> <span x-text="t('product_reviews')"></span>
                                        </span>
                                        <i x-show="form.tone === 'creative' && form.audience === 'shoppers'" class="fas fa-check-circle" style="color: #a855f7; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('product_reviews_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('product_reviews_hint')"></div>
                            </div>

                            <!-- Preset 5: Tech, Software & Developers -->
                            <div @click="applyPersonaPreset('professional', 'developers')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'professional' && form.audience === 'developers' ? 'background: rgba(14,165,233,0.2); border-color: #0ea5e9; box-shadow: 0 0 16px -2px rgba(14,165,233,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #0ea5e9;">
                                            <i class="fas fa-code"></i> <span x-text="t('tech_dev')"></span>
                                        </span>
                                        <i x-show="form.tone === 'professional' && form.audience === 'developers'" class="fas fa-check-circle" style="color: #0ea5e9; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('tech_dev_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('tech_dev_hint')"></div>
                            </div>

                            <!-- Preset 6: Business B2B & Startups -->
                            <div @click="applyPersonaPreset('professional', 'professionals')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'professional' && form.audience === 'professionals' ? 'background: rgba(20,184,166,0.2); border-color: #14b8a6; box-shadow: 0 0 16px -2px rgba(20,184,166,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #14b8a6;">
                                            <i class="fas fa-briefcase"></i> <span x-text="t('business_b2b')"></span>
                                        </span>
                                        <i x-show="form.tone === 'professional' && form.audience === 'professionals'" class="fas fa-check-circle" style="color: #14b8a6; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('business_b2b_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('business_b2b_hint')"></div>
                            </div>

                            <!-- Preset 7: Step-by-Step & How-To Guides -->
                            <div @click="applyPersonaPreset('informative', 'beginners')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'informative' && form.audience === 'beginners' ? 'background: rgba(99,102,241,0.2); border-color: #6366f1; box-shadow: 0 0 16px -2px rgba(99,102,241,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #818cf8;">
                                            <i class="fas fa-graduation-cap"></i> <span x-text="t('edu_guides')"></span>
                                        </span>
                                        <i x-show="form.tone === 'informative' && form.audience === 'beginners'" class="fas fa-check-circle" style="color: #818cf8; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('edu_guides_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('edu_guides_hint')"></div>
                            </div>

                            <!-- Preset 8: Health, Wellness & Medical -->
                            <div @click="applyPersonaPreset('authoritative', 'general')"
                                 class="persona-card"
                                 role="button"
                                 tabindex="0"
                                 :style="form.tone === 'authoritative' && form.audience === 'general' ? 'background: rgba(244,63,94,0.2); border-color: #f43f5e; box-shadow: 0 0 16px -2px rgba(244,63,94,0.5);' : ''">
                                <div>
                                    <div class="persona-card-header">
                                        <span class="persona-card-title" style="color: #f43f5e;">
                                            <i class="fas fa-heartbeat"></i> <span x-text="t('health_medical')"></span>
                                        </span>
                                        <i x-show="form.tone === 'authoritative' && form.audience === 'general'" class="fas fa-check-circle" style="color: #f43f5e; font-size: 0.85rem;"></i>
                                    </div>
                                    <div class="persona-card-desc" x-text="t('health_medical_desc')"></div>
                                </div>
                                <div class="persona-card-hint" x-text="t('health_medical_hint')"></div>
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
                                <div style="font-size: 0.65rem; color: #00A58B; font-weight: 800; margin-top: 3px;" x-text="getTierCreditCost(300) + (form.language === 'ar' ? ' كريديت' : ' CRS')"></div>
                            </button>
                            <button @click="form.word_count = 500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_micro')">MICRO</div>
                                <div class="length-card-words" x-text="t('words_500')">~500</div>
                                <div style="font-size: 0.65rem; color: #00A58B; font-weight: 800; margin-top: 3px;" x-text="getTierCreditCost(500) + (form.language === 'ar' ? ' كريديت' : ' CRS')"></div>
                            </button>
                            <button @click="form.word_count = 800" type="button"
                                    class="length-card"
                                    :class="form.word_count === 800 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_short')">SHORT</div>
                                <div class="length-card-words" x-text="t('words_800')">~800</div>
                                <div style="font-size: 0.65rem; color: #00A58B; font-weight: 800; margin-top: 3px;" x-text="getTierCreditCost(800) + (form.language === 'ar' ? ' كريديت' : ' CRS')"></div>
                            </button>
                            <button @click="form.word_count = 1500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 1500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_medium')">MEDIUM</div>
                                <div class="length-card-words" x-text="t('words_1500')">~1.5k</div>
                                <div style="font-size: 0.65rem; color: var(--aw-cyan); font-weight: 800; margin-top: 3px;" x-text="getTierCreditCost(1500) + (form.language === 'ar' ? ' كريديت' : ' CRS')"></div>
                            </button>
                            <button @click="form.word_count = 2500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 2500 ? 'length-card-active' : ''">
                                <div class="length-card-title" x-text="t('length_long')">LONG</div>
                                <div class="length-card-words" x-text="t('words_2500')">~2.5k</div>
                                <div style="font-size: 0.65rem; color: #f59e0b; font-weight: 800; margin-top: 3px;" x-text="getTierCreditCost(2500) + (form.language === 'ar' ? ' كريديت' : ' CRS')"></div>
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
                                <span class="aw-generate-cost" x-text="getCurrentCreditCost() + (form.language === 'ar' ? ' كريديت' : ' CRS')"></span>
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
                                <div style="font-size: 0.72rem; font-weight: 800; margin-top: 6px; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                    <span style="background: rgba(0, 210, 255, 0.12); color: var(--aw-cyan); padding: 0.15rem 0.55rem; border-radius: 6px; border: 1px solid var(--aw-cyan-20); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-file-word"></i>
                                        <span x-text="getLiveWordCount().toLocaleString() + ' ' + (form.language === 'ar' ? 'كلمة' : 'words')"></span>
                                    </span>
                                    <span style="background: rgba(16, 185, 129, 0.12); color: #10b981; padding: 0.15rem 0.55rem; border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-clock"></i>
                                        <span x-text="'~' + getLiveReadingTime() + ' ' + (form.language === 'ar' ? 'دقيقة قراءة' : 'min read')"></span>
                                    </span>
                                    <span style="background: rgba(255, 255, 255, 0.06); color: #e5e7eb; padding: 0.15rem 0.55rem; border-radius: 6px; border: 1px solid var(--aw-border); display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-font"></i>
                                        <span x-text="getLiveCharCount().toLocaleString() + ' ' + (form.language === 'ar' ? 'حرف' : 'chars')"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-shrink: 0; align-items: center; flex-wrap: wrap;">
                            <button @click="view = 'form'" style="background: var(--aw-surface); border: 1px solid var(--aw-border); color: #fff; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-plus" style="margin-right: 4px;"></i> <span x-text="t('btn_new')"></span>
                            </button>
                            <!-- Send to WordPress Button -->
                            <button @click="openWpPublishModal()" class="btn-wp-action">
                                <i class="fab fa-wordpress" style="font-size: 1.05rem;"></i>
                                <span x-text="t('btn_wp_send')"></span>
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
                            <!-- Article Stats Quick Bar -->
                            <div style="margin-bottom: 2rem; padding: 0.85rem 1.25rem; background: rgba(0, 0, 0, 0.35); border: 1px solid var(--aw-border); border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 9px; background: rgba(0, 210, 255, 0.15); display: flex; align-items: center; justify-content: center; color: var(--aw-cyan); font-size: 0.9rem;">
                                            <i class="fas fa-file-word"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.62rem; color: var(--aw-text-label); font-weight: 800; text-transform: uppercase;" x-text="form.language === 'ar' ? 'إجمالي الكلمات' : 'Total Words'"></div>
                                            <div style="font-size: 0.95rem; color: #fff; font-weight: 800;" x-text="getLiveWordCount().toLocaleString() + (form.language === 'ar' ? ' كلمة' : ' words')"></div>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 9px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 0.9rem;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.62rem; color: var(--aw-text-label); font-weight: 800; text-transform: uppercase;" x-text="form.language === 'ar' ? 'وقت القراءة' : 'Reading Time'"></div>
                                            <div style="font-size: 0.95rem; color: #fff; font-weight: 800;" x-text="'~' + getLiveReadingTime() + (form.language === 'ar' ? ' دقيقة' : ' min')"></div>
                                        </div>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 9px; background: rgba(168, 85, 247, 0.15); display: flex; align-items: center; justify-content: center; color: #a855f7; font-size: 0.9rem;">
                                            <i class="fas fa-font"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.62rem; color: var(--aw-text-label); font-weight: 800; text-transform: uppercase;" x-text="form.language === 'ar' ? 'عدد الحروف' : 'Characters'"></div>
                                            <div style="font-size: 0.95rem; color: #fff; font-weight: 800;" x-text="getLiveCharCount().toLocaleString()"></div>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="font-size: 0.72rem; font-weight: 800; color: var(--aw-cyan); background: var(--aw-cyan-10); border: 1px solid var(--aw-cyan-20); padding: 0.3rem 0.75rem; border-radius: 8px;" x-text="(currentArticle.language || 'AR').toUpperCase()"></span>
                                </div>
                            </div>

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

                            <!-- Suggested Tags & CMS Publishing Section -->
                            <div style="margin-top: 1.5rem; padding: 1.5rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 16px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                                    <div class="aw-label" style="margin-bottom: 0;">
                                        <i class="fas fa-tags" style="color: var(--aw-cyan);"></i> <span x-text="t('wp_tags_label')"></span>
                                    </div>
                                    <button type="button" @click="openWpPublishModal()" style="background: linear-gradient(135deg, #0073aa, #00a0d2); border: 1px solid rgba(0, 160, 210, 0.4); color: #fff; padding: 0.35rem 0.85rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fab fa-wordpress"></i> <span x-text="t('btn_wp_send')"></span>
                                    </button>
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <template x-for="tag in (currentArticle.seo_data?.tags || extractSuggestedTags(currentArticle))" :key="tag">
                                        <span style="background: var(--aw-cyan-10); color: var(--aw-cyan); border: 1px solid var(--aw-cyan-20); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-hashtag" style="font-size: 0.65rem; opacity: 0.7;"></i>
                                            <span x-text="tag"></span>
                                        </span>
                                    </template>
                                </div>

                                <!-- CMS Sync Badge if article was already synced -->
                                <template x-if="currentArticle.seo_data?.cms_sync">
                                    <div style="margin-top: 1.25rem; padding: 0.85rem 1.25rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.2); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                                <i class="fab fa-wordpress"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.8rem; font-weight: 800; color: #fff;">
                                                    <span x-text="form.language === 'ar' ? 'تم الحفظ كـ مسودة في ووردبريس' : 'Saved as Draft in WordPress'"></span>
                                                    <span style="color: #10b981; font-size: 0.75rem;" x-text="' (#' + currentArticle.seo_data.cms_sync.post_id + ')'"></span>
                                                </div>
                                                <div style="font-size: 0.7rem; color: #94a3b8;" x-text="currentArticle.seo_data.cms_sync.connection_name + ' • ' + (currentArticle.seo_data.cms_sync.synced_at ? formatDate(currentArticle.seo_data.cms_sync.synced_at) : '')"></div>
                                            </div>
                                        </div>
                                        <a :href="currentArticle.seo_data.cms_sync.edit_url" target="_blank" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 0.35rem 0.85rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-external-link-alt"></i> <span x-text="t('wp_open_draft')"></span>
                                        </a>
                                    </div>
                                </template>
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

    <!-- ============================================================ -->
    <!-- 1. WORDPRESS PUBLISH MODAL (Send Article to WordPress Draft) -->
    <!-- ============================================================ -->
    <div x-show="isWpPublishOpen" 
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="wp-modal-backdrop"
         style="display: none;"
         @keydown.escape.window="closeWpPublishModal()"
         x-cloak>
        <div @click.away="closeWpPublishModal()" 
             class="wp-modal-dialog">
            
            <!-- Modal Header -->
            <div class="wp-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="wp-modal-header-icon">
                        <i class="fab fa-wordpress"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0;" x-text="t('wp_modal_title')">إرسال المقال إلى ووردبريس (مسودة)</h3>
                        <p style="font-size: 0.78rem; color: #94a3b8; margin: 2px 0 0 0;" x-text="t('wp_modal_subtitle')">سيتم إنشاء مسودة في موقعك للمراجعة والتعديل</p>
                    </div>
                </div>
                <button type="button" @click="closeWpPublishModal()" class="btn-wp-close" :title="form.language === 'ar' ? 'إغلاق' : 'Close'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="wp-modal-body custom-scrollbar">
                
                <!-- Notice Banner: Safe Draft Mode -->
                <div style="padding: 0.85rem 1.1rem; background: rgba(0, 160, 210, 0.08); border: 1px solid rgba(0, 160, 210, 0.25); border-radius: 14px; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.85rem;">
                    <i class="fas fa-shield-alt" style="color: #00d2ff; font-size: 1.25rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.8rem; color: #cbd5e1; line-height: 1.5;">
                        <strong style="color: #00d2ff;" x-text="form.language === 'ar' ? 'حفظ آمن كمسودة:' : 'Safe Draft Mode:'">حفظ آمن كمسودة:</strong>
                        <span x-text="form.language === 'ar' ? ' يتم إرسال المقال كمسودة (Draft) ليظل تحت مراجعتك الكاملة في ووردبريس قبل النشر للجمهور.' : ' The article will be saved as a Draft on your WordPress site for editorial review.'"></span>
                    </div>
                </div>

                <!-- Site Selection -->
                <div style="margin-bottom: 1.25rem;">
                    <div class="aw-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span><i class="fab fa-wordpress" style="color: #00d2ff;"></i> <span x-text="t('wp_select_site')">الموقع المستهدف</span></span>
                        <button type="button" @click="openWpSettingsModal()" style="background: transparent; border: none; color: #38bdf8; font-size: 0.76rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            <i class="fas fa-cog"></i> <span x-text="form.language === 'ar' ? 'إدارة المواقع' : 'Manage Sites'"></span>
                        </button>
                    </div>

                    <template x-if="cmsConnections.length === 0">
                        <div style="padding: 1.5rem; background: rgba(245, 158, 11, 0.08); border: 1px dashed rgba(245, 158, 11, 0.35); border-radius: 14px; text-align: center;">
                            <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.6rem; margin-bottom: 0.5rem; display: block;"></i>
                            <p style="font-size: 0.85rem; color: #fbbf24; margin-bottom: 0.85rem; font-weight: 700;" x-text="t('wp_no_sites')">لم تقم بربط أي موقع ووردبريس بعد.</p>
                            <button type="button" @click="openWpSettingsModal()" class="btn-wp-primary" style="padding: 0.55rem 1.3rem !important; font-size: 0.82rem !important;">
                                <i class="fas fa-plug"></i> <span x-text="t('wp_connect_now')">ربط موقعك الأول الآن</span>
                            </button>
                        </div>
                    </template>

                    <template x-if="cmsConnections.length > 0">
                        <div style="display: flex; gap: 0.6rem; align-items: center;">
                            <select x-model="wpPublishForm.connection_id" @change="loadWpCategories($event.target.value)" class="aw-select" style="flex: 1;">
                                <template x-for="conn in cmsConnections" :key="conn.id">
                                    <option :value="conn.id" x-text="conn.name + ' (' + conn.site_url + ')'"></option>
                                </template>
                            </select>
                            <button type="button" @click="openWpSettingsModal()" :title="t('wp_settings_title')" class="btn-wp-pill-test" style="height: 48px; border-radius: 12px; flex-shrink: 0; padding: 0 1rem !important;">
                                <i class="fas fa-cog" style="font-size: 1rem;"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Editable Title -->
                <div style="margin-bottom: 1.15rem;">
                    <div class="aw-label" style="margin-bottom: 0.5rem;">
                        <i class="fas fa-heading"></i> <span x-text="t('wp_article_title')">عنوان المقال</span>
                    </div>
                    <input type="text" x-model="wpPublishForm.title" class="form-control">
                </div>

                <!-- Editable Excerpt / Meta Description -->
                <div style="margin-bottom: 1.15rem;">
                    <div class="aw-label" style="margin-bottom: 0.5rem;">
                        <i class="fas fa-align-left"></i> <span x-text="t('wp_article_excerpt')">المقتطف / الوصف التعريفي</span>
                    </div>
                    <textarea x-model="wpPublishForm.excerpt" rows="2" class="form-control" style="resize: vertical; line-height: 1.5;"></textarea>
                </div>

                <!-- Category & Status Selector -->
                <div class="row g-3" style="margin-bottom: 1.15rem;">
                    <div class="col-md-7">
                        <div class="aw-label" style="margin-bottom: 0.5rem;">
                            <i class="fas fa-folder"></i> <span x-text="t('wp_category_label')">التصنيف في ووردبريس</span>
                        </div>
                        <select x-model="wpPublishForm.category_id" class="aw-select" :disabled="wpCategoriesLoading">
                            <option value="" x-text="wpCategoriesLoading ? (form.language === 'ar' ? 'جارٍ تحميل التصنيفات...' : 'Loading categories...') : t('wp_category_default')"></option>
                            <template x-for="cat in wpCategories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name + (cat.count ? ' (' + cat.count + ')' : '')"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="aw-label" style="margin-bottom: 0.5rem;">
                            <i class="fas fa-clipboard-check"></i> <span x-text="t('wp_status_label')">حالة المنشور</span>
                        </div>
                        <select x-model="wpPublishForm.status" class="aw-select">
                            <option value="draft" x-text="form.language === 'ar' ? 'مسودة (Draft) - للمراجعة' : 'Draft (Recommended)'"></option>
                            <option value="pending" x-text="form.language === 'ar' ? 'قيد المراجعة (Pending)' : 'Pending Review'"></option>
                            <option value="publish" x-text="form.language === 'ar' ? 'نشر مباشر (Publish)' : 'Publish Live'"></option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Tags Chip Editor -->
                <div style="margin-bottom: 1.15rem;">
                    <div class="aw-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span><i class="fas fa-tags" style="color: var(--aw-cyan);"></i> <span x-text="t('wp_tags_label')">الوسوم المقترحة</span></span>
                        <span style="font-size: 0.72rem; color: #38bdf8; font-weight: 700;" x-text="wpPublishForm.tags.length + (form.language === 'ar' ? ' وسم' : ' tags')"></span>
                    </div>
                    <div style="padding: 0.95rem; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px;">
                        <!-- Chips -->
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.85rem; min-height: 34px; align-items: center;">
                            <template x-for="(tag, idx) in wpPublishForm.tags" :key="idx">
                                <span style="background: rgba(0, 160, 210, 0.15); color: #00d2ff; border: 1px solid rgba(0, 160, 210, 0.35); padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 7px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
                                    <i class="fas fa-hashtag" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                    <span x-text="tag"></span>
                                    <i @click="removeWpTag(idx)" class="fas fa-times" style="cursor: pointer; opacity: 0.6; font-size: 0.75rem; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'" :title="form.language === 'ar' ? 'حذف' : 'Remove'"></i>
                                </span>
                            </template>
                            <span x-show="wpPublishForm.tags.length === 0" style="font-size: 0.78rem; color: #94a3b8; font-style: italic;" x-text="form.language === 'ar' ? 'لم تتم إضافة أي وسوم بعد.' : 'No tags added yet.'"></span>
                        </div>
                        <!-- Tag Input -->
                        <div style="display: flex; gap: 0.6rem;">
                            <input type="text" x-model="wpPublishForm.newTagInput" @keydown.enter.prevent="addWpTag()" :placeholder="t('wp_tags_placeholder')" class="form-control" style="font-size: 0.85rem !important;">
                            <button type="button" @click="addWpTag()" class="btn-wp-add-tag">
                                <i class="fas fa-plus"></i> <span x-text="form.language === 'ar' ? 'إضافة' : 'Add'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success Alert Card -->
                <div x-show="wpPublishSuccessData" class="animate-in" style="margin-top: 1.25rem; padding: 1.35rem; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 16px; text-align: center;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(16, 185, 129, 0.25); color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.4rem; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);">
                        <i class="fas fa-check"></i>
                    </div>
                    <h4 style="font-size: 1rem; font-weight: 800; color: #fff; margin-bottom: 0.35rem;" x-text="t('wp_success_title')">تم حفظ المقال بنجاح في ووردبريس!</h4>
                    <p style="font-size: 0.82rem; color: #94a3b8; margin-bottom: 1.15rem;" x-text="wpPublishSuccessData?.message"></p>
                    <div style="display: flex; justify-content: center; gap: 0.85rem; flex-wrap: wrap;">
                        <a :href="wpPublishSuccessData?.edit_url" target="_blank" class="btn-wp-primary" style="text-decoration: none !important;">
                            <i class="fas fa-external-link-alt"></i> <span x-text="t('wp_open_draft')">فتح المسودة في ووردبريس</span>
                        </a>
                        <button type="button" @click="closeWpPublishModal()" class="btn-wp-secondary" x-text="form.language === 'ar' ? 'إغلاق' : 'Close'"></button>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="wp-modal-footer">
                <button type="button" @click="closeWpPublishModal()" class="btn-wp-secondary" x-text="form.language === 'ar' ? 'إلغاء' : 'Cancel'"></button>
                <button type="button" @click="submitWpPublish()" :disabled="wpPublishLoading || cmsConnections.length === 0" class="btn-wp-primary">
                    <i x-show="wpPublishLoading" class="fas fa-spinner fa-spin"></i>
                    <i x-show="!wpPublishLoading" class="fab fa-wordpress"></i>
                    <span x-text="wpPublishLoading ? t('wp_btn_submitting') : t('wp_btn_submit')"></span>
                </button>
            </div>
        </div>
    </div>


    <!-- ============================================================ -->
    <!-- 2. WORDPRESS SETTINGS MODAL (Manage Connected Sites)         -->
    <!-- ============================================================ -->
    <div x-show="isWpSettingsOpen" 
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="wp-modal-backdrop"
         style="display: none;"
         @keydown.escape.window="closeWpSettingsModal()"
         x-cloak>
        <div @click.away="closeWpSettingsModal()" 
             class="wp-modal-dialog" style="max-width: 720px !important;">
            
            <!-- Modal Header -->
            <div class="wp-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="wp-modal-header-icon">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0;" x-text="t('wp_settings_title')">إدارة مواقع ووردبريس</h3>
                        <p style="font-size: 0.78rem; color: #94a3b8; margin: 2px 0 0 0;" x-text="t('wp_settings_subtitle')">ربط المواقع عبر كلمات مرور التطبيقات (Application Passwords)</p>
                    </div>
                </div>
                <button type="button" @click="closeWpSettingsModal()" class="btn-wp-close" :title="form.language === 'ar' ? 'إغلاق' : 'Close'">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="wp-modal-body custom-scrollbar">
                
                <!-- Existing Connected Sites List -->
                <div style="margin-bottom: 2rem;">
                    <div class="aw-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                        <span><i class="fab fa-wordpress" style="color: #00d2ff;"></i> <span x-text="form.language === 'ar' ? 'المواقع المرتبطة حالياً' : 'Connected Sites'"></span></span>
                        <span class="badge" style="background: rgba(0, 160, 210, 0.2); color: #00d2ff; border: 1px solid rgba(0, 160, 210, 0.4); font-size: 0.75rem; border-radius: 8px; padding: 3px 8px;" x-text="cmsConnections.length + (form.language === 'ar' ? ' موقع' : ' sites')"></span>
                    </div>

                    <template x-if="cmsConnections.length === 0">
                        <div style="padding: 1.75rem 1.5rem; background: rgba(0,0,0,0.25); border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: 16px; text-align: center; color: var(--aw-text-dim); font-size: 0.85rem;">
                            <i class="fas fa-globe" style="font-size: 2rem; margin-bottom: 0.6rem; color: #38bdf8; opacity: 0.6; display: block;"></i>
                            <span x-text="form.language === 'ar' ? 'لا يوجد أي موقع ووردبريس مرتبط بعد. استخدم النموذج أدناه لربط موقعك.' : 'No WordPress sites connected yet. Use the form below to connect your first site.'"></span>
                        </div>
                    </template>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <template x-for="conn in cmsConnections" :key="conn.id">
                            <div style="padding: 1rem 1.25rem; background: rgba(0, 0, 0, 0.35); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(0, 115, 170, 0.25); color: #00d2ff; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; border: 1px solid rgba(0, 160, 210, 0.3);">
                                        <i class="fab fa-wordpress"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 0.92rem; color: #fff;" x-text="conn.name"></div>
                                        <div style="font-size: 0.74rem; color: #94a3b8; display: flex; gap: 0.5rem; align-items: center; margin-top: 2px;">
                                            <span x-text="conn.site_url"></span>
                                            <span>•</span>
                                            <span x-text="'@' + conn.username"></span>
                                            <template x-if="conn.last_synced_at">
                                                <span>• <span style="color: #10b981;" x-text="(form.language === 'ar' ? 'آخر مزامنة: ' : 'Last sync: ') + conn.last_synced_at"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <button type="button" @click="testConnection(conn.id)" class="btn-wp-pill-test" :title="form.language === 'ar' ? 'فحص الاتصال' : 'Test Connection'">
                                        <i class="fas fa-sync-alt"></i> <span x-text="form.language === 'ar' ? 'فحص الاتصال' : 'Test'"></span>
                                    </button>
                                    <button type="button" @click="deleteConnection(conn.id)" class="btn-wp-pill-delete" :title="form.language === 'ar' ? 'حذف الموقع' : 'Delete Site'">
                                        <i class="fas fa-trash-alt"></i> <span x-text="form.language === 'ar' ? 'حذف' : 'Delete'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Add New Site Card -->
                <div style="padding: 1.6rem; background: rgba(0, 0, 0, 0.35); border: 1px solid rgba(0, 160, 210, 0.3); border-radius: 18px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);">
                    <h4 style="font-size: 1rem; font-weight: 800; color: #fff; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-plus-circle" style="color: var(--aw-cyan); font-size: 1.1rem;"></i>
                        <span x-text="t('wp_add_site_title')">ربط موقع ووردبريس جديد</span>
                    </h4>

                    <!-- How to get Application Password Guide Accordion -->
                    <div style="margin-bottom: 1.35rem; padding: 1.1rem; background: rgba(0, 160, 210, 0.08); border: 1px solid rgba(0, 160, 210, 0.22); border-radius: 14px;">
                        <div style="font-size: 0.8rem; font-weight: 800; color: #00d2ff; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle"></i> <span x-text="t('wp_guide_title')">كيفية استخراج كلمة مرور التطبيق في 3 خطوات:</span>
                        </div>
                        <ul style="margin: 0; padding-inline-start: 1.25rem; font-size: 0.76rem; color: #cbd5e1; line-height: 1.7;">
                            <li x-text="t('wp_guide_step1')">1. ادخل للوحة تحكم ووردبريس لموقعك > الأعضاء > حسابك الشخصي (Users > Profile).</li>
                            <li x-text="t('wp_guide_step2')">2. انزل لأسفل الصفحة إلى قسم كلمات مرور التطبيقات (Application Passwords).</li>
                            <li x-text="t('wp_guide_step3')">3. اكتب اسماً للتطبيق مثل "VidaNexus" واضغط "إضافة كلمة مرور تطبيق جديدة"، ثم انسخ الكلمة الناتجة والصقها أدناه.</li>
                        </ul>
                    </div>

                    <!-- Input Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="aw-label" style="margin-bottom: 0.5rem;">
                                <i class="fas fa-tag"></i> <span x-text="t('wp_site_name')">اسم الموقع</span>
                            </div>
                            <input type="text" x-model="wpConnectForm.name" :placeholder="t('wp_site_name_ph')" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <div class="aw-label" style="margin-bottom: 0.5rem;">
                                <i class="fas fa-link"></i> <span x-text="t('wp_site_url')">رابط الموقع (URL)</span>
                            </div>
                            <input type="url" x-model="wpConnectForm.site_url" :placeholder="t('wp_site_url_ph')" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <div class="aw-label" style="margin-bottom: 0.5rem;">
                                <i class="fas fa-user"></i> <span x-text="t('wp_username')">اسم المستخدم في ووردبريس</span>
                            </div>
                            <input type="text" x-model="wpConnectForm.username" :placeholder="t('wp_username_ph')" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <div class="aw-label" style="margin-bottom: 0.5rem;">
                                <i class="fas fa-key"></i> <span x-text="t('wp_app_password')">كلمة مرور التطبيق</span>
                            </div>
                            <div class="wp-password-wrap">
                                <input :type="wpConnectForm.showPassword ? 'text' : 'password'" x-model="wpConnectForm.api_key" :placeholder="t('wp_app_password_ph')" class="form-control">
                                <button type="button" @click="wpConnectForm.showPassword = !wpConnectForm.showPassword" class="wp-password-toggle">
                                    <i :class="wpConnectForm.showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- SSL Verification Toggle -->
                    <div style="margin-top: 1.15rem; display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" id="wp_verify_ssl" x-model="wpConnectForm.verify_ssl" style="width: 18px; height: 18px; cursor: pointer; accent-color: #0ea5e9;">
                        <label for="wp_verify_ssl" style="font-size: 0.78rem; color: #cbd5e1; cursor: pointer; margin: 0;" x-text="form.language === 'ar' ? 'التحقق من شهادة أمان SSL (ألغِ التحديد إذا كان الموقع تجريبياً أو محلياً)' : 'Verify SSL Certificate (uncheck for staging or localhost)'"></label>
                    </div>

                    <!-- Submit Add Button -->
                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="button" @click="submitAddConnection()" :disabled="wpConnectForm.isLoading" class="btn-wp-primary">
                            <i x-show="wpConnectForm.isLoading" class="fas fa-spinner fa-spin"></i>
                            <i x-show="!wpConnectForm.isLoading" class="fas fa-check-circle"></i>
                            <span x-text="wpConnectForm.isLoading ? t('wp_btn_testing') : t('wp_btn_test_save')"></span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="wp-modal-footer">
                <button type="button" @click="closeWpSettingsModal()" class="btn-wp-secondary" x-text="form.language === 'ar' ? 'إغلاق' : 'Close'"></button>
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
    /* ============================================================ */
    /* ===== WORDPRESS INTEGRATION & MODALS — CYBER-PREMIUM STYLING */
    /* ============================================================ */
    
    /* Zero-Drift Fullscreen Backdrop */
    .wp-modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
        display: flex;
        align-items: center !important;
        justify-content: center !important;
        background: rgba(3, 7, 18, 0.85) !important;
        backdrop-filter: blur(14px) saturate(160%) !important;
        -webkit-backdrop-filter: blur(14px) saturate(160%) !important;
        padding: 1.25rem !important;
        margin: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .wp-modal-backdrop[style*="display: none"],
    .wp-modal-backdrop[x-cloak] {
        display: none !important;
    }

    /* Perfectly Centered Modal Card */
    .wp-modal-dialog {
        width: 100% !important;
        max-width: 660px !important;
        max-height: calc(100vh - 3rem) !important;
        margin: auto !important;
        position: relative !important;
        background: radial-gradient(circle at 50% 0%, #111d38 0%, #080d1a 100%) !important;
        border: 1px solid rgba(0, 160, 210, 0.35) !important;
        border-radius: 24px !important;
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.95), 0 0 45px rgba(0, 160, 210, 0.15) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        transform-origin: center center !important;
        animation: wpModalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    @keyframes wpModalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.96) translateY(12px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    [data-theme="light"] .wp-modal-dialog {
        background: #ffffff !important;
        border-color: rgba(0, 160, 210, 0.3) !important;
        box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.25) !important;
    }

    /* Modal Header */
    .wp-modal-header {
        padding: 1.25rem 1.75rem !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: linear-gradient(135deg, rgba(0, 115, 170, 0.15), rgba(0, 160, 210, 0.05)) !important;
    }

    .wp-modal-header-icon {
        width: 44px !important;
        height: 44px !important;
        border-radius: 14px !important;
        background: linear-gradient(135deg, rgba(0, 115, 170, 0.35), rgba(0, 160, 210, 0.2)) !important;
        border: 1px solid rgba(0, 160, 210, 0.45) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #00d2ff !important;
        font-size: 1.35rem !important;
        box-shadow: 0 0 15px rgba(0, 160, 210, 0.25) !important;
        flex-shrink: 0 !important;
    }

    /* Modal Body */
    .wp-modal-body {
        padding: 1.5rem 1.75rem !important;
        overflow-y: auto !important;
        flex: 1 1 auto !important;
    }

    /* Modal Footer */
    .wp-modal-footer {
        padding: 1.25rem 1.75rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 0.85rem !important;
        background: rgba(0, 0, 0, 0.25) !important;
    }

    /* Circular Close Button */
    .btn-wp-close {
        width: 36px !important;
        height: 36px !important;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.06) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #94a3b8 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.95rem !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
    }
    .btn-wp-close:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        transform: rotate(90deg) scale(1.05) !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
    }

    /* Ultra-Premium Primary Button */
    .btn-wp-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0099e5 50%, #00c0ff 100%) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 0.88rem !important;
        letter-spacing: 0.3px !important;
        border: none !important;
        border-radius: 14px !important;
        padding: 0.75rem 1.6rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.6rem !important;
        cursor: pointer !important;
        box-shadow: 0 4px 20px rgba(0, 160, 210, 0.45) !important;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
    }
    .btn-wp-primary:hover:not(:disabled) {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 30px rgba(0, 192, 255, 0.6) !important;
        filter: brightness(1.1) !important;
        color: #ffffff !important;
    }
    .btn-wp-primary:active:not(:disabled) {
        transform: translateY(0) scale(0.98) !important;
    }
    .btn-wp-primary:disabled {
        opacity: 0.55 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        transform: none !important;
    }

    /* Secondary Cancel/Close Button */
    .btn-wp-secondary {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #e2e8f0 !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        border-radius: 14px !important;
        padding: 0.75rem 1.5rem !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 0.5rem !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-wp-secondary:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
        transform: translateY(-1px) !important;
    }

    /* Pill Action Buttons (Test / Delete) */
    .btn-wp-pill-test {
        background: rgba(0, 160, 210, 0.12) !important;
        border: 1px solid rgba(0, 160, 210, 0.35) !important;
        color: #00d2ff !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        border-radius: 10px !important;
        padding: 0.45rem 1rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-wp-pill-test:hover {
        background: rgba(0, 160, 210, 0.25) !important;
        border-color: #00d2ff !important;
        color: #ffffff !important;
        box-shadow: 0 0 15px rgba(0, 210, 255, 0.3) !important;
        transform: translateY(-1px) !important;
    }
    .btn-wp-pill-delete {
        background: rgba(244, 63, 94, 0.1) !important;
        border: 1px solid rgba(244, 63, 94, 0.25) !important;
        color: #fb7185 !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        border-radius: 10px !important;
        padding: 0.45rem 1rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }
    .btn-wp-pill-delete:hover {
        background: rgba(244, 63, 94, 0.25) !important;
        border-color: #fb7185 !important;
        color: #ffffff !important;
        box-shadow: 0 0 15px rgba(244, 63, 94, 0.3) !important;
        transform: translateY(-1px) !important;
    }

    /* Header WordPress Button */
    .btn-wp-header {
        background: linear-gradient(135deg, rgba(0, 115, 170, 0.25), rgba(0, 160, 210, 0.12)) !important;
        border: 1px solid rgba(0, 160, 210, 0.45) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border-radius: 14px !important;
        padding: 0.55rem 1.1rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        cursor: pointer !important;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
        box-shadow: 0 4px 15px rgba(0, 160, 210, 0.2) !important;
    }
    .btn-wp-header:hover {
        background: linear-gradient(135deg, rgba(0, 115, 170, 0.45), rgba(0, 160, 210, 0.25)) !important;
        border-color: #00d2ff !important;
        box-shadow: 0 6px 25px rgba(0, 160, 210, 0.4) !important;
        transform: translateY(-2px) !important;
    }
    .wp-icon-badge {
        width: 34px !important;
        height: 34px !important;
        border-radius: 10px !important;
        background: rgba(0, 160, 210, 0.25) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #00d2ff !important;
        font-size: 1.15rem !important;
    }

    /* Result Action Bar WP Send Button */
    .btn-wp-action {
        background: linear-gradient(135deg, #0073aa 0%, #0099e5 100%) !important;
        border: 1px solid rgba(0, 160, 210, 0.5) !important;
        color: #ffffff !important;
        padding: 0.55rem 1.2rem !important;
        border-radius: 12px !important;
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 7px !important;
        box-shadow: 0 4px 15px rgba(0, 115, 170, 0.35) !important;
    }
    .btn-wp-action:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 22px rgba(0, 160, 210, 0.55) !important;
        color: #ffffff !important;
        border-color: #00d2ff !important;
    }

    /* Add Tag Button */
    .btn-wp-add-tag {
        background: linear-gradient(135deg, rgba(0, 160, 210, 0.25), rgba(0, 115, 170, 0.35)) !important;
        border: 1px solid rgba(0, 160, 210, 0.5) !important;
        color: #00d2ff !important;
        padding: 0.55rem 1.15rem !important;
        border-radius: 10px !important;
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        cursor: pointer !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
    }
    .btn-wp-add-tag:hover {
        background: linear-gradient(135deg, #0073aa, #00a0d2) !important;
        color: #fff !important;
        box-shadow: 0 2px 10px rgba(0, 160, 210, 0.4) !important;
    }

    /* Quick Send Button in Sidebar */
    .wp-quick-send-btn {
        background: rgba(0, 115, 170, 0.2) !important;
        border: 1px solid rgba(0, 160, 210, 0.4) !important;
        color: #00d2ff !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 2px 8px rgba(0, 115, 170, 0.2) !important;
        opacity: 0.7 !important;
    }
    .wp-quick-send-btn:hover {
        background: linear-gradient(135deg, #0073aa, #00a0d2) !important;
        border-color: #00d2ff !important;
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(0, 160, 210, 0.6) !important;
        opacity: 1 !important;
        transform: translateY(-50%) scale(1.1) !important;
    }

    /* Modal Inputs Strict Scoping */
    .wp-modal-dialog .form-control,
    .wp-modal-dialog input[type="text"],
    .wp-modal-dialog input[type="url"],
    .wp-modal-dialog input[type="password"],
    .wp-modal-dialog textarea,
    .wp-modal-dialog .aw-select {
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease !important;
    }
    .wp-modal-dialog .form-control:focus,
    .wp-modal-dialog input:focus,
    .wp-modal-dialog textarea:focus,
    .wp-modal-dialog .aw-select:focus {
        border-color: #00a0d2 !important;
        box-shadow: 0 0 15px rgba(0, 160, 210, 0.25) !important;
        background: rgba(255, 255, 255, 0.07) !important;
    }

    .wp-password-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .wp-password-wrap input {
        padding-inline-end: 2.8rem !important;
    }
    .wp-password-toggle {
        position: absolute;
        inset-inline-end: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent !important;
        border: none !important;
        color: #94a3b8 !important;
        cursor: pointer !important;
        padding: 6px !important;
        font-size: 1rem !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .wp-password-toggle:hover {
        color: #00d2ff !important;
    }

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

    /* ===== PERSONA MASTERY GUIDE GRID ===== */
    .persona-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
    }
    @media (max-width: 1200px) {
        .persona-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .persona-grid {
            grid-template-columns: 1fr;
        }
    }
    .persona-card {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 86px;
        position: relative;
        text-align: inherit;
    }
    .persona-card:hover {
        background: rgba(15, 23, 42, 0.95);
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.5);
    }
    .persona-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .persona-card-title {
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .persona-card-desc {
        font-size: 0.72rem;
        color: var(--aw-text-dim);
        line-height: 1.35;
        font-weight: 500;
    }
    .persona-card-hint {
        font-size: 0.64rem;
        color: rgba(255, 255, 255, 0.38);
        margin-top: 4px;
        font-weight: 500;
        line-height: 1.3;
    }

    /* ===== LENGTH SELECTOR ===== */
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
    .article-render-container h2 { font-weight: 800; color: #fff; margin-top: 3rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.45rem; }
    .article-render-container h2::before { content: ''; width: 4px; height: 24px; background: var(--aw-cyan); border-radius: 4px; flex-shrink: 0; }
    .article-render-container h3 { font-weight: 700; color: var(--aw-cyan); margin-top: 1.75rem; margin-bottom: 0.75rem; font-size: 1.15rem; }
    .article-render-container p { margin-bottom: 1.35rem; font-size: 1.05rem; opacity: 0.92; }
    .article-render-container ul, .article-render-container ol { margin-bottom: 2rem; padding-inline-start: 1.5rem; }
    .article-render-container li { margin-bottom: 0.75rem; line-height: 1.75; }
    .article-render-container strong { color: var(--aw-cyan); font-weight: 800; }
    .article-render-container blockquote { border-left: 4px solid var(--aw-cyan); padding: 1.25rem 1.75rem; background: var(--aw-cyan-10); border-radius: 0 16px 16px 0; margin: 2rem 0; font-style: italic; }

    /* Quick Summary */
    .article-render-container .quick-summary {
        background: linear-gradient(135deg, rgba(0, 210, 255, 0.08), rgba(0, 210, 255, 0.02));
        border: 1px solid var(--aw-cyan-20);
        border-left: 4px solid var(--aw-cyan);
        padding: 1.75rem 2rem;
        border-radius: 16px;
        margin-bottom: 2.5rem;
        position: relative;
    }
    .article-render-container .quick-summary::before {
        content: '⚡ ' attr(data-label);
        display: block;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 1.5px;
        color: var(--aw-cyan);
        text-transform: uppercase;
        margin-bottom: 0.75rem;
    }
    [dir="ltr"] .article-render-container .quick-summary::before { content: '⚡ EXECUTIVE SUMMARY'; }
    [dir="rtl"] .article-render-container .quick-summary::before { content: '⚡ الموجز التنفيذي السريع'; }

    /* Key Takeaways */
    .article-render-container .key-takeaways {
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.06) 0%, rgba(16, 185, 129, 0.02) 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 18px;
        padding: 1.75rem 2rem;
        margin: 2.5rem 0;
    }
    .article-render-container .key-takeaways h2 {
        color: #10b981;
        font-size: 1.25rem;
        margin-top: 0;
        margin-bottom: 1.25rem;
    }
    .article-render-container .key-takeaways h2::before { background: #10b981; }
    .article-render-container .key-takeaways ul { list-style: none; padding: 0; margin: 0; }
    .article-render-container .key-takeaways li {
        position: relative;
        padding-inline-start: 1.75rem;
        margin-bottom: 0.85rem;
    }
    .article-render-container .key-takeaways li::before {
        content: '✓';
        position: absolute;
        inset-inline-start: 0;
        top: 0;
        color: #10b981;
        font-weight: 900;
    }
    .article-render-container .key-takeaways strong { color: #34d399; }

    /* FAQ Section */
    .article-render-container .faq-section {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--aw-border);
        border-radius: 18px;
        padding: 2rem;
        margin: 3rem 0;
    }
    .article-render-container .faq-section h2 { margin-top: 0; }
    .article-render-container .faq-section h3 {
        background: rgba(0, 210, 255, 0.06);
        border: 1px solid var(--aw-cyan-20);
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        font-size: 1.05rem;
        color: #fff;
        margin-top: 1.5rem;
    }
    .article-render-container .faq-section p {
        padding-inline-start: 1rem;
        margin-top: 0.75rem;
        margin-bottom: 1.5rem;
    }

    /* Internal Links Suggestions */
    .article-render-container .internal-links-suggestions {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.06), rgba(129, 140, 248, 0.02));
        border: 1px solid rgba(129, 140, 248, 0.2);
        border-radius: 18px;
        padding: 1.75rem 2rem;
        margin: 2.5rem 0;
    }
    .article-render-container .internal-links-suggestions h2 {
        color: #818cf8;
        font-size: 1.25rem;
        margin-top: 0;
    }
    .article-render-container .internal-links-suggestions h2::before { background: #818cf8; }
    .article-render-container .internal-links-suggestions strong { color: #a5b4fc; }

    /* Clean HTML Tables */
    .article-render-container table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 2rem 0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid var(--aw-border);
        background: rgba(0, 0, 0, 0.25);
    }
    .article-render-container th {
        background: rgba(0, 210, 255, 0.12);
        color: var(--aw-cyan);
        font-weight: 800;
        padding: 1rem 1.25rem;
        text-align: start;
        border-bottom: 1px solid var(--aw-border);
        font-size: 0.92rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .article-render-container td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        font-size: 0.95rem;
        color: #e5e7eb;
    }
    .article-render-container tr:last-child td { border-bottom: none; }
    .article-render-container tr:hover td { background: rgba(255, 255, 255, 0.03); }

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
        cmsConnections: @json($cmsConnections ?? []),
        currentArticle: null,

        // WordPress Integration State
        isWpPublishOpen: false,
        isWpSettingsOpen: false,
        wpPublishLoading: false,
        wpCategoriesLoading: false,
        wpCategories: [],
        wpPublishSuccessData: null,
        wpPublishForm: {
            article_id: null,
            connection_id: '',
            title: '',
            excerpt: '',
            tags: [],
            newTagInput: '',
            category_id: '',
            status: 'draft',
            slug: ''
        },
        wpConnectForm: {
            name: '',
            site_url: '',
            username: '',
            api_key: '',
            verify_ssl: true,
            showPassword: false,
            isLoading: false
        },
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
                persona_guide_hint: 'Click any niche preset to auto-apply optimal tone & audience',
                news_trends: 'NEWS, TRENDS & SPORTS',
                news_trends_desc: 'Informative + General Audience',
                news_trends_hint: 'Live breaking news, match fixtures & Google Discover trends',
                gold_markets: 'GOLD, COMMODITIES & PRICES',
                gold_markets_desc: 'Professional + General Audience',
                gold_markets_hint: 'Live price tables, bullion workmanship & market trends',
                ecommerce_store: 'WOOCOMMERCE & STORES',
                ecommerce_store_desc: 'Marketing & Sales + Shoppers',
                ecommerce_store_hint: 'High-converting product descriptions & store campaigns',
                product_reviews: 'REVIEWS & COMPARISONS',
                product_reviews_desc: 'Creative & Engaging + Shoppers',
                product_reviews_hint: 'Specs tables, real-world pros/cons & buying verdicts',
                tech_dev: 'TECH, SAAS & DEVELOPERS',
                tech_dev_desc: 'Professional + Developers & Tech',
                tech_dev_hint: 'Code tutorials, software architecture & AI tools',
                business_b2b: 'BUSINESS & STARTUPS',
                business_b2b_desc: 'Professional + Industry Experts',
                business_b2b_hint: 'B2B frameworks, executive insights & market analysis',
                edu_guides: 'STEP-BY-STEP & HOW-TO',
                edu_guides_desc: 'Informative + Beginners',
                edu_guides_hint: 'Actionable numbered steps, checklists & FAQs',
                health_medical: 'HEALTH & WELLNESS',
                health_medical_desc: 'Authoritative Expert + General Audience',
                health_medical_hint: 'Evidence-based advice, nutrition & E-E-A-T health guides',
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
                btn_wp_send: 'Send to WordPress',
                wp_connected_sites: 'WordPress Sites',
                wp_modal_title: 'Send Article to WordPress (Draft)',
                wp_modal_subtitle: 'Creates a draft on your site for review & editing',
                wp_select_site: 'Target WordPress Site',
                wp_no_sites: 'No WordPress sites connected yet.',
                wp_connect_now: 'Connect Your WordPress Site',
                wp_article_title: 'Article Title',
                wp_article_excerpt: 'Excerpt / Meta Description',
                wp_tags_label: 'Suggested SEO Tags',
                wp_tags_placeholder: 'Type a tag and press Enter...',
                wp_category_label: 'WordPress Category',
                wp_category_default: 'Default (Uncategorized)',
                wp_status_label: 'Post Status',
                wp_btn_submit: 'Send Draft to WordPress',
                wp_btn_submitting: 'Pushing to WordPress...',
                wp_success_title: 'Draft Saved to WordPress Successfully!',
                wp_open_draft: 'Open Draft in WordPress',
                wp_settings_title: 'WordPress Integrations',
                wp_settings_subtitle: 'Connect and manage your WordPress websites via Application Passwords.',
                wp_add_site_title: 'Connect New WordPress Site',
                wp_site_name: 'Site Name',
                wp_site_name_ph: 'e.g. My Tech Blog',
                wp_site_url: 'WordPress Site URL',
                wp_site_url_ph: 'https://example.com',
                wp_username: 'WordPress Username',
                wp_username_ph: 'e.g. admin or editor',
                wp_app_password: 'Application Password',
                wp_app_password_ph: 'xxxx xxxx xxxx xxxx',
                wp_btn_test_save: 'Test & Save Connection',
                wp_btn_testing: 'Verifying Connection...',
                wp_guide_title: 'How to generate an Application Password in 3 steps:',
                wp_guide_step1: '1. Log in to your WordPress admin > Users > Profile.',
                wp_guide_step2: '2. Scroll down to the Application Passwords section.',
                wp_guide_step3: '3. Enter an application name (e.g. VidaNexus) and click "Add New Application Password", then paste the generated key below.',
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
                persona_guide_hint: 'اضغط على أي تخصص لتطبيق النبرة والجمهور المثالي فوراً',
                news_trends: 'الأخبار والترند والرياضة',
                news_trends_desc: 'إخباري وتثقيفي + الجمهور العام',
                news_trends_hint: 'تغطيات حية، مباريات، وترند جوجل ديسكفر',
                gold_markets: 'الذهب والأسعار والأسواق',
                gold_markets_desc: 'احترافي وموثوق + الجمهور العام',
                gold_markets_hint: 'جداول أسعار لحظية، سبائك، وتحركات السوق',
                ecommerce_store: 'متاجر ووكمرس والمنتجات',
                ecommerce_store_desc: 'تسويقي وترويجي + المتسوقين',
                ecommerce_store_hint: 'وصف المنتجات، عروض التخفيضات، وزيادة المبيعات',
                product_reviews: 'المراجعات ومقارنة الأجهزة',
                product_reviews_desc: 'إبداعي وشيق + المهتمين بالشراء',
                product_reviews_hint: 'جداول مواصفات، مميزات وعيوب، وترشيحات',
                tech_dev: 'التقنية والبرمجة والسوفتوير',
                tech_dev_desc: 'احترافي وموثوق + المطورين والتقنيين',
                tech_dev_hint: 'شروحات تقنية، أدوات SaaS، والذكاء الاصطناعي',
                business_b2b: 'الشركات وبيزنس B2B',
                business_b2b_desc: 'احترافي وموثوق + المتخصصين والخبراء',
                business_b2b_hint: 'استراتيجيات الأعمال، القيادة، والشركات الناشئة',
                edu_guides: 'الشروحات والأدلة (How-To)',
                edu_guides_desc: 'إخباري وتثقيفي + المبتدئين والباحثين',
                edu_guides_hint: 'خطوات مرتبة رقمياً، أدلة استخدام، وإرشادات',
                health_medical: 'الصحة والطب والتغذية',
                health_medical_desc: 'خبير متخصص + الجمهور العام',
                health_medical_hint: 'معلومات طبية موثوقة، لياقة وتغذية بمعايير E-E-A-T',
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
                btn_wp_send: 'إرسال إلى ووردبريس',
                wp_connected_sites: 'مواقع ووردبريس',
                wp_modal_title: 'إرسال المقال إلى ووردبريس (مسودة)',
                wp_modal_subtitle: 'سيتم إنشاء مسودة في موقعك للمراجعة والتعديل',
                wp_select_site: 'الموقع المستهدف',
                wp_no_sites: 'لم تقم بربط أي موقع ووردبريس بعد.',
                wp_connect_now: 'ربط موقعك الأول الآن',
                wp_article_title: 'عنوان المقال',
                wp_article_excerpt: 'المقتطف / الوصف التعريفي',
                wp_tags_label: 'الوسوم المقترحة للسيو',
                wp_tags_placeholder: 'اكتب وسماً ثم اضغط Enter للإضافة...',
                wp_category_label: 'التصنيف في ووردبريس',
                wp_category_default: 'التصنيف الافتراضي (عام)',
                wp_status_label: 'حالة المنشور',
                wp_btn_submit: 'إرسال المسودة إلى ووردبريس',
                wp_btn_submitting: 'جارٍ الإرسال إلى ووردبريس...',
                wp_success_title: 'تم حفظ المقال بنجاح في ووردبريس!',
                wp_open_draft: 'فتح المسودة في ووردبريس',
                wp_settings_title: 'إدارة مواقع ووردبريس',
                wp_settings_subtitle: 'ربط المواقع عبر كلمات مرور التطبيقات (Application Passwords)',
                wp_add_site_title: 'ربط موقع ووردبريس جديد',
                wp_site_name: 'اسم الموقع',
                wp_site_name_ph: 'مثال: مدونة التقنية',
                wp_site_url: 'رابط الموقع (URL)',
                wp_site_url_ph: 'https://example.com',
                wp_username: 'اسم المستخدم في ووردبريس',
                wp_username_ph: 'مثال: admin أو editor',
                wp_app_password: 'كلمة مرور التطبيق',
                wp_app_password_ph: 'xxxx xxxx xxxx xxxx',
                wp_btn_test_save: 'فحص وحفظ الاتصال',
                wp_btn_testing: 'جارٍ التحقق من الاتصال...',
                wp_guide_title: 'كيفية استخراج كلمة مرور التطبيق في 3 خطوات:',
                wp_guide_step1: '1. ادخل للوحة تحكم ووردبريس لموقعك > الأعضاء > حسابك الشخصي (Users > Profile).',
                wp_guide_step2: '2. انزل لأسفل الصفحة إلى قسم كلمات مرور التطبيقات (Application Passwords).',
                wp_guide_step3: '3. اكتب اسماً للتطبيق مثل "VidaNexus" واضغط "إضافة كلمة مرور تطبيق جديدة"، ثم انسخ الكلمة الناتجة والصقها أدناه.',
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

            // Initialize active WordPress connection
            if (this.cmsConnections && this.cmsConnections.length > 0) {
                this.wpPublishForm.connection_id = this.cmsConnections[0].id;
                this.loadWpCategories(this.cmsConnections[0].id);
            }
        },

        getCurrentCreditCost() {
            const wc = this.form.word_count || 1500;
            if (this.settings.credit_costs && this.settings.credit_costs[wc] !== undefined) {
                return this.settings.credit_costs[wc];
            }
            return this.settings.credit_cost || 5;
        },

        getTierCreditCost(words) {
            if (this.settings.credit_costs && this.settings.credit_costs[words] !== undefined) {
                return this.settings.credit_costs[words];
            }
            return this.settings.credit_cost || 5;
        },

        getLiveWordCount() {
            if (!this.currentArticle || !this.currentArticle.content) return 0;
            const clean = this.currentArticle.content.replace(/<[^>]*>/g, ' ').replace(/&[a-z0-9#]+;/gi, ' ').trim();
            if (!clean) return 0;
            const matches = clean.match(/[\p{L}\p{N}]+/gu);
            return matches ? matches.length : 0;
        },

        getLiveCharCount() {
            if (!this.currentArticle || !this.currentArticle.content) return 0;
            const clean = this.currentArticle.content.replace(/<[^>]*>/g, ' ').replace(/&[a-z0-9#]+;/gi, ' ').trim();
            return clean.length;
        },

        getLiveReadingTime() {
            const words = this.getLiveWordCount();
            return Math.max(1, Math.ceil(words / 200));
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

                    const newBal = (typeof data.balance !== 'undefined') ? data.balance : data.credits_balance;
                    if (window.VidaCredits) {
                        if (typeof newBal !== 'undefined' && newBal !== null) {
                            window.VidaCredits.updateAll(newBal);
                        } else {
                            window.VidaCredits.refresh();
                        }
                    }
                    if (typeof newBal !== 'undefined' && newBal !== null) {
                        window.dispatchEvent(new CustomEvent('credits:update', { detail: { balance: newBal } }));
                        document.dispatchEvent(new CustomEvent('credits:update', { detail: { balance: newBal } }));
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
        },

        /* ============================================================
         * WORDPRESS & CMS INTEGRATION METHODS
         * ============================================================ */
        async openWpPublishModal(targetItem = null) {
            let art = targetItem || this.currentArticle;
            if (!art) {
                this.showError(this.form.language === 'ar' ? 'يرجى اختيار مقال أولاً.' : 'Please select an article first.');
                return;
            }

            // If targetItem is from history summary and content is missing or short, fetch full article
            if (!art.content && art.id) {
                this.isProcessing = true;
                try {
                    const res = await fetch(`{{ url('dashboard/article-writer') }}/${art.id}`);
                    art = await res.json();
                    this.currentArticle = art;
                } catch (e) {
                    this.showError(this.form.language === 'ar' ? 'فشل تحميل المقال' : 'Failed to load article');
                    this.isProcessing = false;
                    return;
                } finally {
                    this.isProcessing = false;
                }
            }

            // Populate Form
            this.wpPublishForm.article_id = art.id;
            this.wpPublishForm.title = art.title || art.topic || '';
            this.wpPublishForm.excerpt = art.meta_description || '';
            this.wpPublishForm.content = art.content || '';
            this.wpPublishForm.status = 'draft';
            this.wpPublishForm.category_id = '';
            this.wpPublishForm.newTagInput = '';

            const seo = art.seo_data || {};
            this.wpPublishForm.slug = seo.slug_ar || seo.slug_en || '';
            this.wpPublishForm.tags = (seo.tags && Array.isArray(seo.tags) && seo.tags.length > 0)
                ? [...seo.tags]
                : this.extractSuggestedTags(art);

            if (this.cmsConnections.length > 0) {
                if (!this.wpPublishForm.connection_id || !this.cmsConnections.find(c => c.id == this.wpPublishForm.connection_id)) {
                    this.wpPublishForm.connection_id = this.cmsConnections[0].id;
                }
                this.loadWpCategories(this.wpPublishForm.connection_id);
            }

            this.wpPublishSuccessData = null;
            this.isWpPublishOpen = true;
        },

        closeWpPublishModal() {
            this.isWpPublishOpen = false;
        },

        openWpSettingsModal() {
            this.isWpSettingsOpen = true;
            this.wpConnectForm = {
                name: '',
                site_url: '',
                username: '',
                api_key: '',
                verify_ssl: true,
                showPassword: false,
                isLoading: false,
            };
        },

        closeWpSettingsModal() {
            this.isWpSettingsOpen = false;
        },

        extractSuggestedTags(art) {
            if (!art) return [];
            if (art.seo_data && art.seo_data.tags && Array.isArray(art.seo_data.tags) && art.seo_data.tags.length > 0) {
                return art.seo_data.tags;
            }
            const seo = art.seo_data || {};
            const seeds = [seo.focus_keyword, art.topic, art.title].filter(Boolean);
            const tags = [];
            seeds.forEach(s => {
                const words = s.split(/[,،\-–—\|]/u);
                words.forEach(w => {
                    const clean = w.trim();
                    if (clean.length >= 3 && !tags.includes(clean)) {
                        tags.push(clean);
                    }
                });
            });
            return tags.slice(0, 8);
        },

        addWpTag() {
            const val = (this.wpPublishForm.newTagInput || '').trim();
            if (!val) return;
            const pieces = val.split(/[,،\n]/u);
            pieces.forEach(p => {
                const t = p.trim();
                if (t.length > 0 && !this.wpPublishForm.tags.includes(t)) {
                    this.wpPublishForm.tags.push(t);
                }
            });
            this.wpPublishForm.newTagInput = '';
        },

        removeWpTag(index) {
            this.wpPublishForm.tags.splice(index, 1);
        },

        async loadWpCategories(connectionId) {
            if (!connectionId) {
                this.wpCategories = [];
                return;
            }
            this.wpCategoriesLoading = true;
            this.wpCategories = [];
            try {
                const res = await fetch(`{{ url('dashboard/article-writer/cms/connections') }}/${connectionId}/categories`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.status === 'success') {
                    this.wpCategories = data.categories || [];
                }
            } catch (err) {
                console.warn('Failed to load categories:', err);
            } finally {
                this.wpCategoriesLoading = false;
            }
        },

        async submitWpPublish() {
            if (!this.wpPublishForm.connection_id) {
                this.showError(this.form.language === 'ar' ? 'يرجى اختيار موقع ووردبريس أولاً.' : 'Please select a WordPress site first.');
                return;
            }

            this.wpPublishLoading = true;
            try {
                const res = await fetch(`{{ url('dashboard/article-writer/articles') }}/${this.wpPublishForm.article_id}/publish-cms`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        connection_id: this.wpPublishForm.connection_id,
                        title: this.wpPublishForm.title,
                        excerpt: this.wpPublishForm.excerpt,
                        content: this.wpPublishForm.content,
                        tags: this.wpPublishForm.tags,
                        category_id: this.wpPublishForm.category_id || null,
                        status: this.wpPublishForm.status || 'draft',
                        slug: this.wpPublishForm.slug || null,
                    })
                });

                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    this.wpPublishSuccessData = data;

                    // Update article in currentArticle and history
                    const syncData = {
                        platform: 'wordpress',
                        connection_id: this.wpPublishForm.connection_id,
                        connection_name: (this.cmsConnections.find(c => c.id == this.wpPublishForm.connection_id)?.name) || 'WordPress',
                        post_id: data.post_id,
                        post_url: data.post_url,
                        edit_url: data.edit_url,
                        synced_at: new Date().toISOString()
                    };

                    if (this.currentArticle && this.currentArticle.id == this.wpPublishForm.article_id) {
                        this.currentArticle.seo_data = this.currentArticle.seo_data || {};
                        this.currentArticle.seo_data.cms_sync = syncData;
                        this.currentArticle.seo_data.tags = [...this.wpPublishForm.tags];
                    }

                    const histItem = this.history.find(h => h.id == this.wpPublishForm.article_id);
                    if (histItem) {
                        histItem.seo_data = histItem.seo_data || {};
                        histItem.seo_data.cms_sync = syncData;
                        histItem.seo_data.tags = [...this.wpPublishForm.tags];
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || (this.form.language === 'ar' ? 'تم حفظ المسودة في ووردبريس بنجاح!' : 'Draft saved to WordPress!'),
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#17181c',
                        color: '#fff'
                    });
                } else {
                    this.showError(data.message || (this.form.language === 'ar' ? 'فشل إرسال المقال إلى ووردبريس' : 'Failed to publish to WordPress'));
                }
            } catch (err) {
                this.showError(this.form.language === 'ar' ? 'حدث خطأ في الاتصال بالخادم' : 'Server connection error: ' + err.message);
            } finally {
                this.wpPublishLoading = false;
            }
        },

        async submitAddConnection() {
            if (!this.wpConnectForm.site_url || !this.wpConnectForm.username || !this.wpConnectForm.api_key) {
                this.showError(this.form.language === 'ar' ? 'جميع الحقول (رابط الموقع، اسم المستخدم، كلمة مرور التطبيق) مطلوبة.' : 'Site URL, username, and application password are all required.');
                return;
            }

            this.wpConnectForm.isLoading = true;
            try {
                const res = await fetch(`{{ url('dashboard/article-writer/cms/connections') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.wpConnectForm.name,
                        site_url: this.wpConnectForm.site_url,
                        username: this.wpConnectForm.username,
                        api_key: this.wpConnectForm.api_key,
                        platform: 'wordpress',
                        verify_ssl: this.wpConnectForm.verify_ssl,
                    })
                });

                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    this.cmsConnections.unshift(data.connection);
                    if (!this.wpPublishForm.connection_id) {
                        this.wpPublishForm.connection_id = data.connection.id;
                        this.loadWpCategories(data.connection.id);
                    }

                    // Reset form
                    this.wpConnectForm.name = '';
                    this.wpConnectForm.site_url = '';
                    this.wpConnectForm.username = '';
                    this.wpConnectForm.api_key = '';

                    Swal.fire({
                        icon: 'success',
                        title: this.form.language === 'ar' ? 'تم الربط بنجاح!' : 'Connected Successfully!',
                        text: data.message,
                        background: '#17181c',
                        color: '#fff',
                        confirmButtonColor: '#0ea5e9'
                    });
                } else {
                    this.showError(data.message || (this.form.language === 'ar' ? 'فشل فحص الاتصال بموقع ووردبريس' : 'Connection test failed'));
                }
            } catch (err) {
                this.showError(this.form.language === 'ar' ? 'حدث خطأ في الاتصال' : 'Connection error: ' + err.message);
            } finally {
                this.wpConnectForm.isLoading = false;
            }
        },

        async testConnection(connectionId) {
            Swal.fire({
                title: this.form.language === 'ar' ? 'جارٍ فحص الاتصال...' : 'Testing connection...',
                text: this.form.language === 'ar' ? 'يتم الاتصال بـ WordPress REST API' : 'Connecting to WordPress REST API',
                background: '#17181c',
                color: '#fff',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`{{ url('dashboard/article-writer/cms/connections/test') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ connection_id: connectionId })
                });

                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: this.form.language === 'ar' ? 'الاتصال نشط وسليم!' : 'Connection Active!',
                        text: data.message,
                        background: '#17181c',
                        color: '#fff',
                        confirmButtonColor: '#0ea5e9'
                    });
                } else {
                    this.showError(data.message || (this.form.language === 'ar' ? 'فشل فحص الاتصال' : 'Connection check failed'));
                }
            } catch (err) {
                this.showError(err.message);
            }
        },

        async deleteConnection(connectionId) {
            const isAr = this.form.language === 'ar';
            const result = await Swal.fire({
                title: isAr ? 'حذف ربط هذا الموقع؟' : 'Remove this connection?',
                text: isAr ? 'لن تتمكن من إرسال المقالات إلى هذا الموقع حتى تقوم بربطه مجدداً.' : 'You will not be able to publish articles to this site until you reconnect it.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4b4b',
                cancelButtonColor: '#2b2d35',
                confirmButtonText: isAr ? 'نعم، احذف' : 'Yes, Delete',
                cancelButtonText: isAr ? 'إلغاء' : 'Cancel',
                background: '#17181c',
                color: '#fff'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch(`{{ url('dashboard/article-writer/cms/connections') }}/${connectionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        this.cmsConnections = this.cmsConnections.filter(c => c.id !== connectionId);
                        if (this.wpPublishForm.connection_id == connectionId) {
                            this.wpPublishForm.connection_id = this.cmsConnections.length > 0 ? this.cmsConnections[0].id : '';
                            if (this.wpPublishForm.connection_id) {
                                this.loadWpCategories(this.wpPublishForm.connection_id);
                            }
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#17181c',
                            color: '#fff'
                        });
                    }
                } catch (err) {
                    this.showError(isAr ? 'فشل حذف الموقع' : 'Failed to delete site');
                }
            }
        }
    }
}
</script>
@endpush
@endsection
