        <main>
            <div class="tool-hero">
                <i class="fas {{ $tool['icon'] }} tool-icon-large"></i>
                <h1 class="tool-title">{{ $tool['name'] }}</h1>
                <p class="tool-tagline">{{ $tool['tagline'] }}</p>
                <div class="tool-description">
                    {!! $tool['marketing_content'] ?? nl2br(e($tool['description'])) !!}
                </div>
            </div>

            <div class="features-grid">
                @foreach($tool['features'] as $feature)
                <div class="feature-item">
                    <i class="fas {{ $feature['icon'] }} feature-icon"></i>
                    <h3 class="feature-title">{{ $feature['title'] }}</h3>
                    <p class="feature-desc">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>

            <div class="cta-section">
                @auth
                    @if($isOwned)
                        @php
                            $targetUrl = isset($tool['route']) ? route($tool['route']) : '/dashboard';
                        @endphp
                        <a href="{{ $targetUrl }}" class="vn-btn vn-btn-primary tool-cta-btn">
                            <i class="fas fa-play"></i>
                            <span>Start Using {{ $tool['name'] }}</span>
                        </a>
                    @elseif($isAvailable)
                        <a href="/payment?type=tool&id={{ $tool['slug'] }}" class="vn-btn tool-cta-btn tool-buy-btn">
                            <i class="fas fa-unlock-alt"></i>
                            <span>Get Full Access for {{ number_format($tool['unlock_price']) }} EGP</span>
                        </a>
                    @else
                        <div class="coming-soon-btn tool-cta-btn">
                            <i class="fas fa-lock" style="font-size: 2rem; color: var(--text-muted); opacity: 0.5;"></i>
                            <div class="coming-soon-text">
                                <span class="coming-soon-title">Module Locked</span>
                                <span class="coming-soon-sub">In Active Development</span>
                            </div>
                        </div>
                    @endif
                @else
                    <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="vn-btn vn-btn-primary tool-cta-btn">
                        <span>Login to Access</span>
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                @endauth
            </div>
        </main>
