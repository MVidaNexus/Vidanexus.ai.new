@php
    $defaultPackages = [
        'lite' => [
            'name' => 'Lite Dash',
            'credits' => '100',
            'price' => '35',
            'desc' => 'Perfect for testing a single tool.',
            'icon' => 'fa-seedling',
            'color' => '#00ffaa'
        ],
        'standard' => [
            'name' => 'Creator Pack',
            'credits' => '500',
            'price' => '150',
            'desc' => 'Best for social media managers.',
            'icon' => 'fa-rocket',
            'color' => 'var(--primary-cyan)',
            'popular' => true
        ],
        'pro' => [
            'name' => 'Agency Pro',
            'credits' => '2,500',
            'price' => '650',
            'desc' => 'High-volume SEO & Content.',
            'icon' => 'fa-bolt-lightning',
            'color' => 'var(--neon-purple)'
        ],
        'enterprise' => [
            'name' => 'Power Node',
            'credits' => '10,000',
            'price' => '2,250',
            'desc' => 'Infrastructure level usage.',
            'icon' => 'fa-crown',
            'color' => '#ffcc00'
        ]
    ];
    $savedPackagesJson = \App\Models\Setting::get('marketplace_packages');
    $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);
@endphp


<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; width: 100%;">
    @foreach($packages as $id => $pkg)
        <div class="pricing-card {{ !empty($pkg['popular']) ? 'popular-card' : '' }}" style="
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid {{ !empty($pkg['popular']) ? 'var(--primary-cyan)' : 'var(--glass-border)' }};
            border-radius: 24px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: {{ !empty($pkg['popular']) ? '0 20px 40px rgba(14, 165, 233, 0.15)' : 'var(--pricing-card-shadow)' }};
        ">
            @if(!empty($pkg['popular']))
                <div style="position: absolute; top: 1.2rem; right: -2.5rem; background: linear-gradient(135deg, var(--primary-cyan), #0066ff); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 6px 40px; transform: rotate(45deg); letter-spacing: 1px; box-shadow: 0 5px 15px rgba(0, 102, 255, 0.3);">
                    BEST VALUE
                </div>
            @endif

            <div style="width: 65px; height: 65px; border-radius: 20px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: {{ $pkg['color'] }}; font-size: 1.8rem; box-shadow: inset 0 0 15px rgba(255,255,255,0.05);">
                <i class="fas {{ $pkg['icon'] }}"></i>
            </div>

            <h3 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--text-main); margin-bottom: 0.5rem;">{{ $pkg['name'] }}</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.5rem; height: 3rem;">{{ $pkg['desc'] }}</p>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1.5rem 1rem; margin-bottom: 2rem;">
                <div style="font-family: 'Space+Grotesk', sans-serif; font-size: 2.5rem; font-weight: 800; color: var(--text-main); line-height: 1;">
                    {{ $pkg['credits'] }}
                    <span style="font-size: 1rem; color: var(--primary-cyan); font-weight: 600; display: block; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">Credits</span>
                </div>
            </div>

            <div style="margin-bottom: 2rem; position: relative;">
                @php
                    $discount = isset($pkg['discount']) && is_numeric($pkg['discount']) ? (float)$pkg['discount'] : 0;
                    $basePrice = (float)str_replace(',', '', $pkg['price']);
                    $finalPrice = $discount > 0 ? $basePrice - ($basePrice * ($discount / 100)) : $basePrice;
                @endphp
                
                @if($discount > 0)
                    <!-- Discount Ribbon -->
                    <div style="position: absolute; top: -140px; left: -3rem; background: #ef4444; color: #fff; font-size: 0.7rem; font-weight: 800; padding: 6px 40px; transform: rotate(-45deg); letter-spacing: 1px; box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3); z-index: 10; pointer-events: none;">
                        SAVE {{ rtrim(rtrim(number_format($discount, 1), '0'), '.') }}%
                    </div>
                    
                    <div style="font-size: 0.95rem; color: var(--text-muted); text-decoration: line-through; margin-bottom: 0.1rem; font-weight: 600; opacity: 0.7;">
                        {{ number_format($basePrice) }} EGP
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--horizon-success);">
                        {{ number_format($finalPrice) }} <span style="font-size: 0.9rem; font-weight: 400; color: var(--text-muted);">EGP</span>
                    </div>
                @else
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--text-main);">
                        {{ number_format($basePrice) }} <span style="font-size: 0.9rem; font-weight: 400; color: var(--text-muted);">EGP</span>
                    </div>
                @endif
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">One-time purchase • No expiry</div>
            </div>

            <a href="/payment?type=package&id={{ $id }}" class="vn-btn {{ !empty($pkg['popular']) ? 'vn-btn-primary' : 'vn-btn-outline' }}" style="width: 100%; padding: 1.1rem; border-radius: 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; transition: all 0.3s ease;">
                <span>Purchase Pack</span>
                <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
            </a>
            
            <div style="margin-top: 1.5rem; font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 0.5rem; opacity: 0.6;">
                <i class="fas fa-shield-alt"></i>
                Secure Encryption
            </div>
        </div>
    @endforeach
