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
                    <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 900; letter-spacing: -0.03em; color: #fff;">Pro AI Article Writer</h1>
                    <p class="mb-0" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">SEO-optimized content engine with E-E-A-T compliance</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="header-stat">
                    <div class="header-stat-label">ENGINE</div>
                    <div class="header-stat-value" style="color: #10b981;">AI POWERED</div>
                </div>
                <div class="header-stat">
                    <div class="header-stat-label">OUTPUT</div>
                    <div class="header-stat-value">HTML + SEO</div>
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
                    <span class="aw-sidebar-title">Saved Content</span>
                    <i class="fas fa-history" style="color: var(--aw-text-dim); font-size: 0.85rem;"></i>
                </div>
                <div class="custom-scrollbar" style="max-height: 700px; overflow-y: auto;">
                    <template x-for="item in history" :key="item.id">
                        <div class="history-item-container" style="position: relative; group;">
                            <div @click="loadArticle(item.id)" 
                                 class="history-item"
                                 :class="currentArticle && currentArticle.id == item.id ? 'history-item-active' : ''"
                                 style="padding-right: 3rem;">
                                <div style="font-size: 0.7rem; color: var(--aw-text-dim); margin-bottom: 4px; display: flex; justify-content: space-between;">
                                    <span x-text="formatDate(item.created_at)"></span>
                                    <span style="font-size: 0.6rem; font-weight: 800; color: var(--aw-cyan); text-transform: uppercase;" x-text="item.language"></span>
                                </div>
                                <div style="font-weight: 800; font-size: 0.85rem; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="item.title || item.topic"></div>
                                <div style="font-size: 0.65rem; color: var(--aw-text-dim); margin-top: 4px; display: flex; gap: 0.5rem;">
                                    <span x-text="item.word_count + ' words'"></span>
                                    <span>•</span>
                                    <span x-text="item.model"></span>
                                </div>
                            </div>
                            <!-- Delete Button -->
                            <button @click.stop="deleteArticle(item.id)" 
                                    class="delete-btn"
                                    title="Delete Article"
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: rgba(255,75,75,0.1); border: 1px solid rgba(255,75,75,0.2); color: #ff4b4b; width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; opacity: 0.4;">
                                <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="history.length === 0" style="padding: 3rem 1.5rem; text-align: center; color: var(--aw-text-dim); font-size: 0.85rem; font-style: italic;">
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
                            <i class="fas fa-search"></i> Primary Keyword or Topic
                        </div>
                        <div class="aw-search-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" x-model="form.keyword" 
                                   class="form-control"
                                   placeholder="e.g., Best AI Tools for Digital Marketing in 2026..."
                                   style="padding-left: 3rem;">
                        </div>
                    </div>

                    <!-- Language Selection (Full Width) -->
                    <div style="margin-bottom: 1.5rem;">
                        <div class="aw-label">
                            <i class="fas fa-globe"></i> Article Language
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
                                <i class="fas fa-palette"></i> Editorial Tone
                            </div>
                            <select x-model="form.tone" class="aw-select">
                                <template x-for="tone in settings.tones">
                                    <option :value="tone.value" x-text="tone.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="aw-label">
                                <i class="fas fa-users"></i> Target Audience
                            </div>
                            <select x-model="form.audience" class="aw-select">
                                <template x-for="audience in settings.audiences">
                                    <option :value="audience.value" x-text="audience.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Persona Mastery Guide (New) -->
                    <div style="margin-bottom: 2rem; padding: 1.25rem; background: var(--aw-cyan-10); border: 1px solid var(--aw-cyan-20); border-radius: 16px; position: relative; overflow: hidden;">
                        <div style="position: absolute; right: -10px; top: -10px; font-size: 4rem; color: var(--aw-cyan-10); opacity: 0.3; transform: rotate(-15deg);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                            <i class="fas fa-magic" style="color: var(--aw-cyan);"></i>
                            <h4 style="margin: 0; font-size: 0.85rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 1px;">Persona Mastery Guide</h4>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div style="background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                                <div style="font-size: 0.7rem; font-weight: 900; color: var(--aw-cyan); margin-bottom: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-newspaper"></i> NEWS & TRENDS
                                </div>
                                <div style="font-size: 0.75rem; color: var(--aw-text-dim);">Use <strong style="color: #fff;">Authoritative</strong> + <strong style="color: #fff;">General</strong></div>
                            </div>
                            <div style="background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #a855f7; margin-bottom: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-building"></i> REAL ESTATE
                                </div>
                                <div style="font-size: 0.75rem; color: var(--aw-text-dim);">Use <strong style="color: #fff;">Creative</strong> + <strong style="color: #fff;">Shoppers</strong></div>
                            </div>
                            <div style="background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #10b981; margin-bottom: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-briefcase"></i> BUSINESS B2B
                                </div>
                                <div style="font-size: 0.75rem; color: var(--aw-text-dim);">Use <strong style="color: #fff;">Professional</strong> + <strong style="color: #fff;">Professionals</strong></div>
                            </div>
                            <div style="background: rgba(0,0,0,0.2); padding: 0.75rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                                <div style="font-size: 0.7rem; font-weight: 900; color: #f59e0b; margin-bottom: 4px; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-lightbulb"></i> EDUCATIONAL/BLOG
                                </div>
                                <div style="font-size: 0.75rem; color: var(--aw-text-dim);">Use <strong style="color: #fff;">Informative</strong> + <strong style="color: #fff;">Beginners</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- Article Length -->
                    <div style="margin-bottom: 1.5rem;">
                        <div class="aw-label">
                            <i class="fas fa-ruler-horizontal"></i> Article Length
                        </div>
                        <div class="length-container">
                            <button @click="form.word_count = 300" type="button"
                                    class="length-card"
                                    :class="form.word_count === 300 ? 'length-card-active' : ''">
                                <div class="length-card-title">MINI</div>
                                <div class="length-card-words">~300</div>
                            </button>
                            <button @click="form.word_count = 500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 500 ? 'length-card-active' : ''">
                                <div class="length-card-title">MICRO</div>
                                <div class="length-card-words">~500</div>
                            </button>
                            <button @click="form.word_count = 800" type="button"
                                    class="length-card"
                                    :class="form.word_count === 800 ? 'length-card-active' : ''">
                                <div class="length-card-title">SHORT</div>
                                <div class="length-card-words">~800</div>
                            </button>
                            <button @click="form.word_count = 1500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 1500 ? 'length-card-active' : ''">
                                <div class="length-card-title">MEDIUM</div>
                                <div class="length-card-words">~1.5k</div>
                            </button>
                            <button @click="form.word_count = 2500" type="button"
                                    class="length-card"
                                    :class="form.word_count === 2500 ? 'length-card-active' : ''">
                                <div class="length-card-title">LONG</div>
                                <div class="length-card-words">~2.5k</div>
                            </button>
                        </div>
                    </div>

                    <!-- Output Components (Checkboxes) -->
                    <div style="margin-bottom: 2rem;">
                        <div class="aw-label">
                            <i class="fas fa-puzzle-piece"></i> Include in Article
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <template x-for="comp in settings.components" :key="comp.value">
                                <label class="comp-card"
                                       :class="form.components.includes(comp.value) ? 'comp-card-active' : ''"
                                       @click="toggleComponent(comp.value)">
                                    <input type="checkbox" 
                                           :value="comp.value" 
                                           :checked="form.components.includes(comp.value)"
                                           style="display: none;">
                                    <div class="comp-check">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <span class="comp-label" x-text="comp.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div>
                        <button @click="generate()" 
                                :disabled="isProcessing || !form.keyword"
                                class="aw-generate-btn">
                            <span x-show="!isProcessing">
                                <i class="fas fa-wand-magic-sparkles"></i> GENERATE ARTICLE
                                <span class="aw-generate-cost" x-text="settings.credit_cost + ' CRS'"></span>
                            </span>
                            <span x-show="isProcessing">
                                <i class="fas fa-circle-notch fa-spin"></i> CRAFTING CONTENT...
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
                <h2 style="font-size: 1.5rem; font-weight: 900; color: #fff; margin-bottom: 0.5rem;">Content Engine Active</h2>
                <p style="color: var(--aw-text-dim); font-style: italic; margin-bottom: 0.25rem; font-size: 0.9rem;">Simulating SERP analysis, applying E-E-A-T signals, building heading hierarchy...</p>
                <p style="color: var(--aw-text-label); font-size: 0.75rem;">This may take 30-60 seconds for comprehensive articles.</p>
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
                                    <span x-text="'Model: ' + currentArticle.model"></span>
                                    <span>•</span>
                                    <span x-text="currentArticle.word_count + ' words'"></span>
                                    <span>•</span>
                                    <span x-text="currentArticle.language.toUpperCase()"></span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                            <button @click="view = 'form'" style="background: var(--aw-surface); border: 1px solid var(--aw-border); color: #fff; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-plus" style="margin-right: 4px;"></i> New
                            </button>
                            <button @click="copyContent()" style="background: var(--aw-cyan); border: none; color: #000; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-copy" style="margin-right: 4px;"></i> Copy HTML
                            </button>
                            <button @click="copyPlainText()" style="background: var(--aw-surface); border: 1px solid var(--aw-border); color: #fff; padding: 0.5rem 1rem; border-radius: 10px; font-size: 0.78rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-align-left" style="margin-right: 4px;"></i> Copy Text
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div style="padding: 0 2rem; border-bottom: 1px solid var(--aw-border); display: flex; gap: 0.25rem; background: rgba(0,0,0,0.15);">
                        <button @click="articleTab = 'read'" class="result-tab" :class="articleTab === 'read' ? 'result-tab-active' : ''">
                            <i class="fas fa-book-reader" style="margin-right: 4px;"></i> Article View
                        </button>
                        <button @click="articleTab = 'seo'" class="result-tab" :class="articleTab === 'seo' ? 'result-tab-active' : ''">
                            <i class="fas fa-search" style="margin-right: 4px;"></i> SEO & Meta
                        </button>
                        <button @click="articleTab = 'raw'" class="result-tab" :class="articleTab === 'raw' ? 'result-tab-active' : ''">
                            <i class="fas fa-code" style="margin-right: 4px;"></i> Raw Code
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
                                    <i class="fas fa-heading"></i> SEO Title Tag
                                </div>
                                <div style="font-size: 1.15rem; font-weight: 700; color: #fff; padding: 1rem; background: rgba(0,0,0,0.25); border-radius: 12px; border: 1px solid var(--aw-border);" x-text="currentArticle.title"></div>
                                <div style="text-align: right; font-size: 0.65rem; font-weight: 700; margin-top: 0.5rem;"
                                     :style="(currentArticle.title || '').length <= 60 ? 'color: #10b981;' : 'color: #f59e0b;'"
                                     x-text="(currentArticle.title || '').length + '/60 chars'"></div>
                            </div>
                            
                            <!-- Meta Description -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 16px;">
                                <div class="aw-label" style="margin-bottom: 0.75rem;">
                                    <i class="fas fa-align-left"></i> Meta Description
                                </div>
                                <div style="font-size: 1rem; line-height: 1.6; color: var(--aw-text); padding: 1rem; background: rgba(0,0,0,0.25); border-radius: 12px; border: 1px solid var(--aw-border);" x-text="currentArticle.meta_description || 'Not generated — check Raw Code tab'"></div>
                                <div style="text-align: right; font-size: 0.65rem; font-weight: 700; margin-top: 0.5rem;"
                                     :style="(currentArticle.meta_description || '').length <= 155 ? 'color: #10b981;' : 'color: #f59e0b;'"
                                     x-text="(currentArticle.meta_description || '').length + '/155 chars'"></div>
                            </div>

                            <!-- Google Preview -->
                            <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #fff; border-radius: 16px; color: #000;">
                                <div class="aw-label" style="margin-bottom: 0.75rem; color: rgba(0,0,0,0.4);">
                                    <i class="fab fa-google" style="color: #4285f4;"></i> Google SERP Preview
                                </div>
                                <div style="font-size: 1.15rem; color: #1a0dab; font-weight: 400; margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="currentArticle.title || 'Untitled'"></div>
                                <div style="font-size: 0.8rem; color: #006621; margin-bottom: 4px;">https://yoursite.com/article/...</div>
                                <div style="font-size: 0.85rem; color: #545454; line-height: 1.5;" x-text="currentArticle.meta_description || 'No meta description available'"></div>
                            </div>

                            <!-- SEO Stats Grid -->
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Status</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #10b981;">INDEX READY</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Hierarchy</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #fff;">STRICT H1-H3</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">E-E-A-T</div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: #10b981;">VERIFIED</div>
                                </div>
                                <div style="padding: 1rem; background: var(--aw-surface); border: 1px solid var(--aw-border); border-radius: 12px; text-align: center;">
                                    <div style="font-size: 0.6rem; font-weight: 800; color: var(--aw-text-label); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Word Count</div>
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
        border-bottom: 1px solid var(--aw-border);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .history-item:hover { background: var(--aw-surface-hover); }
    .history-item-active {
        background: var(--aw-cyan-10) !important;
        border-right: 3px solid var(--aw-cyan);
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

        init() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('keyword')) {
                this.form.keyword = urlParams.get('keyword');
            } else if (urlParams.has('topic')) {
                this.form.keyword = urlParams.get('topic');
            }
            
            // Default language to first available
            if (this.settings.languages && this.settings.languages.length > 0) {
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
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Article Generated Successfully!',
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#17181c',
                        color: '#fff'
                    });
                } else {
                    this.showError(data.message || 'Generation failed. Please try again.');
                }
            } catch (err) {
                this.showError('Connection error. Please check your network and try again.');
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
                this.view = 'result';
                this.articleTab = 'read';
            } catch (err) {
                this.showError('Failed to load article');
            } finally {
                this.isProcessing = false;
            }
        },

        async deleteArticle(id) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "This article will be permanently removed from your history.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4b4b',
                cancelButtonColor: 'var(--aw-surface)',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
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
                            title: 'Article Deleted',
                            showConfirmButton: false,
                            timer: 1500,
                            background: '#17181c',
                            color: '#fff'
                        });
                    }
                } catch (err) {
                    this.showError('Finalizing delete failed');
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
                title: 'HTML Copied to Clipboard!',
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
                title: 'Plain Text Copied!',
                showConfirmButton: false,
                timer: 1500,
                background: '#17181c',
                color: '#fff'
            });
        },

        showError(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg,
                background: '#17181c',
                color: '#fff',
                confirmButtonColor: '#0ea5e9'
            });
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    }
}
</script>
@endpush
@endsection
