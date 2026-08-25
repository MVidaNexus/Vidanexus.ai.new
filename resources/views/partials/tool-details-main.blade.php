        <main>
            <div class="tool-hero" id="toolHero">
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

            <div class="cta-section" id="bottomCtaSection">
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

        {{-- Sticky Floating Tool CTA Bar (Docks until bottom is reached) --}}
        <div id="sticky-tool-bar" class="sticky-tool-bar">
            <div class="sticky-tool-info">
                <div class="sticky-tool-icon" style="color: {{ $tool['color'] }}; background: {{ $tool['color'] }}15; border-color: {{ $tool['color'] }}30;">
                    <i class="fas {{ $tool['icon'] }}"></i>
                </div>
                <div class="sticky-tool-texts">
                    <span class="sticky-tool-name">{{ $tool['name'] }}</span>
                    <span class="sticky-tool-tagline">{{ Str::limit($tool['tagline'], 45) }}</span>
                </div>
            </div>

            <div class="sticky-tool-action">
                @auth
                    @if($isOwned)
                        <a href="{{ $targetUrl ?? '/dashboard' }}" class="vn-btn vn-btn-primary sticky-btn">
                            <i class="fas fa-play"></i>
                            <span>Start Using</span>
                        </a>
                    @elseif($isAvailable)
                        <a href="/payment?type=tool&id={{ $tool['slug'] }}" class="vn-btn tool-buy-btn sticky-btn">
                            <i class="fas fa-unlock-alt"></i>
                            <span>Get Access</span>
                        </a>
                    @else
                        <div class="sticky-locked-badge">
                            <i class="fas fa-lock"></i> Locked
                        </div>
                    @endif
                @else
                    <a href="/login?redirect={{ urlencode(request()->fullUrl()) }}" class="vn-btn vn-btn-primary sticky-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                @endauth
            </div>
        </div>

        <script>
        (function() {
            const stickyBar = document.getElementById('sticky-tool-bar');
            const bottomCta = document.getElementById('bottomCtaSection');
            if (!stickyBar || !bottomCta) return;

            function updateStickyVisibility() {
                const scrollY = window.scrollY || window.pageYOffset;
                const bottomCtaRect = bottomCta.getBoundingClientRect();
                const windowHeight = window.innerHeight;

                // Show after scrolling 250px down, hide when bottom CTA is in view
                const isScrolledPastHero = scrollY > 250;
                const isBottomCtaVisible = bottomCtaRect.top < (windowHeight - 80);

                if (isScrolledPastHero && !isBottomCtaVisible) {
                    stickyBar.classList.add('visible');
                } else {
                    stickyBar.classList.remove('visible');
                }
            }

            window.addEventListener('scroll', updateStickyVisibility, { passive: true });
            window.addEventListener('resize', updateStickyVisibility, { passive: true });
            updateStickyVisibility();
        })();
        </script>