</div>

    <!-- Corporate / Custom Package (Horizontal Banner) -->
    <div class="pricing-card corporate-banner" style="
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 2.5rem 1.5rem;
        margin-top: 3rem;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--pricing-card-shadow);
        position: relative;
        overflow: hidden;
        width: 100%;
        max-width: 100% !important;
    ">
        <div class="corporate-accent-bar" style="position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: linear-gradient(180deg, var(--neon-purple), var(--primary-cyan)); opacity: 0.8;"></div>

        <!-- Left Content: Icon & Text -->
        <div class="corporate-left" style="display: flex; align-items: center; gap: 1.5rem; width: 35%; padding-left: 1rem;">
            <div style="width: 70px; height: 70px; flex-shrink: 0; border-radius: 20px; background: var(--feature-item-bg); border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; color: var(--text-main); font-size: 2rem;">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--text-main); margin-bottom: 0.3rem;">Corporate & Large Scale</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.4; margin: 0;">Need massive credit volumes?<br>Get a tailored enterprise discount.</p>
            </div>
        </div>

        <!-- Middle Content: Stats -->
        <div class="corporate-middle" style="display: flex; align-items: center; gap: 3rem; justify-content: center; width: 40%; border-left: 1px dashed var(--glass-border); border-right: 1px dashed var(--glass-border); padding: 0 1.5rem;">
            <div style="text-align: center;">
                <div style="font-family: 'Space+Grotesk', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1;">
                    Custom
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; display: block; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px;">Volume</span>
                </div>
            </div>
            <div style="text-align: center;">
                <div style="font-family: 'Space+Grotesk', sans-serif; font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1;">
                    Let's Talk
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; display: block; margin-top: 6px; text-transform: uppercase; letter-spacing: 1px;">Pricing</span>
                </div>
            </div>
        </div>

        <!-- Right Content: Buttons -->
        <div class="corporate-right" style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem; width: 25%;">
            <div style="display: flex; flex-direction: column; gap: 0.5rem; width: 100%; max-width: 200px;">
                <a href="mailto:info@vidanexus.com" class="vn-btn vn-btn-outline" style="width: 100%; padding: 0.9rem 1.1rem; border-radius: 14px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; transition: all 0.3s ease; white-space: nowrap;">
                    <span>Email Sales</span>
                    <i class="fas fa-envelope" style="font-size: 0.9rem;"></i>
                </a>
                <a href="https://wa.me/201019944589" target="_blank" rel="noopener noreferrer" class="vn-btn" style="width: 100%; padding: 0.9rem 1.1rem; border-radius: 14px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; transition: all 0.3s ease; white-space: nowrap; background: #25D366; color: white; border: none; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.2);">
                    <span>WhatsApp</span>
                    <i class="fab fa-whatsapp" style="font-size: 1.1rem;"></i>
                </a>
            </div>
            
            <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                <i class="fas fa-handshake"></i>
                Dedicated Support
            </div>
        </div>
    </div>
    <style>
        @media(max-width: 900px) {
            .corporate-banner {
                flex-direction: column !important;
                text-align: center !important;
                gap: 2rem !important;
                padding: 2.5rem 1rem !important;
            }
            .corporate-accent-bar {
                top: 0 !important; left: 0 !important; width: 100% !important; height: 6px !important;
            }
            .corporate-left, .corporate-middle, .corporate-right {
                width: 100% !important;
                border: none !important;
                justify-content: center !important;
                text-align: center !important;
                padding: 0 !important;
            }
            .corporate-left {
                flex-direction: column !important;
                gap: 1rem !important;
            }
        }
    </style>
</div>
