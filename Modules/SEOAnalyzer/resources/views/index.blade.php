@extends('seoanalyzer::layouts.master')

@section('content')
<div class="container-fluid py-4 font-tajawal" dir="ltr">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">SEO & Traffic Analyzer</h2>
            <p class="text-muted">Analyze your headlines and content to achieve top performance in Google Discover</p>
        </div>
    </div>

    @include('partials.tool-usage-badge', ['slug' => 'seo-analyzer'])

    <div class="row g-4" x-data="seoAnalyzer()">
        <!-- Headline Analysis -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-heading text-primary me-2"></i>Headline Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Enter headline for review</label>
                        <textarea 
                            class="form-control border-light-subtle rounded-3" 
                            rows="3" 
                            placeholder="e.g: The Future of Crypto in 2026: A Comprehensive Guide"
                            x-model="headline"
                            @input.debounce.500ms="analyzeHeadline()"
                        ></textarea>
                    </div>

                    <div x-show="headlineAnalysis" x-transition>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="score-ring me-3" :class="getScoreColor(headlineAnalysis.score)">
                                    <span x-text="headlineAnalysis.score"></span>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5" x-text="'Grade: ' + headlineAnalysis.grade"></div>
                                    <div class="text-muted small" x-text="mb_strlen(headline) + ' characters'"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Results Lists -->
                        <div class="mb-3" x-show="headlineAnalysis.strengths.length">
                            <h6 class="fw-bold text-success small mb-2">Strengths</h6>
                            <ul class="list-unstyled mb-0">
                                <template x-for="s in headlineAnalysis.strengths">
                                    <li class="small mb-1 text-success" x-text="s"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="mb-3" x-show="headlineAnalysis.issues.length">
                            <h6 class="fw-bold text-danger small mb-2">Alerts</h6>
                            <ul class="list-unstyled mb-0">
                                <template x-for="i in headlineAnalysis.issues">
                                    <li class="small mb-1 text-danger" x-text="i"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="mb-0" x-show="headlineAnalysis.suggestions.length">
                            <h6 class="fw-bold text-primary small mb-2">Suggestions for Improvement</h6>
                            <template x-for="sg in headlineAnalysis.suggestions">
                                <div class="alert alert-primary bg-opacity-10 border-0 rounded-3 p-2 small mb-2" x-text="sg"></div>
                            </template>
                        </div>
                    </div>
                    
                    <div x-show="!headlineAnalysis && headline" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Analysis -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt text-success me-2"></i>Content Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Target Keyword</label>
                        <input type="text" class="form-control border-light-subtle rounded-3" placeholder="Optional..." x-model="keyword">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Article Content</label>
                        <textarea 
                            class="form-control border-light-subtle rounded-3" 
                            rows="8" 
                            placeholder="Paste article content here for analysis..."
                            x-model="content"
                            @input.debounce.800ms="analyzeContent()"
                        ></textarea>
                    </div>

                    <div x-show="contentAnalysis" x-transition>
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 text-center">
                                    <div class="text-muted small mb-1">Score</div>
                                    <div class="fw-bold h4 mb-0" :class="getScoreTextColor(contentAnalysis.score)" x-text="contentAnalysis.score"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 text-center">
                                    <div class="text-muted small mb-1">Words</div>
                                    <div class="fw-bold h4 mb-0" x-text="contentAnalysis.word_count"></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 text-center">
                                    <div class="text-muted small mb-1">Density</div>
                                    <div class="fw-bold h4 mb-0 text-primary" x-text="contentAnalysis.keyword_density + '%'"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" x-show="contentAnalysis.strengths.length">
                            <h6 class="fw-bold text-success small mb-2">Strengths</h6>
                            <ul class="list-unstyled mb-0">
                                <template x-for="s in contentAnalysis.strengths">
                                    <li class="small mb-1 text-success" x-text="s"></li>
                                </template>
                            </ul>
                        </div>

                        <div class="mb-0" x-show="contentAnalysis.issues.length">
                            <h6 class="fw-bold text-danger small mb-2">Alerts and Suggestions</h6>
                            <ul class="list-unstyled mb-0">
                                <template x-for="i in contentAnalysis.issues">
                                    <li class="small mb-1 text-danger" x-text="i"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .score-ring {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        border: 4px solid transparent;
    }
    .score-high { border-color: #10b981; color: #10b981; background: #ecfdf5; }
    .score-mid { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }
    .score-low { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
</style>

<script>
    function seoAnalyzer() {
        return {
            headline: '',
            keyword: '',
            content: '',
            headlineAnalysis: null,
            contentAnalysis: null,

            mb_strlen(str) {
                return str.length;
            },

            getScoreColor(score) {
                if (score >= 80) return 'score-high';
                if (score >= 50) return 'score-mid';
                return 'score-low';
            },

            getScoreTextColor(score) {
                if (score >= 80) return 'text-success';
                if (score >= 50) return 'text-warning';
                return 'text-danger';
            },

            analyzeHeadline() {
                if (!this.headline) {
                    this.headlineAnalysis = null;
                    return;
                }
                
                fetch('/dashboard/seo-analyzer/analyze-headline', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ headline: this.headline })
                })
                .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
                .then(({ ok, data }) => {
                    if (!ok) {
                        if (data.message && data.message.includes('Insufficient balance')) {
                            showInsufficientBalanceAlert(data.message);
                        }
                        return;
                    }
                    this.headlineAnalysis = data;
                    if (window.VidaCredits) window.VidaCredits.apply(data);
                });
            },

            analyzeContent() {
                if (!this.content) {
                    this.contentAnalysis = null;
                    return;
                }

                fetch('/dashboard/seo-analyzer/analyze-content', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content: this.content, keyword: this.keyword })
                })
                .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
                .then(({ ok, data }) => {
                    if (!ok) {
                        if (data.message && data.message.includes('Insufficient balance')) {
                            showInsufficientBalanceAlert(data.message);
                        }
                        return;
                    }
                    this.contentAnalysis = data;
                    if (window.VidaCredits) window.VidaCredits.apply(data);
                });
            }
        }
    }
</script>
@endsection
