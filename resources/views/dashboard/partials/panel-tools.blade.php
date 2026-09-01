                <div class="content-panel" id="subscriptions" style="display: none;">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-box-open"></i> Tool Access Hub</h2>
                        <a href="#billing" class="dash-nav-link" style="color: var(--primary-cyan); text-decoration: none; font-size: 0.9rem; font-weight: 500;">Purchase Credits <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                    </div>
                    
                    <div class="my-tools-grid">
                        @foreach($tools as $tool)
                            <div class="dash-tool-card premium-tool-card {{ $tool['accessible'] ? 'unlocked' : '' }} {{ !$tool['is_available'] ? 'is-coming-soon' : '' }}">
                                @if(!$tool['is_available'])
                                    <div class="coming-soon-overlay">
                                        <div class="coming-soon-badge-glow">
                                            <div class="cs-icon"><i class="fas fa-lock"></i></div>
                                            <div class="cs-title">COMING SOON</div>
                                            <div class="cs-subtitle">In Active Development</div>
                                        </div>
                                    </div>
                                @endif

                                <div class="tool-card-content" style="display: flex; flex-direction: column; height: 100%;">
                                    @if($tool['accessible'])
                                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #00A58B, #34d399); box-shadow: 0 0 10px #00A58B;"></div>
                                        <div class="tool-status status-active" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #00A58B; box-shadow: 0 0 10px rgba(16,185,129,0.2);"><i class="fas fa-unlock" style="margin-right: 4px;"></i> Unlocked</div>
                                    @elseif(!$tool['is_available'])
                                        <div class="tool-status" style="background: rgba(0, 168, 230, 0.1); color: var(--primary-cyan); border: 1px solid rgba(0, 168, 230, 0.2);">
                                            <i class="fas fa-clock" style="margin-right: 4px;"></i> Coming Soon
                                        </div>
                                    @else
                                        <div class="tool-status status-locked premium-locked-badge"><i class="fas fa-lock" style="margin-right: 4px;"></i> Marketplace</div>
                                    @endif
                                    
                                    <div class="dash-tool-header">
                                        <div class="dash-tool-icon premium-tool-icon" style="color: {{ $tool['color'] }}; text-shadow: 0 0 15px {{ $tool['color'] }}66;">
                                            <i class="fas {{ $tool['icon'] }}"></i>
                                        </div>
                                        <h3 class="dash-tool-title" style="font-size: 1.15rem;">{{ $tool['name'] }}</h3>
                                    </div>
                                    
                                    <p class="dash-tool-desc" style="flex-grow: 1;">{{ $tool['description'] }}</p>
                                    
                                    @if($tool['accessible'])
                                        <div class="premium-action-cost" style="margin-bottom: 1.5rem; padding: 0.5rem 0.75rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                            <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Action Cost</span>
                                            <span style="font-size: 0.85rem; color: var(--primary-cyan); font-weight: 700;">{{ $tool['credit_cost'] ?? $tool['credit_cost_per_action'] ?? 1 }} Credits</span>
                                        </div>
                                    @endif
                                    
                                    @if($tool['accessible'])
                                        @if($tool['slug'] === 'discover-headlines')
                                            <a href="{{ route('headlines.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'seo-analyzer')
                                            <a href="{{ route('dashboard.seo-analyzer.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'drama-trends')
                                            <a href="{{ route('dashboard.drama-trends.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'ai-keyword-radar')
                                            <a href="{{ route('dashboard.ai-keyword-radar.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'global-news-monitor')
                                            <a href="{{ route('dashboard.global-news-monitor.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'trending-search-monitor')
                                            <a href="{{ route('dashboard.trending-searches.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'seo-auditor')
                                            <a href="{{ route('dashboard.seo-auditor.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'audit-x')
                                            <a href="{{ route('dashboard.audit-x.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'folio-ocr')
                                            <a href="{{ route('dashboard.folio-ocr.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'img-compress')
                                            <a href="{{ route('dashboard.img-compress.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'web-to-app')
                                            <a href="{{ route('dashboard.web-to-app.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @elseif($tool['slug'] === 'money-printer')
                                            <a href="{{ route('dashboard.money-printer.index') }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @else
                                            <a href="/tools/{{ $tool['slug'] }}" class="vn-btn vn-btn-primary dash-action-btn" style="box-shadow: 0 5px 15px rgba(0, 168, 230,0.3);">Launch Tool <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
                                        @endif
                                    @elseif(!$tool['is_available'])
                                        <button class="vn-btn vn-btn-outline dash-action-btn" style="cursor: not-allowed; opacity: 0.6; background: var(--card-bg); border-color: var(--glass-border);" disabled>
                                            <i class="fas fa-clock mr-2"></i> In Development
                                        </button>
                                    @else
                                        <a href="/payment?type=tool&id={{ $tool['slug'] }}" class="vn-btn premium-unlock-btn">
                                            <i class="fas fa-unlock"></i>
                                            <span>Unlock Access — {{ number_format($tool['unlock_price']) }} EGP</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
