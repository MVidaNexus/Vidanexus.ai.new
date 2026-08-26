<style>
    .app-footer, .app-footer * {
        direction: ltr !important;
        text-align: left !important;
        unicode-bidi: isolate !important;
    }
</style>
<footer class="app-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col brand-col">
                <a href="/" style="text-decoration: none;">
                    <div class="logo-container" style="margin-bottom: 1.5rem;">
                        <img src="{{ asset('assets/brand-logo.png?v=2026_2') }}" alt="VidaNexus" class="logo-img" style="height: 60px; width: auto; object-fit: contain;">
                    </div>
                </a>
                <p class="footer-bio">
                    VidaNexus is a high-performance, modular AI ecosystem designed for long-term scalability. We are building the backbone of next-generation intelligent automation.
                </p>
            </div>
            
            <div class="footer-col">
                <h4>Platform</h4>
                <ul class="footer-links">
                    <li><a href="/">Home</a></li>
                    <li><a href="/pricing">Pricing Plans</a></li>
                    <li><a href="{{ auth()->check() ? '/dashboard' : '/login' }}">My Dashboard</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Resources</h4>
                <ul class="footer-links">
                    <li><a href="/help-center">Help Center</a></li>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/refund">Refund Policy</a></li>
                    <li><a href="/shipping">Shipping Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Contact & Operations</h4>
                <ul class="footer-contact">
                    <li>
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <a href="mailto:info@vidanexus.net" style="color: inherit; text-decoration: none;">info@vidanexus.net</a>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <a href="tel:+201019944589" style="color: inherit; text-decoration: none;">+20 1019944589</a>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <span>VidaNexus Hub — Egypt</span>
                    </li>
                    <li>
                        <div class="contact-icon"><i class="fas fa-headset"></i></div>
                        <a href="mailto:technical@vidanexus.net" style="color: inherit; text-decoration: none;">technical@vidanexus.net</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <a href="https://vidanexus.net/" style="color: inherit; text-decoration: none;">Vida Nexus</a> AI. All rights reserved.</p>
            <div class="footer-social">
                <a href="https://www.facebook.com/VidaNexus" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <a href="mailto:info@vidanexus.net" aria-label="Email"><i class="fas fa-envelope"></i></a>
                <a href="https://www.linkedin.com/company/vida-nexus/" aria-label="LinkedIn" target="_blank" rel="noopener noreferrer"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
</footer>

{{-- Floating Action Buttons (Back to Top Only) --}}
<div id="floating-actions" style="
    position: fixed;
    bottom: 5rem;
    right: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    z-index: 9999;
">
    {{-- Back to Top --}}
    <button id="btn-scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top" style="
        width: 46px; height: 46px;
        border-radius: 50%;
        border: 1px solid var(--glass-border, rgba(0, 168, 230,0.25));
        background: var(--card-bg, rgba(10,15,30,0.85));
        backdrop-filter: blur(12px);
        color: var(--primary-cyan, #00A8E6);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.35s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 4px 20px rgba(0,0,0,0.3), 0 0 15px rgba(0, 168, 230,0.08);
        opacity: 0;
        transform: translateY(20px) scale(0.8);
        pointer-events: none;
    ">
        <i class="fas fa-chevron-up" style="font-size: 1rem;"></i>
    </button>
</div>

<style>
    #btn-scroll-top:hover {
        background: rgba(0, 168, 230,0.15) !important;
        border-color: rgba(0, 168, 230,0.5) !important;
        box-shadow: 0 4px 25px rgba(0, 168, 230,0.2), 0 0 20px rgba(0, 168, 230,0.15) !important;
    }
    #btn-scroll-top.visible {
        opacity: 1 !important;
        transform: translateY(0) scale(1) !important;
        pointer-events: auto !important;
    }
    @media (max-width: 640px) {
        #floating-actions { bottom: 5.2rem !important; right: 1rem !important; }
        #btn-scroll-top { width: 40px !important; height: 40px !important; }
    }
</style>

<script>
(function(){
    const btn = document.getElementById('btn-scroll-top');
    if (!btn) return;
    let ticking = false;
    window.addEventListener('scroll', function(){
        if (!ticking) {
            window.requestAnimationFrame(function(){
                if (window.scrollY > 400) {
                    btn.classList.add('visible');
                } else {
                    btn.classList.remove('visible');
                }
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
})();
</script>
{!! App\Models\Setting::get('footer_script') !!}
