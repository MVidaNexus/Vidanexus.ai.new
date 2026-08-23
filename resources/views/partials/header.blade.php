@if(session('impersonate_admin_id'))
    <div style="background: linear-gradient(90deg, var(--accent), var(--primary-cyan)); color: #fff; padding: 0.6rem; text-align: center; font-size: 0.9rem; font-weight: 600; position: fixed; top: 0; left: 0; right: 0; z-index: 1001; display: flex; justify-content: center; align-items: center; gap: 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
        <span><i class="fas fa-user-secret"></i> You are currently viewing as <strong>{{ auth()->user()->name }}</strong></span>
        <a href="{{ route('admin.stop-impersonating') }}" style="background: #fff; color: #000; padding: 0.3rem 0.8rem; border-radius: 6px; text-decoration: none; font-size: 0.8rem; transition: all 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            Return to Admin
        </a>
    </div>
@endif

<!-- Reusable Platform Header (Exact Match with Home Page) -->
<header id="site-header" dir="ltr" style="position: fixed; top: {{ session('impersonate_admin_id') ? '40px' : '0' }}; left: 0; right: 0; z-index: 1000; display: flex; justify-content: space-between; align-items: center; padding: 1rem 5%; width: 100%; box-sizing: border-box; backdrop-filter: blur(15px); background: var(--header-bg); border-bottom: 1px solid var(--header-border); transition: top 0.3s;">
    <style>
        @media (max-width: 768px) {
            #site-header { padding: 1rem 1rem !important; min-height: 65px; }
            .logo-text { font-size: 1.1rem !important; }
            .logo-img { height: 28px !important; }
        }
    </style>
    <a href="/" style="text-decoration: none;" class="flex items-center">
        <div class="logo-container">
            <img src="{{ asset('assets/brand-logo.png?v=2026') }}" alt="VidaNexus" class="logo-img" style="height: 38px; width: auto; object-fit: contain;">
        </div>
    </a>

    <nav class="desktop-nav" style="display: flex; gap: 2rem; align-items: center; margin: 0 auto;">
        <a href="/" style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
        <a href="/#tools" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Tools</a>
        <a href="/pricing" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Pricing</a>
    </nav>

    <div class="desktop-nav" style="display: flex; gap: 1.5rem; align-items: center;">
        <label class="theme-switch-dribbble">
            <input type="checkbox" checked onchange="handleThemeChange(event)">
            <div class="dribbble-slider">
                <div class="star star-1"></div>
                <div class="star star-2"></div>
                <div class="star star-3"></div>
                <div class="cloud cloud-1"></div>
                <div class="cloud cloud-2"></div>
                <div class="crater crater-1"></div>
                <div class="crater crater-2"></div>
                <div class="crater crater-3"></div>
            </div>
        </label>

        @guest
            <a href="/login" style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 600; padding: 0.6rem 1.2rem; border-radius: 8px; border: 1px solid var(--glass-border); transition: all 0.3s;" onmouseover="this.style.background='var(--primary-cyan)'; this.style.color='#000'; this.style.borderColor='var(--primary-cyan)'" onmouseout="this.style.background='none'; this.style.color='var(--text-main)'; this.style.borderColor='var(--glass-border)'">Login</a>
            <a href="/register" class="notify-btn" style="padding: 0.6rem 1.2rem; text-decoration: none;">
                <span>Get Started</span>
            </a>
        @endguest

        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.horizon.index') }}" style="color: var(--primary-cyan); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;" onmouseover="this.style.textShadow='0 0 10px var(--primary-cyan)'" onmouseout="this.style.textShadow='none'">
                    <i class="fas fa-user-shield"></i>
                    <span>Admin Control</span>
                </a>
            @endif
            
            <a href="/dashboard" style="color: var(--text-main); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; text-shadow: 0 0 10px var(--title-glow);">
                <i class="fas fa-layer-group"></i>
                <span>My Dashboard</span>
            </a>

            <div class="feature-chip" style="background: rgba(0, 168, 230, 0.05); border-color: var(--primary-cyan); margin: 0; padding: 0.5rem 1rem;">
                <i class="fas fa-wallet" style="color: var(--primary-cyan);"></i>
                <span style="font-weight: 700;"
                      class="js-credit-balance"
                      data-credit-value="{{ (float) (auth()->user()->wallet->balance_credits ?? 0) }}"
                      data-decimals="2"
                      data-suffix=" Credits">{{ number_format(auth()->user()->wallet->balance_credits ?? 0, 2) }} Credits</span>
            </div>

            <form action="/logout" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ff4b4b; cursor: pointer; font-size: 1.1rem;" title="Sign Out">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        @endauth
    </div>
    
    <button class="hamburger" aria-label="Toggle Navigation">
        <i class="fas fa-bars"></i>
    </button>
</header>

<!-- Global Session Alerts -->
@if(session('error') || session('success') || session('status'))
<div id="global-alert" style="position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 2000; width: 90%; max-width: 600px; animation: slideDown 0.5s ease-out;">
    <div style="backdrop-filter: blur(15px); background: {{ session('error') ? 'rgba(255, 75, 75, 0.15)' : 'rgba(0, 168, 230, 0.15)' }}; border: 1px solid {{ session('error') ? '#ff4b4b44' : 'var(--primary-cyan)44' }}; border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <i class="fas {{ session('error') ? 'fa-exclamation-circle' : 'fa-check-circle' }}" style="color: {{ session('error') ? '#ff4b4b' : 'var(--primary-cyan)' }}; font-size: 1.2rem;"></i>
        <span style="color: var(--text-main); font-size: 0.95rem; font-weight: 500;">{{ session('error') ?? session('success') ?? session('status') }}</span>
        <button onclick="document.getElementById('global-alert').remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; margin-left: auto;">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<style>
@keyframes slideDown {
    from { transform: translate(-50%, -20px); opacity: 0; }
    to { transform: translate(-50%, 0); opacity: 1; }
}
</style>
<script>
    setTimeout(() => {
        const alert = document.getElementById('global-alert');
        if (alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 5000);
</script>
@endif



<!-- Mobile Sidebar Navigation -->
<div class="mobile-overlay" id="mobileOverlay"></div>
<div class="mobile-sidebar" id="mobileSidebar" dir="ltr">
    <a href="/">
        <div class="logo-container" style="padding-bottom: 1.5rem; border-bottom: 1px solid var(--header-border);">
            <img src="{{ asset('assets/brand-logo.png?v=2026') }}" alt="VidaNexus" class="logo-img" style="height: 36px; width: auto; object-fit: contain;">
        </div>
    </a>
    <a href="/"><i class="fas fa-home" style="width: 25px;"></i> Home</a>
    <a href="/#tools"><i class="fas fa-layer-group" style="width: 25px;"></i> Tools</a>
    <a href="/pricing"><i class="fas fa-tags" style="width: 25px;"></i> Pricing</a>
    
    <label class="theme-switch-dribbble" style="margin: 1rem 0;">
        <input type="checkbox" checked onchange="handleThemeChange(event)">
        <div class="dribbble-slider">
            <div class="star star-1"></div>
            <div class="star star-2"></div>
            <div class="star star-3"></div>
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="crater crater-1"></div>
            <div class="crater crater-2"></div>
            <div class="crater crater-3"></div>
        </div>
    </label>

    @guest
        <a href="/login" style="margin-top: 2rem;"><i class="fas fa-sign-in-alt" style="width: 25px;"></i> Login</a>
        <a href="/register" style="color: var(--primary-cyan); border: 1px solid var(--primary-cyan);"><i class="fas fa-user-plus" style="width: 25px;"></i> Get Started</a>
    @endguest

    @auth
        <meta name="credits-balance-url" content="{{ route('dashboard.credits.balance') }}">
        <div style="border-top: 1px solid var(--glass-border); padding-top: 1.5rem; margin-top: 1.5rem;">
            <div style="padding: 0 1rem 0.5rem; color: var(--text-muted); font-size: 0.9rem;">My Dashboard</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.horizon.index') }}" style="color: var(--primary-cyan);"><i class="fas fa-user-shield" style="width: 25px;"></i> Admin Control</a>
            @endif
            <a href="/dashboard" style="color: var(--text-main); text-decoration: none;"><i class="fas fa-desktop" style="width: 25px;"></i> Dashboard</a>
            <div style="padding: 1rem; color: var(--primary-cyan); font-weight: bold; font-family: var(--font-heading);"><i class="fas fa-wallet" style="width: 25px;"></i> <span class="js-credit-balance" data-credit-value="{{ (float) (auth()->user()->wallet->balance_credits ?? 0) }}" data-decimals="2" data-suffix=" Credits">{{ number_format(auth()->user()->wallet->balance_credits ?? 0, 2) }} Credits</span></div>
            
            <form action="/logout" method="POST" style="margin: 0; padding: 0; width: 100%;">
                @csrf
                <button type="submit" style="color: #ff4b4b; display: flex; align-items: center; gap: 0.5rem; background: none; border: none; font-size: 1.2rem; cursor: pointer; padding: 1rem; text-align: left; width: 100%;">
                    <i class="fas fa-sign-out-alt" style="width: 25px;"></i> Sign Out
                </button>
            </form>
        </div>
    @endauth
</div>

@auth
    <style>
        .js-credit-balance { transition: color 0.35s ease, text-shadow 0.35s ease; }
        .credit-flash-down { color: #ff4b4b !important; text-shadow: 0 0 12px rgba(255,75,75,0.6); }
        .credit-flash-up   { color: #10b981 !important; text-shadow: 0 0 12px rgba(16,185,129,0.6); }
    </style>
    <script src="{{ asset('credits-live.js') }}?v=2" defer></script>
@endauth
